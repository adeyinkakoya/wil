<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Helpers\Time;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Frontend\User;
use WilokeListingTools\Models\NotificationsModel;

class NotificationsController extends Controller {
	protected static $connect = '___';
	protected static $notificationKey = 'notifications';

	public function __construct() {
		/*
		 * Listen Notifications
		 */
		add_action('wilcity/submitted-new-review', array($this, 'addReviewNotification'), 10, 3);
		add_action('wilcity/review/discussion', array($this, 'addReviewDiscussionNotification'), 10, 2);
		add_action('wiloke/claim/approved', array($this, 'addClaimApproved'), 10, 2);
		add_action('wiloke/claim/cancelled', array($this, 'addClaimCancelled'), 10, 2);
		add_action('wilcity/submitted-report', array($this, 'addReportNotification'), 10, 2);
		add_action('wiloke/submitted-listing', array($this, 'addSubmittedListingNotification'), 10, 2);

		add_action('wilcity/event/after-inserted-comment', array($this, 'afterSubmittingEventComment'), 10, 3);
		//add_action('wiloke-listing-tools/notification', array($this, 'update'), 10, 5);
		add_action('wp_ajax_wilcity_fetch_notifications', array($this, 'fetchNotifications'));
		add_action('wp_ajax_wilcity_delete_notification', array($this, 'deleteNotification'));

		//add_action('post_updated', array($this, 'updateNotifications'), 10, 3);
		add_action('wilcity/header/after-menu', array($this, 'quickNotification'), 15);
		add_action('wp_ajax_wilcity_fetch_list_notifications', array($this, 'fetchNotifications'));
		add_action('wp_ajax_wilcity_count_new_notifications', array($this, 'fetchCountNewNotifications'));
		add_action('wp_ajax_wilcity_reset_new_notifications', array($this, 'resetNewNotifications'));
		add_action('dokan_new_product_added', array($this, 'addNotificationToAdminAboutNewProduct'), 10);
	}

	public function resetNewNotifications(){
		SetSettings::setUserMeta(get_current_user_id(), NotificationsModel::$countNewKey, 0);
	}

	public function fetchCountNewNotifications(){
		$count = GetSettings::getUserMeta(get_current_user_id(), NotificationsModel::$countNewKey);
		$count = empty($count) ? 0 : abs($count);
		wp_send_json_success($count);
	}

	public function addNotificationToAdminAboutNewProduct($postID){
	    $oSuperAdmin = User::getFirstSuperAdmin();
		NotificationsModel::add($oSuperAdmin->ID, 'dokan_submitted_a_product', $postID);
    }

	public function addReviewNotification($reviewID, $parentID, $senderID){
		$receiverID = get_post_field('post_author', $parentID);
		NotificationsModel::add($receiverID, 'review', $reviewID, $senderID);
	}

	public function addReportNotification($listingID, $reportID){
		$receiverID = get_post_field('post_author', $listingID);
		NotificationsModel::add($receiverID, 'report', $reportID, '');
    }

    public function addClaimApproved($claimerID, $listingID){
	    NotificationsModel::add($claimerID, 'claim_approved', $listingID, '');
    }

    public function addClaimCancelled($claimerID, $listingID){
	    NotificationsModel::add($claimerID, 'claim_rejected', $listingID, '');
    }

	public function addReviewDiscussionNotification($discussionID, $reviewID){
		$receiverID = get_post_field('post_author', $reviewID);
		NotificationsModel::add($receiverID, 'review_discussion', $discussionID, User::getCurrentUserID());
	}

	public function afterSubmittingEventComment($commentID, $userID, $parentID){
		NotificationsModel::add(get_post_field('post_author', $parentID), 'comment_discussion', $commentID, $userID);
    }

	public function addSubmittedListingNotification($senderID, $listingID){
		$aSuperAdmins = get_super_admins();
		foreach ($aSuperAdmins as $admin){
		    $oUser = get_user_by('login', $admin);
			NotificationsModel::add($oUser->ID, 'submitted_listing', $listingID, $senderID);
        }
    }

	public function quickNotification(){
		if ( !is_user_logged_in() || !GetWilokeSubmission::isSystemEnable() ){
			return '';
		}
		?>
		<div id="wilcity-quick-notifications" class="header_loginItem__oVsmv">
			<quick-notifications></quick-notifications>
		</div>
		<?php
	}

	public function deleteNotification(){
		if ( !isset($_POST['ID']) || empty($_POST['ID']) ){
			wp_send_json_error(array(
				'msg' => esc_html__('The notification id is required', 'wiloke-listing-tools')
			));
		}

		$userID = get_current_user_id();
		NotificationsModel::deleteOfReceiver($_POST['ID'], $userID);
		wp_send_json_success();
	}

	public function updateNotifications($postID, $oBeforePost, $oAfterPost){
		if ( $oAfterPost->post_type !== 'review' ){
			return false;
		}


	}

	public function test(){
		$this->update(1, 1756, 'review', 'add', array());
	}

	/*
	 * @receivedID: the id of receiver
	 * @type: The type of action: comment, review, like
	 * @status: remove/update
	 * @aOtherArgs: The other information
	 */
	public function update($receivedID, $targetID, $type, $status, $aOtherInfo){

	}

	public function getAuthorInfo($postID){
		$authorID = get_post_field('post_author', $postID);
		$aData['authorName']  = get_the_author_meta('display_name', $authorID);
		$aData['authorAvatar']  = User::getAvatar($authorID);
		return $aData;
	}

	public static function getReport($oInfo){
		$postStatus = get_post_status($oInfo->objectID);
		if ( empty($postStatus) || is_wp_error($postStatus) ){
			return array(
				'content' => esc_html__('Report no longer available', 'wiloke-listing-tools'),
				'link'    => '#',
				'time' => Time::timeFromNow(strtotime($oInfo->date)),
				'type' => 'report',
				'ID'   => absint($oInfo->ID)
			);
		}else{
			$postID = GetSettings::getPostMeta($oInfo->objectID, 'listing_name');
			return array(
				'title' => esc_html__('Warning', 'wiloke-listing-tools'),
				'featuredImg'      => User::getAvatar($postID),
				'content' => __('You got a report for', 'wiloke-listing-tools'),
				'contentHighlight' => get_the_title($postID),
				'link'    => add_query_arg(
					array(
						'action' => 'edit',
						'post'   => $oInfo->objectID
					),
					admin_url('post.php')
				),
				'time' => Time::timeFromNow(strtotime($oInfo->date)),
                'type' => 'report',
                'ID'   => absint($oInfo->ID)
			);
		}
	}

	public static function getDokanOrderCompleted($oInfo){
	    $oUserAdmin = User::getFirstSuperAdmin();
		$productID = GetSettings::getFirstDokanProductByOrder( $oInfo->objectID );

		return array(
			'title'       => User::getField('display_name', $oUserAdmin->ID),
			'featuredImg' => GetSettings::getProductThumbnail($productID),
			'content' => sprintf(__('Congrats! You made a sale from %s.', 'wiloke-listing-tools'), get_the_title($oInfo->objectID)),
			'contentHighlight' => esc_html__('View Order', 'wiloke-listing-tools'),
			'link'    => function_exists('dokan_get_navigation_url') ? dokan_get_navigation_url('orders') : '#',
			'time' => Time::timeFromNow(strtotime(GetSettings::getOrderDate($oInfo->objectID))),
            'type' => 'dokan_order_completed',
			'ID'   => absint($oInfo->ID)
		);
    }

	public static function getDokanCustomerSubmittedAProductToSite($oInfo){
		$oSuperAdmin = User::getFirstSuperAdmin();
		$postAuthor = get_post_field($oInfo->objectID);

		if ( $postAuthor == $oSuperAdmin->ID ){
			return '';
		}

		return array(
			'title'       => User::getField('display_name', $postAuthor),
			'featuredImg' => User::getAvatar($postAuthor),
			'content'     => sprintf(__('just submitted a product - %s - to your site.', 'wiloke-listing-tools'), get_the_title($oInfo->objectID)),
			'contentHighlight' => esc_html__('View Detail', 'wiloke-listing-tools'),
			'link'        => add_query_arg(
                array(
                    'post' => $oInfo->objectID,
                    'action' => 'edit'
                ),
                admin_url('post.php')
            ),
			'time'        => Time::timeFromNow(strtotime(get_post_field('post_date', $oInfo->objectID))),
			'type' => 'dokan_submitted_a_product',
			'ID'   => absint($oInfo->ID)
		);
	}

	public static function getDokanApprovedWithdrawal($oInfo){
	    $oUserAdmin = User::getFirstSuperAdmin();

	    return array(
		    'title'       => User::getField('display_name', $oUserAdmin->ID),
		    'featuredImg' => User::getAvatar($oUserAdmin->ID),
		    'content'     => __('Your withdrawal has been processed.', 'wiloke-listing-tools'),
		    'contentHighlight' => esc_html__('View Detail', 'wiloke-listing-tools'),
		    'link'        => function_exists('dokan_get_navigation_url') ? dokan_get_navigation_url('withdraw') : '#',
		    'time'        => Time::timeFromNow(strtotime(GetSettings::getDokanWithDrawField('date', $oInfo->objectID))),
		    'type' => 'dokan_approved_withdrawal',
		    'ID'   => absint($oInfo->ID)
	    );
    }

    public static function getDokanCancelledWithdrawal($oInfo){
	    $oUserAdmin = User::getFirstSuperAdmin();

	    return array(
		    'title'       => User::getField('display_name', $oUserAdmin->ID),
		    'featuredImg' => User::getAvatar($oUserAdmin->ID),
		    'content'     => __('Unfortunately, Your withdrawal has been cancelled.', 'wiloke-listing-tools'),
		    'contentHighlight' => esc_html__('View Detail', 'wiloke-listing-tools'),
		    'link'        => function_exists('dokan_get_navigation_url') ? dokan_get_navigation_url('withdraw') : '#',
		    'time'        => Time::timeFromNow(strtotime(GetSettings::getDokanWithDrawField('date', $oInfo->objectID))),
		    'type' => 'dokan_cancelled_withdrawal',
		    'ID'   => absint($oInfo->ID)
	    );
    }

    public static function getDokanRequestWithdrawnal($oInfo){
	    $userID = User::dokanGetUserIDByWithDrawID($oInfo->objectID);
        $amount = GetSettings::getDokanWithDrawField('amount', $oInfo->objectID);
        $priceFormat = get_woocommerce_price_format();
        $symbol = get_woocommerce_currency_symbol();
	    $amount = sprintf($priceFormat, html_entity_decode($symbol), $amount);

	    return array(
		    'title'       => User::getField('display_name', $userID),
		    'featuredImg' => User::getAvatar($userID),
		    'content'     => sprintf(__('requested %s withdrawal.', 'wiloke-listing-tools'), $amount),
		    'contentHighlight' => esc_html__('View Detail', 'wiloke-listing-tools'),
		    'link'        => add_query_arg(
                array(
                    'page' => 'dokan#/withdraw?status=pending'
                ),
                admin_url('admin.php')
            ),
		    'time'        => Time::timeFromNow(strtotime(GetSettings::getDokanWithDrawField('date', $oInfo->objectID))),
		    'type' => 'dokan_requested_withdrawal',
		    'ID'   => absint($oInfo->ID)
	    );
    }

	public static function getDokanPublished($oInfo){
		return array(
			'title' => get_the_title($oInfo->objectID),
			'featuredImg' => GetSettings::getProductThumbnail($oInfo->objectID),
			'content' => __('is ready for sale.', 'wiloke-listing-tools'),
			'contentHighlight' => esc_html__('View Detail', 'wiloke-listing-tools'),
			'link'    => get_permalink($oInfo->objectID),
			'time' => Time::timeFromNow(strtotime(get_post_field('post_date_gmt', $oInfo->objectID)), true),
			'type' => 'dokan_product_published',
			'ID'   => absint($oInfo->ID)
		);
	}

	public static function getSubmittedListing($oInfo){
		$postStatus = get_post_status($oInfo->objectID);
		if ( empty($postStatus) || is_wp_error($postStatus) ){
			return array(
                'type'    => 'submitted_listing',
                'ID'   => absint($oInfo->ID),
                'time' => Time::timeFromNow(strtotime($oInfo->date)),
				'content' => esc_html__('Listing no longer available', 'wiloke-listing-tools'),
				'link'    => '#',
                'isNoLogger' => 'yes'
			);
		}else{
		    $authorID = get_post_field('post_author', $oInfo->objectID);

			return array(
				'title' => User::getField('display_name', $authorID),
				'featuredImg'      => User::getAvatar($authorID),
				'content' => sprintf(__('submitted a listing - %s - to your site', 'wiloke-listing-tools'), get_the_title($oInfo->objectID)),
				'contentHighlight' => get_the_title($oInfo->objectID),
				'link'    => add_query_arg(
					array(
						'action' => 'edit',
                        'post'   => $oInfo->objectID
					),
					admin_url('post.php')
				),
				'time' => Time::timeFromNow(strtotime($oInfo->date)),
				'type' => 'submitted_listing',
				'ID'   => absint($oInfo->ID)
			);
		}
	}

	public static function getClaimApproved($oInfo){
		return array(
			'title'         => User::getField('display_name', $oInfo->senderID),
			'featuredImg'   => User::getAvatar($oInfo->senderID),
			'content'       => sprintf(__('Congratulations! Your claim %s has been approved.', 'wiloke-listing-tools'), get_the_title($oInfo->objectID)),
			'link'          => get_permalink($oInfo->objectID),
			'time'          => Time::timeFromNow(strtotime($oInfo->date)),
			'type' => 'claim_approved',
			'ID'   => absint($oInfo->ID)
		);
    }

	public static function getClaimRejected($oInfo){
		return array(
			'title'             => User::getField('display_name', $oInfo->senderID),
			'featuredImg'       => User::getAvatar($oInfo->senderID),
			'content'           => sprintf(__('We are regret to inform you that your claim %s has been rejected.', 'wiloke-listing-tools'), get_the_title($oInfo->objectID)),
			'contentHighlight'  => '',
			'link'              => get_permalink($oInfo->objectID),
			'time'              => Time::timeFromNow(strtotime($oInfo->date)),
			'type' => 'claim_rejected',
			'ID'   => absint($oInfo->ID)
		);
	}

	public static function getReviewDiscussion($oInfo){
		$postStatus = get_post_status($oInfo->objectID);
		if ( empty($postStatus) || is_wp_error($postStatus) ){
			return array(
				'content' => esc_html__('Review no longer available', 'wiloke-listing-tools'),
				'link'    => '#',
				'type' => 'review_discussion',
                'objectID'    => $oInfo->objectID,
				'ID'   => absint($oInfo->ID),
				'featuredImg' => ''
			);
		}else{
			$authorID = get_post_field('post_author', $oInfo->objectID);
			$reviewID = get_post_field('post_parent', $oInfo->objectID);
			$postID   = get_post_field('post_parent', $reviewID);

			return array(
				'title' => User::getField('display_name', $authorID),
				'featuredImg'      => User::getAvatar($authorID),
				'content' => __('left a comment on', 'wiloke-listing-tools'),
				'contentHighlight' => get_the_title($reviewID),
				'link'    => add_query_arg(
					array(
						'st'  => 'wilcity-js-review-discussion-'.$oInfo->objectID,
						'tab' => 'reviews'
					),
					get_permalink($postID)
				),
				'time' => Time::timeFromNow(strtotime($oInfo->date), false),
                'type' => 'review_discussion',
                'parentID'    => $reviewID,
                'objectID'    => $oInfo->objectID,
				'ID'   => absint($oInfo->ID)
			);
		}
	}

	public static function getReview($oInfo){
		$postStatus = get_post_status($oInfo->objectID);
		if ( empty($postStatus) || is_wp_error($postStatus) ){
			return array(
				'content' => esc_html__('Review no longer available', 'wiloke-listing-tools'),
				'link'    => '#',
				'type' => 'review',
                'isNoLogger' => 'yes'
			);
		}else{
			$authorID = get_post_field('post_author', $oInfo->objectID);
			$parentID = get_post_field('post_parent', $oInfo->objectID);
			$postDateUTC = get_post_field('post_date_gmt', $oInfo->objectID);

			return array(
				'title' => User::getField('display_name', $authorID),
				'featuredImg' => User::getAvatar($authorID),
				'content' => __('left a review on', 'wiloke-listing-tools'),
				'contentHighlight' => get_the_title($parentID),
				'link'    => $postStatus == 'publish' ? add_query_arg(
					array(
						'st'  => 'wilcity-js-review-item-'.$oInfo->objectID,
						'tab' => 'reviews'
					),
					get_permalink($parentID)
				) : add_query_arg(
					array(
						'post' => $oInfo->objectID,
						'action' => 'edit'
					),
					admin_url('post.php')
				),
				'time' => Time::timeFromNow(strtotime($postDateUTC), true),
                'type' => 'review',
                'objectID' => $oInfo->objectID,
                'parentID' => $parentID,
				'ID'   => absint($oInfo->ID)
			);
		}
	}

	public static function getLike($targetID){
		return false;
	}

	public static function getEventDiscussion($oInfo){
	    $parentID = wp_get_post_parent_id($oInfo->objectID);
		return array(
			'title'             => User::getField('display_name', $oInfo->senderID),
			'featuredImg'       => User::getAvatar($oInfo->senderID),
			'content'           => __('left a comment on', 'wiloke-listing-tools'),
			'contentHighlight'  => get_the_title($parentID),
			'link'              => get_permalink($parentID),
			'time'              => Time::timeFromNow(strtotime($oInfo->date)),
			'type'              => 'comment_discussion',
            'ID'                => absint($oInfo->ID),
			'objectID'          => absint($oInfo->objectID),
            'senderID'          => $oInfo->senderID
		);
    }
	
	public static function getNotificationType($oInfo){
		$notification = '';
		switch ($oInfo->type){
			case 'review':
				$notification = self::getReview($oInfo);
				break;
			case 'review_discussion':
				$notification = self::getReviewDiscussion($oInfo);
				break;
			case 'report':
				$notification = self::getReport($oInfo);
				break;
			case 'claim_approved':
				$notification = self::getClaimApproved($oInfo);
				break;
			case 'claim_rejected':
				$notification = self::getClaimRejected($oInfo);
				break;
			case 'submitted_listing':
				$notification = self::getSubmittedListing($oInfo);
				break;
			case 'dokan_product_published':
				$notification = self::getDokanPublished($oInfo);
				break;
			case 'dokan_order_completed':
				$notification = self::getDokanOrderCompleted($oInfo);
				break;
			case 'dokan_requested_withdrawal':
				$notification = self::getDokanRequestWithdrawnal($oInfo);
				break;
			case 'dokan_approved_withdrawal':
				$notification = self::getDokanApprovedWithdrawal($oInfo);
				break;
			case 'dokan_cancelled_withdrawal':
				$notification = self::getDokanCancelledWithdrawal($oInfo);
				break;
			case 'dokan_submitted_a_product':
				$notification = self::getDokanCustomerSubmittedAProductToSite($oInfo);
				break;
			case 'like':
				$notification = self::getLike($oInfo);
				break;
            case 'comment_discussion':
	            $notification = self::getEventDiscussion($oInfo);
                break;
		}
		return $notification;
    }

	public function fetchNotifications(){
		$userID = get_current_user_id();
		$this->middleware(['isUserLoggedIn'], array());
		$errMsg = esc_html__('No Notifications', 'wiloke-listing-tools');

		$limit = isset($_POST['limit']) || $_POST['limit'] > 100 ? abs($_POST['limit']) : 20;
		$paged = isset($_POST['paged']) ? abs($_POST['paged']) : 1;
		$offset = ($paged-1)*$limit;

		$aNotifications = NotificationsModel::get($userID, $limit, $offset);

		if ( !$aNotifications ){
			wp_send_json_error(array(
				'msg' => $errMsg
			));
		}else{
			$aNotificationsInfo = array();
			foreach ($aNotifications['aResults'] as $oInfo){
				$notification = self::getNotificationType($oInfo);

				if ( $notification ){
					$notification['ID']     = $oInfo->ID;
					$aNotificationsInfo[]   = $notification;
				}
			}

			if ( empty($aNotificationsInfo) ){
				if ( !isset($_POST['paged']) ){
					wp_send_json_error(array(
						'msg' => $errMsg
					));
				}else{
					wp_send_json_success(array(
						'isFinished' => true
					));
				}
			}

			$dashboardUrl = '';
			if ( isset($_POST['needDashboardUrl']) && $_POST['needDashboardUrl'] == 'yes' ){
			    $dashboardUrl = GetWilokeSubmission::getField('dashboard_page', true);
				$dashboardUrl .= '#/notifications';
            }

			wp_send_json_success(
				array(
					'oInfo'         => $aNotificationsInfo,
					'dashboardUrl'  => $dashboardUrl,
					'maxPages'      => ceil($aNotifications['total']/$limit)
				)
			);
		}
	}
}