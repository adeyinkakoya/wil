<?php
$aThemeOptions = Wiloke::getThemeOptions(true);

if ( !empty($aThemeOptions['general_google_language']) ){
	$googleCaptchaUrl = 'https://www.google.com/recaptcha/api.js?onload=vueRecaptchaApiLoaded&render=explicit&hl='.$aThemeOptions['general_google_language'];
}else{
	$googleCaptchaUrl = 'https://www.google.com/recaptcha/api.js?onload=vueRecaptchaApiLoaded&render=explicit';
}

return array(
    'scripts' => array(
    	'js' => array(
		    array('vuejs', 'vue/vue.js', 'isVendor'=>true),
		    array('vue-router', 'vue/vue-router.min.js', 'isVendor'=>true, 'conditional'=>'wilcityIsDashboardPage'),
		    array('vue-sortable', 'vue/Sortable.min.js', 'isVendor'=>true, 'conditional'=>'wilcityIsSingleListingPage'),
		    array('vue-draggable', 'vue/vuedraggable.min.js', 'isVendor'=>true, 'conditional'=>'wilcityIsSingleListingPage'),
		    array('vuex', 'vue/vuex.min.js', 'isVendor'=>true),
//	    , 'conditional'=>'wilcityIsSingleListingPage'/
		    array('stripe', '//checkout.stripe.com/checkout.js', 'isExternal'=>true, 'conditional'=>'wilCityAllowToEnqueueStripe'),
		    array('googleplaces-async', '//maps.googleapis.com/maps/api/js?libraries=places&key=', 'isGoogleAPI'=>true),
		    array('jquery-ui-slider', 'isWPLIB'=>true),
		    array('jquery-ui-touch-punch', 'touchpunch/jquery.ui.touch-punch.min.js', 'isVendor'=>true, 'conditional'=>'wp_is_mobile'),
		    array('jquery-ui-datepicker', 'isWPLIB'=>true),
		    array('spectrum', 'spectrum/spectrum.js', 'isVendor'=>true, 'conditional'=>'wilcityIsAddListingPage'),
		    array('chartjs', 'chartjs/Chart.js', 'isVendor'=>true),
		    array('jqueryeasing', 'jquery.easing/jquery.easing.js', 'isVendor'=>true),
		    array('perfect-scrollbar', 'perfect-scrollbar/perfect-scrollbar.min.js', 'isVendor'=>true),
		    array('magnific-popup', 'magnific-popup/jquery.magnific-popup.js', 'isVendor'=>true),
		    array('jquery-select2', 'select2/select2.js', 'isVendor'=>true),
		    array('swiper', 'swiper/swiper.js', 'isVendor'=>true),
		    array('WilcityMagnificGallery', 'MagnificGalleryPopup.js'),
		    array('WilcityFavoriteStatistics', 'FavoriteStatistics.js'),
		    array('theia-sticky-sidebar', 'theia-sticky-sidebar/theia-sticky-sidebar.js', 'isVendor'=>true),
		    array('snazzy-info-window', 'googlemap/snazzy-info-window.min.js', 'isVendor'=>true, 'conditional'=>'wilcityIsMapPage'),
		    array('markerclusterer', 'googlemap/markerclusterer.js', 'isVendor'=>true, 'conditional'=>'wilcityIsMapPage'),
		    array('wilcity-shortcodes', 'shortcodes.min.js'),
		    array('waypoints-vendor', 'waypoints/jquery.waypoints.min.js', 'isVendor'=>true),
		    array('bundle', 'bundle.min.js'),
		    array('quick-search', 'quick-search.min.js'),
		    array('map', 'map.min.js', 'conditional'=>'wilcityIsMapPage'),
		    array('review', 'review.min.js'),
		    array('googleReCaptcha', $googleCaptchaUrl, 'isExternal'=>true, 'conditional'=>'wilcityIsNotUserLoggedIn'),
		    array('dashboard', 'dashboard.min.js', 'conditional'=>'wilcityIsDashboardPage'),
		    array('app', 'app.min.js'),
		    array('reset-password', 'resetPassword.min.js', 'conditional'=>'wilcityIsResetPassword'),
		    array('no-map-search', 'no-map-search.min.js', 'conditional'=>'wilcityIsNoMapTemplate')
	    ),
        'css' => array(
	        array('bootstrap', 'bootstrap/grid.css', 'isVendor'=>true),
	        array('spectrum', 'spectrum/spectrum.css', 'isVendor'=>true, 'conditional'=>'wilcityIsAddListingPage'),
	        array('perfect-scrollbar', 'perfect-scrollbar/perfect-scrollbar.min.css', 'isVendor'=>true),
	        array('font-awesome', 'fontawesome/font-awesome.min.css', 'isFont'=>true),
	        array('Poppins', 'Poppins:400,500,600,700,900|Roboto:300,400', 'isGoogleFont'=>true),
	        array('line-awesome', 'line-awesome/line-awesome.css', 'isFont'=>true),
	        array('magnific-popup', 'magnific-popup/magnific-popup.css', 'isVendor'=>true),
	        array('magnific-select2', 'select2/select2.css', 'isVendor'=>true),
	        array('swiper', 'swiper/swiper.css', 'isVendor'=>true),
	        array('jquery-ui-custom-style', 'ui-custom-style/ui-custom-style.min.css', 'isVendor'=>true),
	        array('snazzy-info-window', 'googlemap/snazzy-info-window.min.css', 'isVendor'=>true),
	        array('app', 'app.min.css'),
	        array('wilcity-woocommerce', 'woocommerce.min.css'),
        )
    ),
    'register_nav_menu'  => array(
	    'menu'  => array(
		    array(
			    'key'   => 'wilcity_menu',
			    'name'  => esc_html__('WilCity Menu', 'wilcity'),
		    )
	    ),
	    'config'=> array(
		    'wilcity_menu'=> array(
			    'theme_location'  => 'wilcity_menu',
			    'name'            => esc_html__('WilCity Menu', 'wilcity'),
			    'menu'            => '',
			    'container'       => '',
			    'container_class' => '',
			    'container_id'    => '',
			    'menu_class'      => 'nav-menu',
			    'menu_id'         => 'wilcity-menu',
			    'echo'            => true,
			    'before'          => '',
			    'after'           => '',
			    'link_before'     => '',
			    'link_after'      => '',
			    'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
			    'depth'           => 0,
			    'walker'          => ''
		    )
	    )
    ),
);