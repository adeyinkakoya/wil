<?php

namespace WilokeListingTools\Models;


use Stripe\Util\Set;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\Message;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Helpers\Time;
use WilokeListingTools\Frontend\User;

class UserModel {
	public static $aPlans;
	protected $userID;
	protected $nextBillingDateGMT;
	protected $gateway;
	protected $paymentID;
	protected $billingType;
	protected $planID;
	protected $newPlanID;
	protected $oldPlanID;
	protected $postType;
	protected $planType;
	protected $remainingItems;
	protected $isTrial=false;

	protected function resetObject(){
		foreach ($this as $key => $val){
			unset($this->$key);
		}
	}

	public function __get( $name ) {
		if ( property_exists($this, $name) ){
			return $this->{$name};
		}

		return false;
	}

	public function __set( $name, $value ) {
		$this->{$name} = $value;
	}

	public static function getStripeID($userID=null){
		if ( !is_user_logged_in() ){
			return false;
		}

		if ( !empty($userID) && !current_user_can('administrator') ){
			return false;
		}

		$userID = empty($userID) ? get_current_user_id() : $userID;
		return GetSettings::getUserMeta($userID, wilokeListingToolsRepository()->get('user:stripeCustomerID'));
	}

	public static function setStripeID($val, $userID=null){
		$userID = empty($userID) ? get_current_user_id() : $userID;
		return SetSettings::setUserMeta($userID, wilokeListingToolsRepository()->get('user:stripeCustomerID'), $val);
	}

	public static function deleteStripeID($userID=null){
		$userID = empty($userID) ? get_current_user_id() : $userID;
		return SetSettings::deleteUserMeta($userID, wilokeListingToolsRepository()->get('user:stripeCustomerID'));
	}

	public static function getAllPlans($userID=null, $isFocus=false){
		$userID = empty($userID) ? get_current_user_id() : $userID;
		if ( empty($userID) ){
			return false;
		}
		self::$aPlans = GetSettings::getUserPlans($userID, $isFocus);
		return self::$aPlans;
	}

	public static function getSpecifyUserPlanType($planType, $userID=null, $isFocus=false){
		$aPlans = self::getAllPlans($userID, $isFocus);

		return isset($aPlans[$planType]) ? $aPlans[$planType] : false;
	}

	public static function getSpecifyUserPlanID($planID, $userID=null, $isFocus=false){
		$planType = get_post_type($planID);
		$aPlans = self::getSpecifyUserPlanType($planType, $userID, $isFocus);

		return isset($aPlans[$planID]) ? $aPlans[$planID] : false;
	}

	public function setUserID($userID){
		$this->userID = $userID;
		return $this;
	}

	public function setNewPlanID($planID){
		$this->newPlanID = $planID;
		return $this;
	}

	public function setOldPlanID($planID){
		$this->oldPlanID = $planID;
		return $this;
	}

	public function setNextBillingDateGMT($nextBillingDateGMT){
		if ( !is_numeric($nextBillingDateGMT) ){
			date_default_timezone_set('UTC');
			$this->nextBillingDateGMT = strtotime($nextBillingDateGMT);
		}else{
			$this->nextBillingDateGMT = $nextBillingDateGMT;
		}
		return $this;
	}

	public function setGateway($gateway){
		$this->gateway = $gateway;
		return $this;
	}

	public function setIsTrial($isTrial){
		$this->isTrial = $isTrial;
		return $this;
	}

	public function setPaymentID($paymentID){
		$this->paymentID = $paymentID;
		return $this;
	}

	public function setBillingType($billingType){
		$this->billingType = $billingType;
		return $this;
	}

	public function setPlanID($planID){
		$this->planID = $planID;
		return $this;
	}

	public function setPostType($postType){
		$this->postType = $postType;
		return $this;
	}

	public function deleteUserPlan($planID){
		$aPlans = self::getAllPlans($this->userID, true);
		if ( empty($aPlans) ){
			return true;
		}

		$this->planType = get_post_type($planID);
		if ( !isset($aPlans[$this->planType]) ){
			return true;
		}

		if ( !isset($aPlans[$this->planType][$planID]) ){
			return true;
		}

		unset($aPlans[$this->planType][$planID]);

		if ( count($aPlans[$this->planType]) == 0 ){
			unset($aPlans[$this->planType]);
		}

		if ( empty($aPlans) ){
			SetSettings::deleteUserPlan($this->userID);
		}else{
			SetSettings::setUserPlans($this->userID, $this->planType, $aPlans);
		}

	}

	public function calculateRemainingItems(){
		$instRemainingItems = new RemainingItems();
		$instRemainingItems->setUserID($this->userID)->setGateway($this->gateway)->setPlanID($this->planID)->setBillingType($this->billingType)->setPaymentID($this->paymentID);

		$this->remainingItems = $instRemainingItems->getRemainingItems();
	}
	
	public static function getRemainingItemsOfPlans($planID){
		$aUserPlan = self::getSpecifyUserPlanID($planID, get_current_user_id());
		if ( empty($aUserPlan) ){
			return 0;
		}

		if ( isset($aUserPlan['remainingItems']) && !empty($aUserPlan['remainingItems']) ){
			return absint($aUserPlan['remainingItems']);
		}

		return 0;
	}

	public function updateRemainingItemsUserPlan($planID, $userID=null){
		$userID = empty($userID) ? get_current_user_id() : $userID;
		$aUserPlan = self::getSpecifyUserPlanID($planID, $userID);

		$this->userID = $userID;
		foreach ($aUserPlan as $property => $val){
			$this->{$property} = $val;
		}

		$this->calculateRemainingItems();
		$aUserPlan['remainingItems'] = $this->remainingItems <= 0 ? 0 : abs($this->remainingItems);

		$aUserPlans = self::getAllPlans($userID);
		$this->planType = get_post_type($planID);

		if ( empty($this->remainingItems) ){
			if ( GetWilokeSubmission::isNonRecurringPayment($this->billingType) ){
				unset($aUserPlans[$this->planType][$planID]);

				if ( empty($aUserPlans[$this->planType]) ){
					unset($aUserPlans[$this->planType]);
				}
			}else{
				$aUserPlans[$this->planType][$planID] = $aUserPlan;
			}
		}else{
			$aUserPlans[$this->planType][$planID] = $aUserPlan;
		}

		SetSettings::setUserPlans($userID, $aUserPlans);
	}

	public function updateNextBillingDateGMT($nextBillingDateGMT, $planID, $userID=null, $paymentID=null){
		if ( empty($nextBillingDateGMT) ){
			Message::error(esc_html__('Wrong the next billing date value.', 'wiloke-listing-tools'));
		}

		if ( !is_numeric($nextBillingDateGMT) ){
			date_default_timezone_set('UTC');
			$nextBillingDateGMT = strtotime($nextBillingDateGMT);
		}

		$currentTimeStamp = Time::timestampUTCNow();
		$nextBillingDateGMTCache = $nextBillingDateGMT;
		settype($nextBillingDateGMTCache, 'integer');

		$now = Time::timestampUTCNow();
		settype($now, 'int');
		if (  $nextBillingDateGMTCache < $currentTimeStamp ){
			Message::error(esc_html__('The next billing date must be bigger than the current date.', 'wiloke-listing-tools'));
		}

		$this->userID = empty($userID) ? get_current_user_id() : $userID;
		$aUserPlans = self::getAllPlans($this->userID);
		$aUserPlans = empty($aUserPlans) ? array() : $aUserPlans;

		$this->planType = get_post_type($planID);
		$this->nextBillingDateGMT = $nextBillingDateGMT;
		$aUserPlans[$this->planType][$planID]['nextBillingDateGMT'] = $nextBillingDateGMT;

		$this->gateway      = $aUserPlans[$this->planType][$planID]['gateway'];
		$this->planID       = $planID;
		$this->billingType  = $aUserPlans[$this->planType][$planID]['billingType'];
		$this->paymentID    = empty($paymentID) ? $aUserPlans[$this->planType][$planID]['paymentID'] : $paymentID;

		PaymentMetaModel::setNextBillingDateGMT($this->nextBillingDateGMT, $this->paymentID);

		$this->calculateRemainingItems();
		$aUserPlans[$this->planType][$planID]['remainingItems'] = $this->remainingItems <= 0 ? 0 : $this->remainingItems;
		SetSettings::setUserPlans($userID, $aUserPlans);
	}

	public function updateUserPlan(){
		$aPlans = self::getAllPlans($this->userID, true);
		$aPlans = empty($aPlans) ? array() : $aPlans;

		if ( empty($aPlans) ){
			return false;
		}

		$this->planType = get_post_type($this->oldPlanID);
		$this->calculateRemainingItems();

		$aNewPlan = $aPlans[$this->planType][$this->oldPlanID];
		$aNewPlan['remainingItems'] = $this->remainingItems;
		$aNewPlan['nextBillingDateGMT'] = $this->nextBillingDateGMT;
		$aNewPlan['paymentID'] = $this->paymentID;
		$aNewPlan['gateway'] = $this->gateway;
		$aNewPlan['planID'] = $this->planID;
		$aPlans[$this->planType][$this->planID] = $aNewPlan;
		unset($aPlans[$this->planType][$this->oldPlanID]);

		SetSettings::setUserPlans($this->userID, $aPlans);
	}

	public function setUserPlan(){
		$aPlans = self::getAllPlans($this->userID, true);
		$aPlans = empty($aPlans) ? array() : $aPlans;

		$this->planType = get_post_type($this->planID);

		$aNewPlan = array(
			'nextBillingDateGMT'=> $this->nextBillingDateGMT,
			'gateway'           => $this->gateway,
			'paymentID'         => $this->paymentID,
			'billingType'       => $this->billingType,
			'planID'            => $this->planID,
			'isTrial'           => $this->isTrial,
			'postType'          => $this->postType
		);
		SetSettings::deleteUserPlans($this->userID);

		if ( empty($aPlans) ){
			$aPlans = array(
				$this->planType => array(
					$this->planID => $aNewPlan
				)
			);
		}else{
			unset($aPlans[$this->planType][$this->planID]);
			$aPlans[$this->planType][$this->planID] = $aNewPlan;
		}

		$this->calculateRemainingItems();
		$aPlans[$this->planType][$this->planID]['remainingItems'] = $this->remainingItems;

		SetSettings::setUserPlans($this->userID, $aPlans);

		if ( $this->billingType == wilokeListingToolsRepository()->get('payment:billingTypes', true)->sub('recurring') ){
			PaymentMetaModel::set($this->paymentID, wilokeListingToolsRepository()->get('addlisting:nextBillingDateGMT'), $this->nextBillingDateGMT);
		}
		do_action('wiloke-submission/updated-new-user-plan', $this);
	}

	public static function setUsedTrialPlans($planID, $userID=null){
		$userID = empty($userID) ? get_current_user_id() : $userID;

		$usedPlanIDs = GetSettings::getUserMeta($userID, wilokeListingToolsRepository()->get('user:usedTrialPlans'));
		if ( empty($usedPlanIDs) ){
			$aPlansIDs = array();
			$aPlansIDs[] = $planID;
		}else{
			$aPlansIDs = explode(',', $usedPlanIDs);
			array_push($aPlansIDs, $planID);
		}

		SetSettings::setUserMeta($userID, wilokeListingToolsRepository()->get('user:usedTrialPlans'), $aPlansIDs);
	}

	public static function isMyFavorite($postID, $isApp=false){
		if ( !User::isUserLoggedIn($isApp) ){
			return false;
		}

		if ( !$isApp ){
			$userID = get_current_user_id();
		}else{
			$userID = User::getUserID();
		}

		$aFavorites = GetSettings::getUserMeta($userID, 'my_favorites');
		if ( empty($aFavorites) ){
			return false;
		}

		return in_array($postID, $aFavorites);
	}

	public static function getLatestUserPlan($planType){
		$aUserPlans = self::getSpecifyUserPlanType($planType);
		if ( !empty($aUserPlans) ){
			$aUserPlan = end($aUserPlans);
			return $aUserPlan;
		}
		return false;
	}

	public static function getLatestPlanID($planType){
		$aUserPlan = self::getLatestUserPlan($planType);
		if ( !empty($aUserPlan) ){
			return $aUserPlan['planID'];
		}
		return false;
	}

	public static function isExceededRecurringPaymentPlan($planType){
		$aUserPlan = self::getLatestUserPlan($planType);
		if ( !empty($aUserPlan) ){
			if ( GetWilokeSubmission::isNonRecurringPayment($aUserPlan['billingType']) ){
				return false;
			}
			if ( empty($aUserPlan['remainingItems']) || GetWilokeSubmission::isPlanExists($aUserPlan['planID']) ){
				return true;
			}
		}
		return false;
	}
}