<?php

namespace WilokeListingTools\Controllers;
//jY9mDe9iOBN0

use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Store\Session;
use WilokeListingTools\Register\WilokeSubmissionConfiguration;

trait PrintAddListingSettings {
	protected function removeAllUncheckedSection($aSections){
		global $wiloke;
		if ( !isset($wiloke->aThemeOptions['addlisting_unchecked_features_type']) || $wiloke->aThemeOptions['addlisting_unchecked_features_type'] == 'disable' ){
			return $aSections;
		}

		foreach ($aSections as $sectionKey => $aSection){
			if ( isset($this->aPlanSettings['toggle_'.$aSection['key']]) && $this->aPlanSettings['toggle_'.$aSection['key']] == 'disable' ){
				unset($aSections[$sectionKey]);
			}else if ( isset($aSection['fields']) ){
				foreach ($aSection['fields'] as $fieldKey => $aField){
					if ( isset($aField['toggle']) && $aField['toggle'] == 'disable' ){
						unset($aSections[$sectionKey]['fields'][$fieldKey]);
					}else if ( isset($aField['isRequired']) && $aField['isRequired'] == 'no' ){
						unset($aSections[$sectionKey]['fields'][$fieldKey]['isRequired']);
					}
				}
			}
		}

		return $aSections;
	}

	protected function excludeSocialNetworks(){
		if ( isset($_GET['listing_type']) && !empty($_GET['listing_type']) ){
//			$aListingSettings = GetSettings::getOptions();
			$aListingSettings = GetSettings::getOptions(General::getUsedSectionKey($_GET['listing_type']));
			echo '<pre>';
			var_export($aListingSettings);
			echo '</pre>';
			die();
		}
	}

	public function printAddListingSettings(){
		global $post;
		$this->listingID = isset($_REQUEST['postID']) && !empty($_REQUEST['postID']) ? absint($_REQUEST['postID']) : '';
		if ( !empty($this->listingID) ){
			$listingType = get_post_type($this->listingID);
		}else{
			$listingType = isset($_REQUEST['listing_type']) ? esc_js($_REQUEST['listing_type']) : 'listing';
		}
		$aInfo = array('listingType'=>$listingType);

		if ( isset($_REQUEST['planID']) && !empty($_REQUEST['planID']) ){
			$planID = $_REQUEST['planID'];
			$this->aPlanSettings = GetSettings::getPostMeta($planID, 'add_listing_plan');
		}else{
			$planID = '';
			$this->aPlanSettings = array();

			if ( GetWilokeSubmission::isFreeAddListing() ){
				$aPlans = GetWilokeSubmission::getAddListingPlans($listingType.'_plans');
				if ( is_array($aPlans) ){
					$planID = end($aPlans);
					$this->aPlanSettings = GetSettings::getPostMeta($planID, 'add_listing_plan');
				}
			}
		}

		$addListingPageID = GetWilokeSubmission::getField('addlisting');

		if ( isset($post->ID) && (GetWilokeSubmission::getField('addlisting') == $post->ID || General::wpmlIsLangDuplicate($post->ID, $addListingPageID)) ){
			$this->aSections = $this->getAvailableFields();
			$this->aSections = $this->removeAllUncheckedSection($this->aSections);
			$this->mergeSettingValues();

			$aInfo = array_merge($aInfo, array(
				'aUsedSections'  => $this->aSections,
				'oSocialNetworks'=> class_exists('\WilokeSocialNetworks') ? \WilokeSocialNetworks::$aSocialNetworks : array(),
				'aAllSections'      => wilokeListingToolsRepository()->get('settings:allSections'),
				'aPriceRange'       => wilokeListingToolsRepository()->get('addlisting:aPriceRange'),
				'oDayOfWeek'        => wilokeListingToolsRepository()->get('general:aDayOfWeek'),
				'oTimeRange'        => wilokeListingToolsRepository()->get('addlisting:aTimeRange'),
				'oTimeFormats'      => array(
					array(
						'value' => 12,
						'name'  => esc_html__('12-Hour Format', 'wiloke-listing-tools'),
					),
					array(
						'value' => 24,
						'name'  => esc_html__('24-Hour Format', 'wiloke-listing-tools'),
					)
				),
				'listingID'        => $this->listingID,
				'aPlanSettings'    => $this->aPlanSettings,
				'planID'           => $planID,
				'oBusinessHours'   => General::generateBusinessHours(),
			));
		}

		wp_localize_script('jquery-migrate', 'WILCITY_ADDLISTING', $aInfo);
	}
}