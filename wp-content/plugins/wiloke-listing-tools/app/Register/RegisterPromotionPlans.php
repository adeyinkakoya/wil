<?php

namespace WilokeListingTools\Register;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\Inc;
use WilokeListingTools\Framework\Helpers\SetSettings;

class RegisterPromotionPlans {
	use ListingToolsGeneralConfig;

	public $slug = 'promotion';

	public function __construct() {
		add_action('admin_menu', array($this, 'register'));
		add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
		add_action('wp_ajax_wiloke_save_promotion_settings', array($this, 'savePromotionSettings'));
	}

	public function savePromotionSettings(){
		if ( !current_user_can('edit_theme_options') ){
			wp_send_json_error();
		}

		$aPlans = array();

		foreach ($_POST['plans'] as $key => $aValue){
			$aPlans[$key] = $aValue;

			if ( isset($aValue['productAssociation']) && is_array($aValue['productAssociation']) && !empty($aValue['productAssociation']) ){
				$aPlans[$key]['productAssociation'] = $aValue['productAssociation'][0]['id'];
			}
		}

		SetSettings::setOptions('toggle_promotion', $_POST['toggle']);
		SetSettings::setOptions('promotion_plans', $aPlans);

		wp_send_json_success();
	}

	public function enqueueScripts($hook){
		if ( strpos($hook, $this->slug) === false ){
			return false;
		}
		$this->requiredScripts();
		$this->generalScripts();

		wp_enqueue_script('wiloke-promotion-script', WILOKE_LISTING_TOOL_URL . 'admin/source/js/promotion-script.js', array('jquery'), WILOKE_LISTING_TOOL_VERSION, true);

		$aPlans = GetSettings::getOptions('promotion_plans');
		$aPositions = array(
			'listing_sidebar',
			'listing_location_sidebar',
			'listing_category_sidebar',
			'listing_tag_sidebar',
			'blog_sidebar',
			'single_post_sidebar',
			'single_page_sidebar',
			'listing_slider_sc',
			'listing_grid_sc',
			'top_of_search'
		);

		$aAvailablePositions = $aPositions;
		if ( !empty($aPlans) ){
			foreach ($aPlans as $order => $aPlan){
				$key = array_search($aPlan['position'], $aAvailablePositions);
				if ( isset($aPlan['productAssociation']) ){
					$productID = $aPlan['productAssociation'];
					unset($aPlan['productAssociation']);
					$aPlans[$order]['productAssociation'] = array(
						array(
							'name'  => get_the_title($productID),
							'id'    => $productID
						)
					);
				}
				unset($aAvailablePositions[$key]);
			}
		}

		wp_localize_script('wiloke-promotion-script', 'WILOKE_PROMOTIONS',
			array(
				'plans'     => empty($aPlans) ? array() : $aPlans,
				'toggle'    => empty(GetSettings::getOptions('toggle_promotion')) ? 'disable' : GetSettings::getOptions('toggle_promotion'),
				'positions' => $aPositions,
				'availablePositions' => $aAvailablePositions
			)
		);
	}

	public function showPromotions(){
		Inc::file('promotion-settings:index');
	}

	public function register(){
		add_submenu_page($this->parentSlug, 'Promotions', 'Promotions', 'administrator', $this->slug, array($this, 'showPromotions'));
	}
}