<?php

namespace WILCITY_APP\Controllers;


use WilokeListingTools\Controllers\SharesStatisticController;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\Time;
use WilokeListingTools\Frontend\BusinessHours;
use WilokeListingTools\Frontend\SingleListing;
use WilokeListingTools\Frontend\User as WilcityUser;
use WilokeListingTools\Frontend\User;
use WilokeListingTools\Models\EventModel;
use WilokeListingTools\Models\FavoriteStatistic;
use WilokeListingTools\Models\ReviewMetaModel;
use WilokeListingTools\Models\ReviewModel;
use WilokeListingTools\Models\UserModel;
use WilokeListingTools\MetaBoxes\Listing as ListingMetaBoxes;
use WilokeListingTools\Controllers\ReviewController;

class JsonSkeleton {
	private $aIDs = array();
	private $listingID = '';
	private $aThemeOptions;
	public $aExcludeImgSizes = array();

	public function getOptionField($key=''){
		if ( !empty($this->aThemeOptions) ){
			return isset($this->aThemeOptions[$key]) ? $this->aThemeOptions[$key] : '';
		}

		$this->aThemeOptions = \Wiloke::getThemeOptions(true);
		return isset($this->aThemeOptions[$key]) ? $this->aThemeOptions[$key] : '';
	}

	protected function getSecurityAuthKey(){
		$auth = $this->getOptionField('wilcity_security_authentication_key');
		if ( !empty($auth) ){
			return $auth;
		}

		if ( defined('SECURE_AUTH_KEY') && SECURE_AUTH_KEY !== '' ){
			return SECURE_AUTH_KEY;
		}

		return $auth;
	}

	public  function buildSelectOptions($aOptions, $default=''){
		$aFinalOptions = array();

		foreach ($aOptions as $key){
			$aFinalOptions[] = array(
				'name' => ucfirst(str_replace(array('-', '_'), array(' ', ' '), $key)),
				'id'   => $key,
				'selected' => empty($default) || $default != $key ? false : true
			);
		}
		return $aFinalOptions;
	}

	private function getPostIDBySlug($slug){
		global $wpdb;
		if ( isset($this->aIDs[$slug]) ){
			return $this->aIDs[$slug];
		}

		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE name=%s",
				$slug
			)
		);
		$this->aIDs[$slug] = $id;
		return empty($id) ? false : $id;
	}

	public function getReviewItem($post, $postParent, $aDetails){
		$aReview['ID'] = $post->ID;
		$aReview['postTitle'] = get_the_title($post->ID);
		$aReview['postContent'] = strip_tags(get_post_field('post_content', $post->ID));
		$aReview['postDate'] = get_the_date(get_option('date_format'), $post->ID);
		$aReview['countLiked'] = ReviewController::countLiked($post->ID);
		$aReview['countShared'] = SharesStatisticController::renderShared($post->ID, false);
		$aReview['countDiscussions'] = ReviewController::countDiscussion($post->ID);
		$aReview['hasDiscussion'] = ReviewModel::hasDiscussion($post->ID);
		$aReview['oGallery'] = $this->getGallery($post->ID);
		$aReviewDetails = ReviewMetaModel::getReviewDetailsScore($post->ID, $aDetails, true);
		$aReview['oReviews']= array(
			'oDetails' => $aReviewDetails['oDetails'],
			'quality'  => ReviewMetaModel::getReviewQualityString($aReviewDetails['average'], $postParent),
			'average'  => $aReviewDetails['average']
		);
		$aReview['oUserInfo'] = $this->getUserInfo($post->post_author);
		return $aReview;
	}

	public function getPostMeta($aData){
		if ( !is_numeric($aData['target']) ){
			$postID = $this->getPostIDBySlug($aData['target']);
		}else{
			$postID = $aData['target'];
		}
		$aContent = null;
		switch ($aData['metaKey']){
			case 'photos':
				$aContent = $this->getGallery($postID);
				break;
			case 'videos':
				$maxItems = isset($aData['maximumItemsOnHome']) ? abs($aData['maximumItemsOnHome']) : '';
				$aContent = $this->getVideos($postID, $maxItems);
				break;
			case 'content':
			case 'listing_content':
				$aContent = get_post_field('post_content', $postID);
				break;
			case 'tags':
				$aRawTags = wp_get_post_terms($postID, 'listing_tag');
				if ( empty($aRawTags) || is_wp_error($aRawTags) ){
					$aContent = false;
				}else{
					foreach ($aRawTags as $oTag){
						$aTag = get_object_vars($oTag);
						$aTag['oIcon'] = \WilokeHelpers::getTermOriginalIcon($oTag);
						$aContent[] = $aTag;
					}
				}
				break;
			case 'events':
				$this->aExcludeImgSizes = array('wilcity_500x275', 'thumbnail', 'wilcity_290x165', 'wilcity_360x200');
				if ( isset($aData['maximumItemsOnHome']) ){
					$postsPerPage = $aData['maximumItemsOnHome'];
				}else if ( isset($aData['postsPerPage']) ){
					$postsPerPage = $aData['postsPerPage'];
				}else{
					$postsPerPage = 10;
				}

				$oEventQuery = new \WP_Query(
					array(
						'post_type'         => 'event',
						'posts_per_page'    => $postsPerPage,
						'post_status'       => 'publish',
						'post_parent'       => $postID
					)
				);
				if ( $oEventQuery->have_posts() ) {
					while ( $oEventQuery->have_posts() ) {
						$oEventQuery->the_post();
						$aContent[] = $this->listingSkeleton($oEventQuery->post);
					}
				}else{
					$aContent = false;
				}
				wp_reset_postdata();
				$this->aExcludeImgSizes = array();
				break;
			case 'reviews':
				$this->aExcludeImgSizes = array('wilcity_500x275', 'thumbnail', 'wilcity_290x165', 'wilcity_360x200');
				if ( isset($aData['maximumItemsOnHome']) ){
					$postsPerPage = $aData['maximumItemsOnHome'];
				}else if ( isset($aData['postsPerPage']) ){
					$postsPerPage = $aData['postsPerPage'];
				}else{
					$postsPerPage = 10;
				}

				$page = isset($aData['page']) ? abs($aData['page']) : 1;
				$query = new \WP_Query(
					array(
						'post_type'      => 'review',
						'post_status'    => 'publish',
						'posts_per_page' => $postsPerPage,
						'post_parent'    => abs($postID)
					)
				);

				$aContent['mode'] = GetSettings::getOptions(General::getReviewKey('mode', get_post_type($postID)));
				$aContent['mode'] = empty($aContent['mode']) ? 5: abs($aContent['mode']);
				$average = GetSettings::getPostMeta($postID, 'average_reviews');
				$postType = get_post_type($postID);

				$aAverageReviews = ReviewMetaModel::getAverageCategoriesReview($postID);

				$aContent['average'] = empty($average) ? 0 : floatval($average);
				$aContent['oAverageDetailsReview'] = $aAverageReviews;
				$aContent['quality'] = ReviewMetaModel::getReviewQualityString($average, $postType);

				if ( $query->have_posts() ){
					$aContent['total']      = abs($query->found_posts);
					$aContent['maxPages']   = abs($query->max_num_pages);

					if ( $page == $query->max_num_pages ){
						$aContent['next'] = false;
					}else{
						$aContent['next'] = $page+1;
					}

					$aDetails = GetSettings::getOptions(General::getReviewKey('details', $postType));
					global $post;
					while ($query->have_posts()){
						$query->the_post();
						$aContent['aReviews'][] = $this->getReviewItem($post, $postID, $aDetails);
					}
					wp_reset_postdata();
				}else{
					$aContent = false;
				}
				$this->aExcludeImgSizes = array();
				break;
			default:
				$aContent = $this->getCustomSection($aData['target'], $aData['metaKey']);
				break;
		}
		return $aContent;
	}

	public function getOrderBy(){
		return apply_filters('wilcity/app/orderby', array(
			'post_date'   => esc_html__('Latest Listings', WILCITY_MOBILE_APP),
			'post_title'  => esc_html__('Title', WILCITY_MOBILE_APP),
			'best_viewed' => esc_html__('View', WILCITY_MOBILE_APP),
			'best_rated'  => esc_html__('Rating', WILCITY_MOBILE_APP),
			'best_shared' => esc_html__('Sharing', WILCITY_MOBILE_APP),
			'menu_order'  => esc_html__('Our Suggestion', WILCITY_MOBILE_APP)
		));
	}

	protected function getUserProfile($userID, $excludeFollowContact=false){
		$oUser = new \WP_User($userID);
		$aUserInfo = array(
			'oBasicInfo'    => array(
				'userID'        => $userID,
				'user_name'     => $oUser->user_login,
				'first_name'    => $oUser->user_firstname,
				'last_name'     => $oUser->user_lastname,
				'display_name'  => $oUser->display_name,
				'avatar'        => User::getAvatar($oUser->ID),
				'cover_image'   => User::getCoverImage($oUser->ID),
				'position'      => User::getPosition($oUser->ID),
				'description'   => $oUser->description,
				'email'         => $oUser->user_email
			)
		);

		if ( !$excludeFollowContact ){
			$aUserInfo['oFollowAndContact'] = array(
				'address'       => User::getAddress($oUser->ID),
				'website'       => User::getWebsite($oUser->ID),
				'phone'         => User::getPhone($oUser->ID),
				'social_networks' =>  ''
			);

			$aRawSocialNetworks = User::getSocialNetworks($oUser->ID);
			if ( !empty($aRawSocialNetworks) ){
				$aSocialNetworks = array();
				foreach ($aRawSocialNetworks as $icon => $url){
					$aSocialNetworks[] = array(
						'id'   => $icon,
						'url'  => $url
					);
				}

				$aUserInfo['oFollowAndContact']['social_networks'] = $aSocialNetworks;
			}
		}

		return $aUserInfo;
	}

	protected function parseCustomShortcode($shortcode, $postID=''){
		if ( empty($shortcode) ){
			return '';
		}
		$this->listingID = $postID;
		$shortcode = str_replace(array('{{', '}}'), array('"', '"'), $shortcode);

		return trim(preg_replace_callback('/\s+/', function($matched){
			if ( !empty($this->listingID) ){
				return ' is_mobile="yes" post_id="'.$this->listingID.'" ';
			}

			return ' is_mobile="yes" ';
		}, $shortcode, 1));
	}

	public function getCustomSection($postID, $metaKey){
		$post = get_post($postID);
		$aSettings = SingleListing::getNavOrder($post);

		if ( !isset($aSettings[$metaKey]) ){
			return false;
		}else {
			$customShortcode = $this->parseCustomShortcode( $aSettings[ $metaKey ]['content'], $postID );

			if ( empty( $customShortcode ) ) {
				return $aSettings[ $metaKey ]['content'];
			}
			$rawParsedSC = do_shortcode($customShortcode);

			if ( ! is_array( $rawParsedSC ) ) {
				$testJSON = json_decode( $rawParsedSC, true );
				if ( $testJSON ) {
					$content = $testJSON;
				} else {
					$content = $rawParsedSC;
				}
			} else {
				$content = $rawParsedSC;
			}

			return $content;
		}
	}

	protected function eventCommentItem($post){
		return array(
			'ID'            => $post->ID,
			'postTitle'     => get_the_title($post->ID),
			'postContent'   => strip_tags(get_post_field('post_content', $post->ID)),
			'postDate'      => date_i18n(get_option('date_format'), strtotime($post->post_date)),
			'oAuthor'       => array(
				'avatar'        => User::getAvatar($post->post_author),
				'displayName'   => User::getField('display_name', $post->post_author),
			),
			'countDiscussions' => GetSettings::countNumberOfChildrenReviews($post->ID),
			'countLiked'       => ReviewController::countLiked($post->ID),
			'countShared'      => SharesStatisticController::renderShared($post->ID, false),
			'isMyLiked'        => ReviewController::isLikedReview($post->ID)
		);
	}

	public function getFeaturedImg($post, $aSizes=array()){
		$aSizes = !empty($aSizes) ? $aSizes : $this->imgSizes();
		$aFeaturedImg = array();
		if ( has_post_thumbnail($post->ID) ){
			foreach ($aSizes as $size){
				if ( is_array($size) ){
					$sizeName = 'wilcity_'.$size[0].'x'.$size[1];
				}else{
					$sizeName = $size;
				}
				$aFeaturedImg[$sizeName] = get_the_post_thumbnail_url($post->ID, $size);
			}
		}else{
			$aThemeOptions = \Wiloke::getThemeOptions();

			if ( isset($aThemeOptions['listing_featured_image']) && isset($aThemeOptions['listing_featured_image']['id']) && !empty($aThemeOptions['listing_featured_image']['id']) ){
				foreach ($aSizes as $size){
					if ( is_array($size) ){
						$sizeName = 'wilcity_'.$size[0].'x'.$size[1];
					}else{
						$sizeName = $size;
					}
					$img = wp_get_attachment_image_url($aThemeOptions['listing_featured_image']['id'], $sizeName);
					$aFeaturedImg[$sizeName] = $img ? $img : WILCITY_APP_IMG_PLACEHOLDER;
				}
			}
		}
		return $aFeaturedImg;
	}

	protected function getListingData($key, $post, $aAtts=array()){
		$content = '';
		switch ($key){
			case 'listing_content':
				$content = get_post_field('post_content', $post->ID);
				break;
			case 'featured_image':
				$content = $this->getFeaturedImg($post);
				break;
			case 'category':
			case 'listing_cat':
			case 'location':
			case 'listing_location':
			case 'tag':
			case 'listing_tag':
				$key = $key == 'category' ? 'cat' : 'category';
				$taxonomy = strpos($key, 'listing_') === 0 ? $key : 'listing_'.$key;
				$RawTerms = \WilokeHelpers::getTermByPostID($post->ID, $taxonomy, false);
				$aTerms = array();
				if ( $RawTerms ){
					foreach ($RawTerms as $oRawTerm){
						$aTerm = get_object_vars($oRawTerm);
						$aTerm['oGradient'] = \WilokeHelpers::getTermGradient($oRawTerm);
						$aTerms[] = (object)$aTerm;
					}
				}
				$content = $aTerms;
				break;
			case 'gallery':
			case 'photos':
				$content = $this->getGallery($post->ID);
				break;
		}

		return $content;
	}

	public function getNavigationAndHome($post){
		$aSettings = SingleListing::getNavOrder($post);
		$this->listingID = $post->ID;

		$aHomeSections = array_filter($aSettings, function($aSection){
			if ( $aSection['isShowOnHome'] == 'yes' ){
				if ( !isset($aSection['isCustomSection']) || $aSection['isCustomSection'] == 'no' ){
					return true;
				}

				$content = $this->getCustomSection($this->listingID, $aSection['key']);
				return !empty($content);
			}

		});

		$aNavigation = array_filter($aSettings, function($aSection){
			return ( $aSection['status'] == 'yes' );
		});

		$aPost['oNavigation'] = $aNavigation;
		$aPost['oHomeSections'] = $aHomeSections;
		return $aPost;
	}

	public function getUserInfo($userID){
		$aData['avatar'] = User::getAvatar($userID);
		$aData['displayName'] = User::getField('display_name', $userID);
		$aData['position'] = User::getPosition($userID);
		$aData['phone'] = User::getPhone($userID);
		$aData['address'] = User::getAddress($userID);
		$aData['oSocialNetworks'] = User::getSocialNetworks($userID);
		$aData['coverImage'] = User::getCoverImage($userID);
		$aData['website'] = User::getField('url', $userID);
		$aData['email'] = User::getField('email', $userID);
		return $aData;
	}

	public function imgSizes(){
		$aSizes = apply_filters('wilcity/mobile/featured-image/sizes', array(
			'large',
			'medium',
			'thumbnail',
			'wilcity_500x275',
			'wilcity_290x165',
			'wilcity_360x200'
		));

		if ( empty($this->aExcludeImgSizes) ){
			return $aSizes;
		}

		return array_diff($aSizes, $this->aExcludeImgSizes);
	}

	public function getFirstCat($post){
		$oFirstCat = GetSettings::getLastPostTerm($post->ID, 'listing_cat');

		if ( !empty($oFirstCat) ){
			$aCat = get_object_vars($oFirstCat);
			$aCat['oIcon'] = \WilokeHelpers::getTermOriginalIcon($oFirstCat);
		}

		return false;
	}

	public function eventItem($post){
		global $wiloke;
		$aEvent['ID'] = $post->ID;
		$aEvent['postTitle'] = get_the_title($post->ID);
		$aEvent['postContent'] = strip_tags(get_post_field('post_content', $post->ID));
		$aEvent['postExcerpt'] = \Wiloke::contentLimit($wiloke->aThemeOptions['listing_excerpt_length'], $post, false, $post->post_content, true);
		$aEvent['oAuthorInfo'] = $this->getUserInfo($post->post_author);
		$aEvent['isMyInterested'] = UserModel::isMyFavorite($post->ID) ? 'yes' : 'no';
		$aEvent['totalInterested'] = FavoriteStatistic::countFavorites($post->ID);
		$aEvent['oMapInfo'] = GetSettings::getListingMapInfo($post->ID);
		$aEvent['oFeaturedImg'] = $this->getFeaturedImg($post);
		$aEvent['website'] = GetSettings::getPostMeta($post->ID, 'website');
		$aEvent['phone'] = GetSettings::getPostMeta($post->ID, 'phone');
		$aEvent['email'] = GetSettings::getPostMeta($post->ID, 'email');
		$aEvent['oFirstCat'] = $this->getFirstCat($post);

		$aEventData = EventModel::getEventData($post->ID);
		$frequency  = $aEventData['frequency'];
		$timezone   = GetSettings::getPostMeta($post->ID, 'timezone');

		$aTimeInformation = array(
			'oStarts' => array(
				'on' => Time::toDateFormat($aEventData['startsOn']),
				'at' => Time::toTimeFormat($aEventData['startsOn'])
			),
			'oEnds' => array(
				'on' => Time::toDateFormat($aEventData['endsOn']),
				'at' => Time::toTimeFormat($aEventData['endsOn'])
			),
			'timezone' => Time::findUTCOffsetByTimezoneID($timezone)
		);

		switch ($frequency){
			case 'occurs_once':
				$aTimeInformation['type'] = esc_html__('Occurs Once', WILOKE_LISTING_DOMAIN);
				break;
			case 'daily':
				$aTimeInformation['type'] = esc_html__('Daily', WILOKE_LISTING_DOMAIN);
				break;
			case 'weekly':
				$specifyDay = $aEventData['specifyDays'];
				$dayName = wilokeListingToolsRepository()->get('general:aDayOfWeek', true)->sub($specifyDay);
				$aTimeInformation['type'] = sprintf( esc_html__('Every %s', WILOKE_LISTING_DOMAIN),  $dayName);
				break;
		}
		$aEvent['oSchedule'] = $aTimeInformation;
		return $aEvent;
	}

	public function getReviewDetails($postID){
		return ReviewMetaModel::getGeneralReviewData($postID);
	}

	public function getVideos($postID, $maximumItems=null){
		$aVideos = GetSettings::getPostMeta($postID, 'video_srcs');

		if ( empty($aVideos) ){
			$aVideos = false;
		}else{
			if ( !empty($maximumItems) ){
				$aVideos = array_splice($aVideos, 0, $maximumItems);
			}
		}

		return $aVideos;
	}

	public function getGallery($postID, $maximumItems=null){
		$this->aExcludeImgSizes = array('wilcity_500x275', 'thumbnail', 'wilcity_290x165', 'wilcity_360x200');
		$aRawGallery = GetSettings::getPostMeta($postID, 'gallery');
		if ( empty($aRawGallery) ){
			$aGallery = false;
		}else{
			$aGallery = array();
			$aGalleryIDs = array_keys($aRawGallery);
			if ( !empty($maximumItems) ){
				$aGalleryIDs = array_splice($aGalleryIDs, 0, $maximumItems);
			}

			$aImgSizes = $this->imgSizes();
			foreach ($aGalleryIDs as $imgID) {
				foreach ($aImgSizes as $imgSize) {
					$rawImg = wp_get_attachment_image_url($imgID, $imgSize);
					if ( !empty($rawImg) ){
						$aGallery[$imgSize][] = $rawImg;
					}
				}
			}
		}
		$this->aExcludeImgSizes = array();
		return $aGallery;
	}

	private function getFavoritesData($post){
		$isMyFavorite   = UserModel::isMyFavorite($post->ID, true);
		$totalFavorites = FavoriteStatistic::countFavorites($post->ID);

		if ( $post->post_type == 'event' ){
			$text = $totalFavorites > 1 ? esc_html__('people interested', 'wilcity-mobile-app') : esc_html__('people interested', 'wilcity-mobile-app');
		}else{
			$text = $totalFavorites > 1 ? esc_html__('Favorites', 'wilcity-mobile-app') : esc_html__('Favorite', 'wilcity-mobile-app');
		}

		return array(
			'isMyFavorite'      => $isMyFavorite,
			'totalFavorites'    => $totalFavorites,
			'text'              => $text
		);
	}

	public function listingSkeleton($post, $aExcludes=array()){
		$aSizes = $this->imgSizes();
		$aFeaturedImg = array();

		foreach ($aSizes as $size){
			if ( is_array($size) ){
				$sizeName = 'wilcity_'.$size[0].'x'.$size[1];
			}else{
				$sizeName = $size;
			}
			$aFeaturedImg[$sizeName] = GetSettings::getFeaturedImg($post->ID, $size);
			if ( empty($aFeaturedImg[$sizeName]) ){
				$aFeaturedImg[$sizeName] = WILCITY_APP_IMG_PLACEHOLDER;
			}
		}

		$averageReviews = GetSettings::getPostMeta($post->ID, 'average_reviews');

		$aResponse = array(
			'ID'        => $post->ID,
			'postTitle' => get_the_title($post->ID),
			'postLink'  => get_permalink($post->ID),
			'tagLine'   => GetSettings::getTagLine($post, true, true),
			'phone'     => GetSettings::getPostMeta($post->ID, 'phone'),
			'logo'      => GetSettings::getLogo($post->ID, 'large'),
			'oVideos'   => GetSettings::getPostMeta($post->ID, 'video_srcs'),
			'timezone'  => GetSettings::getTimezone($post->ID),
			'coverImg'  => GetSettings::getCoverImage($post->ID),
			'oAddress'  => ListingMetaBoxes::getListingAddress($post->ID),
			'oFeaturedImg'      => $aFeaturedImg,
			'businessStatus'    => BusinessHours::isEnableBusinessHour($post) ? BusinessHours::getCurrentBusinessHourStatus($post) : '',
			'oPriceRange'       => GetSettings::getPriceRange($post->ID),
			'claimStatus'       => GetSettings::getPostMeta($post->ID, 'claim_status'),
			'oSocialNetworks'   => GetSettings::getSocialNetworks($post->ID),
			'oGallery'          => $this->getGallery($post->ID),
			'oCustomSettings'   => GetSettings::getPostMeta($post->ID, 'custom_settings'),
			'oReview'           => array(
				'mode'    => abs(GetSettings::getOptions(General::getReviewKey('mode', $post->post_type))),
				'average' => floatval($averageReviews),
				'quality' => ReviewMetaModel::getReviewQualityString($averageReviews, $post->post_type)
			),
			'oFavorite'   => $this->getFavoritesData($post),
			'oAuthor'   =>  array(
				'ID'          => $post->post_author,
				'displayName' => WilcityUser::getField('display_name', $post->post_author),
				'avatar'      => WilcityUser::getAvatar($post->post_author)
			)
		);

		$aResponse = apply_filters('wilcity/app/single-skeletons/'.$post->post_type, $aResponse, $post);

		$oFirstCat = GetSettings::getLastPostTerm($post->ID, 'listing_cat');

		if ( !empty($oFirstCat) ){
			$aResponse['oTerm'] = $oFirstCat;
			$aResponse['oIcon'] = \WilokeHelpers::getTermOriginalIcon($oFirstCat);
		}

		if ( !empty($aExcludes) ){
			foreach ($aExcludes as $key){
				if ( isset($aResponse[$key]) ){
					unset($aResponse[$key]);
				}
			}
		}

		return $aResponse;
	}
}