<?php
use \WILCITY_SC\SCHelpers;
use \WilokeListingTools\Framework\Helpers\General;

return [
	'wilcity_app_hero'   => array(
		'name'          => 'App Hero',
		'icon'          => 'sl-paper-plane',
		'category'      => WILCITY_MOBILE_CAT,
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
					'admin_label'   => false
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
					'admin_label'   => false
				),
				array(
					'name'          => 'image_bg',
					'label'         => 'Background Image',
					'type'          => 'attach_image_url'
				),
				array(
					'name'          => 'overlay_color',
					'label'         => 'Overlay Color',
					'type'          => 'color_picker',
					'admin_label'   => false
				)
			)
		)
	),
	'wilcity_app_heading' => array(
		'name'          => 'App Heading',
		'icon'          => 'sl-paper-plane',
		'css_box'       => true,
		'category'      => WILCITY_MOBILE_CAT,
		'params'        => array(
			'general' => array(
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
	'wilcity_app_term_boxes' => array(
		'name'          => 'App Term Boxes',
		'icon'          => 'sl-paper-plane',
		'css_box'       => true,
		'category'      => WILCITY_MOBILE_CAT,
		'params'        => array(
			'general'   => array(
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
						'none'      => 'None'
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
	'wilcity_app_listings_on_mobile' => array(
		'name'          => 'App Listings',
		'icon'          => 'sl-paper-plane',
		'css_box'       => true,
		'category'      => WILCITY_MOBILE_CAT,
		'params'        => array(
			'general' => array(
				array(
					'name'          => 'post_type',
					'label'         => 'Directory Type',
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
					'name'        => 'orderby',
					'options'     => array(
						'post_date'     => 'Listing Date',
						'post_title'    => 'Listing Title',
						'menu_order'    => 'Listing Order',
						'best_viewed'   => 'Popular Viewed',
						'best_rated'    => 'Popular Rated',
						'best_shared'   => 'Popular Shared',
						'rand'          => 'Random'
					)
				)
			)
		)
	),
	'wilcity_kc_events_mobile' => array(
		'name'          => 'App Events',
		'icon'          => 'sl-paper-plane',
		'css_box'       => true,
		'category'      => WILCITY_MOBILE_CAT,
		'params'        => array(
			'general' => array(
				array(
					'type'        => 'autocomplete',
					'label'       => 'Select Tags',
					'description' => 'Get Listings from the specified Tags. Leave empty to set all Tags.',
					'name'        => 'listing_tags'
				),
				array(
					'type'        => 'autocomplete',
					'label'       => 'Select Categories',
					'description' => 'Get Categories from the specified Categories. Leave empty to set all Categories.',
					'name'        => 'listing_cats'
				),
				array(
					'type'        => 'autocomplete',
					'label'       => 'Select Locations',
					'description' => 'Get Locations from the specified Locations. Leave empty to set all Locations.',
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
						'rand'            => 'Random'
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
			)
		)
	),
];