<?php
$aConfigureMailchimp = array();

if ( !function_exists('wilcitylistBusinessHours') ){
	function wilcitylistBusinessHours(){
		if ( function_exists('wilokeListingToolsRepository') ){
			$aRawBusinessHours = \WilokeListingTools\Framework\Helpers\General::generateBusinessHours();
			$aBusinessHours = array(''=>'---');
			foreach ($aRawBusinessHours as $aData){
				$aBusinessHours[$aData['value']] = $aData['name'];
			}
			return $aBusinessHours;
		}else{
			return array(
				'' => 'Please activate Wiloke Listing Tools: Appearance -> Install Plugins'
			);
		}
	}
}


return array(
	'menu_name' => esc_html__('Theme Options', 'wilcity'),
	'menu_slug' => 'wiloke',
	'redux'     => array(
		'args'      => array(
			// TYPICAL -> Change these values as you need/desire
			'opt_name'             => 'wiloke_options',
			// This is where your data is stored in the database and also becomes your global variable name.
			'display_name'         => 'wiloke',
			// Name that appears at the top of your panel
			'display_version'      => WILOKE_THEMEVERSION,
			// Version that appears at the top of your panel
			'menu_type'            => 'submenu',
			//Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
			'allow_sub_menu'       => false,
			// Show the sections below the admin menu item or not
			'menu_title'           => esc_html__( 'Theme Options', 'wilcity' ),
			'page_title'           => esc_html__( 'Theme Options', 'wilcity' ),
			// You will need to generate a Google API key to use this feature.
			// Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
			'google_api_key'       => '',
			// Set it you want google fonts to update weekly. A google_api_key value is required.
			'google_update_weekly' => false,
			// Must be defined to add google fonts to the typography module
			'async_typography'     => true,
			// Use a asynchronous font on the front end or font string
			//'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own google fonts loader
			'admin_bar'            => true,
			// Show the panel pages on the admin bar
			'admin_bar_icon'     => 'dashicons-portfolio',
			// Choose an icon for the admin bar menu
			'admin_bar_priority' => 50,
			// Choose an priority for the admin bar menu
			'global_variable'      => '',
			// Set a different name for your global variable other than the opt_name
			'dev_mode'             => false,
			// Show the time the page took to load, etc
			'update_notice'        => false,
			// If dev_mode is enabled, will notify developer of updated versions available in the GitHub Repo
			'customizer'           => false,
			// Enable basic customizer support
			//'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
			//'disable_save_warn' => true,                    // Disable the save warning when a user changes a field

			// OPTIONAL -> Give you extra features
			'page_priority'        => null,
			// Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
			'page_parent'          => 'themes.php',
			// For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
			'page_permissions'     => 'manage_options',
			// Permissions needed to access the options panel.
			'menu_icon'            => '',
			// Specify a custom URL to an icon
			'last_tab'             => '',
			// Force your panel to always open to a specific tab (by id)
			'page_icon'            => 'icon-themes',
			// Icon displayed in the admin panel next to your menu_title
			'page_slug'            => '',
			// Page slug used to denote the panel, will be based off page title then menu title then opt_name if not provided
			'save_defaults'        => true,
			// On load save the defaults to DB before user clicks save or not
			'default_show'         => false,
			// If true, shows the default value next to each field that is not the default value.
			'default_mark'         => '',
			// What to print by the field's title if the value shown is default. Suggested: *
			'show_import_export'   => true,
			// Shows the Import/Export panel when not used as a field.

			// CAREFUL -> These options are for advanced use only
			'transient_time'       => 60 * MINUTE_IN_SECONDS,
			'output'               => true,
			// Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
			'output_tag'           => true,
			// Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
			// 'footer_credit'     => '',                   // Disable the footer credit of Redux. Please leave if you can help it.

			// FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
			'database'             => '',
			// possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
			'system_info'          => false,
			// REMOVE

			// HINTS
			'hints'             => array(
				'icon'          => 'el el-question-sign',
				'icon_position' => 'right',
				'icon_color'    => 'lightgray',
				'icon_size'     => 'normal',
				'tip_style'     => array(
					'color'   => 'light',
					'shadow'  => true,
					'rounded' => false,
					'style'   => '',
				),
				'tip_position'  => array(
					'my' => 'top left',
					'at' => 'bottom right',
				),
				'tip_effect'    => array(
					'show' => array(
						'effect'   => 'slide',
						'duration' => '500',
						'event'    => 'mouseover',
					),
					'hide' => array(
						'effect'   => 'slide',
						'duration' => '500',
						'event'    => 'click mouseleave',
					),
				),
			)
		),
		'sections'  => apply_filters('wilcity/theme-options/configurations',array(
			array(
				'title'            => esc_html__('General Settings', 'wilcity'),
				'id'               => 'general_settings',
				'subsection'       => false,
				'icon'             => 'dashicons dashicons-admin-generic',
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'general_favicon',
						'description' => 'You should upload PNG format',
						'type'      => 'media',
						'title'     => 'Favicon',
						'default'   => ''
					),
					array(
						'id'        => 'general_logo',
						'type'      => 'media',
						'title'     => esc_html__('Logo', 'wilcity'),
						'default'   => ''
					),
					array(
						'id'        => 'general_retina_logo',
						'type'      => 'media',
						'title'     => esc_html__('Retina Logo', 'wilcity'),
						'default'   => ''
					),
					array(
						'id'        => 'general_listing_logo',
						'type'      => 'media',
						'title'     => 'Listing Logo',
						'description'=> 'If a listing does not have a logo, this logo will be used.',
					),
					array(
						'id'        => 'general_menu_color',
						'title'     => 'Menu Color',
						'type'      => 'color_rgba',
					),
					array(
						'id'        => 'general_author_menu_background',
						'type'      => 'select',
						'title'     => 'Author Menu Background',
						'default'   => 'transparent',
						'options'   => array(
							'transparent' => 'Transparent',
							'dark' => 'Dark',
							'light' => 'Light',
							'custom' => 'Custom Background Color'
						)
					),
					array(
						'id'        => 'general_author_custom_menu_background',
						'type'      => 'color_rgba',
						'title'     => 'Author Custom Background Color',
						'default'   => '',
						'required'  => array('general_author_menu_background', '=', 'custom')
					),
					array(
						'id'        => 'general_listing_menu_background',
						'type'      => 'select',
						'title'     => 'Listing Details Menu Background',
						'default'   => 'transparent',
						'options'   => array(
							'transparent' => 'Transparent',
							'dark' => 'Dark',
							'light' => 'Light',
							'custom'=> 'Custom'
						)
					),
					array(
						'id'        => 'general_custom_listing_menu_background',
						'type'      => 'color_rgba',
						'title'     => 'Listing Custom Background Color',
						'default'   => '',
						'required'  => array('general_listing_menu_background', '=', 'custom')
					),
					array(
						'id'        => 'general_menu_background',
						'type'      => 'select',
						'title'     => 'Menu Background (Excluding Listing Details)',
						'default'   => 'dark',
						'options'   => array(
							'dark' => 'Dark',
							'light' => 'Light',
							'transparent' => 'Transparent',
							'custom'=> 'Custom'
						)
					),
					array(
						'id'        => 'general_custom_menu_background',
						'type'      => 'color_rgba',
						'title'     => 'Menu Custom Background Color (Excluding Listing Details)',
						'default'   => '',
						'required'  => array('general_menu_background', '=', 'custom')
					),
					array(
						'id'        => 'general_toggle_follow',
						'type'      => 'select',
						'title'     => 'Toggle Follow Feature',
						'default'   => 'enable',
						'options'   => array(
							'enable'    => 'Enable',
							'disable'   => 'Disable'
						)
					),
					array(
						'id'        => 'general_toggle_show_full_text',
						'type'      => 'select',
						'title'     => 'Always Show Full Text',
						'description' => 'For instance, we have this text "I want to show full text". If this feature is disabled, it will show "I want to ...." on the small screen.',
						'default'   => 'disable',
						'options'   => array(
							'enable'    => 'Enable',
							'disable'   => 'Disable'
						)
					)
				)
			),
			array(
				'title'            => 'SEO',
				'id'               => 'seo_settings',
				'subsection'       => false,
				'icon'             => 'dashicons dashicons-search',
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'toggle_fb_ogg_tag_to_listing',
						'type'      => 'select',
						'title'     => 'Added \'og:image\' property to Listing Page.',
						'options'   => array(
							'enable'  => 'Enable',
							'disable' => 'Disable'
						),
						'default' => 'enable'
					),
				)
			),
			array(
				'title'            => esc_html__('Front-end Dashboard', 'wilcity'),
				'id'               => 'frontend_dashboard',
				'subsection'       => false,
				'icon'             => 'dashicons dashicons-feedback',
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'dashboard_profile_section',
						'type'      => 'section',
						'title'     => 'Profile Section',
						'indent'    => true
					),
					array(
						'id'        => 'dashboard_profile_description',
						'type'      => 'textarea',
						'title'     => 'Description',
						'default'   => 'We do not sell or share your details without your permission. Find out more in our <a href="#">Privacy Policy</a>.'
					),
					array(
						'id'        => 'dashboard_profile_section_end',
						'type'      => 'section',
						'indent'    => false
					)
				)
			),
			array(
				'title'            => esc_html__('Register And Login', 'wilcity'),
				'id'               => 'register_login',
				'subsection'       => false,
				'icon'             => 'dashicons dashicons-feedback',
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'toggle_register',
						'type'      => 'select',
						'title'     => esc_html__('Toggle Register', 'wilcity'),
						'options'   => array(
							'enable'  => 'Enable',
							'disable' => 'Disable'
						),
						'default' => 'enable'
					),
					array(
						'id'        => 'toggle_google_recaptcha',
						'required'  => array('toggle_register', '=', 'enable'),
						'options'   => array(
							'enable'  => 'Enable',
							'disable' => 'Disable'
						),
						'title'     => 'Toggle Google reCaptcha',
						'default'   => 'disable',
						'type'      => 'select'
					),
					array(
						'id'        => 'recaptcha_site_key',
						'required'  => array('toggle_google_recaptcha', '=', 'enable'),
						'title'     => 'Google reCAPTCHA - Site Key',
						'default'   => '',
						'type'      => 'text'
					),
					array(
						'id'        => 'recaptcha_secret_key',
						'required'  => array('toggle_google_recaptcha', '=', 'enable'),
						'title'     => 'Google reCAPTCHA - Secret Key',
						'default'   => '',
						'type'      => 'text'
					),
					array(
						'id'        => 'toggle_privacy_policy',
						'required'  => array('toggle_register', '=', 'enable'),
						'options'   => array(
							'enable'  => 'Enable',
							'disable' => 'Disable'
						),
						'title'     => esc_html__('Toggle agree To the Privacy Policy', 'wilcity'),
						'default'   => 'enable',
						'type'      => 'select'
					),
					array(
						'id'        => 'privacy_policy_desc',
						'title'     => esc_html__('Privacy Policy Description', 'wilcity'),
						'type'      => 'textarea',
						'required'  => array('toggle_privacy_policy', '=', 'enable'),
						'default'   => 'I agree to the <a href="#" target="_blank">Privacy Policy</a>'
					),
					array(
						'id'        => 'toggle_terms_and_conditionals',
						'required'  => array('toggle_register', '=', 'enable'),
						'options'   => array(
							'enable'  => 'Enable',
							'disable' => 'Disable'
						),
						'title'     => esc_html__('Toggle Terms and conditionals', 'wilcity'),
						'default'   => 'enable',
						'type'      => 'select'
					),
					array(
						'id'        => 'terms_and_conditionals_desc',
						'type'      => 'textarea',
						'title'     => esc_html__('Terms and conditionals description', 'wilcity'),
						'required'  => array('toggle_terms_and_conditionals', '=', 'enable'),
						'default'   => 'I agree to the <a href="#" target="_blank">Terms and Conditions</a>'
					),
					array(
						'id'        => 'login_redirect_type',
						'type'      => 'select',
						'options'   => array(
							'specify_page' => esc_html__('Specify page', 'wilcity'),
							'self_page' => esc_html__('Self page', 'wilcity')
						),
						'default' => 'self_page',
						'title'     => esc_html__('Login Redirect Type', 'wilcity'),
						'description'=> esc_html__('Leave empty to refresh the self page', 'wilcity')
					),
					array(
						'id'        => 'login_redirect_to',
						'type'      => 'select',
						'required'  => array('login_redirect_type', '=', 'specify_page'),
						'data'      => 'posts',
						'args'  => array(
							'post_type'=> 'page',
							'posts_per_page' => 100
						),
						'title'     => esc_html__('Login Redirect To', 'wilcity'),
						'description'=> esc_html__('Leave empty to refresh the self page', 'wilcity')
					),
					array(
						'id'        => 'toggle_confirmation',
						'type'      => 'select',
						'title'     => 'Confirm Users Email Address',
						'required'  => array('toggle_terms_and_conditionals', '=', 'enable'),
						'default'   => 'disable',
						'options'   => array(
							'disable' => 'Disable',
							'enable'  => 'Enable'
						)
					),
					array(
						'id'        => 'confirmation_notification',
						'type'      => 'textarea',
						'title'     => 'Confirmation Notification',
						'required'  => array('toggle_confirmation', '=', 'enable'),
						'default'   => 'Wait ... It is almost done! We sent a confirmation link to your email address. Check your mailbox now, then click on the link to activate your account.'
					),
					array(
						'id'        => 'confirmation_page',
						'type'      => 'select',
						'required'  => array('toggle_confirmation', '=', 'enable'),
						'data'      => 'posts',
						'args'  => array(
							'post_type'=> 'page',
							'posts_per_page' => 100
						),
						'title'     => 'Confirmation Page',
						'description'=> 'Go to page -> Create a new page and then set this page to Wilcity Confirm Account template -> Assign the page to this field. When customer clicks on the confirmation link on his/her email, it will redirect to this page.'
					),
					array(
						'id'        => 'created_account_redirect_to',
						'type'      => 'select',
						'data'      => 'posts',
						'args'  => array(
							'post_type'=> 'page',
							'posts_per_page' => 100
						),
						'title'     => esc_html__('Redirect to this page after creating an account', 'wilcity')
					),
					array(
						'id'        => 'welcome_message',
						'type'      => 'textarea',
						'title'     => 'Internal Welcome Message',
						'description' => 'This is the welcome message to new users using the WilCity onsite messaging system',
						'default'   => 'Thank You for joining us today! Wilcity is a WordPress theme that helps you easily build any type of directory website. To learn more about Wilcity, please watch <a href="https://www.youtube.com/channel/UCFcStj2m0N7YOkuP0bmCmfA" target="_blank" style="color: red;">Wilcity Tutorial</a>'
					),
					array(
						'id'        => 'reset_password_page',
						'type'      => 'select',
						'data'      => 'posts',
						'args'  => array(
							'post_type'=> 'page',
							'posts_per_page' => 100
						),
						'title'         => 'Front-end Reset Password Page',
						'description'   => 'The users will reset their password on this page instead of the default WordPress page. To create a Reset Password, please click on Pages -> Add New -> Set this page to Reset Password template.'
					),
					array(
						'id'        => 'general_fb_info',
						'title'     => 'Facebook Login',
						'type'      => 'section',
						'indent'    => true
					),
					array(
						'id'        => 'fb_toggle_login',
						'type'      => 'select',
						'title'     => 'Toggle Facebook Login',
						'default'   => 'disable',
						'options'   => array(
							'enable' => 'Enable',
							'disable'=> 'Disable'
						)
					),
					array(
						'id'        => 'fb_api_language',
						'type'      => 'text',
						'title'     => 'Language',
						'description'=> 'Please read <a href="https://developers.facebook.com/docs/internationalization/" target="_blank">Facebook Localization</a> to know more',
						'default'   => 'en_US'
					),
					array(
						'id'        => 'fb_api_id',
						'type'      => 'text',
						'title'     => 'Api ID',
						'default'   => ''
					),
					array(
						'id'        => 'fb_app_secret',
						'type'      => 'password',
						'title'     => 'Api Secret',
						'default'   => ''
					),
					array(
						'id'        => 'fb_access_token',
						'type'      => 'password',
						'title'     => 'Access Token',
						'default'   => ''
					),
					array(
						'id'        => 'close_general_fb_info',
						'title'     => '',
						'type'      => 'section',
						'indent'    => false
					),
					array(
						'id'        => 'general_fb_info',
						'title'     => 'Mobile Settings',
						'type'      => 'section',
						'indent'    => true
					),
					array(
						'id'        => 'mobile_term_label',
						'type'      => 'text',
						'title'     => 'Term Term Label',
						'default'   => 'Agree to our terms and conditional'
					),
					array(
						'id'        => 'mobile_term_page',
						'type'      => 'select',
						'data'      => 'posts',
						'args'  => array(
							'post_type'=> 'page',
							'posts_per_page' => 100
						),
						'title'     => 'Term Page'
					),
					array(
						'id'        => 'mobile_policy_label',
						'type'      => 'text',
						'title'     => 'Policy Label',
						'default'   => 'Agree to our Policy Privacy'
					),
					array(
						'id'        => 'mobile_policy_page',
						'type'      => 'select',
						'data'      => 'posts',
						'args'  => array(
							'post_type'=> 'page',
							'posts_per_page' => 100
						),
						'title'     => 'Policy Page'
					),
					array(
						'id'        => 'general_fb_info',
						'title'     => '',
						'type'      => 'section',
						'indent'    => false
					)
				)
			),
			array(
				'title'            => esc_html__('Users', 'wilcity'),
				'id'               => 'users_settings',
				'subsection'       => false,
				'icon'             => 'dashicons dashicons-admin-users',
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'cover_image',
						'type'      => 'media',
						'title'     => esc_html__('Default Cover Image', 'wilcity'),
						'default'   => ''
					),
					array(
						'id'        => 'user_avatar',
						'type'      => 'media',
						'title'     => esc_html__('Default User Avatar', 'wilcity'),
						'default'   => ''
					),
					array(
						'id'        => 'user_toggle_follow',
						'type'      => 'select',
						'title'     => esc_html__('Toggle Follow Feature', 'wilcity'),
						'default'   => 'enable',
						'options'   => array(
							'enable' => 'Enable',
							'disable'=> 'Disable'
						)
					),
				)
			),
			array(
				'title'            => 'Customize Taxonomies',
				'id'               => 'customize_taxonomies_slug',
				'icon'             => 'dashicons dashicons-admin-links',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'listing_location_featured_image',
						'type'      => 'media',
						'title'     => 'Default Location Featured Image',
						'default'   => ''
					),
					array(
						'id'        => 'listing_cat_featured_image',
						'type'      => 'media',
						'title'     => 'Default Category Featured Image',
						'default'   => ''
					),
					array(
						'id'        => 'listing_tag_featured_image',
						'type'      => 'media',
						'title'     => 'Default Tag Featured Image',
						'default'   => ''
					),
					array(
						'id'        => 'listing_location_slug',
						'type'      => 'text',
						'description' => 'After changing the slug, please click on Settings -> Permalinks -> Re-save Post Name',
						'title'     => 'Listing Location Slug',
						'default'   => 'listing-location'
					),
					array(
						'id'        => 'listing_cat_slug',
						'type'      => 'text',
						'title'     => 'Listing Category Slug',
						'default'   => 'listing-cat'
					),
					array(
						'id'        => 'listing_tag_slug',
						'type'      => 'text',
						'title'     => 'Listing Tag Slug',
						'default'   => 'listing-tag'
					),
					array(
						'id'        => 'taxonomy_image_size',
						'type'      => 'text',
						'title'     => 'Custom Taxonomy Image Size',
						'desc'      => 'Set the image size for listing on Listing Location, Listing Category and Listing Tag Page',
						'default'   => ''
					),
					array(
						'id'        => 'open_sub_taxonomies_section',
						'type'      => 'section',
						'title'     => 'Sub Categories Settings',
						'indent'    => true
					),
					array(
						'id'        => 'sub_taxonomies_columns',
						'type'      => 'select',
						'title'     => 'Number of Columns',
						'description'      => 'Set the image size for listing on Listing Location, Listing Category and Listing Tag Page',
						'default'   => 'col-md-6 col-lg-6',
						'options'   => array(
							'col-md-6 col-lg-6' => '2 Columns',
							'col-md-4 col-lg-4' => '3 Columns',
							'col-md-3 col-lg-3' => '4 Columns'
						)
					),
					array(
						'id'        => 'sub_taxonomies_maximum_can_be_shown',
						'type'      => 'text',
						'title'     => 'Maximum Taxonomies Can Be Shown',
						'default'   => 1000
					),
					array(
						'id'        => 'sub_taxonomies_orderby',
						'type'      => 'select',
						'title'     => 'Sub-Categories Order By',
						'default'   => 'count',
						'options'   => array(
							'count'     => 'Number of children',
							'name'      => 'Term Name',
							'term_order'=> 'Term Order',
							'id'        => 'Term ID',
							'slug'      => 'Term Slug',
							'none'      => 'None',
							'include'   => 'Include'
						)
					),
					array(
						'id'        => 'sub_taxonomies_order',
						'type'      => 'select',
						'title'     => 'Sub-Categories Order',
						'default'   => 'DESC',
						'options'   => array(
							'DESC' => 'DESC',
							'ASC'  => 'ASC'
						)
					),
					array(
						'id'        => 'sub_taxonomies_toggle_show_some_listings',
						'type'      => 'select',
						'title'     => 'Toggle Show Some Listings Belongs To This Category',
						'default'   => 'enable',
						'options'   => array(
							'enable'    => 'Enable',
							'disable'   => 'Disable'
						)
					),
					array(
						'id'        => 'sub_taxonomies_listings_title',
						'type'      => 'text',
						'title'     => 'Listings Title',
						'default'   => 'Popular Listings'
					),
					array(
						'id'        => 'sub_taxonomies_listings_columns',
						'type'      => 'select',
						'title'     => 'Number of Sub-Locations/Categories Columns',
						'default'   => 'col-md-6 col-lg-6',
						'options'   => array(
							'col-md-6 col-lg-6' => '2 Columns',
							'col-md-4 col-lg-4' => '3 Columns',
							'col-md-3 col-lg-3' => '4 Columns'
						),
						'required'  => array('sub_taxonomies_toggle_show_some_listings', '=', 'enable'),
					),
					array(
						'id'        => 'sub_taxonomies_maximum_listings_can_be_shown',
						'type'      => 'text',
						'title'     => 'Maximum Listings Can Be Shown',
						'default'   => 8,
						'required'  => array('sub_taxonomies_toggle_show_some_listings', '=', 'enable'),
					),
					array(
						'id'        => 'sub_taxonomies_maximum_listings_orderby',
						'type'      => 'select',
						'title'     => 'Listings Order By',
						'default'   => 'menu_order post_date',
						'options'   => array(
							'post_date'         => 'Listing Date',
							'post_title'        => 'Listing Title',
							'menu_order post_date' => 'Listing Order',
							'best_viewed'       => 'Popular Viewed',
							'best_rated'        => 'Popular Rated',
							'best_shared'       => 'Popular Shared'
						),
						'required'  => array('sub_taxonomies_toggle_show_some_listings', '=', 'enable'),
					),
					array(
						'id'        => 'sub_taxonomies_maximum_listings_order',
						'type'      => 'select',
						'title'     => 'ORDER',
						'default'   => 'DESC',
						'required'  => array('sub_taxonomies_toggle_show_some_listings', '=', 'enable'),
						'options'   => array(
							'DESC'  => 'DESC',
							'ASC'   => 'ASC'
						)
					),
					array(
						'id'        => 'close_sub_taxonomies_section',
						'type'      => 'section',
						'title'     => '',
						'indent'    => false
					)
				)
			),
			array(
				'title'            => 'Google Map Settings',
				'id'               => 'google_map_settings',
				'icon'             => 'dashicons dashicons-location',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields' => array(
					array(
						'id'        => 'general_google_api',
						'type'      => 'text',
						'title'     => esc_html__('Map API', 'wilcity'),
						'default'   => ''
					),
					array(
						'id'        => 'general_google_language',
						'description' => 'You can find your Country Code on <a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2" target="_blank">Wiki ISO 3166-1 alpha-2</a>',
						'type'      => 'text',
						'title'     => 'Google Language',
						'default'   => ''
					),
					array(
						'id'        => 'map_grid_size',
						'type'      => 'text',
						'title'     => 'Grid Size',
						'description' => 'The grid size of a cluster in pixels.',
						'default'   => 10
					),
					array(
						'id'        => 'map_max_zoom',
						'type'      => 'text',
						'title'     => 'Maximum Zoom Value',
						'description' => 'If you are using a cache plugin, please flush cache to this setting take effect on your site.',
						'default'   => 21
					),
					array(
						'id'        => 'map_minimum_zoom',
						'type'      => 'text',
						'title'     => 'Minimum Zoom Value',
						'default'   => 1
					),
					array(
						'id'        => 'map_default_zoom',
						'type'      => 'text',
						'title'     => 'Default Zoom Value',
						'default'   => 2
					),
					array(
						'id'        => 'map_center',
						'type'      => 'text',
						'title'     => esc_html__('Default Map Center', 'wilcity'),
						'description'=> esc_html__('Enter in the default latitude and longitude. For example: -33.866,151.196', 'wilcity')
					),
					array(
						'id'        => 'map_bound_toggle',
						'type'      => 'select',
						'default'   => 'disable',
						'title'     => esc_html__('Enable Map Bound Feature', 'wilcity'),
						'subtitle'  => esc_html__('This setting is useful for local business.', 'wilcity'),
						'options'   => array(
							'enable'  => 'Enable',
							'disable' => 'Disable'
						)
					),
					array(
						'id'        => 'map_bound_start',
						'type'      => 'text',
						'required'  => array('map_bound_toggle', '=', 'enable'),
						'title'     => esc_html__('Google Map Bound Start', 'wilcity'),
						'subtitle'  => esc_html__('This setting is useful for local business.', 'wilcity'),
						'description' => 'Enter in the Latitude and Longitude of the start position. For example: 12356,7890. 12356 is Latitude. 7890 is Longitude. You can easily find the Latitude and Longitude at https://www.latlong.net/'
					),
					array(
						'id'        => 'map_bound_end',
						'required'  => array('map_bound_toggle', '=', 'enable'),
						'type'      => 'text',
						'title'     => esc_html__('Google Map Bound End', 'wilcity'),
						'description' => 'Enter in the Latitude and Longitude of the end position.'
					),
					array(
						'id'        => 'map_theme',
						'type'      => 'select',
						'title'     => 'Map Style',
						'default'   => 'black',
						'options'   => array(
							'black' => 'black',
							'blurWater' => 'blurWater',
							'ultraLight' => 'ultraLight',
							'shadesOfGrey' => 'shadesOfGrey',
							'sergey' => 'Sergey',
							'custom'       => 'custom'
						)
					),
					array(
						'id'        => 'map_custom_theme',
						'type'      => 'textarea',
						'title'     => 'Map Theme',
						'description'=> 'You can get the map theme at <a href="https://snazzymaps.com/" target="_blank">www.snazzymaps.com</a>',
						'required'  => array('map_theme', '=', 'custom')
					),
					array(
						'id'        => 'google_map_single_map_open',
						'type'      => 'section',
						'title'     => 'Google Map On Single Listing Page',
						'indent'    => true
					),
					array(
						'id'        => 'single_map_max_zoom',
						'type'      => 'text',
						'title'     => 'Maximum Zoom Value',
						'description' => 'If you are using a cache plugin, please flush cache to this setting take effect on your site.',
						'default'   => 21
					),
					array(
						'id'        => 'single_map_minimum_zoom',
						'type'      => 'text',
						'title'     => 'Minimum Zoom Value',
						'default'   => 1
					),
					array(
						'id'        => 'single_map_default_zoom',
						'type'      => 'text',
						'title'     => 'Default Zoom Value',
						'default'   => 3
					),
					array(
						'id'        => 'google_map_section_close',
						'type'      => 'section',
						'indent'    => false
					),
					array(
						'id'        => 'google_map_single_event_open',
						'type'      => 'section',
						'title'     => 'Google Map On Single Event Page',
						'indent'    => true
					),
					array(
						'id'        => 'single_event_map_max_zoom',
						'type'      => 'text',
						'title'     => 'Maximum Zoom Value',
						'description' => 'If you are using a cache plugin, please flush cache to this setting take effect on your site.',
						'default'   => 21
					),
					array(
						'id'        => 'single_event_map_minimum_zoom',
						'type'      => 'text',
						'title'     => 'Minimum Zoom Value',
						'default'   => 1
					),
					array(
						'id'        => 'single_event_map_default_zoom',
						'type'      => 'text',
						'title'     => 'Default Zoom Value',
						'default'   => 5
					),
					array(
						'id'        => 'google_map_single_event_open_close',
						'type'      => 'section',
						'indent'    => false
					),
				)
			),
			array(
				'title'            => esc_html__('Directory Type', 'wilcity'),
				'id'               => 'listing_settings',
				'icon'             => 'dashicons dashicons-palmtree',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'listing_template',
						'type'      => 'select',
						'title'     => 'Listing Template',
						'default'   => 'featured_image_fullwidth',
						'options'   => array(
							'featured_image_fullwidth' => 'Featured Image Full-Width',
							'slider' => 'Slider'
						)
					),
					array(
						'id'        => 'listing_slider_img_size',
						'type'      => 'text',
						'title'     => 'Listing Slider Image Size',
						'required'  => array('listing_template', '=', 'slider'),
						'default'   => 'medium'
					),
					array(
						'id'        => 'listing_slider_autoplay',
						'type'      => 'text',
						'title'     => 'Delay between transitions (in ms). Leave empty to means Auto Play feature.',
						'required'  => array('listing_template', '=', 'slider'),
						'default'   => 5000
					),
					array(
						'id'        => 'listing_overlay_color',
						'type'      => 'color_rgba',
						'title'     => 'Overlay Color',
						'default'   => ''
					),
					array(
						'id'        => 'listing_posts_per_page',
						'type'      => 'text',
						'title'     => esc_html__('Posts Per Page', 'wilcity'),
						'default'   => 10
					),
					array(
						'id'        => 'listing_excerpt_length',
						'type'      => 'text',
						'title'     => esc_html__('Excerpt Length', 'wilcity'),
						'description' => esc_html__('If the tagline is empty, the excerpt length will be used.', 'wilcity'),
						'default'   => 40
					),
					array(
						'id'        => 'listing_featured_image',
						'type'      => 'media',
						'title'     => esc_html__('Featured Image', 'wilcity'),
						'desc'      => esc_html__('If the featured image is emptied, this image will be used', 'wilcity'),
						'default'   => ''
					),
					array(
						'id'        => 'listing_video_thumbnail',
						'type'      => 'media',
						'title'     => esc_html__('Video Thumbnail', 'wilcity'),
						'desc'      => esc_html__('If the video does not come from Youtube / Vimeo, the default thumbnail will used.', 'wilcity'),
						'default'   => ''
					),
					array(
						'id'        => 'timeformat',
						'type'      => 'select',
						'title'     => esc_html__('Time Format', 'wilcity'),
						'desc'      => esc_html__('You can override this setting in each listing', 'wilcity'),
						'default'   => 12,
						'options'   => array(
							12 => '12h Format',
							24 => '24h Format'
						)
					),
					array(
						'id'        => 'map_page',
						'type'      => 'select',
						'data'      => 'posts',
						'post_types'=> array('page'),
						'title'     => esc_html__('Map page', 'wilcity'),
						'default'   => 12,
						'args'      => array(
							'post_type'         => 'page',
							'posts_per_page'    => -1,
							'orderby'           => 'post_date',
							'post_status'       => 'publish'
						),
					),
					array(
						'id'        => 'search_page',
						'type'      => 'select',
						'data'      => 'posts',
						'post_types'=> array('page'),
						'title'     => esc_html__('Search page', 'wilcity'),
						'default'   => 12,
						'args'      => array(
							'post_type'         => 'page',
							'posts_per_page'    => -1,
							'orderby'           => 'post_date',
							'post_status'       => 'publish'
						),
					),
					array(
						'id'        => 'listing_toggle_favorite',
						'type'      => 'select',
						'title'     => esc_html__('Toggle Favorite Feature', 'wilcity'),
						'default'   => 'enable',
						'options'   => array(
							'enable'  => 'Enable',
							'disbale' => 'Disable'
						)
					),
//			        array(
//				        'id'        => 'general_open_search_form_section',
//				        'type'      => 'section',
//				        'title'     => 'General Search Form Settings',
//				        'indent'    => true
//			        ),
//			        array(
//				        'id'      => 'search_country_restriction',
//				        'type'    => 'text',
//				        'title'   => 'Country restriction',
//				        'description'   => 'You can find your country code at <a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2" target="_blank">https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2</a>',
//				        'default' => ''
//			        ),
//			        array(
//				        'id'        => 'general_close_search_form_section',
//				        'type'      => 'section',
//				        'indent'    => false
//			        ),
					array(
						'id'        => 'listing_open_bh_section',
						'type'      => 'section',
						'title'     => esc_html__('Business Hours', 'wilcity'),
						'indent'    => true
					),
					array(
						'id'        => 'listing_default_opening_hour',
						'type'      => 'select',
						'title'     => esc_html__('Default Opening Hour', 'wilcity'),
						'default'   => 'select',
						'options'   => wilcitylistBusinessHours()
					),
					array(
						'id'        => 'listing_default_closed_hour',
						'type'      => 'select',
						'title'     => esc_html__('Default Closed Hour', 'wilcity'),
						'default'   => 'select',
						'options'   => wilcitylistBusinessHours()
					),
					array(
						'id'        => 'listing_default_second_opening_hour',
						'type'      => 'select',
						'title'     => esc_html__('Default Second Opening Hour', 'wilcity'),
						'default'   => 'select',
						'options'   => wilcitylistBusinessHours()
					),
					array(
						'id'        => 'listing_default_second_closed_hour',
						'type'      => 'select',
						'title'     => esc_html__('Default Second Closed Hour', 'wilcity'),
						'default'   => 'select',
						'options'   => wilcitylistBusinessHours()
					),
					array(
						'id'        => 'listing_close_bh_section',
						'type'      => 'section',
						'title'     => '',
						'indent'    => false
					)
				)
			),
			array(
				'title'            => 'Add Listing General Settings',
				'id'               => 'addlisting_general_settings',
				'icon'             => 'dashicons dashicons-edit',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'addlisting_unchecked_features_type',
						'type'      => 'select',
						'title'     => 'Unchecked features will',
						'default'   => 'disable',
						'options'   => array(
							'disable'   => 'Show on Add Listing page, but it will be Disabled',
							'hidden'    => 'It should not shown on Add Listing page'
						)
					),
					array(
						'id'        => 'addlisting_skip_preview_step',
						'type'      => 'select',
						'title'     => 'Skip Preview Step',
						'default'   => 'disable',
						'options'   => array(
							'disable'  => 'Disable',
							'enable'   => 'Enable'
						)
					),
				)
			),
			array(
				'title'            => 'Booking Settings',
				'id'               => 'booking_settings',
				'icon'             => 'dashicons dashicons-book',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'bookingcom_section_open',
						'type'      => 'section',
						'title'     => 'Booking.com Settings',
						'indent'    => true
					),
					array(
						'id'        => 'bookingcom_affiliate_id',
						'type'      => 'text',
						'description' => 'Your affiliate ID is a unique number that allows Booking.com to track commission. If you are not an affiliate yet, check <a href="https://www.booking.com/affiliate-program/v2/index.html" target="_blank">Booking.com affiliate programme</a> and get an affiliate ID. It\'s easy and fast. Start earning money, <a href="https://www.booking.com/affiliate-program/v2/index.html" target="_blank">sign up now!</a>',
						'title'     => 'Your affiliate ID'
					),
					array(
						'id'        => 'bookingcom_section_close',
						'type'      => 'section',
						'indent'    => false
					)
				)
			),
			array(
				'title'            => 'Google AdSense',
				'id'               => 'google_adsense_settings',
				'icon'             => 'dashicons dashicons-megaphone',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'            => 'google_adsense_client_id',
						'type'          => 'text',
						'title'         => 'Client ID',
						'description'   => 'Please read <a href="https://documentation.wilcity.com/knowledgebase/how-to-insert-google-adsense-code-to-wilcity/" target="_blank">How to Insert Google AdSense code to Wilcity?</a> to know how to embed your Google AdsSense code to this field.',
						'default'       => ''
					),
					array(
						'id'            => 'google_adsense_slot_id',
						'type'          => 'text',
						'title'         => 'Slot ID',
						'description'   => '',
						'default'       => ''
					),
					array(
						'id'        => 'google_adsense_directory_type',
						'type'      => 'section',
						'title'     => 'Google Ads on Directory Type',
						'indent'    => true
					),
					array(
						'id'        => 'google_adsense_directory_content_position',
						'type'      => 'select',
						'title'     => 'Content Position',
						'description'=> 'The Google Ads will not show if the listing/event belongs to a Plan that does not allow showing Ads.',
						'options'   => array(
							'above'     => 'Above Listing Content',
							'below'     => 'Below Listing Content',
							'disable'   => 'Do not show'
						),
						'default'   => 'above'
					),
					array(
						'id'        => 'google_adsense_directory_type',
						'type'      => 'section',
						'title'     => '',
						'indent'    => false
					)
				)
			),
			array(
				'title'            => 'Blog',
				'id'               => 'blog_settings',
				'icon'             => 'dashicons dashicons-welcome-write-blog',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'blog_excerpt_length',
						'type'      => 'text',
						'title'     => 'Excerpt Length',
						'default'   => 100
					),
					array(
						'id'        => 'blog_featured_image',
						'type'      => 'media',
						'title'     => 'Default Featured Image',
						'default'   => ''
					)
				)
			),
			array(
				'title'            => 'Sidebar',
				'id'               => 'sidebar_settings',
				'icon'             => 'dashicons dashicons-editor-table',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'blog_sidebar_layout',
						'type'      => 'select',
						'title'     => 'Blog Sidebar Layout',
						'options'   => array(
							'left'  => 'Left Sidebar',
							'right' => 'Right Sidebar',
							'no'    => 'No Sidebar'
						),
						'default'   => 'right'
					),
					array(
						'id'        => 'single_post_sidebar_layout',
						'type'      => 'select',
						'title'     => 'Single Post Sidebar Layout',
						'options'   => array(
							'left'  => 'Left Sidebar',
							'right' => 'Right Sidebar',
							'no'    => 'No Sidebar'
						),
						'default'   => 'right'
					),
					array(
						'id'        => 'single_event_sidebar',
						'type'      => 'select',
						'title'     => 'Single Event Sidebar Layout',
						'options'   => array(
							'left'  => 'Left Sidebar',
							'right' => 'Right Sidebar',
							'no'    => 'No Sidebar'
						),
						'default'   => 'right'
					),
					array(
						'id'        => 'single_page_sidebar_layout',
						'type'      => 'select',
						'title'     => 'Single Page Sidebar Layout',
						'options'   => array(
							'left'  => 'Left Sidebar',
							'right' => 'Right Sidebar',
							'no'    => 'No Sidebar'
						),
						'default'   => 'right'
					),
					array(
						'id'        => 'single_listing_sidebar_layout',
						'type'      => 'select',
						'title'     => 'Single Listing Sidebar Layout',
						'options'   => array(
							'left'  => 'Left Sidebar',
							'right' => 'Right Sidebar',
							'no'    => 'No Sidebar'
						),
						'default'   => 'right'
					),
					array(
						'id'        => 'woocommerce_sidebar',
						'type'      => 'select',
						'title'     => 'WooCommerce Sidebar Layout',
						'options'   => array(
							'left'  => 'Left Sidebar',
							'right' => 'Right Sidebar',
							'no'    => 'No Sidebar'
						),
						'default'   => 'no'
					),
				)
			),
			// Social networks
			array(
				'title'            => esc_html__('Social Networks', 'wilcity'),
				'id'               => 'social_network_settings',
				'subsection'       => false,
				'icon'             => 'dashicons dashicons-share',
				'customizer_width' => '500px',
				'fields'           => WilokeSocialNetworks::render_setting_field()
			),
			array(
				'title'            => esc_html__('404', 'wilcity'),
				'id'               => 'page_not_found',
				'icon'             => 'dashicons dashicons-hidden',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => '404_bg',
						'type'      => 'media',
						'title'     => esc_html__('Image Background', 'wilcity')
					),
					array(
						'id'        => '404_heading',
						'type'      => 'textarea',
						'title'     => esc_html__('Heading', 'wilcity'),
						'default'   => '404'
					),
					array(
						'id'        => '404_description',
						'type'      => 'textarea',
						'title'     => esc_html__('Description', 'wilcity'),
						'default'   => 'Sorry, We couldn\'t find what you were looking for. Maybe try searching for an alternative?'
					)
				)
			),
			array(
				'title'            => 'Footer',
				'id'               => 'footer_settings',
				'icon'             => 'dashicons dashicons-editor-kitchensink',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'footer_items',
						'type'      => 'select',
						'title'     => 'Number Of Footer Items',
						'options'   => array(
							4   => '4 Items',
							3   => '3 Items',
							2   => '2 Items'
						),
						'default'   => 4
					),
					array(
						'id'        => 'copyright',
						'type'      => 'textarea',
						'title'     => 'Copyright',
						'default'   => 'Copyright © 2018 Wiloke.com.'
					)
				)
			),
			array(
				'title'            => 'Customize URL',
				'id'               => 'custom_url_settings',
				'icon'             => 'dashicons dashicons-admin-links',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'taxonomy_add_parent_to_permalinks',
						'type'      => 'select',
						'title'     => 'Add Parent Location To Permalink',
						'options'   => array(
							'disable'  => 'Disable',
							'enable'   => 'Enable'
						),
						'default'   => 'disable'
					),
					array(
						'id'        => 'listing_permalink_settings_open',
						'type'      => 'section',
						'title'     => 'Single Listing Settings',
						'indent'    => true
					),
					array(
						'id'        => 'listing_permalink_settings',
						'type'      => 'text',
						'title'     => 'Listings Permalink Settings',
						'default'   => '',
						'subtitle'  => 'Leave empty to use the default setting. After adding your customize permalink, please go to <a href="'.admin_url('options-permalink.php').'" target="_blank">Settings -> Permalinks</a> -> Re-save Post Name.',
						'description' => 'Please read <a href="https://tinyurl.com/y7cr8z3w" target="_blank">this tutorial</a> to learn how to customize your single listing url.'
					),
					array(
						'id'        => 'listing_mobile_section_close',
						'type'      => 'section',
						'title'     => '',
						'indent'    => false
					)
				)
			),
			array(
				'title'            => esc_html__('Advanced Settings', 'wilcity'),
				'id'               => 'advanced_settings',
				'icon'             => 'dashicons dashicons-dashboard',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'advanced_google_fonts',
						'type'      => 'select',
						'title'     => esc_html__('Google Fonts', 'wilcity'),
						'options'   => array(
							'default'   => esc_html__('Default', 'wilcity'),
							'general'   => esc_html__('Custom', 'wilcity'),
							// 'detail'    => esc_html__('Detail Custom', 'wilcity')
						),
						'default'   => 'default'
					),
					array(
						'id'            => 'advanced_general_google_fonts',
						'type'          => 'text',
						'title'         => esc_html__('Google Fonts', 'wilcity'),
						'required'      => array('advanced_google_fonts', '=', 'general'),
						'description'   => esc_html__('The theme allows replace current Google Fonts with another Google Fonts. Go to https://fonts.google.com/specimen to get a the Font that you want. For example: https://fonts.googleapis.com/css?family=Prompt', 'wilcity')
					),
					array(
						'id'            => 'advanced_general_google_fonts_css_rules',
						'type'          => 'text',
						'required'      => array('advanced_google_fonts', '=', 'general'),
						'title'         => esc_html__('Css Rules', 'wilcity'),
						'description'   => esc_html__('This code shoule be under Google Font link. For example: font-family: \'Prompt\', sans-serif;', 'wilcity')
					),
					array(
						'id'        => 'advanced_main_color',
						'type'      => 'select',
						'title'     => esc_html__('Theme Color', 'wilcity'),
						'options'   => array(
							''        => 'Default',
							'cyan'    => 'Cyan',
							'blue'    => 'Blue',
							'pink'    => 'Pink',
							'red'     => 'red',
							'custom'  => 'Custom'
						),
						'default'   => ''
					),
					array(
						'id'        => 'advanced_custom_main_color',
						'type'      => 'color_rgba',
						'title'     => esc_html__('Custom Color', 'wilcity'),
						'required'  => array('advanced_main_color', '=', 'custom')
					),
					array(
						'id'          => 'sidebar_additional',
						'type'        => 'text',
						'title'       => esc_html__('Add More Sidebar', 'wilcity'),
						'description' => esc_html__('You can add more sidebar by entering in your sidebar id here. For example: my_custom_sidebar_1,my_custom_sidebar_2', 'wilcity'),
						'default'     => ''
					),
					array(
						'id'        => 'advanced_css_code',
						'type'      => 'ace_editor',
						'title'     => esc_html__('Custom CSS Code', 'wilcity'),
						'mode'      => 'css',
						'theme'    => 'monokai'
					),
					array(
						'id'        => 'advanced_js_code',
						'type'      => 'ace_editor',
						'title'     => esc_html__('Custom Javascript Code', 'wilcity'),
						'mode'      => 'javascript',
						'default'   => ''
					),
				)
			),
			array(
				'title'            => 'Mobile General Settings',
				'id'               => 'mobile_general_settings',
				'icon'             => 'dashicons dashicons-smartphone',
				'subsection'       => false,
				'customizer_width' => '500px',
				'fields'           => array(
					array(
						'id'        => 'mobile_app_page',
						'type'      => 'select',
						'data'      => 'posts',
						'args'  => array(
							'post_type'=> 'page',
							'posts_per_page' => 100
						),
						'title'     => 'Mobile App Home page',
						'description'=> 'Building your App Home Page by following<a href="https://documentation.wilcity.com/knowledgebase/design-my-app/" target="_blank">this tutorial</a>, the assign this page to this setting.'
					),
			        array(
				        'id'        => 'wilcity_security_authentication_key',
				        'type'      => 'password',
				        'title'     => 'SECURE AUTH KEY',
				        'description' => ' The SECURE AUTH KEY you provided must contain some special characters (*&!@%^#$) and 12 characters at least. You can generate a token by clicking on <a href="https://api.wordpress.org/secret-key/1.1/salt/" target="_blank">WordPress API</a>.',
				        'default'   => ''
			        ),
					array(
						'id'        => 'wilcity_token_expired_after',
						'type'      => 'text',
						'title'     => 'Token Expiration after (in day)',
						'description' => 'After a customer logged into your site, the app will keep logged status in x days',
						'default'   => 30
					),
					array(
						'id'        => 'app_listings_orderby',
						'type'      => 'select',
						'title'     => 'Listings Order By',
						'options'   => array(
							'post_date'         => 'Listing Date',
							'post_title'        => 'Listing Title',
							'menu_order post_date' => 'Listing Order',
							'best_viewed'       => 'Popular Viewed',
							'best_rated'        => 'Popular Rated',
							'best_shared'       => 'Popular Shared'
						),
						'default'   => 'menu_order post_date'
					),
					array(
						'id'        => 'app_listings_order',
						'type'      => 'select',
						'title'     => 'ORDER',
						'default'   => 'DESC',
						'options'   => array(
							'DESC'  => 'DESC',
							'ASC'   => 'ASC'
						)
					),
				)
			)
		))
	)
);