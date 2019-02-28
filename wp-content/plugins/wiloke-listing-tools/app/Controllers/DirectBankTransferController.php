<?php
namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Payment\Billable;
use WilokeListingTools\Framework\Payment\Checkout;
use WilokeListingTools\Framework\Payment\DirectBankTransfer\DirectBankTransferNonRecurringPayment;
use WilokeListingTools\Framework\Payment\DirectBankTransfer\DirectBankTransferRecurringPayment;
use WilokeListingTools\Framework\Payment\Receipt;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Framework\Store\Session;

class DirectBankTransferController extends Controller {
	public $gateway = 'banktransfer';
	public $planID;
	public $userID;

	public function   __construct(){
		add_action('wp_ajax_wiloke_submission_pay_with_directbanktransfer', array($this, 'preparePayment'));
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

//		$isNonRecurring = Session::getSession(wilokeListingToolsRepository()->get('payment:focusNonRecurringPayment'), true);

		if ( !$isNonRecurring ){
			$isNonRecurring = GetWilokeSubmission::isNonRecurringPayment();
		}

		if ( $isNonRecurring ){
			$oPayPalMethod = new DirectBankTransferNonRecurringPayment();
		}else{
			$oPayPalMethod = new DirectBankTransferRecurringPayment();
		}

		$oCheckout = new Checkout();
		$aCheckAcceptPaymentStatus = $oCheckout->begin($oReceipt, $oPayPalMethod);
		return $aCheckAcceptPaymentStatus;
	}

	public function preparePayment($aData){
		$aData = empty($aData) ? $_POST : $aData;
		$this->planID = Session::getSession(wilokeListingToolsRepository()->get('payment:storePlanID'));
		$this->userID = get_current_user_id();
		$listingID = Session::getSession(wilokeListingToolsRepository()->get('payment:sessionObjectStore'));

		new Billable(
			array(
				'gateway'   => $this->gateway,
				'planID'    => $this->planID,
				'listingType' => get_post_type($listingID)
			)
		);

		$this->middleware(['verifyDirectBankTransfer'], array(
			'userID' => $this->userID,
			'planID' => $this->planID
		));

		$isNonRecurring = GetWilokeSubmission::isNonRecurringPayment();
		$oReceipt = new Receipt(array(
			'planID'    => $this->planID,
			'userID'    => $this->userID,
			'couponCode'=> $aData['couponCode'],
			'isNonRecurringPayment' => $isNonRecurring,
			'aRequested'=> $_REQUEST
		));
		$oReceipt->setupPlan();

		if ( GetWilokeSubmission::isNonRecurringPayment() ){
			$oDirectBankTransferMethod = new DirectBankTransferNonRecurringPayment();
		}else{
			$oDirectBankTransferMethod = new DirectBankTransferRecurringPayment();
		}
		$oCheckout = new Checkout();

		$aCheckAcceptPaymentStatus = $oCheckout->begin($oReceipt, $oDirectBankTransferMethod);
		if ( $aCheckAcceptPaymentStatus['status'] == 'success' ){
			$aCheckAcceptPaymentStatus['redirectTo'] = GetWilokeSubmission::getField('thankyou', true);
			wp_send_json_success($aCheckAcceptPaymentStatus);
		}else{
			wp_send_json_error($aCheckAcceptPaymentStatus);
		}
	}
}

