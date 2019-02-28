<?php
use WILCITY_SC\SCHelpers;
use WilokeListingTools\Framework\Helpers\General;

$aPricingOptions = array('flexible' => 'Depends on Listing Type Request');
if ( class_exists('WilokeListingTools\Framework\Helpers\General') ){
	$aPostTypes = General::getPostTypeKeys(false, false);
	$aPricingOptions = $aPricingOptions + array_combine($aPostTypes, $aPostTypes);
}


$live_tmpl = KC_PATH.KDS.'shortcodes'.KDS.'live_editor'.KDS;

return array(
	'shortcodes' => array(
		'kc_tabs' => array(
			'name' => 'Tabs - Sliders',
			'description' => 'Tabbed or Sliders content',
			'category' => 'Content',
			'icon' => 'kc-icon-tabs',
			'title' => 'Tabs - Sliders Settings',
			'is_container' => true,
			'views' => array(
				'type' => 'views_sections',
				'sections' => 'kc_tab'
			),
			'priority'  => 120,
			'live_editor' => $live_tmpl.'kc_tabs.php',
			'params' => array(
				'general' => array(
					array(
						'name'  => 'class',
						'label' => 'Extra Class',
						'type'  => 'text'
					),
					array(
						'name'  => 'type',
						'label' => 'How Display',
						'type'  => 'select',
						'options' => array(
							'horizontal_tabs'   => 'Horizontal Tabs',
							'vertical_tabs'     => 'Vertical Tabs',
							'slider_tabs'       => 'Owl Sliders'
						),
						'description' => 'Use sidebar view of your tabs as horizontal, vertical or slider.',
						'value' => 'horizontal_tabs'
					),
					array(
						'name'  => 'title_slider',
						'label' => 'Display Titles?',
						'type'  => 'toggle',
						'relation' => array(
							'parent'    => 'type',
							'show_when' => 'slider_tabs'
						),
						'description' => 'Display tabs title above of the slider',
					),
					array(
						'name'  => 'items',
						'label' => 'Number Items?',
						'type'  => 'number_slider',
						'options' => array(
							'min' => 1,
							'max' => 10,
							'show_input' => true
						),
						'relation' => array(
							'parent'    => 'type',
							'show_when' => 'slider_tabs'
						),
						'description' => 'Display number of items per each slide (Desktop Screen)'
					),
					array(
						'name'  => 'tablet',
						'label' => 'Items on tablet?',
						'type'  => 'number_slider',
						'options' => array(
							'min' => 1,
							'max' => 6,
							'show_input' => true
						),
						'relation' => array(
							'parent'    => 'type',
							'show_when' => 'slider_tabs'
						),
						'description' => 'Display number of items per each slide (Tablet Screen)'
					),
					array(
						'name' => 'mobile',
						'label' => 'Items on smartphone?',
						'type' => 'number_slider',
						'options' => array(
							'min' => 1,
							'max' => 4,
							'show_input' => true
						),
						'relation' => array(
							'parent' => 'type',
							'show_when' => 'slider_tabs'
						),
						'description' => 'Display number of items per each slide (Smartphone Screen)'
					),
					array(
						'name' => 'speed',
						'label' => 'Speed of slider',
						'type' => 'number_slider',
						'options' => array(
							'min' => 100,
							'max' => 1000,
							'show_input' => true
						),
						'value' => 450,
						'relation'      => array(
							'parent'    => 'type',
							'show_when' => 'slider_tabs'
						),
						'description'   => 'The speed of sliders in millisecond'
					),
					array(
						'name'  => 'navigation',
						'label' => 'Navigation',
						'type'  => 'toggle',
						'relation' => array(
							'parent'    => 'type',
							'show_when' => 'slider_tabs'
						),
						'description'   => 'Display the "Next" and "Prev" buttons.'
					),
					array(
						'name'  => 'pagination',
						'label' => 'Pagination',
						'type'  => 'toggle',
						'relation' => array(
							'parent'    => 'type',
							'show_when' => 'slider_tabs'
						),
						'value' => 'yes',
						'description' => 'Show the pagination.',
					),
					array(
						'name' => 'autoplay',
						'label' => 'Auto Play',
						'type' => 'toggle',
						'relation' => array(
							'parent'    => 'type',
							'show_when' => 'slider_tabs'
						),
						'description' => 'The slider automatically plays when site loaded'
					),
					array(
						'name'  => 'autoheight',
						'label' => 'Auto Height',
						'type'  => 'toggle',
						'relation' => array(
							'parent'    => 'type',
							'show_when' => 'slider_tabs'
						),
						'description' => 'The slider height will change automatically'
					),
					array(
						'name' => 'effect_option',
						'label' => 'Enable fadein effect?',
						'type' => 'toggle',
						'relation' => array(
							'parent'    => 'type',
							'hide_when' => 'slider_tabs'
						),
						'description' => 'Quickly apply fade in and face out effect when users click on tab.'
					),
					array(
						'name' => 'tabs_position',
						'label' => 'Position',
						'type' => 'select',
						'options' => array(
							'wil-text-left'      => 'Left',
							'wil-text-center'    => 'Center',
							'wil-text-right'     => 'Right'
						),
						'relation' => array(
							'parent'    => 'type',
							'show_when' => array('horizontal_tabs', 'vertical_tabs')
						)
					),
					array(
						'name' => 'nav_item_style',
						'label' => 'Nav Item Style',
						'description' => 'The position of the tab name and icon',
						'type' => 'select',
						'options' => array(
							'' => 'Horizontal',
							'wilTab_iconLg__2Ibz5' => 'Vertical '
						),
						'relation' => array(
							'parent'    => 'type',
							'show_when' => array('horizontal_tabs', 'vertical_tabs')
						),
						'value' => ''
					),
					array(
						'name'  => 'open_mouseover',
						'label' => 'Open on mouseover',
						'type'  => 'toggle',
						'relation' => array(
							'parent'    => 'type',
							'hide_when' => 'slider_tabs'
						),
					)
				),
				'styling' => array(
					array(
						'name'    => 'css_custom',
						'type'    => 'css',
						'options' => array(
							array(
								"screens" => "any,1024,999,767,479",
								'Tab' => array(
									array('property' => 'font-family,font-size,line-height,font-weight,text-transform,text-align', 'label' => 'Font family', 'selector' => '.kc_tabs_nav, .kc_tabs_nav > li a,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li>a'),
									array('property' => 'font-size,color,padding', 'label' => 'Icon Size,Icon Color,Icon Spacing', 'selector' => '.kc_tabs_nav a i,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li>a i'),
									array('property' => 'color', 'label' => 'Text Color', 'selector' => '.kc_tabs_nav a, .kc_tabs_nav,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li>a'),
									array('property' => 'background-color', 'label' => 'Background Color', 'selector' => '.kc_tabs_nav,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav'),
									array('property' => 'background-color', 'label' => 'Background Color tab item', 'selector' => '.kc_tabs_nav li,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav li'),
									array('property' => 'border', 'label' => 'Border', 'selector' => '.kc_tabs_nav > li, .kc_tab.ui-tabs-body-active, .kc_tabs_nav,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav ~ div.kc_tab.ui-tabs-body-active,+.kc_vertical_tabs.tabs_right>.kc_wrapper>ul.ui-tabs-nav ~ div.kc_tab'),
									array('property' => 'border-radius', 'label' => 'Border-radius', 'selector' => '.kc_tabs_nav > li, .kc_tab.ui-tabs-body-active, .kc_tabs_nav,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav ~ div.kc_tab.ui-tabs-body-active,+.kc_vertical_tabs.tabs_right>.kc_wrapper>ul.ui-tabs-nav ~ div.kc_tab'),
									array('property' => 'padding', 'label' => 'Padding', 'selector' => '.kc_tabs_nav > li > a,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li>a'),
									array('property' => 'margin', 'label' => 'Margin', 'selector' => '.kc_tabs_nav > li > a,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li'),
									array('property' => 'width', 'label' => 'Width', 'selector' => '.kc_tabs_nav > li,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li'),
								),

								'Tab Hover' => array(
									array('property' => 'color', 'label' => 'Text Color', 'selector' => '.kc_tabs_nav li:hover a, .kc_tabs_nav li:hover, .kc_tabs_nav > .ui-tabs-active:hover a,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li>a:hover,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li.ui-tabs-active > a'),
									array('property' => 'color', 'label' => 'Icon Color', 'selector' => '.kc_tabs_nav li:hover a i,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li>a:hover i,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li.ui-tabs-active > a i'),
									array('property' => 'background-color', 'label' => 'Background Color', 'selector' => '.kc_tabs_nav > li:hover, .kc_tabs_nav > li:hover a, .kc_tabs_nav > li > a:hover,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li>a:hover,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li.ui-tabs-active > a'),
								),
								'Tab Active' => array(
									array('property' => 'color', 'label' => 'Text Color', 'selector' => '.kc_tabs_nav li.ui-tabs-active a,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li.ui-tabs-active > a'),
									array('property' => 'color', 'label' => 'Icon Color', 'selector' => '.kc_tabs_nav li.ui-tabs-active a i, .kc_tabs_nav > .ui-tabs-active:focus a i,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li.ui-tabs-active > a i'),
									array('property' => 'background-color', 'label' => 'Background Color', 'selector' => '.kc_tabs_nav > .ui-tabs-active:focus, .kc_tabs_nav > .ui-tabs-active, .kc_tabs_nav > .ui-tabs-active > a,+.kc_vertical_tabs>.kc_wrapper>ul.ui-tabs-nav>li.ui-tabs-active > a'),
								),
								'Tab Body' => array(
									array('property' => 'background-color', 'label' => 'Background Color', 'selector' => '.kc_tab'),
									array('property' => 'padding', 'label' => 'Spacing', 'selector' => '.kc_tab .kc_tab_content'),
									array('property' => 'display', 'label' => 'Display'),
								),


							)
						)
					)
				),
				'animate' => array(
					array(
						'name'    => 'animate',
						'type'    => 'animate'
					)
				),
			),
			'content' => '[kc_tab title="New Tab"][/kc_tab]'
		),
		'wilcity_kc_heading' => array(
			'name'          => 'Heading',
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'type'          => 'text',
						'name'          => 'blur_mark',
						'label'         => 'Blur Mark',
						'value'         => ''
					),
					array(
						'type'          => 'color_picker',
						'name'          => 'blur_mark_color',
						'label'         => 'Blur Mark Color',
						'value'         => ''
					),
					array(
						'type'          => 'text',
						'name'          => 'heading',
						'label'         => 'Heading',
						'value'         => ''
					),
					array(
						'type'          => 'color_picker',
						'name'          => 'heading_color',
						'label'         => 'Heading Color',
						'value'         => '#252c41'
					),
					array(
						'type'          => 'textarea',
						'name'          => 'description',
						'label'         => 'Description',
						'value'         => ''
					),
					array(
						'type'          => 'color_picker',
						'name'          => 'description_color',
						'label'         => 'Description Color',
						'value'         => '#70778b'
					),
					array(
						'type'          => 'select',
						'name'          => 'alignment',
						'label'         => 'Alignment',
						'value'         => 'wil-text-center',
						'options'		=> array(
							'wil-text-center' => 'Center',
							'wil-text-right'  => 'Right',
							'wil-text-left'	  => 'Left'
						)
					)
				)
			)
		),
		'wilcity_kc_testimonials' => array(
			'name'          => 'Testimonials',
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'name'          => 'icon',
						'label'         => 'Icon',
						'type'          => 'icon_picker',
						'value'         => 'la la-quote-right'
					),
					array(
						'name'          => 'autoplay',
						'label'         => 'Auto Play',
						'description'   => 'Leave empty to disable this feature. Or specify auto-play each x seconds',
						'type'          => 'text',
						'value'         => ''
					),
					array(
						'name'          => 'testimonials',
						'label'         => 'Testimonials',
						'type'          => 'group',
						'value'         => '',
						'params' => array(
							array(
								'type' 	=> 'text',
								'label' => 'Customer Name',
								'name' 	=> 'name'
							),
							array(
								'type' 	=> 'textarea',
								'label' => 'Testimonial',
								'name' 	=> 'testimonial'
							),
							array(
								'type' 	=> 'text',
								'label' => 'Customer Profesional',
								'name' 	=> 'profesional'
							),
							array(
								'type' 	=> 'attach_image_url',
								'label' => 'Avatar',
								'name' 	=> 'avatar'
							)
						)
					)
				)
			)
		),
		'wilcity_kc_wiloke_wave' => array(
			'name'          => 'Wiloke Wave',
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'name'          => 'heading',
						'label'         => 'Heading',
						'type'          => 'text',
						'admin_label'   => true
					),
					array(
						'name'          => 'description',
						'label'         => 'Description',
						'type'          => 'textarea',
						'value'         => '',
						'admin_label'   => true
					),
					array(
						'type'          => 'color_picker',
						'name'          => 'left_gradient_color',
						'label'         => 'Left Gradient Color',
						'value'         => '#f06292'
					),
					array(
						'type'          => 'color_picker',
						'name'          => 'right_gradient_color',
						'label'         => 'Right Gradient Color',
						'value'         => '#f97f5f'
					),
					array(
						'name'          => 'btn_group',
						'label'         => 'Buttons Group',
						'type'          => 'group',
						'value'         => '',
						'params' => array(
							array(
								'type' 	=> 'icon_picker',
								'label' => 'Icon',
								'name' 	=> 'icon'
							),
							array(
								'type' 	=> 'text',
								'label' => 'Button name',
								'name' 	=> 'name'
							),
							array(
								'type' 	=> 'text',
								'label' => 'Button URL',
								'name' 	=> 'url'
							),
							array(
								'type' 	=> 'select',
								'label' => 'Open Type',
								'name' 	=> 'open_type',
								'options'=> array(
									'_self' => 'In the same window',
									'_blank'=> 'In a New Window'
								)
							)
						)
					),
				)
			)
		),
		'wilcity_kc_box_icon' => array(
			'name'          => 'Box Icon',
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'name'          => 'icon',
						'label'         => 'Icon',
						'type'          => 'icon_picker',
						'value'         => '',
						'admin_label'   => true
					),
					array(
						'name'          => 'heading',
						'label'         => 'Heading',
						'type'          => 'text',
						'value'         => '',
						'admin_label'   => true
					),
					array(
						'name'          => 'description',
						'label'         => 'Description',
						'type'          => 'textarea',
						'value'         => ''
					),
				)
			)
		),
		'wilcity_kc_events_grid'	=> array(
			'name'          => 'Events Grid Layout',
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Tags',
						'name'        => 'listing_tags'
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Categories',
						'name'        => 'listing_cats'
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Locations',
						'name'        => 'listing_locations'
					),
					array(
						'type'        => 'select',
						'label'       => 'Order By',
						'name'        => 'orderby',
						'options'     => array(
							'post_date'       => 'Listing Date',
							'post_title'      => 'Listing Title',
							'menu_order'      => 'Listing Order',
							'upcoming_event'  => 'Upcoming Events',
							'happening_event' => 'Happening Events',
						)
					),
					array(
						'type'        => 'text',
						'label'       => 'Maximum Items',
						'name'        => 'posts_per_page',
						'value'		  => 6
					),
					array(
						'type'        => 'text',
						'label'       => 'Image Size',
						'description' => 'For example: 200x300. 200: Image width. 300: Image height',
						'name'        => 'img_size',
						'value'		  => 'wilcity_360x200'
					)
				),
				'device settings' => array(
					array(
						'name'          => 'maximum_posts_on_lg_screen',
						'label'         => 'Items / row on >=1200px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 1400px ',
						'type'          => 'select',
						'value'         => 'col-lg-4',
						'options'		=> array(
							'col-lg-2'  => '6 Items / row',
							'col-lg-3'  => '4 Items / row',
							'col-lg-4'  => '3 Items / row',
							'col-lg-6'  => '2 Items / row',
							'col-lg-12' => '1 Item / row'
						),
						'admin_label'   => true
					),
					array(
						'name'          => 'maximum_posts_on_md_screen',
						'label'         => 'Items / row on >=960px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 1200px ',
						'type'          => 'select',
						'options'		=> array(
							'col-md-2'  => '6 Items / row',
							'col-md-3'  => '4 Items / row',
							'col-md-4'  => '3 Items / row',
							'col-md-6'  => '2 Items / row',
							'col-md-12' => '1 Item / row'
						),
						'value'         => 'col-md-3',
						'admin_label'   => true
					),
					array(
						'name'          => 'maximum_posts_on_sm_screen',
						'label'         => 'Items / row on >=720px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 640px ',
						'type'          => 'select',
						'options'		=> array(
							'col-sm-2'  => '6 Items / row',
							'col-sm-3'  => '4 Items / row',
							'col-sm-4'  => '3 Items / row',
							'col-sm-6'  => '2 Items / row',
							'col-sm-12' => '1 Item / row'
						),
						'value'         => 'col-sm-12',
						'admin_label'   => true
					)
				)
			)
		),
		'wilcity_kc_grid'	=> array(
			'name'          => 'Listings Grid Layout',
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'name'          => 'post_type',
						'label'         => 'Post Type',
						'type'          => 'select',
						'value'         => 'listing',
						'admin_label'   => true,
						'options'		=> SCHelpers::getPostTypeKeys(true)
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Categories',
						'name'        => 'listing_cats'
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Locations',
						'name'        => 'listing_locations'
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Tags',
						'name'        => 'listing_tags'
					),
					array(
						'type'        => 'text',
						'label'       => 'Maximum Items',
						'name'        => 'posts_per_page',
						'value'		  => 6
					),
					array(
						'type'        => 'text',
						'label'       => 'Image Size',
						'description' => 'For example: 200x300. 200: Image width. 300: Image height',
						'name'        => 'img_size',
						'value'		  => 'wilcity_360x200'
					),
					array(
						'type'        => 'select',
						'label'       => 'Order By',
						'description' => 'In order to use Order by Random, please disable the cache plugin or exclude this page from cache.',
						'name'        => 'orderby',
						'options'     => array(
							'post_date'     => 'Listing Date',
							'post_title'    => 'Listing Title',
							'menu_order'    => 'Listing Order',
							'best_viewed'   => 'Popular Viewed',
							'best_rated'    => 'Popular Rated',
							'best_shared'   => 'Popular Shared',
							'rand'          => 'Random',
							'nearbyme'      => 'Near By Me',
							'premium_listings' => 'Premium Listings'
						)
					),
					array(
						'type'        => 'text',
						'label'       => 'Radius',
						'description' => 'Fetching all listings within x radius',
						'name'        => 'radius',
						'value'       => 10,
						'relation'    => array(
							'parent'    => 'orderby',
							'show_when' => array('orderby', '=', 'nearbyme')
						)
					),
					array(
						'type'        => 'select',
						'label'       => 'Unit',
						'name'        => 'unit',
						'relation'    => array(
							'parent'    => 'orderby',
							'show_when' => array('orderby', '=', 'nearbyme')
						),
						'options'   => array(
							'km'    => 'KM',
							'm'     => 'Miles'
						),
						'value' => 'km'
					),
					array(
						'type'        => 'text',
						'label'       => 'Tab Name',
						'description' => 'If the grid layout is inside of a tab, we recommend putting the Tab ID to this field. If the tab is emptied, the listings will be shown after the browser is loaded. Otherwise, it will be shown after someone clicks on the Tab Name.',
						'name'        => 'tabname',
						'value'       => '',
						'relation'    => array(
							'parent'    => 'orderby',
							'show_when' => array('orderby', '=', 'nearbyme')
						)
					)
				),
				'devices settings' => array(
					array(
						'name'          => 'maximum_posts_on_lg_screen',
						'label'         => 'Items / row on >=1200px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 1400px ',
						'type'          => 'select',
						'value'         => 'col-lg-4',
						'options'		=> array(
							'col-lg-2'  => '6 Items / row',
							'col-lg-3'  => '4 Items / row',
							'col-lg-4'  => '3 Items / row',
							'col-lg-6'  => '2 Items / row',
							'col-lg-12' => '1 Item / row'
						)
					),
					array(
						'name'          => 'maximum_posts_on_md_screen',
						'label'         => 'Items / row on >=960px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 1200px ',
						'type'          => 'select',
						'options'		=> array(
							'col-md-2'  => '6 Items / row',
							'col-md-3'  => '4 Items / row',
							'col-md-4'  => '3 Items / row',
							'col-md-6'  => '2 Items / row',
							'col-md-12' => '1 Item / row'
						),
						'value'         => 'col-md-3'
					),
					array(
						'name'          => 'maximum_posts_on_sm_screen',
						'label'         => 'Items / row on >=720px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 640px ',
						'type'          => 'select',
						'options'		=> array(
							'col-sm-2'  => '6 Items / row',
							'col-sm-3'  => '4 Items / row',
							'col-sm-4'  => '3 Items / row',
							'col-sm-6'  => '2 Items / row',
							'col-sm-12' => '1 Item / row'
						),
						'value'         => 'col-sm-12'
					),
				)
			)
		),
		'wilcity_kc_hero'   => array(
			'name'          => esc_html__('Hero', 'wilcity-shortcodes'),
			'nested'        => true,
			'icon'          => 'sl-paper-plane',
			'accept_child'  => 'wilcity_kc_search_form',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'name'          => 'heading',
						'label'         => 'Title',
						'type'          => 'text',
						'value'         => 'Explore This City',
						'admin_label'   => false
					),
					array(
						'name'          => 'heading_color',
						'label'         => 'Title Color',
						'type'          => 'color_picker',
						'value'         => ''
					),
					array(
						'name'          => 'heading_font_size',
						'label'         => 'Title Font Size',
						'description'   => 'Eg: 50px',
						'type'          => 'text',
						'value'         => '50px'
					),
					array(
						'name'          => 'description',
						'label'         => 'Description',
						'type'          => 'textarea',
						'admin_label'   => false
					),
					array(
						'name'          => 'description_color',
						'label'         => 'Description Color',
						'type'          => 'color_picker',
						'value'         => ''
					),
					array(
						'name'          => 'description_font_size',
						'label'         => 'Description Font Size',
						'description'   => 'Eg: 17px',
						'type'          => 'text',
						'value'         => '17px'
					),
					array(
						'name'          => 'toggle_button',
						'label'         => 'Toggle Button',
						'type'          => 'select',
						'admin_label'   => false,
						'value'         => 'enable',
						'options'       => array(
							'enable'     => 'Enable',
							'disable'    => 'Disable'
						)
					),
					array(
						'name'          => 'button_icon',
						'label'         => 'Button Icon',
						'value'         => 'la la-pencil-square',
						'type'          => 'icon_picker',
						'admin_label'   => false,
						'relation'      => array(
							'toggle_button' => 'enable'
						)
					),
					array(
						'name'          => 'button_name',
						'label'         => 'Button Name',
						'value'         => 'Check out',
						'type'          => 'text',
						'admin_label'   => false,
						'relation'      => array(
							'toggle_button' => 'enable'
						)
					),
					array(
						'name'          => 'button_link',
						'label'         => 'Button Link',
						'type'          => 'text',
						'value'         => '#',
						'admin_label'   => false,
						'relation'      => array(
							'toggle_button' => 'enable'
						)
					),
					array(
						'name'          => 'button_text_color',
						'label'         => 'Button Text Color',
						'type'          => 'color_picker',
						'value'         => '#fff',
						'relation'      => array(
							'toggle_button' => 'enable'
						)
					),
					array(
						'name'          => 'button_background_color',
						'label'         => 'Button Background Color',
						'type'          => 'color_picker',
						'value'         => '',
						'relation'      => array(
							'toggle_button' => 'enable'
						)
					),
					array(
						'name'          => 'button_size',
						'label'         => 'Button Size',
						'type'          => 'select',
						'relation'      => array(
							'toggle_button' => 'enable'
						),
						'value'         => 'wil-btn--sm',
						'options'       => array(
							'wil-btn--sm' => 'Small',
							'wil-btn--md' => 'Medium',
							'wil-btn--lg' => 'Large'
						)
					),
					array(
						'name'          => 'toggle_dark_and_white_background',
						'label'         => 'Toggle Dark and White Background',
						'type'          => 'select',
						'default'       => 'disable',
						'options'       => array(
							'enable'     => 'Enable',
							'disable'    => 'Disable'
						)
					),
					array(
						'name'          => 'bg_overlay',
						'label'         => 'Background Overlay',
						'type'          => 'color_picker',
						'default'       => ''
					),
					array(
						'name'          => 'bg_type',
						'label'         => 'Is Using Slider Background?',
						'type'          => 'select',
						'default'       => 'image',
						'options'       => array(
							'image'     => 'Image Background',
							'slider'    => 'Slider Background'
						)
					),
					array(
						'name'          => 'image_bg',
						'label'         => 'Background Image',
						'type'          => 'attach_image_url',
						'relation'      => array(
							'parent'    => 'bg_type',
							'show_when' => 'image'
						)
					),
					array(
						'name'          => 'slider_bg',
						'label'         => 'Background Slider',
						'type'          => 'attach_images',
						'relation'      => array(
							'parent'    => 'bg_type',
							'show_when' => 'slider'
						)
					),
					array(
						'name'          => 'img_size',
						'label'         => 'Image Size',
						'type'          => 'text',
						'value'         => 'large',
						'description'   => 'Entering full keyword to display the original size',
						'admin_label'   => false
					)
				),
				'search form' => array(
					array(
						'name'          => 'search_form_position',
						'label'         => 'Search Form Style',
						'type'          => 'select',
						'admin_label'   => false,
						'value'         => 'bottom',
						'options'       => array(
							'right'     => 'Right of Screen',
							'bottom'    => 'Bottom'
						)
					),
					array(
						'name'          => 'search_form_background',
						'label'         => 'Search Form Background',
						'type'          => 'select',
						'admin_label'   => false,
						'value'         => 'hero_formDark__3fCkB',
						'options'       => array(
							'hero_formWhite__3fCkB'   => 'White',
							'hero_formDark__3fCkB'    => 'Black'
						)
					),
				),
				'list of suggestions ' => array(
					array(
						'name'         => 'toggle_list_of_suggestions',
						'label'        => 'Toggle The List Of Suggestions',
						'description'  => 'A list of suggestion locations/categories will be shown on the Hero section if this feature is enabled.',
						'type'         => 'select',
						'options'      => array(
							'enable'   => 'Enable',
							'disable'  => 'Disable'
						),
						'value' => 'enable'
					),
					array(
						'name'  => 'maximum_terms_suggestion',
						'label' => 'Maximum Locations / Categories',
						'type'  => 'text',
						'value' => 6
					),
					array(
						'name'          => 'taxonomy',
						'label'         => 'Get By',
						'type'          => 'select',
						'options'      => array(
							'listing_cat'       => 'Listing Category',
							'listing_location'  => 'Listing Location'
						),
						'value' => 'listing_cat'
					),
					array(
						'name'          => 'orderby',
						'label'         => 'Order By',
						'type'          => 'select',
						'options'      => array(
							'count' => 'Number of children',
							'id'    => 'ID',
							'slug'  => 'Slug',
							'specify_terms' => 'Specify Locations/Categories'
						),
						'value' => 'count'
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Categories',
						'description' => 'This feature is available for Order By Specify Categories',
						'name'        => 'listing_cats',
						'relation'    => array(
							'parent'    => 'taxonomy',
							'show_when' => array('taxonomy', '=', 'listing_cat')
						)
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Locations (Optional)',
						'description' => 'This feature is available for Order By Specify Locations',
						'name'        => 'listing_locations',
						'relation'    => array(
							'parent'    => 'taxonomy',
							'show_when' => array('taxonomy', '=', 'listing_location')
						)
					)
				)
			)
		),
		'wilcity_kc_search_form' => array(
			'name'          => esc_html__('Search Form', 'wilcity-shortcodes'),
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general'  => array(
					array(
						'name'  => 'items',
						'label' => 'Search Tab',
						'type'  => 'group',
						'value' => '',
						'params'=> array(
							array(
								'name'      => 'name',
								'label'     => 'Tab Name',
								'type'      => 'text',
								'value'     => 'Listing'
							),
							array(
								'name'      => 'post_type',
								'label'     => 'Directory Type',
								'type'      => 'select',
								'value'     => 'listing',
								'options'   => class_exists('WilokeListingTools\Framework\Helpers\General') ? General::getPostTypeOptions(false, false) : array('listing', 'event')
							)
						)
					)
				)
			)
		),
		'wilcity_kc_term_boxes'    => array(
			'name' => esc_html__('Term Boxes', 'wilcity-shortcodes'),
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'name'          => 'taxonomy',
						'label'         => 'Taxonomy',
						'type'          => 'select',
						'value'         => 'listing_cat',
						'options'       => array(
							'listing_cat'       => 'Listing Category',
							'listing_location'  => 'Listing Location',
							'listing_tag'       => 'Listing Tag'
						),
						'admin_label'   => true
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Categories (Optional)',
						'description' => 'If this setting is empty, it will get terms by "Order By" setting',
						'name'        => 'listing_cats',
						'relation'    => array(
							'parent'    => 'taxonomy',
							'show_when' => array('taxonomy', '=', 'listing_cat')
						)
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Locations (Optional)',
						'description' => 'If this setting is empty, it will get terms by "Order By" setting',
						'name'        => 'listing_locations',
						'relation'    => array(
							'parent'    => 'taxonomy',
							'show_when' => array('taxonomy', '=', 'listing_location')
						)
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Tags (Optional)',
						'description' => 'If this setting is empty, it will get terms by "Order By" setting',
						'name'        => 'listing_tags',
						'relation'    => array(
							'parent'    => 'taxonomy',
							'show_when' => array('taxonomy', '=', 'listing_tag')
						)
					),
					array(
						'name'          => 'number',
						'label'         => 'Maximum Terms Can Be Shown',
						'description'   => 'This feature is useful if you do not want to specify what Terms should be shown on the page.',
						'type'          => 'text',
						'value'         => ''
					),
					array(
						'name'          => 'items_per_row',
						'label'         => 'Items Per Row',
						'type'          => 'select',
						'value'         => 'col-lg-3',
						'options'       => array(
							'col-lg-2'       => '6 Items / Row',
							'col-lg-3'       => '4 Items / Row',
							'col-lg-4'       => '3 Items / Row',
							'col-lg-6'       => '2 Items / Row',
							'col-lg-12'      => '1 Items / Row'
						)
					),
					array(
						'name'          => 'orderby',
						'label'         => 'Order By',
						'description'   => 'This feature is not available if the "Select Locations/Select Tags/Select Categories" is not empty',
						'type'          => 'select',
						'value'         => 'count',
						'options'       => array(
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
						'name'  => 'order',
						'label' => 'Order',
						'type'  => 'select',
						'value' => 'DESC',
						'options'   => array(
							'DESC'  => 'DESC',
							'ASC'   => 'ASC'
						)
					)
				),
				'box style' => array(
					array(
						'name'          => 'toggle_box_gradient',
						'label'         => 'Toggle Box Gradient',
						'description'   => 'In order to use this feature, please upload a Featured Image to each Listing Location/Category: Listings -> Listing Locations / Categories -> Your Location/Category -> Featured Image.',
						'type'          => 'select',
						'value'         => 'disable',
						'options'       => array(
							'enable'   => 'Enable',
							'disable'  => 'Disable'
						)
					)
				)
			)
		),
		'wilcity_kc_modern_term_boxes'    => array(
			'name'          => esc_html__('Modern Term Boxes', 'wilcity-shortcodes'),
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'name'          => 'taxonomy',
						'label'         => 'Taxonomy',
						'type'          => 'select',
						'value'         => 'listing_cat',
						'options'       => array(
							'listing_cat'       => 'Listing Category',
							'listing_location'  => 'Listing Location',
							'listing_tag'       => 'Listing Tag'
						),
						'admin_label'   => true
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Categories (Optional)',
						'description' => 'If this setting is empty, it will get terms by "Order By" setting',
						'name'        => 'listing_cats',
						'relation'    => array(
							'parent'    => 'taxonomy',
							'show_when' => array('taxonomy', '=', 'listing_cat')
						)
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Locations (Optional)',
						'description' => 'If this setting is empty, it will get terms by "Order By" setting',
						'name'        => 'listing_locations',
						'relation'    => array(
							'parent'    => 'taxonomy',
							'show_when' => array('taxonomy', '=', 'listing_location')
						)
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Tags (Optional)',
						'description' => 'If this setting is empty, it will get terms by "Order By" setting',
						'name'        => 'listing_tags',
						'relation'    => array(
							'parent'    => 'taxonomy',
							'show_when' => array('taxonomy', '=', 'listing_tag')
						)
					),
					array(
						'name'          => 'items_per_row',
						'label'         => 'Items Per Row',
						'type'          => 'select',
						'value'         => 'col-lg-6',
						'options'       => array(
							'col-lg-2'       => '6 Items / Row',
							'col-lg-3'       => '4 Items / Row',
							'col-lg-4'       => '3 Items / Row',
							'col-lg-6'       => '2 Items / Row',
							'col-lg-12'      => '1 Items / Row'
						)
					),
					array(
						'name'          => 'col_gap',
						'label'         => 'Col Gap',
						'type'          => 'text',
						'value'         => 20
					),
					array(
						'name'          => 'image_size',
						'label'         => 'Image Size',
						'description'   => 'You can use the defined image sizes like: full, large, medium, wilcity_560x300 or 400,300 to specify the image width and height.',
						'type'          => 'text',
						'value'         => 'wilcity_560x300'
					),
					array(
						'name'          => 'orderby',
						'label'         => 'Order By',
						'description'   => 'This feature is not available if the "Select Locations/Select Tags/Select Categories" is not empty',
						'type'          => 'select',
						'value'         => 'count',
						'options'       => array(
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
						'name'  => 'order',
						'label' => 'Order',
						'type'  => 'select',
						'value' => 'DESC',
						'options'   => array(
							'DESC'  => 'DESC',
							'ASC'   => 'ASC'
						)
					)
				)
			)
		),
		'wilcity_kc_listings_slider'    => array(
			'name'     => 'Listings Slider',
			'icon'     => 'sl-paper-plane',
			'category' => WILCITY_SC_CATEGORY,
			'css_box'  => true,
			'params'   => array(
				'general' => array(
					array(
						'name'          => 'heading',
						'label'         => 'Heading',
						'type'          => 'text',
						'value'         => 'The Latest Listings',
						'admin_label'   => true
					),
					array(
						'name'          => 'desc',
						'label'         => 'Description',
						'type'          => 'textarea',
						'admin_label'   => true
					),
					array(
						'name'          => 'post_type',
						'label'         => 'Post Type',
						'type'          => 'select',
						'options'		=> SCHelpers::getPostTypeKeys(true),
						'value'         => 'listing',
						'admin_label'   => true
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Tags',
						'name'        => 'listing_tags'
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Categories',
						'name'        => 'listing_cats'
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Locations',
						'name'        => 'listing_locations'
					),
					array(
						'type'        => 'select',
						'label'       => 'Order By',
						'name'        => 'orderby',
						'options'     => array(
							'post_date'     => 'Listing Date',
							'post_title'    => 'Listing Title',
							'menu_order'    => 'Listing Order',
							'best_viewed'   => 'Popular Viewed',
							'best_rated'    => 'Popular Rated',
							'best_shared'   => 'Popular Shared',
							'premium_listings'   => 'Premium Listings'
						)
					)
				),
				'listings on screens' => array(
					array(
						'name'  => 'desktop_image_size',
						'label' => 'Desktop Image Size',
						'description'   => 'You can use the defined image sizes like: full, large, medium, wilcity_560x300 or 400,300 to specify the image width and height.',
						'type'          => 'text',
						'value'         => ''
					),
					array(
						'name'          => 'maximum_posts',
						'label'         => 'Maximum Listings',
						'type'          => 'text',
						'value'         => 8,
						'admin_label'   => true
					),
					array(
						'name'          => 'maximum_posts_on_extra_lg_screen',
						'label'         => 'Items on >=1600px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 1600px ',
						'type'          => 'text',
						'value'         => 6,
						'admin_label'   => true
					),
					array(
						'name'          => 'maximum_posts_on_lg_screen',
						'label'         => 'Items on >=1400px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 1400px ',
						'type'          => 'text',
						'value'         => 5,
						'admin_label'   => true
					),
					array(
						'name'          => 'maximum_posts_on_md_screen',
						'label'         => 'Items on >=1200px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 1200px ',
						'type'          => 'text',
						'value'         => 5,
						'admin_label'   => true
					),
					array(
						'name'          => 'maximum_posts_on_sm_screen',
						'label'         => 'Items on >=992px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 992px ',
						'type'          => 'text',
						'value'         => 2,
						'admin_label'   => true
					),
					array(
						'name'          => 'maximum_posts_on_extra_sm_screen',
						'label'         => 'Items on >=640px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 640px ',
						'type'          => 'text',
						'value'         => 1,
						'admin_label'   => true
					)
				),
				'slider configurations'=> array(
					array(
						'name'          => 'is_auto_play',
						'label'         => 'Is Auto Play',
						'type'          => 'select',
						'options'       => array(
							'enable'    => 'Enable',
							'disable'   => 'Disable'
						),
						'value'         => 'disable',
						'admin_label'   => true
					)
				),
				'styling' => array(
					array(
						'name'    => 'css_custom',
						'type'    => 'css'
					)
				)
			)
		),
		'wilcity_kc_events_slider'    => array(
			'name'     => 'Events Slider',
			'icon'     => 'sl-paper-plane',
			'category' => WILCITY_SC_CATEGORY,
			'css_box'  => true,
			'params'   => array(
				'general' => array(
					array(
						'name'          => 'heading',
						'label'         => 'Heading',
						'type'          => 'text',
						'value'         => 'The Latest Events',
						'admin_label'   => true
					),
					array(
						'name'          => 'desc',
						'label'         => 'Description',
						'type'          => 'textarea',
						'admin_label'   => true
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Tags',
						'name'        => 'listing_tags'
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Categories',
						'name'        => 'listing_cats'
					),
					array(
						'type'        => 'autocomplete',
						'label'       => 'Select Locations',
						'name'        => 'listing_locations'
					),
					array(
						'type'        => 'select',
						'label'       => 'Order By',
						'name'        => 'orderby',
						'options'     => array(
							'post_date'       => 'Listing Date',
							'post_title'      => 'Listing Title',
							'menu_order'      => 'Listing Order',
							'upcoming_event'  => 'Upcoming Events',
							'happening_event' => 'Happening Events',
						)
					)
				),
				'listings on screens' => array(
					array(
						'name'  => 'desktop_image_size',
						'label' => 'Desktop Image Size',
						'description'   => 'You can use the defined image sizes like: full, large, medium, wilcity_560x300 or 400,300 to specify the image width and height.',
						'type'          => 'text',
						'value'         => ''
					),
					array(
						'name'          => 'maximum_posts',
						'label'         => 'Maximum Listings',
						'type'          => 'text',
						'value'         => 8,
						'admin_label'   => true
					),
					array(
						'name'          => 'maximum_posts_on_extra_lg_screen',
						'label'         => 'Items on >=1600px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 1600px ',
						'type'          => 'text',
						'value'         => 6,
						'admin_label'   => true
					),
					array(
						'name'          => 'maximum_posts_on_lg_screen',
						'label'         => 'Items on >=1400px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 1400px ',
						'type'          => 'text',
						'value'         => 5,
						'admin_label'   => true
					),
					array(
						'name'          => 'maximum_posts_on_md_screen',
						'label'         => 'Items on >=1200px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 1200px ',
						'type'          => 'text',
						'value'         => 5,
						'admin_label'   => true
					),
					array(
						'name'          => 'maximum_posts_on_sm_screen',
						'label'         => 'Items on >=992px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 992px ',
						'type'          => 'text',
						'value'         => 2,
						'admin_label'   => true
					),
					array(
						'name'          => 'maximum_posts_on_extra_sm_screen',
						'label'         => 'Items on >=640px',
						'description'   => 'Set number of listings will be displayed when the screen is larger or equal to 640px ',
						'type'          => 'text',
						'value'         => 1,
						'admin_label'   => true
					)
				),
				'slider configurations'=> array(
					array(
						'name'          => 'is_auto_play',
						'label'         => 'Is Auto Play',
						'type'          => 'select',
						'options'       => array(
							'enable'    => 'Enable',
							'disable'   => 'Disable'
						),
						'value'         => 'disable',
						'admin_label'   => true
					)
				),
				'styling' => array(
					array(
						'name'    => 'css_custom',
						'type'    => 'css'
					)
				)
			)
		),
		'wilcity_kc_pricing' => array(
			'name'          => esc_html__('Pricing Table', 'wilcity-shortcodes'),
			'description'   => esc_html__('Display single icon', 'wilcity-shortcodes'),
			'icon'          => 'sl-paper-plane',
			'category'      => WILCITY_SC_CATEGORY,
			'params' => array(
				'general' => array(
					array(
						'name'          => 'items_per_row',
						'label'         => esc_html__('Items / Row', 'wilcity-shortcodes'),
						'type'          => 'select',
						'admin_label'   => true,
						'options' => array(  // THIS FIELD REQUIRED THE PARAM OPTIONS
							'col-md-4 col-lg-4' => '3 Items / Row',
							'col-md-3 col-lg-3' => '4 Items / Row',
							'col-md-6 col-lg-6' => '2 Items / Row',
							'col-md-12 col-lg-12' => '1 Item / Row'
						)
					),
					array(
						'name'          => 'listing_type',
						'label'         => 'Directory Type',
						'type'          => 'select',
						'admin_label'   => true,
						'options' => $aPricingOptions
					)
				)
			)
		),
		'wilcity_kc_contact_us' => array(
			'name'          => 'Contact Us',
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'name'          => 'contact_info_heading',
						'label'         => 'Heading',
						'type'          => 'text',
						'value'         => 'Contact Info'
					),
					array(
						'name'          => 'contact_info',
						'label'         => 'Contact Info',
						'type'          => 'group',
						'value'         => '',
						'params' => array(
							array(
								'type' 	=> 'icon_picker',
								'label' => 'Icon',
								'name' 	=> 'icon'
							),
							array(
								'type' 	=> 'textarea',
								'label' => 'Info',
								'name' 	=> 'info'
							),
							array(
								'type' 	=> 'text',
								'label' => 'link',
								'description' => 'Enter in # if it is not a real link.',
								'name' 	=> 'link'
							),
							array(
								'type' 	=> 'select',
								'label' => 'Type',
								'name' 	=> 'type',
								'value' 	=> 'default',
								'options' => array(
									'default' => 'Default',
									'phone'   => 'Phone',
									'mail'    => 'mail'
								)
							),
							array(
								'type' 	=> 'select',
								'label' => 'Open Type',
								'description' => 'After clicking on this link, it will be opened in',
								'name' 	=> 'target',
								'value' => '_self',
								'options' => array(
									'_self' => 'Self page',
									'_blank'=> 'New Window'
								)
							)
						)
					)
				),
				'Contact Form' => array(
					array(
						'name'          => 'contact_form_heading',
						'label'         => 'Heading',
						'type'          => 'text',
						'value'         => 'Contact Us'
					),
					array(
						'type'          => 'autocomplete',
						'name'          => 'contact_form_7',
						'label'         => 'Contact Form 7',
						'options'       => array(
							'post_type'     => 'wpcf7_contact_form',
						)
					)
				)
			)
		),
		'wilcity_kc_intro_box' => array(
			'name'          => 'Intro Box',
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'name'          => 'bg_img',
						'label'         => 'Background Image',
						'type'          => 'attach_image_url',
						'value'         => ''
					),
					array(
						'name'          => 'video_intro',
						'label'         => 'Video Intro',
						'type'          => 'text',
						'value'         => ''
					),
					array(
						'name'          => 'intro',
						'label'         => 'Intro',
						'type'          => 'editor',
						'value'         => ''
					)
				)
			)
		),
		'wilcity_kc_team_intro_slider' => array(
			'name'          => 'Team Intro Slider',
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'name'          => 'get_by',
						'label'         => 'Get users who are',
						'type'          => 'select',
						'value'         => 'administrator',
						'options'       => array(
							'administrator' => 'Administrator',
							'editor'        => 'Editor',
							'contributor'   => 'Contributor',
							'custom'        => 'Custom',
						)
					),
					array(
						'name'          => 'members',
						'label'         => 'Members',
						'relation'        => array(
							'parent' => 'get_by',
							'show_when' => 'custom'
						),
						'type'          => 'group',
						'value'         => '',
						'params' => array(
							array(
								'type' 	=> 'attach_image_url',
								'label' => 'Avatar',
								'name' 	=> 'avatar'
							),
							array(
								'type' 	=> 'attach_image_url',
								'label' => 'Picture',
								'name' 	=> 'picture'
							),
							array(
								'type' 	=> 'text',
								'label' => 'Name',
								'name' 	=> 'display_name'
							),
							array(
								'type' 	=> 'text',
								'label' => 'Position',
								'name' 	=> 'position'
							),
							array(
								'type' 	=> 'textarea',
								'label' => 'Intro',
								'name' 	=> 'intro'
							),
							array(
								'name'   => 'social_networks',
								'label'  => 'Social Networks',
								'description'  => 'Eg: facebook:https://facebook.com,google-plus:https://googleplus.com',
								'type'   => 'textarea'
							)
						)
					)
				)
			)
		),
		'wilcity_kc_author_slider' => array(
			'name'          => 'Author Slider',
			'icon'          => 'sl-paper-plane',
			'css_box'       => true,
			'category'      => WILCITY_SC_CATEGORY,
			'params'        => array(
				'general' => array(
					array(
						'name'          => 'role__in',
						'label'         => 'Role in',
						'description'   => 'Limit the returned users that have one of the specified roles',
						'type'          => 'multiple',
						'is_multiple'   => true,
						'value'         => 'administrator,contributor',
						'options'       => array(
							'administrator' => 'Administrator',
							'editor'        => 'Editor',
							'contributor'   => 'Contributor',
							'subscriber'    => 'Subscriber',
						)
					),
					array(
						'name'          => 'orderby',
						'label'         => 'Order by',
						'type'          => 'select',
						'value'         => 'post_count',
						'options'       => array(
							'registered'    => 'Registered',
							'post_count'    => 'Post Count',
							'ID'            => 'ID'
						)
					),
					array(
						'name'          => 'number',
						'label'         => 'Maximum Users',
						'type'          => 'text',
						'value'         => 8
					)
				)
			)
		)
	),
	'aDaysOfWeek' => array(
		'monday'    => esc_html__('Monday', 'wilcity-shortcodes'),
		'tuesday'   => esc_html__('Tuesday', 'wilcity-shortcodes'),
		'wednesday' => esc_html__('Wednesday', 'wilcity-shortcodes'),
		'thursday'  => esc_html__('Thursday', 'wilcity-shortcodes'),
		'friday'    => esc_html__('Friday', 'wilcity-shortcodes'),
		'saturday'  => esc_html__('Saturday', 'wilcity-shortcodes'),
		'sunday'    => esc_html__('Sunday', 'wilcity-shortcodes'),
	)
);