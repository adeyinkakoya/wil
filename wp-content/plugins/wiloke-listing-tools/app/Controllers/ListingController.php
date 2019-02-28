<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Frontend\User;
use WilokeListingTools\MetaBoxes\Listing;
use WilokeListingTools\Models\FavoriteStatistic;
use WilokeListingTools\Models\ListingModel;
use WilokeListingTools\Models\PostModel;
use WilokeListingTools\Models\PromotionModel;
use WilokeListingTools\Models\ReviewMetaModel;
use WilokeListingTools\Models\SharesStatistic;
use WilokeListingTools\Models\ViewStatistic;

class ListingController extends Controller {
	use SingleJsonSkeleton;

	public function __construct() {
		add_action('wp_ajax_wilcity_fetch_listings_json', array($this, 'fetchListingsJson'));
		add_action('wp_ajax_wilcity_fetch_general_data', array($this, 'fetchGeneralData'));
		add_action('wp_ajax_wilcity_load_more_listings', array($this, 'loadMoreListings'));
		add_action('wp_ajax_nopriv_wilcity_load_more_listings', array($this, 'loadMoreListings'));
		add_action('wp_ajax_wilcity_fetch_listing_promotions', array($this, 'fetchPromotionDetails'));
		add_action('wp_ajax_wilcity_button_settings', array($this, 'fetchButtonSettings'));
		add_action('wp_ajax_wilcity_save_page_button', array($this, 'saveButtonSettings'));
	}

	public function saveButtonSettings(){
		$this->middleware(['isPostAuthor'], [
			'postID' => $_POST['postID'],
			'postAuthor' => get_current_user_id()
		]);

		$aButtonSettings = $_POST['aButtons'];
		if ( isset($aButtonSettings['websiteLink']) && !empty($aButtonSettings['websiteLink']) ){
			SetSettings::setPostMeta($_POST['postID'], 'button_link', $aButtonSettings['websiteLink']);
		}else{
			SetSettings::deletePostMeta($_POST['postID'], 'button_link');
		}

		if ( isset($aButtonSettings['icon']) && !empty($aButtonSettings['icon']) ){
			SetSettings::setPostMeta($_POST['postID'], 'button_icon', $aButtonSettings['icon']);
		}

		if ( isset($aButtonSettings['buttonName']) && !empty($aButtonSettings['buttonName']) ){
			SetSettings::setPostMeta($_POST['postID'], 'button_name', $aButtonSettings['buttonName']);
		}

		wp_send_json_success(array(
			'msg' => esc_html__('Congratulations! The button settings have been updated.', 'wiloke-listing-tools')
		));
	}

	public function fetchButtonSettings(){
		$aSettings['buttonName'] = GetSettings::getPostMeta($_POST['postID'], 'button_name');
		$aSettings['websiteLink'] = GetSettings::getPostMeta($_POST['postID'], 'button_link');
		$aSettings['icon'] = GetSettings::getPostMeta($_POST['postID'], 'button_icon');
		wp_send_json_success($aSettings);
	}

	public function fetchPromotionDetails(){
		if ( !isset($_POST['postID']) || empty($_POST['postID']) ){
			wp_send_json_error(array(
				'msg' => esc_html__('The post id is required.', 'wiloke-listing-tools'),
			));
		}

		$aRawPromotions = PromotionModel::getListingPromotions($_POST['postID']);
		if ( empty($aRawPromotions) ){
			wp_send_json_error(array(
				'msg' => esc_html__('There are no promotions.', 'wiloke-listing-tools'),
			));
		}

		$aPromotions = array();
		$aRawPromotionPlans = GetSettings::getOptions('promotion_plans');

		$aPromotionPlans = array();
		foreach ($aRawPromotionPlans as $aPlan){
			$aPromotionPlans[$aPlan['position']] = $aPlan;
		}

		foreach ($aRawPromotions as $aPromotion){
			$position = str_replace('wilcity_promote_', '', $aPromotion['meta_key']);
			$aPromotions[] = array(
				'name'     => $aPromotionPlans[$position]['name'],
				'position' => $position,
				'preview'  => $aPromotionPlans[$position]['preview'],
				'expiryOn' => date_i18n(get_option('date_format'), $aPromotion['meta_value'])
			);
		}

		wp_send_json_success($aPromotions);
	}

	public static function renderClaimStatus($postID){
		return PostModel::isClaimed($postID) ? esc_html__('Claimed', 'wiloke-listing-tools') : '';
	}

	public function fetchGeneralData(){
		$aRawPostTypes = GetSettings::getFrontendPostTypes();

		$aPostTypes = array();
		$aPostStatus = General::getPostsStatus();

		$aCountPosts = array();
		foreach ($aRawPostTypes as $aOption){
			$aPostTypes[] = array(
				'name' => $aOption['singular_name'],
				'value'=> $aOption['key']
			);

			$aCountPosts[$aOption['key']] = User::countUserPosts(get_current_user_id(), $aOption['key']);
		}

		wp_send_json_success(array(
			'oPostTypes' => $aPostTypes,
			'aCountPosts'=> $aCountPosts,
			'aPostStatus'=> $aPostStatus
		));
	}

	public function loadMoreListings(){
		$page  = isset($_POST['page']) ? abs($_POST['page']) : 2;
		$aPostTypeKeys = General::getPostTypeKeys(true);

		if ( !in_array($_POST['postType'], $aPostTypeKeys) ){
			wp_send_json_error(array(
				'msg' => esc_html__('You do not have permission to access this page', 'wiloke-listing-tools')
			));
		}

		$aData = array();
		foreach ($_POST as $key => $val){
			$aData[$key] = sanitize_text_field($val);
		}

		$query = new \WP_Query(
			array(
				'post_type'         => $aData['postType'],
				'posts_per_page'    => $aData['postsPerPage'],
				'paged'             => $page,
				'post_status'       => 'publish'
			)
		);

		if ( $query->have_posts() ){
			ob_start();
			while ($query->have_posts()){
				$query->the_post();
				wilcity_render_grid_item($query->post, array(
					'img_size'                   => $aData['img_size'],
					'maximum_posts_on_lg_screen' => $aData['maximum_posts_on_lg_screen'],
					'maximum_posts_on_md_screen' => $aData['maximum_posts_on_md_screen'],
					'maximum_posts_on_sm_screen' => $aData['maximum_posts_on_sm_screen'],
				));
			}
			$contents = ob_get_contents();
			ob_end_clean();
			wp_send_json_success(array('msg'=>$contents));
		}else{
			wp_send_json_error(
				array(
					'msg' => sprintf(esc_html__('Oops! Sorry, We found no %s', 'wiloke-listing-tools'), $aData['postType'])
				)
			);
		}
	}

	public function fetchListingsJson(){
		$query = new \WP_Query(
			array(
				'post_type'     => $_POST['postType'],
				'post_status'   => $_POST['postStatus'],
				'posts_per_page'=> 10,
				'paged'         => isset($_POST['page']) ? abs($_POST['page']) : 1,
				'author'        => get_current_user_id()
			)
		);

		if ( !$query->have_posts() ){
			wp_reset_postdata();

			wp_send_json_error(array(
				'msg' => esc_html__('You do not have any listing yet.', 'wiloke-listing-tools'),
			));
		}

		$aListings = array();

		$reviewMode = GetSettings::getOptions(General::getReviewKey('mode', $_POST['postType']));
		$reviewMode = empty($reviewMode) ? 5 : $reviewMode;

		while ($query->have_posts()){
			$query->the_post();
			$aListing = $this->json($query->post);
			$aListing['oReview'] = array(
				'mode' => $reviewMode,
				'average' => GetSettings::getAverageRating($query->post->ID)
			);
			$aListings[] = $aListing;
		}

		wp_send_json_success(array(
			'listings' => $aListings,
			'maxPages' => $query->max_num_pages
		));
	}
}