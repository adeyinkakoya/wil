<div id="wilcity-line-loading" class="hidden line-loading_module__SUlA1 pos-a-top"><div class="line-loading_loader__FjIcM"></div><div class="core-code-html" style="height: 0; overflow: hidden; visibility: hidden;"><span data-toggle-html-button="line-loading_module__SUlA1 pos-a-top" data-title="line-loading_module" data-toggle-number-button="65"></span></div></div>
<div id="wilcity-root" class="page-wrap">
	<?php
	global $wiloke;
	$menuLocation = $wiloke->aConfigs['frontend']['register_nav_menu']['menu'][0]['key'];
	?>
	<!-- header_module__Snpib -->
	<header id="wilcity-header-section" class="header_module__Snpib js-header-sticky" data-header-theme="<?php echo esc_attr(apply_filters('wilcity/header/header-style', 'dark')); ?>" data-menu-color="<?php echo esc_attr(WilokeThemeOptions::getColor('general_menu_color')); ?>">
		<?php do_action('wilcity/after-open-header-tag'); ?>
		<div class="wil-tb">
			<div class="wil-tb__cell">
				<div class="header_logo__2HmDH js-header-logo">
					<a href="<?php echo esc_url(home_url('/')); ?>">
						<?php if ( isset($wiloke->aThemeOptions['general_logo']['url']) ) : ?>
							<img src="<?php echo esc_url($wiloke->aThemeOptions['general_logo']['url']); ?>" alt="<?php bloginfo('name'); ?>"/>
						<?php else: ?>
							<?php echo get_bloginfo(); ?>
						<?php endif; ?>
					</a>
				</div>
			</div>
			<?php
			if ( class_exists('\WilokeListingTools\Framework\Helpers\GetSettings') ) :
				$aQuickSearchForm = \WilokeListingTools\Framework\Helpers\GetSettings::getOptions('quick_search_form_settings');
				if ( !isset($aQuickSearchForm['quick_search_form_settings']) || ($aQuickSearchForm['quick_search_form_settings'] == 'yes' ) ) : ?>
					<div class="wil-tb__cell">
						<?php get_template_part('templates/quick-search'); ?>
					</div>
				<?php endif; endif; ?>

			<div class="wil-tb__cell">
				<div class="header_navWrapper__B2C9n">
					<?php if ( has_nav_menu($menuLocation) ) : ?>
						<nav class="wil-nav">
							<?php wp_nav_menu($wiloke->aConfigs['frontend']['register_nav_menu']['config'][$menuLocation]); ?>
						</nav>
					<?php endif; ?>
					<?php
					/*
					 * AddListingButtonController@printAddListingButton 5
					 * RegisterLoginController@printRegisterLoginButton 20
					 * DashboardController@printProfileNavigation 30
					 * NotificationsController@quickNotification 10
					 */
					do_action('wilcity/header/after-menu');
					?>
					<div class="header_loginItem__oVsmv"><a class="header_loginHead__3HoVP toggle-menu-mobile" href="#" data-menu-toggle="vertical"><i class="la la-bars"></i></a></div>
				</div>
			</div>
		</div>
		<?php do_action('wilcity/before-close-header-tag'); ?>
	</header><!-- End / header_module__Snpib -->
	<?php
	if ( has_nav_menu($menuLocation) ) :
	$aNavMenuConfiguration = $wiloke->aConfigs['frontend']['register_nav_menu']['config'][$menuLocation];
	$aNavMenuConfiguration['menu_id'] = 'wilcity-mobile-menu';
	?>
	<nav class="nav-mobile" data-menu-content="vertical">
		<?php wp_nav_menu($aNavMenuConfiguration); ?>
	</nav>
<?php endif; ?>