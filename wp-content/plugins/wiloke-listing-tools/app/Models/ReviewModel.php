<?php

namespace WilokeListingTools\Models;


use WilokeListingTools\AlterTable\AlterTableReviewMeta;
use WilokeListingTools\AlterTable\AlterTableReviews;
use WilokeListingTools\Framework\Helpers\DebugStatus;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;

class ReviewModel {
	public static $postType = 'review';
	public static $aPostsOfUsers = array();

	public static function getPostsOfUser($userID){
		if ( isset(self::$aPostsOfUsers[$userID]) ){
			return self::$aPostsOfUsers[$userID];
		}

		global $wpdb;
		$postsTbl = $wpdb->posts;
		$aPostTypes = General::getPostTypeKeys(false, false);

		$aPostTypes = array_map(
			function($type){
				global $wpdb;
				return $wpdb->_real_escape($type);
			},
			$aPostTypes
		);

		$aRawPostParentIDs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID FROM $postsTbl WHERE $postsTbl.post_status=%s AND $postsTbl.post_type IN ('".implode("','", $aPostTypes)."') AND $postsTbl.post_author=%d",
				'publish', $userID
			),
			ARRAY_A
		);

		if ( empty($aRawPostParentIDs) ){
			self::$aPostsOfUsers[$userID] = false;
			return false;
		}

		$aPostParentIDs = array_map(function($aPost){
			return $aPost['ID'];
		}, $aRawPostParentIDs);

		self::$aPostsOfUsers[$userID] = $aPostParentIDs;
		return $aPostParentIDs;
	}


	public static function countAllReviewed(){
		global $wpdb;
		$postsTbl = $wpdb->posts;
		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT($postsTbl.ID) FROM $postsTbl WHERE $postsTbl.post_type=%s AND $postsTbl.post_status='publish'",
				'review'
			)
		);
		return abs($value);
	}

	public static function countAllReviewedOfListing($postID){
		global $wpdb;
		$postsTbl = $wpdb->posts;
		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT($postsTbl.ID) FROM $postsTbl WHERE $postsTbl.post_type=%d AND $postsTbl.post_parent=%d AND $postsTbl.post_status='publish'",
				'review', $postID
			)
		);
		return abs($value);
	}

	public static function getAuthorAverageRatings($userID){
		global $wpdb;
		$postsTbl = $wpdb->posts;
		$postMetaTbl = $wpdb->postmeta;

		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT AVG($postMetaTbl.meta_value) FROM $postMetaTbl LEFT JOIN $postsTbl ON ($postMetaTbl.post_id = $postsTbl.ID) WHERE $postsTbl.post_author=%d AND $postMetaTbl.meta_key=%s",
				$userID, 'wilcity_average_reviews'
			)
		);
		return abs($value);
	}

	public static function getAuthorAverageRatingsInRange($userID, $start, $end){
		global $wpdb;
		$postsTbl = $wpdb->posts;
		$reviewMeta = $wpdb->prefix . AlterTableReviewMeta::$tblName;

		$aPostParentIDs = self::getPostsOfUser($userID);
		if ( empty($aPostParentIDs) ){
			return 0;
		}

		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT AVG($reviewMeta.meta_value) FROM $reviewMeta LEFT JOIN $postsTbl ON ($reviewMeta.reviewID = $postsTbl.ID) WHERE $postsTbl.post_parent IN (".implode(',', $aPostParentIDs).") AND $postsTbl.post_status=%s AND $postsTbl.post_type=%s AND $reviewMeta.date BETWEEN %s AND %s",
				'publish', 'review', $start, $end
			)
		);
		return abs($value);
	}

	public static function getAuthorAverageRatingsInDay($userID, $day){
		global $wpdb;
		$postsTbl = $wpdb->posts;
		$reviewMeta = $wpdb->prefix . AlterTableReviewMeta::$tblName;

		$aPostParentIDs = self::getPostsOfUser($userID);
		if ( empty($aPostParentIDs) ){
			return 0;
		}

		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT AVG($reviewMeta.meta_value) FROM $reviewMeta LEFT JOIN $postsTbl ON ($reviewMeta.reviewID = $postsTbl.ID) WHERE $postsTbl.post_parent IN (".implode(',', $aPostParentIDs).") AND $postsTbl.post_status=%s AND $postsTbl.post_type=%s AND $reviewMeta.date=%s",
				'publish', 'review', $day
			)
		);

		return abs($value);
	}

	public static function userWroteReviewBefore($postID, $userID){
		global $wpdb;
		$reviewTbl = $wpdb->prefix . AlterTableReviews::$tblName;

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $reviewTbl WHERE objectID=%d AND userID=%d",
				$postID, $userID
			)
		);
	}

	public static function isReviewExist($reviewID){
		global $wpdb;
		$reviewTbl = $wpdb->prefix . AlterTableReviews::$tblName;

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $reviewTbl WHERE ID=%d",
				$reviewID
			)
		);
	}

	public static function patchReview($id, $postID, $userID, $title, $content, $parentID=null){
		global $wpdb;
		$reviewTbl = $wpdb->prefix . AlterTableReviews::$tblName;

		return $wpdb->update(
			$reviewTbl,
			array(
				'objectID' => $postID,
				'userID'   => $userID,
				'title'    => $title,
				'content'  => $content,
				'parentID' => $parentID
			),
			array(
				'ID' => $id
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%d'
			),
			array(
				'%d'
			)
		);
	}

	/*
	 * @param int $postID
	 * @param int $userID
	 * @param longtext $content
	 * @param int $parentID
	 */
	public static function setReview($postID, $userID, $title, $content, $parentID=null){
		global $wpdb;
		$reviewTbl = $wpdb->prefix . AlterTableReviews::$tblName;
		$reviewID  = self::userWroteReviewBefore($postID, $userID);
		if ( $reviewID ){
			self::patchReview($reviewID, $postID, $userID, $title, $content, $parentID=null);
			return $reviewID;
		}else{
			$status = $wpdb->insert(
				$reviewTbl,
				array(
					'objectID' => $postID,
					'userID'   => $userID,
					'title'    => $title,
					'content'  => $content,
					'parentID' => $parentID
				),
				array(
					'%d',
					'%d',
					'%s',
					'%s',
					'%d'
				)
			);

			return $status ? $wpdb->insert_id : false;
		}
	}

	public static function getReviewID($postID, $userID=null){
		global $wpdb;
		$reviewTbl = $wpdb->prefix . AlterTableReviews::$tblName;

		$userID = empty($userID) ? get_current_user_id() : $userID;

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $reviewTbl WHERE  objectID=%d and userID=%d",
				$postID, $userID
			)
		);
	}

	public static function getReviewMode($postType){
		$mode = GetSettings::getOptions(General::getReviewKey('mode', $postType));
		return empty($mode) ? 5 :absint($mode);
	}

	public static function getReview($postID, $userID=null){
		global $wpdb;
		$reviewTbl = $wpdb->prefix . AlterTableReviews::$tblName;
		$userID = empty($userID) ? get_current_user_id() : $userID;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $reviewTbl WHERE objectID=%d AND userID=%d AND  parentID IS NULL ORDER BY ID DESC",
				$postID, $userID
			),
			ARRAY_A
		);
	}

	public static function isEnabledReview($postID, $postType=''){
		if( empty($postType) ){
			$postType = get_post_type($postID);
		}
		$toggleReview = GetSettings::getOptions(General::getReviewKey('toggle', $postType));
		return $toggleReview == 'enable';
	}

	public static function isUserReviewed($postID, $userID=null){
		if ( DebugStatus::status('WILOKE_TESTING_REVIEW') ){
			return false;
		}

		if ( empty($userID) ){
			$userID = get_current_user_id();
		}

		global $wpdb;
		$reviewTbl = $wpdb->posts;

		$status = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $reviewTbl WHERE post_type=%s AND post_parent=%d AND post_author=%d",
				self::$postType, $postID, $userID
			)
		);

		return empty($status) ? false : true;
	}

	public static function getReviews($parentID, $aArgs){
		$numberOfReviews = isset($aArgs['postsPerPage']) ? $aArgs['postsPerPage'] : 3;
		$aArgs['page'] = isset($aArgs['page']) ? $aArgs['page'] : 1;

		$postType = get_post_type($parentID) == 'event_comment' ? 'event_comment' : 'review';

		$query = new \WP_Query(array(
			'post_parent' => $parentID,
			'post_type'   => $postType,
			'post_status' => 'publish',
			'orderby'     => 'menu_order post_date',
			'posts_per_page' => $numberOfReviews,
			'paged' => $aArgs['page']
		));

		if ( !$query->have_posts() ){
			wp_reset_postdata();
			return false;
		}

		return $query;
	}

	/*
	 * @postID: int this is listing ID
	 */
	public static function countTotalReviews($postID){
		global $wpdb;
		$reviewTbl = $wpdb->posts;
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT($reviewTbl.ID) FROM $reviewTbl WHERE post_parent=%d AND post_status='publish' AND post_type=%s",
				$postID, 'review'
			)
		);
	}

	public static function countDiscussion($reviewID){
		global $wpdb;
		$postTbl = $wpdb->posts;

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT($postTbl.ID) FROM $postTbl WHERE post_parent=%d AND post_status='publish' AND post_type='review'",
				$reviewID
			)
		);
	}

	/*
	 * @param int $reviewID
	 */
	public static function getDiscussions($reviewID, $numberOfChild=null){
		return self::getReviews($reviewID, array('postsPerPage'=>$numberOfChild));
	}

	public static function deleteDiscussion($reviewID, $userID=null){
		global $wpdb;
		$reviewTbl = $wpdb->prefix . AlterTableReviews::$tblName;
		$userID = empty($userID) ? get_current_user_id() : $userID;

		return $wpdb->delete(
			$reviewTbl,
			array(
				'ID'        => $reviewID,
				'userID'    => $userID
			),
			array(
				'%d',
				'%d'
			)
		);
	}

	/*
	 * @param int $parentID
	 * @param int $objectID => Or Listing ID
	 * @param string $content
	 * @param string $reviewPostType
	 */
	public static function setDiscussion($parentID, $reviewPostType, $content){
		$postID = wp_insert_post(
			array(
				'post_type'     => $reviewPostType,
				'post_content'  => $content,
				'post_title'    => esc_html__('Discussion of ') . get_the_title($parentID),
				'post_parent'   => $parentID,
				'post_status'   => 'publish'
			)
		);

		return $postID;
	}

	/*
	 * @param int $discussionID
	 * @param string $content
	 */
	public static function patchDiscussion($discussionID, $content){
		global $wpdb;
		$reviewTbl = $wpdb->prefix . AlterTableReviews::$tblName;

		return $wpdb->update(
			$reviewTbl,
			array(
				'content'   => $content
			),
			array(
				'ID'        => $discussionID,
				'userID'    => get_current_user_id()
			),
			array(
				'%s'
			),
			array(
				'%d',
				'%d'
			)
		);
	}

	public static function hasDiscussion($reviewID){
		global $wpdb;
		$status = $wpdb->get_var($wpdb->prepare(
			"SELECT ID FROM $wpdb->posts WHERE post_parent=%d AND post_status='publish' AND post_type='review'",
			$reviewID
		));

		if ( $status ){
			return 'yes';
		}

		return 'no';
	}
}