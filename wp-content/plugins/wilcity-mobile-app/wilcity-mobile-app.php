<?php
/*
 * Plugin Name: Wilcity Mobile App
 * Plugin URI: https://wilcity.com
 * Author: Wilcity
 * Author URI: https://wilcity.com
 * Version: 1.3
 * Description: Wilcity Mobile App
 */
use WILCITY_APP\Controllers\HomeController;
use WILCITY_APP\Controllers\TermController;
use WILCITY_APP\Controllers\Taxonomies as WilokeMTaxonomies;
use WILCITY_APP\Controllers\PostTypes as PostTypes;
use WILCITY_APP\Controllers\Listings;
use WILCITY_APP\Controllers\Listing;
use WILCITY_APP\Controllers\OrderBy;
use WILCITY_APP\Controllers\Filter;
use WILCITY_APP\Controllers\NearByMe;
use WILCITY_APP\Controllers\Translations;
use \WILCITY_APP\Controllers\Events;
use \WILCITY_APP\Controllers\Event;
use \WILCITY_APP\Controllers\Review;
use \WILCITY_APP\Controllers\Blog;
use \WILCITY_APP\Controllers\MenuController;
use \WILCITY_APP\Controllers\SearchField;

function wilcityAppGetLanguageFiles($field='', $lang=''){
	$aFinalTranslation = include get_template_directory() . '/configs/config.translation.php';

	if ( empty($lang) ){
		$translationDir = get_stylesheet_directory() . '/configs/config.translation.php';
	}else{
		$translationDir = get_stylesheet_directory() . '/configs/config.translation-'.$lang.'.php';
	}
	if ( is_file($translationDir) ){
		$aTranslation = include $translationDir;
	}

	if ( isset($aTranslation) ){
		$aFinalTranslation = $aTranslation + $aFinalTranslation;
	}

	if ( !empty($field) ){
		if ( isset($aFinalTranslation[$field]) ){
			return $aFinalTranslation[$field];
		}
		return '';
	}

	return $aFinalTranslation;
}

add_action('wiloke-listing-tools/run-extension', function(){
	if ( !defined('WILCITY_SC_VERSION') ){
		return false;
	}

	define('WILOKE_PREFIX', 'wiloke');
	define('WILCITY_MOBILE_APP', 'wiloke');
	define('WILCITY_MOBILE_CAT', 'Wilcity Mobile App');
	define('WILCITY_APP_PATH', plugin_dir_path(__FILE__));
	define('WILCITY_APP_URL', plugin_dir_url(__FILE__));
	define('WILCITY_APP_IMG_PLACEHOLDER', WILCITY_APP_URL . 'assets/img/app-img-placeholder.jpg');

	require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

	new HomeController;
	new TermController;
	new WilokeMTaxonomies;
	new PostTypes;
	new Listings;
	new Listing;
	new OrderBy;
	new Filter;
	new NearByMe;
	new Translations;
	new Events;
	new Event;
	new Review;
	new Blog;
	new MenuController;
	new SearchField;

// Sidebar Items
	new \WILCITY_APP\SidebarOnApp\TermBox;
	new \WILCITY_APP\SidebarOnApp\Tags;
	new \WILCITY_APP\SidebarOnApp\Statistic;
	new \WILCITY_APP\SidebarOnApp\PriceRange;
	new \WILCITY_APP\SidebarOnApp\CustomContent;
	new \WILCITY_APP\SidebarOnApp\Claim;
	new \WILCITY_APP\SidebarOnApp\Categories;
	new \WILCITY_APP\SidebarOnApp\BusinessHours;
	new \WILCITY_APP\SidebarOnApp\BusinessInfo;
	new \WILCITY_APP\Controllers\GeneralSettings;
	new \WILCITY_APP\Controllers\LoginRegister;
	new \WILCITY_APP\Controllers\FavoritesController;
	new \WILCITY_APP\Controllers\MyDirectoryController;
	new \WILCITY_APP\Controllers\UserController;
	new \WILCITY_APP\Controllers\ReportController;
	new \WILCITY_APP\Controllers\DashboardController;
	new \WILCITY_APP\Controllers\NotificationController;
	new \WILCITY_APP\Controllers\MessageController;

	require_once WILCITY_APP_PATH . 'mobile-shortcodes.php';

});

