<?php

namespace WILCITY_APP\Controllers;


use WilokeListingTools\Controllers\ReviewController;
use WilokeListingTools\Controllers\ShareController;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Frontend\User;


class Review extends JsonSkeleton {
	use VerifyToken;

	public function __construct() {
		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', '/event-detail/(?P<eventID>\w+)/discussions', array(
				'methods' => 'GET',
				'callback' => array($this, 'getEventDiscussions'),
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX . '/v2', '/post-review', array(
				'methods'  => 'POST',
				'callback' => array( $this, 'postReview' )
			) );
		} );
	}

	public function postReview(){
		$oToken = $this->verifyToken();
		if ( !$oToken ){
			return $this->tokenExpiration();
		}
		$oToken->getPayLoad();

		if ( !$this->oPayLoad ){
			return array(
				'status' => 'error',
				'msg'    => 403
			);
		}

		return array(
			'status' => 'success',
			'msg' => 'success'
		);
	}

	public function getEventDiscussions($aData){
		if ( !isset($aData['eventID']) || empty($aData['eventID']) ){
			return array(
				'status' => 'error',
				'msg'    => esc_html__('There are no discussions', 'wilcity-mobile-app')
			);
		}
		$eventID = abs($aData['eventID']);
		$paged = isset($aData['page']) ? abs($aData['page']) : 1;
		$postsPerPage = isset($aData['postsPerPage']) ? abs($aData['postsPerPage']) : 6;

		$query = new \WP_Query(
			$aArgs = array(
				'post_type'     => 'event_comment',
				'post_status'   => 'publish',
				'post_parent'   => $eventID,
				'paged'         => $paged,
				'posts_per_page'=> $postsPerPage
			)
		);

		if ( !$query->have_posts() ){
			return array(
				'status' => 'error',
				'msg'    => $paged > 1 ? esc_html__('All discussions have been loaded', 'wilcity-listing-tools') : esc_html__('We found no discussions', 'wilcity-listing-tools')
			);
		}
		$aComments = array();

		while ($query->have_posts()){
			$query->the_post();
			$aComments[] = $this->eventCommentItem($query->post);
		}
		wp_reset_postdata();

		$basedOnPostType = get_post_field('post_type', $eventID);
		if ( $basedOnPostType  == 'event_comment' ){
			$authorID = get_post_field('post_author', $eventID);
			$displayName = User::getField('display_name', $authorID);
			$repliedOn = sprintf(esc_html__('Replied on %s discussion', 'wiloke-mobile-app'), $displayName);
		}else{
			$title = get_the_title($eventID);
			$repliedOn = sprintf(esc_html__('All discussions on %s', 'wiloke-mobile-app'), $title);
		}

		return array(
			'status'           => 'success',
			'discussionsOn'    => $repliedOn,
			'countDiscussions' => GetSettings::countNumberOfChildrenReviews($eventID),
			'next'             => $query->max_num_pages > $paged ? $paged+1 : false,
			'oResults'         => $aComments
		);
	}
}