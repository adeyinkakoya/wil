<?php
namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Payment\Billable;
use WilokeListingTools\Framework\Payment\Checkout;
use WilokeListingTools\Framework\Payment\Stripe\StripeChangePlan;
use WilokeListingTools\Framework\Payment\Stripe\Webhook;
use WilokeListingTools\Framework\Payment\Receipt;
use WilokeListingTools\Framework\Payment\Stripe\StripeNonRecurringPaymentMethod;
use WilokeListingTools\Framework\Payment\Stripe\StripeRecurringPaymentMethod;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Framework\Store\Session;

class StripeController extends Controller {
	public $gateway = 'stripe';
	public $planID;

	public function   __construct(){
		add_action('wp_ajax_wiloke_submission_buy_plan_via_stripe', array($this, 'preparePayment'));
		add_action('wp_ajax_wiloke_change_plan_via_stripe', array($this, 'changePlan'));
		add_action('init', array($this, 'listenEvents'));
	}

	public function changePlan(){
		if ( !isset($_POST['newPlanID']) || !isset($_POST['currentPlanID']) || !isset($_POST['paymentID']) || !isset($_POST['postType']) ){
			wp_send_json_error(array(
				'msg' => esc_html__('ERROR: The new plan, current plan, post type and payment ID are required', 'wiloke-listing-tools')
			));
		}

		$userID = get_current_user_id();
		$this->middleware(['isMyPaymentSession'], array(
			'paymentID' => abs($_POST['paymentID']),
			'userID'    => $userID
		));


		$oPaymentMethod = new StripeChangePlan($userID, $_POST['paymentID'], $_POST['newPlanID'], $_POST['currentPlanID'], $_POST['postType']);
		$aStatus = $oPaymentMethod->execute();

		if ( $aStatus['status'] == 'success' ){
			wp_send_json_success($aStatus);
		}else{
			wp_send_json_error($aStatus);
		}
	}

	public function listenEvents(){
		if ( !isset($_REQUEST['wiloke-submission-listener']) || ($_REQUEST['wiloke-submission-listener'] != $this->gateway) ){
			return false;
		}

		new Webhook();
	}

	public static function setupReceiptDirectly($aData){
		$isNonRecurring = true;

		$aDefault = array(
			'userID'                => get_current_user_id(),
			'isNonRecurringPayment' => $aData['billingType'] == wilokeListingToolsRepository()->get('payment:billingTypes', true)->sub('nonrecurring'),
			'category'              => Session::getSession(wilokeListingToolsRepository()->get('payment:category'))
		);

		$oReceipt = new Receipt(array_merge($aDefault, $aData));
		$oReceipt->setupPriceDirectly();

		if ( $isNonRecurring ){
			$oPayPalMethod = new StripeNonRecurringPaymentMethod();
		}else{
			$oPayPalMethod = new StripeRecurringPaymentMethod();
		}

		$oCheckout = new Checkout();
		$aCheckAcceptPaymentStatus = $oCheckout->begin($oReceipt, $oPayPalMethod);
		return $aCheckAcceptPaymentStatus;
	}

	public function preparePayment($aData=array()){
		$aData = empty($aData) ? $_POST : $aData;
		$this->planID = Session::getSession(wilokeListingToolsRepository()->get('payment:storePlanID'));
		$listingID = Session::getSession(wilokeListingToolsRepository()->get('payment:sessionObjectStore'));

		new Billable(
			array(
				'gateway'   => $this->gateway,
				'planID'    => $this->planID,
				'listingType' => get_post_type($listingID)
			)
		);

		$isNonRecurring = Session::getSession(wilokeListingToolsRepository()->get('payment:focusNonRecurringPayment'), true);

		if ( !$isNonRecurring ){
			$isNonRecurring = GetWilokeSubmission::isNonRecurringPayment();
		}

		$oReceipt = new Receipt(array(
			'planID'    => $this->planID,
			'userID'    => get_current_user_id(),
			'couponCode'            => $aData['couponCode'],
			'isNonRecurringPayment' => $isNonRecurring,
			'aRequested'=> $_REQUEST
		));
		$oReceipt->setupPlan();

		if ( $isNonRecurring ){
			$oStripeMethod = new StripeNonRecurringPaymentMethod();
		}else{
			$oStripeMethod = new StripeRecurringPaymentMethod();
		}

		$oCheckout = new Checkout();

		$aCheckAcceptPaymentStatus = $oCheckout->begin($oReceipt, $oStripeMethod);

		if ( $aCheckAcceptPaymentStatus['status'] == 'success' ){
			wp_send_json_success($aCheckAcceptPaymentStatus);
		}else{
			wp_send_json_error($aCheckAcceptPaymentStatus);
		}
	}
}