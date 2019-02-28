<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Models\BookingCom;

class BookingComController extends Controller {
	public function __construct() {
		add_action('wilcity/deleted/listing', array($this, 'deleteRelatedBookingComCreator'));
		add_action('after_delete_post', array($this, 'updateBookingComCreatorDependsOnListingStatus'), 10, 1);
	}

	public function updateBookingComCreatorDependsOnListingStatus($postID){
		$aPostTypes = General::getPostTypeKeys(false, false);
		$postStatus = get_post_status($postID);

		if ( !in_array($postStatus, $aPostTypes) ){
			return false;
		}
		$bookingID = BookingCom::getCreatorIDByParentID($postID);

		if ( empty($bookingID) ){
			return false;
		}

		wp_delete_post($bookingID, true);
	}

	public function deleteRelatedBookingComCreator($listingID){
		$bookingID = BookingCom::getCreatorIDByParentID($listingID);
		if ( !empty($bookingID) ){
			wp_delete_post($bookingID, true);
		}
	}
}