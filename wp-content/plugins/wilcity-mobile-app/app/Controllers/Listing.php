<?php

namespace WILCITY_APP\Controllers;


use WilokeListingTools\Controllers\ReviewController;
use WilokeListingTools\Controllers\ShareController;
use WilokeListingTools\Controllers\SharesStatisticController;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Frontend\SingleListing;
use WilokeListingTools\Models\ReviewMetaModel;
use WilokeListingTools\Models\ReviewModel;

class Listing extends JsonSkeleton {
	use BuildQuery;
	private $aCustomSections = array(
		'select_field'    => 'boxIcon',
		'checkbox_field'  => 'boxIcon',
		'checkbox2_field' => 'boxIcon',
		'textarea_field'  => 'text',
		'date_time_field' => 'text',
		'text_field'      => 'text'
	);

	public function __construct() {
		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', '/listing-detail/(?P<target>\w+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'getListing'),
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', '/listing-detail/(?P<target>\d+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'getListing'),
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', '/listing-meta/(?P<target>\d+)/(?P<metaKey>\w+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'getListingMeta'),
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', '/listing-meta/(?P<target>\w+)/(?P<metaKey>\w+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'getListingMeta'),
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', '/listing-custom-section/(?P<target>\w+)/(?P<metaKey>\w+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'getCustomSection')
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', '/review-discussion/(?P<reviewID>\d+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'getDiscussion'),
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', '/listing/sidebar/(?P<id>\d+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'getSidebar')
			));
		});

		add_filter('wilcity/nav-order', array($this, 'addTypeToSections'));
//		add_filter('wilcity/sidebar-order', array($this, 'addTypeToSidebar'));
	}

	private function detectShortcodeType($content){
		foreach ($this->aCustomSections as $fieldType => $category){
			if ( strpos($content, $fieldType) === false ){
				continue;
			}
			return $category;
		}
		return '';
	}

	public function addTypeToSidebar($aSections){
		foreach ($aSections as $key => $aVal){
			if ( isset($aVal['isCustomSection']) && $aVal['isCustomSection'] == 'yes' ){
				$category = $this->detectShortcodeType($aVal['content']);
				if ( !empty($category) ){
					$sc = $this->parseCustomShortcode($aVal['content']);
					if ( !empty($sc) ){
						$aSections[$key]['oContent'] = do_shortcode($sc);
					}
				}
			}
		}
		return $aSections;
	}

	public function addTypeToSections($aSections){
		if ( empty($aSections) ){
			return array();
		}

		foreach ($aSections as $key => $aVal){
			if ( isset($aVal['isCustomSection']) && $aVal['isCustomSection'] == 'yes' ){
				$aSections[$key]['category'] = $this->detectShortcodeType($aVal['content']);
			}else{
				$aSections[$key]['category'] = $aVal['key'];
			}
		}
		return $aSections;
	}

	public function getListingMeta($aData){
		$aResult = $this->getPostMeta($aData);

		if ( empty($aResult) ){
			return array(
				'status' => 'error',
				'msg'    => 'noDataFound'
			);
		}else{
			return array(
				'status'   => 'success',
				'oResults' => $aResult
			);
		}
	}

	public function getSidebar($aData){
		global $post;
		$post = get_post($aData['id']);

		$aRenderMachine = wilokeListingToolsRepository()->get('listing-settings:sidebar_settings', true)->sub('renderMachine');

		$aSidebarSettings = SingleListing::getSidebarOrder($post);
		if ( empty($aSidebarSettings) ){
			return array(
				'status' => 'error',
				'msg'    => esc_html__('There are no sidebar item', WILCITY_MOBILE_APP)
			);
		}
		$aSidebarItems = array();

//		if ( has_filter('wilcity/app/single-listing/sidebar-top') ){
//			$aSidebarItems = apply_filters('wilcity/app/single-listing/sidebar-top', $aSidebarItems, $post);
//		}

		foreach ($aSidebarSettings as $aSidebarSetting){
			if ( !isset($aSidebarSetting['key']) || ( isset($aSidebarSetting['status']) && $aSidebarSetting['status'] == 'no') ){
				continue;
			}
			$aSidebarSetting['isMobile'] = true;

			if ( !isset($aRenderMachine[$aSidebarSetting['key']]) ){
				if ( !isset($aSidebarSetting['content']) ){
					continue;
				}

				if ( isset($aSidebarSetting['isCustomSection']) && $aSidebarSetting['isCustomSection'] == 'yes' ){
					$category = $this->detectShortcodeType($aSidebarSetting['content']);
					if ( !empty($category) ){
						$sc = $this->parseCustomShortcode($aSidebarSetting['content']);
						if ( !empty($sc) ){
							$val = do_shortcode($sc);
							if ( $category == 'boxIcon' ){
								$aSidebarSetting['key'] = 'tag';
								$val = json_decode($val, true);
							}
						}
					}
				}else{
					$val = $aSidebarSetting['content'];
				}

			}else{
				$val = do_shortcode("[".$aRenderMachine[$aSidebarSetting['key']]. " atts='".stripslashes(json_encode($aSidebarSetting))."'/]");
				$val = empty($val) ? $val : json_decode($val, true);
			}

			if ( !empty($val) ){
				$aSidebarItems[] = array(
					'aSettings' => $aSidebarSetting,
					'oContent'  => $val
				);
			}
		}

//		if ( has_filter('wilcity/app/single-listing/sidebar-bottom') ){
//			$aSidebarItems = apply_filters('wilcity/app/single-listing/sidebar-bottom', $aSidebarItems, $post);
//		}

		if ( empty($aSidebarItems) ){
			return array(
				'status' => 'error'
			);
		}else{
			return array(
				'status'    => 'success',
				'oResults'  => $aSidebarItems
			);
		}
	}

	public function getDiscussion($aData){
		$page = isset($aData['page']) ? abs($aData['page']) : 1;
		$postsPerPage = isset($aData['postsPerPage']) ? abs($aData['postsPerPage']) : 10;

		$query = new \WP_Query(array(
			'post_type'      => 'review',
			'post_status'    => 'publish',
			'post_parent'    => $aData['reviewID'],
			'page'           => $page,
			'posts_per_page' => $postsPerPage
		));

		if ( $query->have_posts() ){
			global $post;

			$aResponse['total'] = $query->found_posts;
			$aResponse['maxPages'] = $query->max_num_pages;

			if ( $page < $query->max_num_pages ){
				$aResponse['next'] = $page+1;
			}else{
				$aResponse['next'] = false;
			}

			while ($query->have_posts()){
				$query->the_post();
				$aDiscussion['postTitle']   = get_the_title($post->ID);
				$aDiscussion['postContent'] = strip_tags(get_post_field('post_content', $post->ID));
				$aDiscussion['postDate'] = get_the_date(get_option('date_format'), $post->ID);
				$aDiscussion['oUserInfo']       = $this->getUserInfo($post->post_author);
				$aResponse['aDiscussion'][] = $aDiscussion;
			}

			wp_reset_postdata();
		}else{
			$aResponse = false;
		}

		if ( empty($aResponse) ){
			wp_send_json_error(array(
				'status' => 'error',
				'msg'    => esc_html__('No discussion found', WILCITY_SC_DOMAIN)
			));
		}

		wp_send_json_error(array(
			'status' => 'success',
			'oResults'    => $aResponse
		));
	}

	public function getListing($aData){
		$aArgs = $this->buildSingleQuery($aData);

		$query = new \WP_Query($aArgs);

		if ( $query->have_posts() ){
			global $post;
			$aPost = array();
			while ($query->have_posts()){
				$query->the_post();
				$aPost = $this->listingSkeleton($post);
				$aNavAndHome = $this->getNavigationAndHome($post);
				$aPost = $aPost + $aNavAndHome;
			}

			return array(
				'status'   => 'success',
				'oResults' => $aPost
			);
		}else{
			return array(
				'status' => 'error',
				'msg'    => esc_html__('No Post found', WILCITY_MOBILE_APP)
			);
		}
	}
}