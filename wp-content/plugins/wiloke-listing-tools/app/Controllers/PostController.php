<?php
namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Helpers\Submission;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Models\PaymentMetaModel;
use WilokeListingTools\Models\PaymentModel;
use WilokeListingTools\Models\PlanRelationshipModel;

class PostController extends Controller {
	use SetPostDuration;

	private static $aPostTypeWillBePublished = array('event');
	private static $expirationKey = 'post_expiry';
	private static $almostExpiredKey = 'post_almost_expiry';
	private static $durationKey = 'duration';

	public function __construct() {
		add_action('updated_post_meta', array($this, 'onDirectUpdatePostExpiration'), 10, 4);
		add_action('added_post_meta', array($this, 'onDirectAddedPostExpiration'), 10, 4);
		add_action('post_updated', array($this, 'updatePostExpiration'), 10, 3);
		add_action('wilcity/focus-post-expiration', array($this, 'focusPostExpiration'), 10, 1);

		add_action('wiloke-listing-tools/payment-failed', array($this, 'moveAllPostsToUnPaid'), 5);
		add_action('wiloke-listing-tools/payment-pending', array($this, 'moveAllPostsToUnPaid'), 5);
		add_action('wiloke-listing-tools/payment-cancelled', array($this, 'moveAllPostsToUnPaid'), 10);
		add_action('wiloke-listing-tools/payment-refunded', array($this, 'moveAllPostsToTrash'), 10);

		add_action('wiloke-listing-tools/payment-suspended', array($this, 'moveAllPostsToExpiry'), 30);
		add_action('wiloke-listing-tools/payment-succeeded/listing_plan', array($this, 'migrateToPublish'), 30);
		add_action('wiloke-listing-tools/payment-succeeded/event_plan', array($this, 'migrateToPublish'), 30);
		add_action('wiloke-listing-tools/payment-renewed', array($this, 'migrateToPublish'), 20);
		add_action('wiloke-listing-tools/subscription-reactived', array($this, 'migrateToPublish'), 5);

		add_action('wiloke-listing-tools/woocommerce/after-order-succeeded', array($this, 'migrateAllListingsBelongsToWooCommerceToPublish'), 20);
		add_filter('wilcity/ajax/post-comment/post', array($this, 'insertComment'));

		add_action('wp_ajax_wilcity_hide_listing', array($this, 'hideListing'));
		add_action('wp_ajax_wilcity_republish_listing', array($this, 'rePublishPost'));
		add_action('wp_ajax_wilcity_delete_listing', array($this, 'deleteListing'));

		add_action('wiloke-listing-tools/on-changed-user-plan', array($this, 'updatePostToNewPlan'), 20);
		add_action('wiloke-listing-tools/payment-succeeded/listing_plan', array($this, 'onChangePlanViaPayPal'), 20);

//		add_action('add_post_meta', array($this, 'migrateExpiredEventToTrash'), 10, 4);
//		add_action('updated_post_meta', array($this, 'migrateExpiredEventToTrash'), 10, 4);
		add_action('wiloke/free-claim/submitted', array($this, 'updateExpirationForFreeClaim'), 10, 3);

		add_action('wp_ajax_wilcity_fetch_posts', array($this, 'fetchPosts'));
		add_action('wp_ajax_nopriv_wilcity_fetch_posts', array($this, 'fetchPosts'));
	}

	public function fetchPosts(){
		$aArgs = array(
			'post_type' => 'post',
			'posts_per_page' => 10,
			'post_status' => 'publish'
		);

		if ( isset($_GET['paged']) && !empty($_GET['paged']) ){
			$aArgs['paged'] = $_GET['paged'];
		}

		if ( isset($_GET['parentID']) ){
			$aArgs['post_parent'] = $_GET['parentID'];
		}

		if ( isset($_GET['postNotIn']) && !empty($_GET['postNotIn']) ){
			$aArgs['post__not_in'] = explode(',', $_GET['postNotIn']);
		}

		$query = new \WP_Query($aArgs);
		$aPostIds = array();
		if ( $query->have_posts() ){
			ob_start();
			while ($query->have_posts()){
				$query->the_post();
				?>
				<div class="col-sm-6">
					<?php wilcity_render_grid_post($query->post); ?>
				</div>
				<?php
				$aPostIds[] = $query->post->ID;
			}
			wp_reset_postdata();
			$content = ob_get_contents();
			ob_end_clean();
			wp_send_json_success(array(
				'content'   => $content,
				'maxPages'  => $query->post->max_num_pages,
				'maxPosts'  => $query->post->found_posts,
				'postIDs'   => $aPostIds
			));
		}else{
			if ( isset($_GET['postNotIn']) && !empty($_GET['postNotIn']) ){
				wp_send_json_error(array('isLoaded'=>'yes'));
			}
			wp_send_json_error(array(
				'msg' => esc_html__('There are no posts', 'wiloke-listing-tools'),
				'maxPages' => 0,
				'maxPosts' => 0
			));
		}
	}

	public function updateExpirationForFreeClaim($claimID, $listingID, $planID){
		SetSettings::setPostMeta($listingID, 'belongs_to', $planID);
		$aPlanSettings = GetSettings::getPlanSettings($planID);
		if ( !empty($aPlanSettings['regular_period']) ){
			$duration = strtotime('+'.$aPlanSettings['regular_period'] . ' day');
			SetSettings::setPostMeta($listingID, self::$expirationKey, $duration);
			$this->focusPostExpiration($listingID);
		}
	}

	private function onChangePlan($aInfo, $paymentID){
		global $wpdb;
		$postMetaTbl = $wpdb->postmeta;
		$postTbl = $wpdb->posts;

		$aRawPostMetaIDs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT $postMetaTbl.meta_id, $postTbl.ID FROM $postMetaTbl LEFT JOIN $postTbl ON($postMetaTbl.post_id=$postTbl.ID) WHERE $postTbl.post_author=%d AND $postMetaTbl.meta_key=%s AND meta_value=%d",
				$aInfo['userID'], General::generateMetaKey('belongs_to'), $aInfo['oldPlanID']
			),
			ARRAY_A
		);

		if ( empty($aRawPostMetaIDs) ){
			return false;
		}

		$aPostIDs = $aPostMetaIDs = array();
		foreach ($aRawPostMetaIDs as $aData){
			$aPostMetaIDs[] = abs($aData['meta_id']);
			$aPostIDs[] = $aData['ID'];
		}

		$wpdb->query($wpdb->prepare(
			"UPDATE $postMetaTbl SET $postMetaTbl.meta_value = %d WHERE $postMetaTbl.meta_key=%s AND $postMetaTbl.post_id IN (".implode(',', $aPostMetaIDs).")",
			abs($_POST['planID']), General::generateMetaKey('belongs_to')
		));

		$nextBillingDate = PaymentMetaModel::getNextBillingDateGMT($paymentID);
		if ( !empty($nextBillingDate) ){
			foreach ($aPostIDs as $postID){
				SetSettings::setPostMeta($postID, self::$expirationKey, $nextBillingDate);
			}
		}
	}

	public function onChangePlanViaPayPal($aInfo){
		if ( !isset($aInfo['onChangedPlan']) || ($aInfo['onChangedPlan'] != 'yes') ){
			return false;
		}

		$this->onChangePlan($aInfo, $aInfo['paymentID']);
	}

	public function updatePostToNewPlan($aInfo){
		if ( !isset($aInfo['userID']) || !isset($aInfo['planID']) || !isset($aInfo['oldPlanID']) ||  empty($aInfo['planID']) || empty($aInfo['userID']) || empty($aInfo['oldPlanID']) ){
			return false;
		}

		$this->onChangePlan($aInfo, $aInfo['newPaymentID']);
	}

	public function deleteListing(){
		$this->middleware(['isPostAuthor'], array(
			'postID' => $_POST['postID'],
			'passedIfAdmin' => true
		));

		wp_update_post(array(
			'ID' => $_POST['postID'],
			'post_status' => 'trash'
		));

		do_action('wilcity/deleted/listing', $_POST['postID']);

		wp_send_json_success(array(
			'msg' => esc_html__('Congrats! The listing has been deleted successfully', 'wiloke-listing-tools')
		));
	}

	public function rePublishPost(){
		$this->middleware(['isPostAuthor', 'isTemporaryHiddenPost'], array(
			'postID' => $_POST['postID'],
			'passedIfAdmin' => true
		));

		wp_update_post(array(
			'ID' => $_POST['postID'],
			'post_status' => 'publish'
		));

		wp_send_json_success(array(
			'msg' => esc_html__('Congrats! The listing has been re-published successfully', 'wiloke-listing-tools')
		));
	}

	public function hideListing(){
		$this->middleware(['isPostAuthor', 'isPublishedPost'], array(
			'postID' => $_POST['postID'],
			'passedIfAdmin' => true
		));

		wp_update_post(array(
			'ID' => $_POST['postID'],
			'post_status' => 'temporary_close'
		));

		wp_send_json_success(array(
			'msg' => esc_html__('Congrats! The listing has been hidden successfully', 'wiloke-listing-tools')
		));
	}

	public function insertComment($aData){
		$commentID = wp_insert_comment(array(
			'user_id' => get_current_user_id(),
			'comment_content' => $aData['content']
		));

		global $oReview, $wiloke;
		$wiloke->aThemeOptions = \Wiloke::getThemeOptions();
		$wiloke->aConfigs['translation'] = wilcityGetConfig('translation');

		$aReview = get_comment($commentID, ARRAY_A);
		$aReview['ID'] = $aReview['comment_ID'];
		$aReview['post_content'] = $aReview['comment_content'];
		$oReview = (object)$aReview;

		ob_start();
		get_template_part('reviews/item');
		$html = ob_get_contents();
		ob_end_clean();

		wp_send_json_success(array(
			'html' => $html,
			'commentID' => $commentID
		));
	}

	public static function setDefaultExpiration($fieldArgs, $oField ){
		$duration = GetSettings::getPostMeta($oField->object_id, self::$durationKey);

		if ( empty($duration) ){
			return '';
		}

		return strtotime('+'.$duration . ' day');
	}

	public function onDirectAddedPostExpiration($metaID, $postID, $metaKey, $metaVal){
		if ( $metaKey !== 'wilcity_post_expiry' || empty($metaVal) ){
			return false;
		}

		$this->setScheduleExpiration($postID, $metaVal);
	}

	public function onDirectUpdatePostExpiration($metaID, $postID, $metaKey, $metaVal){
		if ( $metaKey !== 'wilcity_post_expiry' ){
			return false;
		}

		$this->setScheduleExpiration($postID, $metaVal);
	}

	public function updatePostExpiration($postID, $postAfter, $postBefore){
		if ( $postAfter->post_status == $postBefore->post_status ){
			return false;
		}

		if ( $postAfter->post_status != 'publish' ){
			self::clearScheduled($postID);
		}else{
			$duration = GetSettings::getPostMeta($postID, self::$expirationKey);
			if ( empty($duration) ){
				$duration = GetSettings::getPostMeta($postID, 'duration');
				if ( !empty($duration) ){
					self::setExpiration($postID, $duration);
				}
			}
		}
	}

	public function setPostDuration($aData){
		if ( !isset($aData['postID']) || empty($aData['postID']) ){
			return false;
		}
		$isTrial = isset($aData['isTrial']) ? $aData['isTrial'] : false;
		//$this->setDuration($aData['billingType'], $aData['postID'], $aData['planID'], $isTrial);
	}

	private static function clearScheduled($postID){
		wp_clear_scheduled_hook(self::$expirationKey, array($postID));
		wp_clear_scheduled_hook(self::$almostExpiredKey, array($postID));
	}

	protected function setScheduleExpiration($postID, $expirationTimestamp){
		self::clearScheduled($postID);
		$expirationTimestamp = is_numeric($expirationTimestamp) ? $expirationTimestamp : strtotime($expirationTimestamp);
		$beforeOneDay = $expirationTimestamp - 86400;
		if ( $beforeOneDay > 0 ){
			wp_schedule_single_event($beforeOneDay, self::$almostExpiredKey, array($postID));
		}else{
			$beforeHalfDay = $expirationTimestamp - 43200;
			wp_schedule_single_event($beforeHalfDay, self::$almostExpiredKey, array($postID));
		}
		wp_schedule_single_event($expirationTimestamp, self::$expirationKey, array($postID));
	}

	public function focusPostExpiration($postID){
		$duration = GetSettings::getPostMeta($postID, self::$expirationKey);
		if ( !empty($duration) ){
			self::setScheduleExpiration($postID, $duration);
		}
	}

	public static function setExpiration($postID, $duration, $isNextBillingDateVal=false){
		if ( empty($duration) ){
			// forever
			return true;
		}
		$expirationTimestamp = !$isNextBillingDateVal ? strtotime('+'.$duration . ' day') : $duration;
		SetSettings::setPostMeta($postID, self::$expirationKey, $expirationTimestamp);
	}

	public static function changePostsStatusByPaymentID($paymentID, $status){
		$aPostIDs = PlanRelationshipModel::getObjectIDsByPaymentID($paymentID);

		if ( empty($aPostIDs) ){
			return false;
		}

		foreach ($aPostIDs as $aPost){
			wp_update_post(
				array(
					'ID'            => $aPost['objectID'],
					'post_status'   => $status,
					'menu_order'    => 0
				)
			);
		}
	}

	public static function migratePostsToExpiredStatus($paymentID){
		self::changePostsStatusByPaymentID($paymentID, 'expired');
	}

	public static function migratePostsToDraftStatus($paymentID){
		self::changePostsStatusByPaymentID($paymentID, 'draft');
	}

	public static function migratePostsToPublishStatus($paymentID){
		self::changePostsStatusByPaymentID($paymentID, 'publish');
	}

	public static function migratePostsToPendingStatus($paymentID){
		self::changePostsStatusByPaymentID($paymentID, 'pending');
	}

	protected static function detectNewPostStatus($postID){
		$postStatus = get_post_status($postID);
		if ( $postStatus == 'expired' || $postStatus == 'publish' ){
			return 'publish';
		}else{
			return Submission::detectPostStatus();
		}
	}

	protected function inCaseToPublish($aObjectIDs, $aData){

		foreach ($aObjectIDs as $aObjectID){
			if ( !empty($aObjectID['objectID']) ){
				$postStatus = self::detectNewPostStatus($aObjectID['objectID']);
				$aPlanSettings = GetSettings::getPlanSettings($aData['planID']);

				$duration = '';
				if ( isset($aData['nextBillingDateGMT']) && !empty($aData['nextBillingDateGMT']) ){
					$duration = $aData['nextBillingDateGMT'];
					$isBillingDate = true;
				}else{
					if ( isset($aData['isTrial']) && !empty($aData['isTrial']) ){
						$duration = $aPlanSettings['trial_period'];
					}

					if ( empty($duration) ){
						$duration = $aPlanSettings['regular_period'];
					}
					$isBillingDate = false;
				}

				if ( $postStatus == 'publish' ){
					SetSettings::deletePostMeta($aObjectID['objectID'], self::$durationKey);
					self::setExpiration($aObjectID['objectID'], $duration, $isBillingDate);
				}else{
					if ( $isBillingDate ){
						self::setExpiration($aObjectID['objectID'], $duration, $isBillingDate);
					}else{
						SetSettings::setPostMeta($aObjectID['objectID'], 'duration', $duration);
					}
				}

				$aData = array(
					'ID'            => $aObjectID['objectID'],
					'post_status'   => $postStatus
				);
				wp_update_post($aData);
			}
		}
	}

	public static function migratePostsToPendingOrPublishStatus($paymentID){
		$aPostIDs = PlanRelationshipModel::getObjectIDsByPaymentID($paymentID);

		if ( empty($aPostIDs) ){
			return false;
		}

		foreach ($aPostIDs as $aPost){
			$newStatus = self::detectNewPostStatus($aPost['objectID']);
			wp_update_post(
				array(
					'ID'            => $aPost['objectID'],
					'post_status'   => $newStatus
				)
			);
		}
	}

	public function migrateAllListingsBelongsToWooCommerceToPublish($aResponse){
		$aPaymentIDs = PaymentModel::getPaymentIDsByWooOrderID($aResponse['orderID']);
		if ( empty($aPaymentIDs) ){
			return false;
		}

		foreach ($aPaymentIDs as $aPaymentID){
			PaymentModel::updatePaymentStatus('succeeded', $aPaymentID['ID']);
			$aObjectIDs = PlanRelationshipModel::getObjectIDsByPaymentID($aPaymentID['ID']);

			if ( empty($aObjectIDs) ){
				continue;
			}
			$this->inCaseToPublish($aObjectIDs, $aResponse);
		}
	}

	public function moveAllPostsToUnPaid($aData){
		$aObjectIDs = PlanRelationshipModel::getObjectIDsByPaymentID($aData['paymentID']);
		if ( empty($aObjectIDs) ){
			return false;
		}
		foreach ($aObjectIDs as $aObjectID){
			if ( !empty($aObjectID['objectID']) ){
				wp_update_post(
					array(
						'ID' => $aObjectID['objectID'],
						'post_status' => 'unpaid'
					)
				);
			}
		}
	}

	public function moveAllPostsToTrash($aData){
		$aObjectIDs = PlanRelationshipModel::getObjectIDsByPaymentID($aData['paymentID']);
		if ( empty($aObjectIDs) ){
			return false;
		}
		foreach ($aObjectIDs as $aObjectID){
			if ( !empty($aObjectID['objectID']) ){
				wp_update_post(
					array(
						'ID' => $aObjectID['objectID'],
						'post_status' => 'trash'
					)
				);
			}
		}
	}

	public function migrateToPublish($aData){
		$aObjectIDs = PlanRelationshipModel::getObjectIDsByPaymentID($aData['paymentID']);
		if ( empty($aObjectIDs) ){
			return false;
		}

		$this->inCaseToPublish($aObjectIDs, $aData);
	}

	public function moveAllPostsToExpiry($aData){
		$aObjectIDs = PlanRelationshipModel::getObjectIDsByPaymentID($aData['paymentID']);
		if ( empty($aObjectIDs) ){
			return false;
		}
		foreach ($aObjectIDs as $aObjectID){
			if ( !empty($aObjectID['objectID']) ){
				wp_update_post(
					array(
						'ID' => $aObjectID['objectID'],
						'post_status' => 'expired'
					)
				);
			}
		}
	}
}