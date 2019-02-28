<?php
/**
 * WO_FrontEnd Class
 *
 * @category Front end
 * @package Wiloke Framework
 * @author Wiloke Team
 * @version 1.0
 */
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\FileSystem;

if ( !defined('ABSPATH') )
{
    exit;
}
use WilokeListingTools\Frontend\User as WilokeUser;
use WilokeListingTools\Framework\Store\Session;
use \WilokeListingTools\Framework\Helpers\FileSystem as WilcityFileSystem;

class WilokeFrontPage
{
	public $mainStyle = '';
	public $minifyStyle = 'wiloke_minify_theme_css';
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'), 9999);
        add_action('wp_head', array($this, 'addFavicon'));
        add_action('wp_head', array($this, 'fbTags'));
        add_action('wp_enqueue_scripts', array($this, 'loadFBSDK'));
    }

    public function loadFBSDK(){
	    if ( is_user_logged_in() ){
		    return false;
	    }

	    global $wiloke;
	    $isIncludeFB = isset($wiloke->aThemeOptions['fb_toggle_login']) && $wiloke->aThemeOptions['fb_toggle_login'] == 'enable';

	    $isIncludeFB = apply_filters('wilcity/is-include-fb-skd', $isIncludeFB);
	    if ( !$isIncludeFB ){
	        return false;
        }

        $language = isset($wiloke->aThemeOptions['fb_api_language']) ? esc_js($wiloke->aThemeOptions['fb_api_language']) : '';

	    $sdkURL = "https://connect.facebook.net/".$language."/sdk.js";

	    ob_start();
	    ?>
        window.fbAsyncInit = function() {
            FB.init({
                appId      : '<?php echo esc_js($wiloke->aThemeOptions['fb_api_id']);?>',
                cookie     : true,  // enable cookies to allow the server to access
                xfbml      : true,  // parse social plugins on this page
                version    : 'v2.8' // use version 2.2
            });
        };
        // Load the SDK asynchronously
        (function(d, s, id) {
        let js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "<?php echo esc_url($sdkURL); ?>";
        fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
	    <?php
	    $script = ob_get_clean();
	    wp_add_inline_script('jquery-migrate', $script);
    }

    public function fbTags(){
        $aThemeOptions = Wiloke::getThemeOptions();
        if ( !class_exists('\WilokeListingTools\Framework\Helpers\General') ){
            return '';
        }
        global $post;

        $aListingTypes = \WilokeListingTools\Framework\Helpers\General::getPostTypeKeys(false, false);

        if ( !is_singular($aListingTypes) ){
            return '';
        }

        if ( isset($aThemeOptions['toggle_fb_ogg_tag_to_listing']) && $aThemeOptions['toggle_fb_ogg_tag_to_listing'] == 'enable' ){
            ?>
            <meta property="og:title" content="<?php echo get_the_title($post->ID); ?>" />
            <meta property="og:url" content="<?php echo get_permalink($post->ID); ?>" />
            <meta property="og:image" content="<?php echo esc_url(GetSettings::getFeaturedImg($post->ID, 'full')); ?>" />
            <meta property="og:description" content="<?php echo esc_html(Wiloke::contentLimit($aThemeOptions['listing_excerpt_length'], $post, false, $post->post_content, true, '')); ?>" />
            <?php
        }
    }

    public function addFavicon(){
    	global $wiloke;
    	if ( isset($wiloke->aThemeOptions['general_favicon']) && !empty($wiloke->aThemeOptions['general_favicon']) ){
    		?>
		    <link rel="shortcut icon" type="image/png" href="<?php echo esc_url($wiloke->aThemeOptions['general_favicon']['url']); ?>"/>
			<?php
	    }
    }

    public function addAsyncAttributes($tag, $handle){
		if ( strpos($tag, 'async') === false ){
			return $tag;
		}

	    return str_replace( ' src', ' async defer src', $tag );
    }

    public function dequeueScripts()
    {
        wp_dequeue_script('isotope');
        wp_dequeue_script('isotope-css');
    }

    public static function fontUrl($fonts)
    {
        $font_url = '';

        /*
        Translators: If there are characters in your language that are not supported
        by chosen font(s), translate this to 'off'. Do not translate into your own language.
         */
        if ( 'off' !== _x( 'on', 'Google font: on or off', 'wilcity' ) ) {
            $font_url = add_query_arg( 'family', urlencode( $fonts ), "//fonts.googleapis.com/css" );
        }
        return $font_url;
    }

    /**
     * Enqueue scripts into front end
     */
    public function enqueueScripts()
    {
    	global $wiloke, $post;

		$vendorURL = WILOKE_THEME_URI . 'assets/vendors/';
		$cssURL    = WILOKE_THEME_URI . 'assets/production/css/';
		$cssDir    = WILOKE_THEME_DIR . 'assets/production/css/';
		$jsURL     = WILOKE_THEME_URI . 'assets/production/js/';
		$fontURL   = WILOKE_THEME_URI . 'assets/fonts/';

	    $aScripts = $wiloke->aConfigs['frontend']['scripts'];

	    wp_register_script('wilcity-empty', $jsURL . 'activeListItem.js' , array('jquery'), WILOKE_THEMEVERSION, false);
	    wp_enqueue_script('wilcity-empty');

	    $mapTheme = isset($wiloke->aThemeOptions['map_theme']) ? esc_js($wiloke->aThemeOptions['map_theme']) : 'blurWater';

	    $role = '';
	    if ( is_user_logged_in() ){
		    $oUserMeta = get_userdata(get_current_user_id());
		    $role = isset($oUserMeta->roles[0]) ? $oUserMeta->roles[0] : '';
	    }

	    $defaultPostType = 'listing';
	    if ( class_exists('\WilokeListingTools\Framework\Helpers\GetSettings') ){
	        $aDefaultPostTypes = GetSettings::getFrontendPostTypes(true);
	        $defaultPostType = $aDefaultPostTypes[0];
        }

	    $aGlobal = array(
		    'maxUpload' => (int)(ini_get('upload_max_filesize'))*1024,
		    'ajaxurl'   => esc_url(admin_url('admin-ajax.php')),
		    'postID'    => is_single() ? esc_js($post->ID) : '',
		    'isUseMapBound' => 'no',
		    'mapCenter' => isset($wiloke->aThemeOptions['map_center']) ? $wiloke->aThemeOptions['map_center'] : '',
		    'mapMaxZoom'=> isset($wiloke->aThemeOptions['map_max_zoom']) ? abs($wiloke->aThemeOptions['map_max_zoom']) : 7,
		    'mapMinZoom'=> isset($wiloke->aThemeOptions['map_minimum_zoom']) ? abs($wiloke->aThemeOptions['map_minimum_zoom']) : 1,
		    'mapDefaultZoom'=> isset($wiloke->aThemeOptions['map_default_zoom']) ? abs($wiloke->aThemeOptions['map_default_zoom']) : 4,
		    'mapTheme'  => esc_js($mapTheme),
		    'isAddingListing' => !class_exists('\WilokeListingTools\Framework\Store\Session') ||  empty(Session::getSession(wilokeListingToolsRepository()->get('payment:storePlanID'))) ? 'no' : 'yes',
		    'aUsedSocialNetworks' => WilokeSocialNetworks::getUsedSocialNetworks(),
		    'isPaidClaim'   => !class_exists('\WilokeListingTools\Controllers\ClaimController') || !\WilokeListingTools\Controllers\ClaimController::isPaidClaim() ? 'no' : 'yes',
		    'userRole' => $role,
		    'datePickerFormat' => apply_filters('wilcity_date_picker_format', 'mm/dd/yy'),
            'defaultPostType'  => $defaultPostType,
            'isUploadImgViaAjax' => defined('WILCITY_BETA_UPLOAD_IMG_VIA_AJAX') ?  'yes' : 'no'
	    );

	    if ( WilokeThemeOptions::isEnable('map_bound_toggle') ){
		    $aGlobal['isUseMapBound'] = 'yes';
		    $aGlobal['mapBoundStart'] = $wiloke->aThemeOptions['map_bound_start'];
		    $aGlobal['mapBoundEnd']   = $wiloke->aThemeOptions['map_bound_end'];
	    }

	    if ( class_exists('\WilokeListingTools\Framework\Helpers\General') ){
	    	$aPostTypes = \WilokeListingTools\Framework\Helpers\General::getPostTypeKeys(false, false);
	    	if ( is_singular($aPostTypes) ){
			    $aGlobal['oSingleMap']['maxZoom'] = isset($wiloke->aThemeOptions['single_map_max_zoom']) && !empty($wiloke->aThemeOptions['single_map_max_zoom']) ? $wiloke->aThemeOptions['single_map_max_zoom'] : 21;
			    $aGlobal['oSingleMap']['minZoom'] = isset($wiloke->aThemeOptions['single_map_minimum_zoom']) && !empty($wiloke->aThemeOptions['single_map_minimum_zoom']) ? $wiloke->aThemeOptions['single_map_minimum_zoom'] : 21;
			    $aGlobal['oSingleMap']['defaultZoom'] = isset($wiloke->aThemeOptions['single_map_default_zoom']) && !empty($wiloke->aThemeOptions['single_map_default_zoom']) ? $wiloke->aThemeOptions['single_map_default_zoom'] : 21;
		    }
	    }

	    if ( class_exists('WilokeListingTools\Frontend\User') ){
		    if ( is_user_logged_in() ){
			    $userID = get_current_user_id();
			    $aUser['displayName']  = WilokeUser::getField('display_name', $userID);
			    $aUser['avatar']       = WilokeUser::getAvatar($userID);
			    $aUser['position']     = WilokeUser::getPosition($userID);
			    $aGlobal['user']       = $aUser;
			    $aGlobal['isUserLoggedIn'] = 'yes';
		    }else{
			    $aGlobal['isUserLoggedIn'] = 'no';
			    $aGlobal['canRegister'] = \WilokeListingTools\Controllers\RegisterLoginController::canRegister() ? 'yes' : 'no';
		    }
	    }

	    if ( !is_user_logged_in() ){
	        if ( isset($wiloke->aThemeOptions['toggle_google_recaptcha']) && $wiloke->aThemeOptions['toggle_google_recaptcha'] == 'enable' ){
		        $aGlobal['oGoogleReCaptcha']['siteKey'] = $wiloke->aThemeOptions['recaptcha_site_key'];
            }

            if ( isset($wiloke->aThemeOptions['fb_toggle_login']) && $wiloke->aThemeOptions['fb_toggle_login'] == 'enable' ){
	            $aGlobal['oFacebook'] = array(
                    'API' => $wiloke->aThemeOptions['fb_api_id']
                );
            }
        }

	    if ( class_exists('WilokeSocialNetworks') && class_exists('WilokeListingTools\Frontend\User') && \WilokeListingTools\Frontend\User::canSubmitListing() ){
	    	$aGlobal['oSocialNetworks'] = WilokeSocialNetworks::$aSocialNetworks;
	    }

	    if ( isset($wiloke->aThemeOptions['search_country_restriction']) && !empty($wiloke->aThemeOptions['search_country_restriction']) ){
	    	$aGlobal['countryRestriction'] = $wiloke->aThemeOptions['search_country_restriction'];
	    }

	    wp_localize_script('wilcity-empty', 'WILOKE_GLOBAL', $aGlobal);
	    wp_localize_script('jquery-migrate', 'WILCITY_I18', GetSettings::getTranslation());

	    // Enqueue Scripts
	    if ( isset($aScripts['js']) ){
	    	foreach ($aScripts['js'] as $name => $aJs){
			    $isGoodConditional = true;

			    if ( isset($aJs['conditional']) && function_exists($aJs['conditional']) )
			    {
			    	if ( call_user_func($aJs['conditional'], $name) ){
					    $isGoodConditional = true;
				    }else{
					    $isGoodConditional = false;
				    }
			    }

			    if ( !$isGoodConditional ){
			    	continue;
			    }

	    		if ( isset($aJs['isExternal']) && $aJs['isExternal'] ){
	    			wp_register_script($aJs[0], $aJs[1], array('jquery'), WILOKE_THEMEVERSION, true);
	    			wp_enqueue_script($aJs[0]);
			    }else{
	    			if ( isset($aJs['isVendor']) ){
					    wp_register_script($aJs[0], $vendorURL . $aJs[1], array('jquery'), WILOKE_THEMEVERSION, true);
					    wp_enqueue_script($aJs[0]);
				    }else if (isset($aJs['isWPLIB'])){
					    wp_enqueue_script($aJs[0]);
				    }else if(isset($aJs['isGoogleAPI'])){
				    	$googleAPI = isset($wiloke->aThemeOptions['general_google_api']) && !empty($wiloke->aThemeOptions['general_google_api']) ? $wiloke->aThemeOptions['general_google_api'] : '';
					    $url = isset($aJs[1]) ? $aJs[1] : 'https://maps.googleapis.com/maps/api/js?key=';
					    $url = $url.$googleAPI;

					    if ( isset($wiloke->aThemeOptions['general_google_language']) && !empty($wiloke->aThemeOptions['general_google_language']) ){
						    $url .= '&language='.esc_js(trim($wiloke->aThemeOptions['general_google_language']));
					    }

					    wp_enqueue_script($aJs[0], $url);
				    }else{
					    wp_register_script($aJs[0], $jsURL . $aJs[1], array('jquery'), WILOKE_THEMEVERSION, true);
					    wp_enqueue_script($aJs[0]);
				    }
			    }
		    }
	    }

	    if ( isset($aScripts['css']) ){
		    foreach ($aScripts['css'] as $aCSS){
			    if ( isset($aCSS['isExternal']) && $aCSS['isExternal'] ){
				    wp_register_style($aCSS[0], $aCSS[1], array(), WILOKE_THEMEVERSION, false);
				    wp_enqueue_style($aCSS[0]);
			    }else{
				    if ( isset($aCSS['isVendor']) ){
					    wp_register_style($aCSS[0], $vendorURL . $aCSS[1], array(), WILOKE_THEMEVERSION);
					    wp_enqueue_style($aCSS[0]);
				    }else if (isset($aCSS['isWPLIB'])){
					    wp_enqueue_style($aCSS[0]);
				    }elseif( isset($aCSS['isGoogleFont']) ){
					    wp_enqueue_style($aCSS[0], self::fontUrl($aCSS[1]));
				    }else if(isset($aCSS['isFont'])){
					    wp_enqueue_style($aCSS[0], $fontURL . $aCSS[1], array(), WILOKE_THEMEVERSION);
				    }else{
					    wp_register_style($aCSS[0], $cssURL . $aCSS[1], array(), WILOKE_THEMEVERSION);
					    wp_enqueue_style($aCSS[0]);
				    }
			    }
		    }
	    }

	    if ( isset($wiloke->aThemeOptions['advanced_google_fonts']) && $wiloke->aThemeOptions['advanced_google_fonts']=='general' && class_exists('WilokeListingTools\Framework\Helpers\GetSettings') ){
		    if ( isset($wiloke->aThemeOptions['advanced_general_google_fonts']) && !empty($wiloke->aThemeOptions['advanced_general_google_fonts']) ){
			    wp_enqueue_style('wilcity-custom-google-font', esc_url($wiloke->aThemeOptions['advanced_general_google_fonts']));

			    $cssRules = $wiloke->aThemeOptions['advanced_general_google_fonts_css_rules'];
			    if ( !empty($cssRules) ){
				    $googleFont = GetSettings::getOptions('custom_google_font');
				    $fontTextFileName = 'fontText.css';
				    $fontTitleFileName = 'fontTitle.css';

				    if ( $googleFont == urlencode($cssRules) && WilcityFileSystem::isFileExists($fontTextFileName) ){
					    wp_enqueue_style('wilcity-google-font-css-rules', WilcityFileSystem::getFileURI($fontTextFileName));
					    wp_enqueue_style('wilcity-google-font-css-rules', WilcityFileSystem::getFileURI($fontTitleFileName));
				    }else{
					    ob_start();
					    include get_template_directory() . '/assets/production/css/fonts/fontText.css';
					    $fontText = ob_get_clean();
					    $fontText = str_replace('#googlefont', $cssRules, $fontText);

					    ob_start();
					    include get_template_directory() . '/assets/production/css/fonts/fontTitle.css';
					    $fontTitle = ob_get_clean();
					    $fontTitle = str_replace('#googlefont', $cssRules, $fontTitle);

					    if ( WilcityFileSystem::filePutContents($fontTextFileName, $fontText) ){
					    	WilcityFileSystem::filePutContents($fontTitleFileName, $fontTitle);

						    wp_enqueue_style('wilcity-custom-fontText', WilcityFileSystem::getFileURI($fontTextFileName));
						    wp_enqueue_style('wilcity-custom-fontTitle', WilcityFileSystem::getFileURI($fontTitleFileName));
					        \WilokeListingTools\Framework\Helpers\SetSettings::setOptions('custom_google_font', urlencode($cssRules));
					    }else{
						    wp_add_inline_style('wilcity-custom-fontText', $fontText);
						    wp_add_inline_style('wilcity-custom-fontTitle', $fontTitle);
					    }
				    }
			    }
		    }
	    }

	    wp_enqueue_script('comment-reply');
	    wp_enqueue_style(WILOKE_THEMESLUG, get_stylesheet_uri(), array(), WILOKE_THEMEVERSION );

	    if ( isset($wiloke->aThemeOptions['advanced_main_color']) && !empty($wiloke->aThemeOptions['advanced_main_color']) ){
	    	if ( $wiloke->aThemeOptions['advanced_main_color'] != 'custom' ){
			    wp_enqueue_style('wilcity-custom-color', $cssURL . 'colors/'.$wiloke->aThemeOptions['advanced_main_color'].'.css', array(), WILOKE_THEMEVERSION );
		    }else{
	    		if ( class_exists('\WilokeListingTools\Framework\Helpers\FileSystem') ){
	    			$currentCustomColor = get_option('wilcity_current_custom_color');

					if ( WilcityFileSystem::isFileExists('custom-main-color.css') && $currentCustomColor == $wiloke->aThemeOptions['advanced_custom_main_color']['rgba'] ){
						wp_enqueue_style('wilcity-custom-color', WilcityFileSystem::getFileURI('custom-main-color.css'), array(), WILOKE_THEMEVERSION );
					}else{
						if ( isset($wiloke->aThemeOptions['advanced_custom_main_color']) && isset($wiloke->aThemeOptions['advanced_custom_main_color']['rgba']) ) {
							if ( ! function_exists( 'WP_Filesystem' ) ) {
								require_once( ABSPATH . 'wp-admin/includes/file.php' );
							}
							WP_Filesystem();
							global $wp_filesystem;
							$defaultCSS = $wp_filesystem->get_contents( $cssDir . 'colors/default.css' );

							$parseCSS = str_replace( '#f06292', $wiloke->aThemeOptions['advanced_custom_main_color']['rgba'], $defaultCSS );

							$status = WilcityFileSystem::filePutContents( 'custom-main-color.css', $parseCSS );

							if ( $status ) {
								update_option('wilcity_current_custom_color', $wiloke->aThemeOptions['advanced_custom_main_color']['rgba']);
								wp_enqueue_style( 'wilcity-custom-color', WilcityFileSystem::getFileURI( 'custom-main-color.css' ), array(), WILOKE_THEMEVERSION );
							} else {
								wp_add_inline_style( WILOKE_THEMESLUG, $parseCSS );
							}
						}
					}
			    }
		    }
	    }

	    if ( isset($wiloke->aThemeOptions['advanced_css_code']) && !empty($wiloke->aThemeOptions['advanced_css_code']) ){
		    wp_add_inline_style( WILOKE_THEMESLUG, $wiloke->aThemeOptions['advanced_css_code'] );
	    }

	    if ( isset($wiloke->aThemeOptions['advanced_js_code']) && !empty($wiloke->aThemeOptions['advanced_js_code']) )
	    {
		    wp_add_inline_script('jquery-migrate', $wiloke->aThemeOptions['advanced_js_code']);
	    }

    }
}