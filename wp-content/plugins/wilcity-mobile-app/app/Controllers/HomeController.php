<?php
namespace WILCITY_APP\Controllers;

use Stripe\Util\Set;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\SetSettings;

class HomeController extends JsonSkeleton{
	public function __construct() {
		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', '/homepage-sections', array(
				'methods' => 'GET',
				'callback' => array($this, 'homepageSections'),
			));
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', '/homepage', array(
				'methods' => 'GET',
				'callback' => array($this, 'homepageAllSections'),
			));
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', '/homepage-section-detail/(?P<id>\w+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'homepageSectionDetails'),
			));
		} );

		add_action('admin_enqueue_scripts', array($this, 'pageEnqueueScripts'));
		add_action('save_post', array($this, 'saveHomepageSections'), 10, 2);
		add_action('trashed_post', array($this, 'updateHomePageCache'), 10, 1);
		add_action('after_delete_post', array($this, 'updateHomePageCache'), 10, 1);
		add_filter('wilcity/mobile/render_slider_sc', array($this, 'getSliderSC'), 10, 2);
		add_filter('wilcity/mobile/render_listings_on_mobile', array($this, 'getListingsOnMobile'), 10, 2);
		add_action('wilcity/mobile/update-cache', array($this, 'reUpdateAppCache'));
	}

	public function getListingsOnMobile($atts, $post){
		$aListing = $this->listingSkeleton($post,  array('oGallery', 'oSocialNetworks', 'oVideos', 'oNavigation'));
//		$aNavAndHome = $this->getNavigationAndHome($post);
//		return $aListing+$aNavAndHome;
		return $aListing;
	}

	public function getSliderSC($atts, \WP_Query $query){
		$aResponse = array();
		while ($query->have_posts()){
			$query->the_post();
			$aPost = $this->listingSkeleton($query->post);
			$aNavAndHome = $this->getNavigationAndHome($query->post);
			$aResponse[] = $aPost+$aNavAndHome;
		}
		return $aResponse;
	}

	private function proceedSaveAppCache($postID){
		global $kc;
		$rawContent = get_post_field('post_content', $postID);

		if ( empty($rawContent) ){
			return false;
		}

		\WILCITY_SC\SCHelpers::$isApp = true;

		$compliedSC = $kc->do_shortcode($rawContent);

		$aParseContent = explode('%SC%', $compliedSC);
		$aResponse = array();
		$aSections = array();
		foreach ($aParseContent as $sc){
			$id = uniqid('section_');
			$sc = trim($sc);
			$sc = wp_kses($sc, array());
			if ( !empty($sc) ){
				$aParseSC = json_decode($sc, true);
				$aSections[$id] = $aParseSC['TYPE'];
				$aResponse[$id] = base64_encode($sc);
			}
		}
		SetSettings::setOptions('app_homepage', json_encode($aResponse));
		SetSettings::setOptions('app_homepage_section', $aSections);
		SetSettings::setOptions('app_homepage_id', $postID);
		SetSettings::setOptions('app_homepage_last_cache', current_time('timestamp', 1));
	}

	public function updateHomePageCache($postID){
		$mobilePageID = $this->getOptionField('mobile_app_page');
		if ( empty($mobilePageID) ){
		    return false;
		}
		$this->proceedSaveAppCache($mobilePageID);
    }

	public function saveHomepageSections($postID, $post){
		if ( ($post->post_status != 'publish')  ){
			return false;
		}

		$mobilePageID = $this->getOptionField('mobile_app_page');

		if ( empty($mobilePageID) ){
			if ( get_page_template_slug($postID) != 'templates/mobile-app-homepage.php' ){
				return false;
			}
        }else{
		    $postID = $mobilePageID;
        }
		$this->proceedSaveAppCache($postID);
	}

	public function reUpdateAppCache(){
		$lastCache = GetSettings::getOptions('app_homepage_last_cache');
		$now = current_time('timestamp', 1);
		if ( empty($lastCache) || ((($now - $lastCache)/60) > 10) ){
			$postID = GetSettings::getOptions('app_homepage_id');
			$this->proceedSaveAppCache($postID);
		}
	}

	public function pageEnqueueScripts(){
		if ( !isset($_GET['post']) || !is_numeric($_GET['post']) ){
			return false;
		}

		if ( get_page_template_slug($_GET['post']) != 'templates/mobile-app-homepage.php' ){
			return false;
		}

		wp_enqueue_script('wilcity-mobile-app', plugin_dir_url(__FILE__) . '../../assets/js/script.js', array('jquery'), null, true);
	}

	public function compilerBox(){
		if ( !isset($_GET['post']) ){
			return false;
		}

		$pageID = abs($_GET['post']);
		$pageTemplate = get_page_template_slug($pageID);
		if ( $pageTemplate !== 'templates/mobile-app-homepage.php' ){
			return false;
		}
		?>
        <button id="wilcity-compiler-code" class="button button-primary">Compiler code</button>
		<?php
	}

	public function homePageOptions(){
		$rawHomeData = GetSettings::getOptions('app_homepage');
		if ( empty($rawHomeData) ){
			return array('error'=>'Error');
		}

		return json_decode($rawHomeData, true);
	}

	function homepageAllSections(){
		$aParseHomeData = $this->homePageOptions();
		$aResponse = array();
		foreach ($aParseHomeData as $key => $rawSection){
			$aSection = json_decode(base64_decode($rawSection), true);
			$aResponse[$key] = $aSection;
		}

		return array(
			'status' => 'success',
			'oData'  => $aResponse
		);
	}

	public function homepageSections(){
		$this->reUpdateAppCache();
		$aSections = GetSettings::getOptions('app_homepage_section');
		if ( empty($aSections) ){
			return array(
				'status'=> 'error',
				'msg'   => esc_html__('There are sections', WILCITY_MOBILE_APP)
			);
		}

		return array(
			'status' => 'success',
			'oData'  => $aSections
		);
	}

	public function homepageSectionDetails($aData){
		$msg = esc_html__('This section does not exists', WILCITY_MOBILE_APP);
		if ( !isset($aData['id']) || empty($aData['id']) ){
			return array(
				'status'=> 'error',
				'msg'   => $msg
			);
		}
		$aSections = $this->homePageOptions();

		if ( !isset($aSections[$aData['id']]) || empty($aSections[$aData['id']]) ){
			return array(
				'status'=> 'error',
				'msg'   => $msg
			);
		}
		return array(
			'status' => 'success',
			'oData'  => json_decode(base64_decode($aSections[$aData['id']]), true)
		);
	}
}