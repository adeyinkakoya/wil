<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\MetaBoxes\Listing;

trait InsertAddress {
	protected function insertAddress(){
		if ( empty($this->aGoogleAddress) ){
			return false;
		}

		$aParseLatLng = explode(',', $this->aGoogleAddress['latLng']);

		Listing::saveData($this->listingID, array(
			'lat'        => $aParseLatLng[0],
			'lng'        => $aParseLatLng[1],
			'address'    => $this->aGoogleAddress['address']
		));
	}
}