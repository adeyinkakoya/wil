<?php
namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\Time;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Frontend\User;
use WilokeListingTools\Models\PaymentMetaModel;
use WilokeListingTools\Models\PaymentModel;
use WilokeListingTools\Models\UserModel;

class UserPlanController extends Controller {
	public function __construct() {
		$aPostTypes = General::getPostTypeKeys(false, false);
		foreach ($aPostTypes as $planKey){
			add_action('wiloke-listing-tools/payment-succeeded/'.$planKey.'_plan', array($this, 'setUserPlan'));
		}

//		add_action('wiloke-listing-tools/payment-succeeded/event_plan', array($this, 'setUserPlan'));

		add_action('wiloke-listing-tools/payment-renewed', array($this, 'updateNextBillingDateGMT'));
		add_action('wiloke-listing-tools/changed-payment-status', array($this, 'deletePlanIfIsCancelled'));
		add_action('wiloke-listing-tools/changed-payment-status', array($this, 'updateUserPlanIfSucceededOrActivate'));
		add_action('wiloke-listing-tools/woocommerce/after-order-succeeded', array($this, 'setUserPlanOfUserBoughtViaWooCommerce'));
		add_action('wiloke-listing-tools/on-changed-user-plan', array($this, 'changeUserPlan'));
		add_filter('get_avatar' , array($this, 'wilcityAvatar') , 1 , 5 );
	}

	public function wilcityAvatar($avatar, $id_or_email, $size, $default, $alt){
		if ( is_object( $id_or_email ) ) {
			if ( !empty( $id_or_email->user_id ) ) {
				$id = (int) $id_or_email->user_id;
			}
		}else if ( !is_numeric($id_or_email) ){
			$user = get_user_by( 'email', $id_or_email );
			if ( !empty($user) ){
				$id = $user->user_id;
			}
		}else{
			$id = $id_or_email;
		}

		if ( isset($id) ){
			$url = GetSettings::getUserMeta($id, 'avatar');

			if ( !empty($url) ){
				$avatar = "<img alt='{$alt}' src='{$url}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
			}
		}

		return $avatar;
	}

	/*
	 * @aInfo: $planID, $orderID, $productID, $status
	 */
	public function setUserPlanOfUserBoughtViaWooCommerce($aResponse){
		if ( !isset($aResponse['planID']) || empty($aResponse['planID']) ){
			return false;
		}
		$aPaymentIDs = PaymentModel::getPaymentIDsByWooOrderID($aResponse['orderID']);

		if ( empty($aPaymentIDs) ){
			return false;
		}

		$isTrial = isset($aResponse['isTrial']) ? $aResponse['isTrial'] : false;
		foreach ($aPaymentIDs as $aPaymentID){
			$aData = array();

			$aData['userID']        = PaymentModel::getField('userID', $aPaymentID['ID']);
			$aData['billingType']   = PaymentModel::getField('billingType', $aPaymentID['ID']);
			$aData['gateway']       = $aResponse['gateway'];
			$aData['paymentID']     = $aPaymentID['ID'];
			$aData['planID']        = $aResponse['planID'];
			$aData['postID']        = $aResponse['postID'];
			$aData['isTrial']       = $isTrial;

			$this->setUserPlan($aData);
		}
	}

	public function changeUserPlan($aInfo){
		$userID = isset($aInfo['userID']) ? $aInfo['userID'] : get_current_user_id();

		$instUserModel = new UserModel();
		$instUserModel->setUserID($userID)->setGateway($aInfo['gateway'])->setPaymentID($aInfo['paymentID'])->setPlanID($aInfo['planID'])->setOldPlanID($aInfo['oldPlanID'])->setPostType($aInfo['listingType'])->setBillingType($aInfo['billingType']);

		if ( !GetWilokeSubmission::isNonRecurringPayment($aInfo['billingType']) ){
			if ( !isset($aInfo['nextBillingDateGMT']) || empty($aInfo['nextBillingDateGMT']) ){
				PaymentMetaModel::set($aInfo['paymentID'], wilokeListingToolsRepository()->get('addlisting:nextBillingDateGMT'), Time::timestampUTC('+1 day'));
				$instUserModel->setNextBillingDateGMT(Time::timestampUTCNow('+1 day'));
			}else{
				$instUserModel->setNextBillingDateGMT($aInfo['nextBillingDateGMT']);
			}
		}

		$this->middleware(['validateBeforeSetUserPlan'], array(
			'instUserModel' => $instUserModel,
			'billingType'   => $aInfo['billingType']
		));

		$instUserModel->updateUserPlan();
	}

	/*
	 * Set user plan
	 * @aInfo: $status, $gateway, $billingType, $paymentID, $planID
	 */
	public function setUserPlan($aInfo){
		$instUserModel = new UserModel();
		if ( isset($aInfo['category']) && $aInfo['category'] == 'promotion' ){
			return true;
		}

		if ( !isset($aInfo['onChangedPlan']) || ($aInfo['onChangedPlan'] != 'yes') ){
			$userID = isset($aInfo['userID']) ? $aInfo['userID'] : get_current_user_id();
			if ( isset($aInfo['listingType']) && !empty($aInfo['listingType']) ){
				$postType = $aInfo['listingType'];
			}else if ( isset($aInfo['postID']) ){
				$postType = get_post_type($aInfo['postID']);
			}else{
				$postType = '';
			}

			$instUserModel->setUserID($userID)->setBillingType($aInfo['billingType'])->setGateway($aInfo['gateway'])->setPaymentID($aInfo['paymentID'])->setPlanID($aInfo['planID'])->setPostType($postType);

			if ( isset($aInfo['isTrial']) && $aInfo['isTrial'] ){
				UserModel::setUsedTrialPlans($aInfo['planID'], $aInfo['userID']);
				$instUserModel->setIsTrial(true);
			}


			if ( !GetWilokeSubmission::isNonRecurringPayment($aInfo['billingType']) ){
				if ( !isset($aInfo['nextBillingDateGMT']) || empty($aInfo['nextBillingDateGMT']) ){
					PaymentMetaModel::set($aInfo['paymentID'], wilokeListingToolsRepository()->get('addlisting:nextBillingDateGMT'), Time::timestampUTC('+1 day'));
					$instUserModel->setNextBillingDateGMT(Time::timestampUTCNow('+1 day'));
				}else{
					if ( is_string($aInfo['nextBillingDateGMT']) ){
						$nextBillingDate = strtotime($aInfo['nextBillingDateGMT']);
					}else{
						$nextBillingDate = $aInfo['nextBillingDateGMT'];
					}
					PaymentMetaModel::set($aInfo['paymentID'], wilokeListingToolsRepository()->get('addlisting:nextBillingDateGMT'), $nextBillingDate);
					$instUserModel->setNextBillingDateGMT($aInfo['nextBillingDateGMT']);
				}
			}

			$this->middleware(['validateBeforeSetUserPlan'], array(
				'instUserModel' => $instUserModel,
				'billingType'   => $aInfo['billingType']
			));
			$instUserModel->setUserPlan();
		}else{
			$userID = isset($aInfo['userID']) ? $aInfo['userID'] : get_current_user_id();
			$instUserModel->setUserID($userID)->setBillingType($aInfo['billingType'])->setGateway($aInfo['gateway'])->setPaymentID($aInfo['paymentID'])->setOldPlanID($aInfo['oldPlanID'])->setPlanID($aInfo['planID']);

			if ( !GetWilokeSubmission::isNonRecurringPayment($aInfo['billingType']) ){
				if ( !isset($aInfo['nextBillingDateGMT']) || empty($aInfo['nextBillingDateGMT']) ){
					PaymentMetaModel::set($aInfo['paymentID'], wilokeListingToolsRepository()->get('addlisting:nextBillingDateGMT'), Time::timestampUTC('+1 day'));
					$instUserModel->setNextBillingDateGMT(Time::timestampUTCNow('+1 day'));
				}else{
					$instUserModel->setNextBillingDateGMT($aInfo['nextBillingDateGMT']);
				}
			}

			$this->middleware(['validateBeforeSetUserPlan'], array(
				'instUserModel' => $instUserModel,
				'billingType'   => $aInfo['billingType']
			));
			$instUserModel->updateUserPlan();
		}
	}

	public function updateUserPlanIfSucceededOrActivate($aInfo){
		if ( $aInfo['newStatus'] != 'succeeded' && $aInfo['newStatus'] != 'active' ){
			return false;
		}

		$aInfo['billingType']   = PaymentModel::getField('billingType', $aInfo['paymentID']);
		$aInfo['userID']        = PaymentModel::getField('userID', $aInfo['paymentID']);
		$aInfo['planID']        = PaymentModel::getField('planID', $aInfo['paymentID']);
		$aInfo['gateway']       = PaymentModel::getField('gateway', $aInfo['paymentID']);
		$aInfo['nextBillingDateGMT'] = PaymentMetaModel::getNextBillingDateGMT($aInfo['paymentID']);

		$this->setUserPlan($aInfo);
	}

	public function deletePlanIfIsCancelled($aInfo){
		if ( $aInfo['newStatus'] != 'cancelled' && $aInfo['newStatus'] !== 'cancelled_and_unpublish_listing' ){
			return false;
		}

		$planID = PaymentModel::getField('planID', $aInfo['paymentID']);
		$userID = PaymentModel::getField('userID', $aInfo['paymentID']);

		$instUserModel = new UserModel();
		$instUserModel->setUserID($userID);
		$instUserModel->deleteUserPlan($planID);
	}

	/*
	 * @aInfo: $nextBillingPayment (timestamp UTC), paymentID
	 */
	public function updateNextBillingDateGMT($aInfo){
		$userID = PaymentModel::getField('userID', $aInfo['paymentID']);
		$planID = PaymentModel::getField('planID', $aInfo['paymentID']);

		$instUserModel = new UserModel();
		$instUserModel->updateNextBillingDateGMT($aInfo['nextBillingDateGMT'], $planID, $userID);
	}
}