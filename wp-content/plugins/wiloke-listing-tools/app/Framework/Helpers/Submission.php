<?php

namespace WilokeListingTools\Framework\Helpers;


class Submission {
	protected static $aSupportedPostTypes;
	public static function detectPostStatus(){
		if ( GetWilokeSubmission::getField('approved_method') == 'manual_review' ){
			$newStatus = 'pending';
		}else{
			$newStatus = 'publish';
		}

		return $newStatus;
	}

	public static function getSupportedPostTypes(){
		if ( !empty(self::$aSupportedPostTypes) ){
			return self::$aSupportedPostTypes;
		}
		$aPostTypesInfo = GetSettings::getOptions(wilokeListingToolsRepository()->get('addlisting:customPostTypesKey'));

		if ( empty($aPostTypesInfo) ){
			return false;
		}

		$aEventGeneralSetting = GetSettings::getOptions(wilokeListingToolsRepository()->get('event-settings:keys', true)->sub('general'));

		if ( !isset($aEventGeneralSetting['toggle_event']) || $aEventGeneralSetting['toggle_event'] == 'disable' ){

			$aPostTypesInfo = array_filter($aPostTypesInfo, function($aPostType){
				if ( $aPostType['key'] == 'event' ){
					return false;
				}
				return true;
			});
		}

		$aPostTypesInfo = array_filter($aPostTypesInfo, function($aPostType){
			if ( isset($aPostType['isDisableOnFrontend']) && $aPostType['isDisableOnFrontend'] == 'yes' ){
				return false;
			}

			return true;
		});

		self::$aSupportedPostTypes = array_map(function($aData){
			return $aData['key'];
		}, $aPostTypesInfo);


		return self::$aSupportedPostTypes;
	}
}