<?php

namespace WilokeListingTools\Framework\Payment\Stripe;


use WilokeListingTools\Framework\Payment\AbstractSuspend;
use WilokeListingTools\Framework\Payment\Billable;
use WilokeListingTools\Framework\Payment\Checkout;
use WilokeListingTools\Framework\Payment\Receipt;
use WilokeListingTools\Framework\Store\Session;
use WilokeListingTools\Models\PaymentMetaModel;
use WilokeListingTools\Models\PaymentModel;

class StripeChangePlan extends AbstractSuspend {
	use StripeConfiguration;

	protected $newPlanID;
	protected $newPaymentID;
	protected $currentPaymentID;
	protected $currentPlanID;
	protected $userID;
	protected $listingType;
	private $currentAgreementID;

	public function __construct($userID, $currentPaymentID, $newPlanID, $currentPlanID, $listingType) {
		$this->userID = $userID;
		$this->currentPaymentID = $currentPaymentID;
		$this->newPlanID = $newPlanID;
		$this->listingType = $listingType;
		$this->currentPlanID = $currentPlanID;
	}

	private function setSessions(){
		Session::setSession(wilokeListingToolsRepository()->get('payment:storePlanID'), $this->newPlanID);
		Session::setSession(wilokeListingToolsRepository()->get('payment:listingType'), $this->listingType);
		Session::setSession(wilokeListingToolsRepository()->get('payment:onChangedPlan'), 'yes');
		Session::setSession(wilokeListingToolsRepository()->get('payment:oldPlanID'), $this->currentPlanID);
	}

	private function suspendCurrentPlan(){
		$this->setPaymentID($this->currentPaymentID);
		return $this->suspend();
	}

	private function reactivate($subscriptionID, $paymentID){
		try{
			$oSubscription = \Stripe\Subscription::retrieve($subscriptionID);
			$oSubscription->coupon = NULL; // <= It's very important, We will get rid of Free Forever Coupon From this plan
			$oSubscription->save();
			PaymentModel::updatePaymentStatus('active', $paymentID);
			return true;
		}catch (\Exception $oE){
			return false;
		}
	}

	private function maybeReactivatePlan(){
		$this->newPaymentID = PaymentModel::getLastSuspendedByPlan($this->newPlanID, $this->userID);

		if ( empty($this->newPaymentID) ){
			return false;
		}

		$subscriptionID = PaymentMetaModel::get($this->newPaymentID, wilokeListingToolsRepository()->get('payment:stripeSubscriptionID'));

		if ( empty($subscriptionID) ){
			return false;
		}

		return $this->reactivate($subscriptionID, $this->newPaymentID);
	}

	public function execute(){
		$this->setApiContext();

		$isPassedSuspended = $this->suspendCurrentPlan();
		if ( !$isPassedSuspended ){
			return array(
				'success' => false,
				'msg'     => esc_html__('We could not suspend the current plan', 'wiloke-listing-tools')
			);
		}

		$isReactivated = $this->maybeReactivatePlan();

		// If we could not renew the plan, We will create new one
		if ( !$isReactivated ){
			new Billable(array(
				'gateway'     => $this->gateway,
				'planID'      => $this->newPlanID,
				'listingType' => $this->listingType
			));

			$oReceipt = new Receipt(array(
				'planID'                => $this->newPlanID,
				'userID'                => get_current_user_id(),
				'couponCode'            => '',
				'isNonRecurringPayment' => false
			));
			$oReceipt->setupPlan();

			$oPaymentMethod = new StripeRecurringPaymentMethod();

			$this->setSessions();
			$oCheckout = new Checkout();
			$aCheckAcceptPaymentStatus = $oCheckout->begin($oReceipt, $oPaymentMethod);

			if ( $aCheckAcceptPaymentStatus['status'] == 'success' ){
				return array(
					'status'    => 'success',
					//'redirectTo'=> $aCheckAcceptPaymentStatus['redirectTo'],
					'msg'       => esc_html__('Congratulations! Your plan has been updated successfully.', 'wiloke-listing-tools')
				);
			}else{
				// Reactivate the current plan
				$status = $this->reactivate($this->currentAgreementID, $this->paymentID);
				if ( !$status ){
					return array(
						'status' => 'error',
						'msg'    => esc_html__('We could not upgrade to the new plan. We changed the current plan to Suspend status. Please log into your PayPal and reactivate it manually.', 'wiloke-listing-tools')
					);
				}else{
					return array(
						'status' => 'error',
						'msg'    => esc_html__('We could not upgrade to the new plan.', 'wiloke-listing-tools')
					);
				}
			}
		}else{
			/*
			 * UserPlanController@changeUserPlan
			 */
			do_action('wiloke-listing-tools/on-changed-user-plan', array(
				'userID'        => get_current_user_id(),
				'paymentID'     => $this->newPaymentID,
				'oldPaymentID'  => $this->paymentID,
				'oldPlanID'     => $this->currentPlanID,
				'listingType'   => $this->listingType,
				'planID'        => $this->newPlanID,
				'gateway'       => 'stripe',
				'billingType'   => wilokeListingToolsRepository()->get('payment:billingTypes', true)->sub('recurring')
			));

			return array(
				'status' => 'success',
				'msg'    => sprintf(esc_html__('Congratulations! The %s has been reactivated successfully.', 'wiloke-listing-tools'), get_the_title($this->newPlanID))
			);
		}
	}
}