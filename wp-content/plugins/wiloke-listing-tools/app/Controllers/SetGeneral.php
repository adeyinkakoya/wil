<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\SetSettings;

trait SetGeneral {
	protected function getTimeZoneByGeocode(){
		global $wiloke;
		$url = 'https://maps.googleapis.com/maps/api/timezone/json?location='.$this->aGoogleAddress['latLng'].'&timestamp='.time().'&key='.$wiloke->aThemeOptions['general_google_api'];
		$aTimeZone = wp_remote_get(esc_url_raw($url));
		if ( is_wp_error($aTimeZone)  ){
			return '';
		}else{
			$oTimeZone = json_decode($aTimeZone['body']);
			return isset($oTimeZone->timeZoneId) ? $oTimeZone->timeZoneId : '';
		}
	}

	protected function setListingTimeZone(){
		$timeZone = $this->getTimeZoneByGeocode();
		SetSettings::setPostMeta($this->listingID, 'timezone', $timeZone);
	}

	protected function setGeneralSettings(){
		$this->setListingTimeZone();
		foreach ( $this->aGeneralData as $fieldKey => $val ) {
			if ( $fieldKey == 'cover_image' || $fieldKey == 'logo' ){
				SetSettings::setPostMeta($this->listingID, $fieldKey, $val['src']);
				SetSettings::setPostMeta($this->listingID, $fieldKey.'_id', $val['id']);
			}else{
				SetSettings::setPostMeta($this->listingID, $fieldKey, $val);
			}
		}
	}
}