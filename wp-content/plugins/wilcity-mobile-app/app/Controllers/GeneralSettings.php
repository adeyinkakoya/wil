<?php

namespace WILCITY_APP\Controllers;


use WilokeListingTools\Controllers\RegisterLoginController;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Frontend\User;

class GeneralSettings {
	public function __construct() {
		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', 'general-settings', array(
				'methods'   => 'GET',
				'callback'  => array($this, 'getColorPrimary')
			));
		});
	}

	public function getColorPrimary(){
		$aThemeOptions = \Wiloke::getThemeOptions(true);
		$themeColor = $aThemeOptions['advanced_main_color'];
		if ( $themeColor == 'custom' ){
			if ( isset($aThemeOptions['advanced_custom_main_color']['rgba']) ){
				$themeColor = $aThemeOptions['advanced_custom_main_color']['rgba'];
			}
		}

		$googleAPI = isset($aThemeOptions['general_google_api']) && !empty($aThemeOptions['general_google_api']) ? $aThemeOptions['general_google_api'] : '';
		$googleLang = isset($aThemeOptions['general_google_language']) && !empty($aThemeOptions['general_google_language']) ? $aThemeOptions['general_google_language'] : '';

		if ( !isset($aThemeOptions['content_position']) ){
			$contentPosition = 'above_sidebar';
		}else{
			$contentPosition = $aThemeOptions['content_position'];
		}

		return array(
			'colorPrimary' => $themeColor,
			'oGoogleMapAPI'=> array(
				'key'       => $googleAPI,
				'language'  => $googleLang,
				'types'     => 'geocode'
			),
			'oSingleListing' => array(
				'contentPosition' => $contentPosition
			),
			'isAllowRegistering' => RegisterLoginController::canRegister() ? 'yes' : 'no'
		);
	}
}