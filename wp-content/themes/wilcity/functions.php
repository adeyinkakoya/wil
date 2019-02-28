<?php
use \WilokeListingTools\Framework\Helpers\GetSettings;

function wilcityIsNotUserLoggedIn(){
    return !is_user_logged_in();
}

function wilcityIncludeBeforeFooterFile(){
    get_template_part('before-footer');
}

function wilcityIncludeAfterBodyFile(){
	get_template_part('after-body');
}

add_action('wilcity/before-close-root', 'wilcityIncludeBeforeFooterFile');
add_action('wilcity/after-open-body', 'wilcityIncludeAfterBodyFile');
add_action('elementor/theme/before_do_footer', 'wilcityIncludeBeforeFooterFile');


add_action('after_switch_theme', 'wilcityHasNewUpdate');
function wilcityHasNewUpdate(){
	update_option('wilcity_has_new_update', 'yes');
}

add_action('admin_enqueue_scripts', function(){
	wp_enqueue_script('wilcity-notice-after-updating', get_template_directory_uri() . '/admin/source/js/noticeafterupdating.js', array('jquery'), '1.0', true);
});

add_action('wp_ajax_wilcity_read_notice_after_updating', function(){
	delete_option('wilcity_has_new_update');
});

function wilcityNoticeAfterUpdatingNewVersion() {
	if ( !get_option('wilcity_has_new_update') ){
		return '';
	}
	?>
	<div id="wilcity-notice-after-updating" class="notice notice-error is-dismissible">
		<p>After updating to the new version of Wilcity, you may need re-install Wilcity plugin. We recommend reading <a href="https://wiloke.net/themes/changelog/8" target="_blank">Changelog</a> to know how to do it.</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'wilcityNoticeAfterUpdatingNewVersion' );

if ( !defined('WILCITY_NUMBER_OF_DISCUSSIONS') ) {
	define( 'WILCITY_NUMBER_OF_DISCUSSIONS', apply_filters( 'wilcity/number_of_discussions', 2 ) );
}

if ( !function_exists('isJson') ){
	function isJson($string){
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
}

function wilcityIsAddListingPage(){
	if ( is_page_template('wiloke-submission/addlisting.php') ){
		return true;
	}

	return false;
}

function wilcityDequeueScripts() {
	wp_dequeue_script( 'waypoints' );
}
add_action( 'wp_print_scripts', 'wilcityDequeueScripts' );

function wilcityIsDashboardPage(){
	if ( is_page_template('dashboard/index.php') ){
		return true;
	}

	return false;
}

require_once  ( get_template_directory() . '/admin/run.php' );

/*
 |--------------------------------------------------------------------------
 | After theme setup
 |--------------------------------------------------------------------------
 |
 | Run needed functions after the theme is setup
 |
 */

function wilcityAfterSetupTheme(){
	add_theme_support('html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ));
	add_theme_support('title-tag');
	add_theme_support('widgets');
	add_theme_support('woocommerce');
	add_post_type_support( 'post_type', 'woosidebars' );
	add_theme_support('automatic-feed-links');
	add_theme_support('post-thumbnails');
	add_theme_support('title-tag');
	add_theme_support( 'editor-style' );
	// Woocommerce
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	add_image_size('wilcity_40x40', 280, 155, false);
	add_image_size('wilcity_530x290', 530, 290, false);
	add_image_size('wilcity_380x215', 380, 215, false);
	add_image_size('wilcity_500x275', 500, 275, false);
	add_image_size('wilcity_560x300', 560, 300, false);
	add_image_size('wilcity_290x165', 290, 165, false);
	add_image_size('wilcity_360x200', 360, 200, false);
	add_image_size('wilcity_300x300', 360, 300, false);

	$GLOBALS['content_width'] = apply_filters('wiloke_filter_content_width', 1200);
	load_theme_textdomain( 'wilcity', get_template_directory() . '/languages' );
}
add_action('after_setup_theme', 'wilcityAfterSetupTheme');

function wilCityAllowToEnqueueStripe(){
    if ( !function_exists('is_woocommerce') ){
    	$promotion = GetSettings::getOptions('toggle_promotion');
    	$postTypes = \WilokeListingTools\Framework\Helpers\General::getPostTypeKeys(false, false);

        return (wilcityIsDashboard() || is_page_template('wiloke-submission/checkout.php') || ( $promotion == 'enable' && is_singular($postTypes) ) );
    }

    return !is_checkout();
}

function wilcityIsSingleListingPage(){
	if ( !class_exists('WilokeListingTools\Framework\Helpers\Submission') ){
		return false;
	}

	$aSupportedPostTypes = \WilokeListingTools\Framework\Helpers\Submission::getSupportedPostTypes();
	if ( !is_singular($aSupportedPostTypes) ){
		return false;
	}
	return true;
}

function wilcityIsResetPassword(){
	return is_page_template('templates/reset-password.php');
}

function wilcityIsNoMapTemplate(){
	return is_page_template('templates/search-without-map.php') || is_tax() || is_page_template('templates/event-template.php');
}

function wilcityIsMapPage(){
	return is_page_template('templates/map.php');
}

function wilcityIsDashboard(){
	return is_page_template('dashboard/index.php');
}

function wilcityIsFileExists($file){
	$file = get_stylesheet_directory() . '/' . $file . '.php';

	if ( !is_file($file) ){
		$file = get_template_directory() . '/' . $file . '.php';
	}

	return is_file($file);
}

function wilcityFilterBodyClass($classes){
	$aPostTypes = class_exists('\WilokeListingTools\Framework\Helpers\General') ? \WilokeListingTools\Framework\Helpers\General::getPostTypeKeys(false, false) : '';

	global $post;
	if ( is_page() ){
		$stickyStatus = GetSettings::getPostMeta($post->ID, 'toggle_menu_sticky');
		if ( $stickyStatus == 'disable' ){
			return array_merge($classes, array('header-no-sticky'));
		}
	}

	if ( wilcityIsMapPage() || is_author() || ( !empty($aPostTypes) && is_singular($aPostTypes) ) || wilcityIsDashboard() ){
		$classes = array_merge( $classes, array( 'header-no-sticky' ) );
	}

	global $wiloke;

	if ( isset($wiloke->aThemeOptions['general_toggle_show_full_text']) && $wiloke->aThemeOptions['general_toggle_show_full_text'] == 'enable' ){
		$classes =  array_merge( $classes, array( 'text-ellipsis-mode-none' ) );
	}
	return $classes;
}

add_filter('body_class', 'wilcityFilterBodyClass');

add_action( 'widgets_init', 'wilcityRegisterSidebars' );
function wilcityRegisterSidebars() {
	register_sidebar( array(
			'name'          => esc_html__('Blog Sidebar', 'wilcity'),
			'description'   => esc_html__('Displaying widget items on the Sidebar area', 'wilcity'),
			'id'            => 'wilcity-blog-sidebar',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar( array(
			'name'          => esc_html__('Single Post Sidebar', 'wilcity'),
			'description'   => esc_html__('Displaying widget items on the Single Post area', 'wilcity'),
			'id'            => 'wilcity-single-post-sidebar',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar( array(
			'name'          => esc_html__('Single Page Sidebar', 'wilcity'),
			'description'   => esc_html__('Displaying widget items on the Single Page area', 'wilcity'),
			'id'            => 'wilcity-single-page-sidebar',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar( array(
			'name'          => esc_html__('Single Event Sidebar', 'wilcity'),
			'description'   => esc_html__('Displaying widget items on the Single Event area', 'wilcity'),
			'id'            => 'wilcity-single-event-sidebar',
			'before_widget' => '<div id="%1$s" class="content-box_module__333d9 widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<header class="content-box_header__xPnGx clearfix"><div class="wil-float-left"><h4 class="content-box_title__1gBHS">',
			'after_title'   => '</h4></div></header>',
		)
	);

	register_sidebar( array(
			'name'          => esc_html__('Listing Taxonomy Sidebar', 'wilcity'),
			'description'   => esc_html__('Displaying widget items on the Listing Tag page, Listing Location page and Listing Category page', 'wilcity'),
			'id'            => 'wilcity-listing-taxonomy',
			'before_widget' => '<div id="%1$s" class="content-box_module__333d9 widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<header class="content-box_header__xPnGx clearfix"><div class="wil-float-left"><h4 class="content-box_title__1gBHS">',
			'after_title'   => '</h4></div></header>',
		)
	);

	register_sidebar( array(
			'name'          => 'WooCommerce Sidebar',
			'description'   => 'Showing Sidebar on the WooCommerce page',
			'id'            => 'wilcity-woocommerce-sidebar',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar( array(
			'name'          => esc_html__('Footer 1', 'wilcity'),
			'description'   => esc_html__('Displaying widget items on the Footer 1 area', 'wilcity'),
			'id'            => 'wilcity-first-footer',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar( array(
			'name'          => esc_html__('Footer 2', 'wilcity'),
			'description'   => esc_html__('Displaying widget items on the Footer 2 area', 'wilcity'),
			'id'            => 'wilcity-second-footer',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar( array(
			'name'          => esc_html__('Footer 3', 'wilcity'),
			'description'   => esc_html__('Displaying widget items on the Footer 3 area', 'wilcity'),
			'id'            => 'wilcity-third-footer',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar( array(
			'name'          => esc_html__('Footer 4', 'wilcity'),
			'description'   => esc_html__('Displaying widget items on the Footer 4 area', 'wilcity'),
			'id'            => 'wilcity-four-footer',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>'
		)
	);

	if ( class_exists('Wiloke') ){
		$aThemeOptions = Wiloke::getThemeOptions(true);
		if ( isset($aThemeOptions['sidebar_additional']) && !empty($aThemeOptions['sidebar_additional']) )
		{
			$aParse = explode(',', $aThemeOptions['sidebar_additional']);

			foreach ( $aParse as $sidebar )
			{
				$sidebar = trim($sidebar);
				register_sidebar(array(
					'name'          => $sidebar,
					'id'            => $sidebar,
					'description'   => 'This is a custom sidebar, which has been created in the Appearance -> Theme Options -> Advanced Settings.',
					'before_widget' => '<section id="%1$s" class="widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h2 class="widget-title">',
					'after_title'   => '</h2>'
				));
			}
		}
	}

}

// Comment
add_action('comment_form_top', 'wilcityAddWrapperBeforeFormField');
function wilcityAddWrapperBeforeFormField(){
	echo '<div class="row">';
}
add_action('comment_form', 'wilcityAddWrapperAfterFormField', 10);
function wilcityAddWrapperAfterFormField(){
	echo '</div>';
}

add_filter('wilcity/header/header-style', 'wilcityMenuBackground', 10, 1);
function wilcityMenuBackground($color){
	global $wiloke, $post;

	if ( is_singular('page') && class_exists('\WilokeListingTools\Framework\Helpers\GetSettings') ){
		$menuBg = GetSettings::getPostMeta($post->ID, 'menu_background');
		if ( !empty($menuBg) && $menuBg != 'inherit' ){
			if ( $menuBg == 'custom' ){
				return GetSettings::getPostMeta($post->ID, 'custom_menu_background');
			}
			return $menuBg;
		}
	}elseif( is_author() ){
		$option = WilokeThemeOptions::getOptionDetail('general_author_menu_background');
		if ( $option != 'custom' ){
			return $option;
		}
		return WilokeThemeOptions::getColor('general_author_custom_menu_background');
	}else {
		$aListings = class_exists('\WilokeListingTools\Framework\Helpers\General') ? \WilokeListingTools\Framework\Helpers\General::getPostTypeKeys(false, true) : array('listing');
		if ( is_singular($aListings) ){
			$option = WilokeThemeOptions::getOptionDetail('general_listing_menu_background');
			if ( $option != 'custom' ){
				return $option;
			}
			return WilokeThemeOptions::getColor('general_custom_listing_menu_background');
		}
	}

	$option = WilokeThemeOptions::getOptionDetail('general_menu_background');
	if ( $option != 'custom' ){
		return empty($option) ? 'dark' : $color;
	}
	return WilokeThemeOptions::getColor('general_custom_menu_background');
}

function wilcityIsHasFooterWidget(){
	global $wiloke;
	if ( !isset($wiloke->aThemeOptions['footer_items']) || empty($wiloke->aThemeOptions['footer_items']) ){
		return false;
	}

	$aFooterIDs = array('wilcity-first-footer', 'wilcity-second-footer', 'wilcity-third-footer', 'wilcity-four-footer');

	for ( $i = 0; $i < abs($wiloke->aThemeOptions['footer_items']);  $i ++ ){
		if ( is_active_sidebar($aFooterIDs[$i]) ){
			return true;
		}
	}
}

function wilcityHasCopyright(){
	global $wiloke;
	return isset($wiloke->aThemeOptions['copyright']) && !empty($wiloke->aThemeOptions['copyright']);
}

function wilcityGetConfig($fileName){
	$fileName = preg_replace_callback('/\.|\//', function($aMatches){
		return '';
	}, $fileName);

	$dir = get_template_directory() . '/configs/config.'.$fileName.'.php';
	if ( is_file($dir) ){
		$config = include get_template_directory() . '/configs/config.'.$fileName.'.php';
		return $config;
	}
	return false;
}
