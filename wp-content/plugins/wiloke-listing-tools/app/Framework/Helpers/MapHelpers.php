<?php

namespace WilokeListingTools\Framework\Helpers;


class MapHelpers {
	public static function getMapMarker($postID){
		$oTerm = \WilokeHelpers::getTermByPostID($postID, 'listing_cat');
		if ( !$oTerm ){
			return false;
		}
		return GetSettings::getTermMeta($oTerm->term_id, 'icon_img');
	}
}