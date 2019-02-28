<?php

namespace WILCITY_APP\Controllers;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;

class SearchField {
	public function __construct() {
		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', '/search-fields/(?P<postType>\w+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'getFields'),
			));
		} );
	}

	public function getFields($aData){
		$postType = !isset($aData['postType']) ? 'listing' : sanitize_text_field($aData['postType']);

		if ( empty($postType) ){
			$postType = General::getDefaultPostTypeKey(false);
		}

		$aRawSearchFields  = GetSettings::getOptions(General::getSearchFieldsKey($postType));

		if ( empty($aRawSearchFields) ){
			return array(
				'status'    => 'error'
			);
		}

		$aSearchFields = array();
		foreach ($aRawSearchFields as $key => $aField){
			$aSearchFields[$key] = $aField;
			$aSearchFields[$key]['key']  = $aField['key'];
			$aSearchFields[$key]['name']  = $aField['label'];
			$aSearchFields[$key]['value'] = '';

			if ( isset($aField['isDefault']) ){
				if ( $aField['isDefault'] == 'true' ){
					$aSearchFields[$key]['isDefault'] = true;
				}else if ( $aField['isDefault'] == 'false' ){
					$aSearchFields[$key]['isDefault'] = false;
				}
			}

			switch ($aField['type']){
				case 'select2':
				case 'select':
				case 'checkbox2':
					$aSearchFields[$key]['type'] = 'select';
					if ( $aField['type'] == 'checkbox2' ){
						$aSearchFields[$key]['isMultiple'] = 'yes';
					}else{
						if ( isset($aField['isMultiple']) && ($aField['isMultiple']=='yes') ){
							$aSearchFields[$key]['isMultiple'] = 'yes';
						}else{
							$aSearchFields[$key]['isMultiple'] = 'no';
						}
					}
					if ( !isset($aField['isAjax']) || ($aField['isAjax'] == 'no') ){
						if ( in_array($aField['key'], array('listing_location', 'listing_cat', 'listing_tag')) ){
							$isParentOnly = isset($aField['isShowParentOnly']) && $aField['isShowParentOnly'] == 'yes';

							$aRawTerms = GetSettings::getTaxonomyHierarchy(array(
								'taxonomy' => $aField['key'],
								'orderby'  => isset($aField['orderBy']) ? $aField['orderBy'] : 'count',
								'parent'   => 0
							), $postType, $isParentOnly, false);

							if ( empty($aRawTerms) || is_wp_error($aRawTerms) ){
								$aSearchFields[$key]['options'] = array(
									array(
										'name' => esc_html__('No categories', 'wiloke-mobile-app'),
										'id'   => -1
									)
								);
							}else{
								$aTerms = array();
								foreach ($aRawTerms as $oTerm){
									$aTerms[] = array(
										'name'      => $oTerm->name,
										'id'        => $oTerm->term_id,
										'slug'      => $oTerm->slug,
										'selected'  => false,
										'count'     => GetSettings::getTermCountInPostType($postType, $aField['key'])
									);
								}
								$aSearchFields[$key]['options'] = $aTerms;
							}
						}
						$aSearchFields[$key]['isAjax'] = 'no';
					}else{
						$aSearchFields[$key]['isAjax'] = 'yes';
						$aSearchFields[$key]['ajaxAction'] = $aField['ajaxAction'];
					}
					switch ($aField['key']){
						case 'price_range':
							$aRawPriceRange = wilokeListingToolsRepository()->get('general:priceRange');
							$aRawPriceRange['nottosay'] = esc_html__('Not to say', 'wilcity-mobile-app');
							$aRawPriceRange['cheap'] = esc_html__('Cheap', 'wilcity-mobile-app');
							$aRawPriceRange['moderate'] = esc_html__('Moderate', 'wilcity-mobile-app');
							$aRawPriceRange['expensive'] = esc_html__('Expensive', 'wilcity-mobile-app');
							$aRawPriceRange['ultra_high'] = esc_html__('Ultra High', 'wilcity-mobile-app');

							$aPriceRange = array();
							foreach ($aRawPriceRange as $priceKey => $priceDesc){
								$aPriceRange[] = array(
									'name' => $priceDesc,
									'id'   => $priceKey,
									'selected' => $priceKey == 'nottosay' ? true : false
								);
							}

							$aSearchFields[$key]['options'] = $aPriceRange;
							break;
						case 'post_type':
							$aSearchFields[$key]['key'] = 'postType';
							$aRawPostTypes = General::getPostTypes(false, false);
							$aPostTypes = array();
							$order = 1;
							foreach ($aRawPostTypes as $type => $aSettings){
								$aPostTypes[] = array(
									'name' => $aSettings['name'],
									'id'   => $type,
									'selected' => $order === 1 ? true : false
								);
								$order++;
							}
							$aSearchFields[$key]['options'] = $aPostTypes;
							break;
					}
					break;
				case 'autocomplete':
					$aSearchFields[$key]['type'] = 'google_auto_complete';
					$aSearchFields[$key]['maxRadius'] = abs($aField['maxRadius']);
					$aSearchFields[$key]['defaultRadius'] = abs($aField['defaultRadius']);
					break;
				case 'checkbox':
					$aSearchFields[$key]['type'] = 'checkbox';
					break;
				case 'wp_search':
					$aSearchFields[$key]['type'] = 'input';
					$aSearchFields[$key]['key'] = 's';
					break;
			}
		}

		return array(
			'status'    => 'success',
			'oResults'  => $aSearchFields
		);
	}
}