<?php

namespace WilokeListingTools\Controllers;


use WILCITY_SC\SCHelpers;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;

class GridItemController {
	protected $aCacheGridItemSettings = array();

	public function __construct() {
		add_action('wilcity/listing-slider/meta-data', array($this, 'gridItemMetaData'));
		add_filter('wilcity/filter-listing-slider/meta-data', array($this, 'aListingBody'), 10, 2);
	}

	protected function getSettings($post){
		if ( isset($this->aCacheGridItemSettings[$post->post_type]) ){
			$aSettings = $this->aCacheGridItemSettings[$post->post_type];
		}else{
			$aSettings = GetSettings::getOptions(General::getSingleListingSettingKey('card', $post->post_type));
			$this->aCacheGridItemSettings[$post->post_type] = $aSettings;
		}
		return $aSettings;
	}

	public function aListingBody($aListing, $post){
		$aSettings = $this->getSettings($post);

		if ( empty($aSettings) ){
			return $aListing;
		}else{
			foreach ($aSettings as $aItem){
				switch ($aItem['type']){
					case 'google_address':
						$address = SCHelpers::renderAddress($post, array(), true);
						if ( !empty($address) ){
							$aListing['oBody']['google_address'] = SCHelpers::renderAddress($post, array(), true);
						}
						break;
					case 'phone':
						$aPhone = SCHelpers::renderPhone($post, array(), true);
						if ( !empty($aPhone['value']) ){
							$aListing['oBody']['phone'] = $aPhone;
						}
						break;
					case 'email':
						$aEmail = SCHelpers::renderEmail($post, array(), true);
						if ( !empty($aEmail['value']) ){
							$aListing['oBody']['email'] = $aEmail;
						}
						break;
					case 'website':
						$aWebsite = SCHelpers::renderWebsite($post, array(), true);
						if ( !empty($aWebsite['value']) ){
							$aListing['oBody']['website'] = $aWebsite;
						}
						break;
					case 'price_range':
						$aPriceRange = SCHelpers::renderPriceRange($post, array(), true);

						if ( !empty($aPriceRange) && !empty($aPriceRange['value']['minimumPrice']) && !empty($aPriceRange['value']['maximumPrice']) && $aPriceRange['value']['mode'] != 'nottosay' ){
							$aListing['oBody']['price_range'] = $aPriceRange;
						}
						break;
					case 'single_price':
						$aSinglePrice = SCHelpers::renderSinglePrice($post, array(), true);
						if ( !empty($aSinglePrice) ){
							$aListing['oBody']['single_price'] = $aSinglePrice;
						}
						break;
					case 'custom_field':
						ob_start();
						SCHelpers::renderCustomField($post, $aItem);
						$customField = ob_get_contents();
						ob_end_clean();

						if ( !empty($customField) ){
							$aListing['oBody']['custom_field'][$aItem['key']] = $customField;
						}
						break;
				}
			}
		}
		return $aListing;
	}

	public function gridItemMetaData($post){
		$aSettings = $this->getSettings($post);

		if ( empty($aSettings) ){
			SCHelpers::renderAddress($post);
			SCHelpers::renderPhone($post);
		}else{
			foreach ($aSettings as $aItem){
				switch ($aItem['type']){
					case 'google_address':
						SCHelpers::renderAddress($post, $aItem);
						break;
					case 'phone':
						SCHelpers::renderPhone($post, $aItem);
						break;
					case 'email':
						SCHelpers::renderEmail($post, $aItem);
						break;
					case 'website':
						SCHelpers::renderWebsite($post, $aItem);
						break;
					case 'price_range':
						SCHelpers::renderPriceRange($post, $aItem);
						break;
					case 'single_price':
						SCHelpers::renderSinglePrice($post, $aItem);
						break;
					case 'custom_field':
						SCHelpers::renderCustomField($post, $aItem);
						break;
				}
			}
		}
	}
}