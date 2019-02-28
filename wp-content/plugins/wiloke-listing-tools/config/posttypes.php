<?php
return [
	'post_types' => array(
		'listing'       => array(
			'labels'             => array(
				'name'               => _x( 'Listings', 'admin menu', 'wiloke-listing-tools' ),
				'singular_name'      => _x( 'Listing', 'admin menu', 'wiloke-listing-tools' ),
				'menu_name'          => _x( 'Listings', 'admin bar', 'wiloke-listing-tools' ),
				'name_admin_bar'     => esc_html__( 'Listing', 'wiloke-listing-tools' ),
				'add_new'            => esc_html__( 'Add New', 'wiloke-listing-tools' ),
				'add_new_item'       => esc_html__( 'Add New Listing', 'wiloke-listing-tools' ),
				'new_item'           => esc_html__( 'New Listing', 'wiloke-listing-tools' ),
				'edit_item'          => esc_html__( 'Edit Listing', 'wiloke-listing-tools' ),
				'view_item'          => esc_html__( 'View Listing', 'wiloke-listing-tools' ),
				'all_items'          => esc_html__( 'All Listings', 'wiloke-listing-tools' ),
				'search_items'       => esc_html__( 'Search Listings', 'wiloke-listing-tools' ),
				'parent_item_colon'  => esc_html__( 'Parent Listings:', 'wiloke-listing-tools' ),
				'not_found'          => esc_html__( 'No Listings found.', 'wiloke-listing-tools' ),
				'not_found_in_trash' => esc_html__( 'No Listings found in Trash.', 'wiloke-listing-tools' )
			),
			'description'        => esc_html__( 'Description.', 'wiloke-listing-tools' ),
			'public'             => true,
			'menu_icon'          => 'dashicons-admin-site',
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'show_in_rest'       => true,
			'rest_base'          => 'listings',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'            => array( 'slug' => 'listing', 'with_front' => false),
			'map_meta_cap'   => true,
			'has_archive'    => true,
			'hierarchical'   => false,
			'menu_position'  => null,
			'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'page-attributes' )
		),
		'review'        => array(
			'labels'     => array(
				'name'               => 'Reviews',
				'singular_name'      => 'Review',
				'menu_name'          => 'Reviews',
				'name_admin_bar'     => 'Review',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New Review',
				'new_item'           => 'New Review',
				'edit_item'          => 'Edit Review',
				'view_item'          => 'View Review',
				'all_items'          => 'All Reviews',
				'search_items'       => 'Search Reviews',
				'parent_item_colon'  => 'Parent Reviews:',
				'not_found'          => 'No Reviews found.',
				'not_found_in_trash' => 'No Reviews found in Trash.',
			),
			'description'        => 'Description',
			'public'             => true,
			'menu_icon'          => 'dashicons-admin-site',
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'show_in_rest'       => true,
			'rest_base'          => 'review',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'            => array( 'slug' => 'review', 'with_front' => false),
			'map_meta_cap'   => true,
			'supports'      => array( 'title', 'editor', 'page-attributes', 'author' )
		),
		'claim_listing' => array(
			'labels'             => array(
				'name'               => _x( 'Claim Listing', 'admin menu', 'wiloke-listing-tools' ),
				'singular_name'      => _x( 'Claim Listing', 'admin menu', 'wiloke-listing-tools' ),
				'menu_name'          => _x( 'Claim Listing', 'admin bar', 'wiloke-listing-tools' ),
				'name_admin_bar'     => esc_html__( 'Claims', 'wiloke-listing-tools' ),
				'add_new'            => esc_html__( 'Add New', 'wiloke-listing-tools' ),
				'add_new_item'       => esc_html__( 'Add New Claim', 'wiloke-listing-tools' ),
				'new_item'           => esc_html__( 'New Claim', 'wiloke-listing-tools' ),
				'edit_item'          => esc_html__( 'Edit Claim', 'wiloke-listing-tools' ),
				'view_item'          => esc_html__( 'View Claim', 'wiloke-listing-tools' ),
				'all_items'          => esc_html__( 'All Claims', 'wiloke-listing-tools' ),
				'search_items'       => esc_html__( 'Search Claims', 'wiloke-listing-tools' ),
				'parent_item_colon'  => esc_html__( 'Parent Claims:', 'wiloke-listing-tools' ),
				'not_found'          => esc_html__( 'No Claims found.', 'wiloke-listing-tools' ),
				'not_found_in_trash' => esc_html__( 'No Claims found in Trash.', 'wiloke-listing-tools' )
			),
			'description'        => esc_html__( 'Description.', 'wiloke-listing-tools' ),
			'public'             => true,
			'menu_icon'          => 'dashicons-admin-site',
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'show_in_rest'       => true,
			'rest_base'          => 'claim-listing',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'            => array( 'slug' => 'claim-listing', 'with_front' => false),
			'map_meta_cap'       => true,
			'supports'           => array('title')
		),
		'listing_plan'  => array(
			'labels'             => array(
				'name'               => _x( 'Listing Plans', 'admin menu', 'wiloke-listing-tools' ),
				'singular_name'      => _x( 'Listing Plan', 'admin menu', 'wiloke-listing-tools' ),
				'menu_name'          => _x( 'Listing Plans', 'admin bar', 'wiloke-listing-tools' ),
				'name_admin_bar'     => esc_html__( 'Listing', 'wiloke-listing-tools' ),
				'add_new'            => esc_html__( 'Add New', 'wiloke-listing-tools' ),
				'add_new_item'       => esc_html__( 'Add New Plan', 'wiloke-listing-tools' ),
				'new_item'           => esc_html__( 'New Plan', 'wiloke-listing-tools' ),
				'edit_item'          => esc_html__( 'Edit Plan', 'wiloke-listing-tools' ),
				'view_item'          => esc_html__( 'View Plan', 'wiloke-listing-tools' ),
				'all_items'          => esc_html__( 'All Plans', 'wiloke-listing-tools' ),
				'search_items'       => esc_html__( 'Search Plans', 'wiloke-listing-tools' ),
				'parent_item_colon'  => esc_html__( 'Parent Plans:', 'wiloke-listing-tools' ),
				'not_found'          => esc_html__( 'No Plans found.', 'wiloke-listing-tools' ),
				'not_found_in_trash' => esc_html__( 'No Planss found in Trash.', 'wiloke-listing-tools' )
			),
			'description'        => esc_html__( 'Description.', 'wiloke-listing-tools' ),
			'public'             => true,
			'menu_icon'          => 'dashicons-cart',
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'show_in_rest'       => true,
			'rest_base'          => 'listing-plan',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'            => array( 'slug' => 'listing-plan', 'with_front' => false),
			'map_meta_cap'   => true,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' )
		),
		'discount'      => array(
			'labels'     => array(
				'name'               => 'Discounts',
				'singular_name'      => 'Discount',
				'menu_name'          => 'Discounts',
				'name_admin_bar'     => 'Discount',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New Discount',
				'new_item'           => 'New Discount',
				'edit_item'          => 'Edit Discount',
				'view_item'          => 'View Discount',
				'all_items'          => 'All Discounts',
				'search_items'       => 'Search Discounts',
				'parent_item_colon'  => 'Parent Discounts:',
				'not_found'          => 'No Discounts found.',
				'not_found_in_trash' => 'No Discounts found in Trash.',
			),
			'description'        => 'Description',
			'public'             => true,
			'menu_icon'          => 'dashicons-share-alt',
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'show_in_rest'       => true,
			'rest_base'          => 'discount',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'            => array( 'slug' => 'discount', 'with_front' => false),
			'map_meta_cap'   => true,
			'supports'      => array( 'title' )
		),
		'event'         => array(
			'labels'     => array(
				'name'               => 'Events',
				'singular_name'      => 'Event',
				'menu_name'          => 'Events',
				'name_admin_bar'     => 'Event',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New',
				'new_item'           => 'New Event',
				'edit_item'          => 'Edit Event',
				'view_item'          => 'View Event',
				'all_items'          => 'All Events',
				'search_items'       => 'Search Events',
				'parent_item_colon'  => 'Parent Events:',
				'not_found'          => 'No Events found.',
				'not_found_in_trash' => 'No Events found in Trash.',
			),
			'description'        => 'Description',
			'public'             => true,
			'menu_icon'          => 'dashicons-calendar-alt',
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'show_in_rest'       => true,
			'rest_base'          => 'event',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'            => array( 'slug' => 'event', 'with_front' => false),
			'map_meta_cap'   => true,
			'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'page-attributes' )
		),
		'event_comment' => array(
			'labels'     => array(
				'name'               => 'Event Comments',
				'singular_name'      => 'Event Comment',
				'menu_name'          => 'Event Comments',
				'name_admin_bar'     => 'Event Comment',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New Comment',
				'new_item'           => 'New Comment',
				'edit_item'          => 'Edit Comment',
				'view_item'          => 'View Comment',
				'all_items'          => 'All Comments',
				'search_items'       => 'Search Comments',
				'parent_item_colon'  => 'Parent Comments:',
				'not_found'          => 'No Comments found.',
				'not_found_in_trash' => 'No Comments found in Trash.',
			),
			'description'        => 'Description',
			'public'             => true,
			'menu_icon'          => 'dashicons-admin-comments',
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'show_in_rest'       => true,
			'rest_base'          => 'event-comment',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'            => array( 'slug' => 'event_comment', 'with_front' => false),
			'map_meta_cap'   => true,
			'supports'      => array( 'title', 'editor', 'author', 'page-attributes' )
		),
		'promotion'   => array(
			'labels'     => array(
				'name'               => 'Promotions',
				'singular_name'      => 'Promotion',
				'menu_name'          => 'Promotions',
				'name_admin_bar'     => 'Promotion',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New Promotion',
				'new_item'           => 'New Promotion',
				'edit_item'          => 'Edit Promotion',
				'view_item'          => 'View Promotion',
				'all_items'          => 'All Promotions',
				'search_items'       => 'Search Promotions',
				'parent_item_colon'  => 'Parent Promotions:',
				'not_found'          => 'No Reports found.',
				'not_found_in_trash' => 'No Reports found in Trash.',
			),
			'description'        => 'Description',
			'public'             => true,
			'menu_icon'          => 'dashicons-megaphone',
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'show_in_rest'       => true,
			'rest_base'          => 'promotion',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'            => array( 'slug' => 'report', 'editor', 'with_front' => false),
			'map_meta_cap'   => true,
			'supports'      => array( 'title', 'custom-fields' )
		),
		'report'   => array(
			'labels'     => array(
				'name'               => 'Reports',
				'singular_name'      => 'Report',
				'menu_name'          => 'Reports',
				'name_admin_bar'     => 'Report',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New Report',
				'new_item'           => 'New Report',
				'edit_item'          => 'Edit Report',
				'view_item'          => 'View Report',
				'all_items'          => 'All Reports',
				'search_items'       => 'Search Reports',
				'parent_item_colon'  => 'Parent Reports:',
				'not_found'          => 'No Reports found.',
				'not_found_in_trash' => 'No Reports found in Trash.',
			),
			'description'        => 'Description',
			'public'             => true,
			'menu_icon'          => 'dashicons-visibility',
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'show_in_rest'       => true,
			'rest_base'          => 'report',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'            => array( 'slug' => 'report', 'with_front' => false),
			'map_meta_cap'   => true,
			'supports'      => array( 'title', 'editor', 'custom-fields', 'author' )
		),
	),
	'taxonomies' => array(
		'listing_location' => array(
			'hierarchical'          => true,
			'show_ui'               => true,
			'show_in_rest'          => true,
			'rest_base'             => 'listing_location',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array('slug' => 'listing-location'),
			'post_types'            => array('listing', 'event'),
			'labels'                => array(
				'name'              => _x( 'Listing Locations', 'taxonomy general name', 'wiloke-listing-tools' ),
				'singular_name'     => _x( 'Listing Location', 'taxonomy singular name', 'wiloke-listing-tools' ),
				'search_items'      => esc_html__( 'Search Listing Locations', 'wiloke-listing-tools' ),
				'all_items'         => esc_html__( 'All Listing Locations', 'wiloke-listing-tools' ),
				'parent_item'       => esc_html__( 'Parent Listing Location', 'wiloke-listing-tools' ),
				'parent_item_colon' => esc_html__( 'Parent Listing Location:', 'wiloke-listing-tools' ),
				'edit_item'         => esc_html__( 'Edit Listing Location', 'wiloke-listing-tools' ),
				'update_item'       => esc_html__( 'Update Listing Location', 'wiloke-listing-tools' ),
				'add_new_item'      => esc_html__( 'Add New Listing Location', 'wiloke-listing-tools' ),
				'new_item_name'     => esc_html__( 'New Listing Location Name', 'wiloke-listing-tools' ),
				'menu_name'         => esc_html__( 'Listing Locations', 'wiloke-listing-tools' )
			)
		),
		'listing_cat' => array(
			'hierarchical'          => true,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'show_in_rest'          => true,
			'rest_base'             => 'listing_cat',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
			'query_var'             => true,
			'rewrite'               => array('slug' => 'listing-cat'),
			'post_types'            => array('listing', 'event'),
			'labels'                => array(
				'name'              => _x( 'Listing Categories', 'taxonomy general name', 'wiloke-listing-tools' ),
				'singular_name'     => _x( 'Listing Category', 'taxonomy singular name', 'wiloke-listing-tools' ),
				'search_items'      => esc_html__( 'Search Listing Categories', 'wiloke-listing-tools' ),
				'all_items'         => esc_html__( 'All Listing Categories', 'wiloke-listing-tools' ),
				'parent_item'       => esc_html__( 'Parent Listing Category', 'wiloke-listing-tools' ),
				'parent_item_colon' => esc_html__( 'Parent Listing Category:', 'wiloke-listing-tools' ),
				'edit_item'         => esc_html__( 'Edit Listing Category', 'wiloke-listing-tools' ),
				'update_item'       => esc_html__( 'Update Listing Category', 'wiloke-listing-tools' ),
				'add_new_item'      => esc_html__( 'Add New Listing Category', 'wiloke-listing-tools' ),
				'new_item_name'     => esc_html__( 'New Listing Category Name', 'wiloke-listing-tools' ),
				'menu_name'         => esc_html__( 'Listing Categories', 'wiloke-listing-tools' ),
			)
		),
		'listing_tag' => array(
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'show_in_rest'          => true,
			'rest_base'             => 'listing_tag',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
			'query_var'             => true,
			'rewrite'               => array('slug' => 'listing-tag'),
			'post_types'            => array('listing', 'event'),
			'labels'                => array(
				'name'              => _x( 'Listing Tags', 'taxonomy general name', 'wiloke-listing-tools' ),
				'singular_name'     => _x( 'Listing Tag', 'taxonomy singular name', 'wiloke-listing-tools' ),
				'search_items'      => esc_html__( 'Search Listing Tags', 'wiloke-listing-tools' ),
				'all_items'         => esc_html__( 'All Listing Tags', 'wiloke-listing-tools' ),
				'parent_item'       => esc_html__( 'Parent Listing Tag', 'wiloke-listing-tools' ),
				'parent_item_colon' => esc_html__( 'Parent Listing Tag:', 'wiloke-listing-tools' ),
				'edit_item'         => esc_html__( 'Edit Listing Tag', 'wiloke-listing-tools' ),
				'update_item'       => esc_html__( 'Update Listing Tag', 'wiloke-listing-tools' ),
				'add_new_item'      => esc_html__( 'Add New Listing Tag', 'wiloke-listing-tools' ),
				'new_item_name'     => esc_html__( 'New Listing Tag Name', 'wiloke-listing-tools' ),
				'menu_name'         => esc_html__( 'Listing Tags', 'wiloke-listing-tools' )
			)
		)
	),
	'post_statuses'=> array(
		'unpaid' => array(
			'label'                     => esc_html__( 'Unpaid', 'wiloke-listing-tools' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'icon'                      => 'la la-money',
			'private'                   => true,
			'label_count'               => _n_noop( 'Unpaid <span class="count">(%s)</span>', 'Unpaid <span class="count">(%s)</span>', 'wiloke-listing-tools' ),
		),
		'expired' => array(
			'label'                     => esc_html__( 'Expired', 'wiloke-listing-tools' ),
			'public'                    => false,
			'private'                   => true,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'icon'                      => 'la la-exclamation-triangle',
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'wiloke-listing-tools' ),
		),
		'temporary_close' => array(
			'label'                     => esc_html__( 'Temporary Close', 'wiloke-listing-tools' ),
			'public'                    => false,
			'protected'                 => true,
			'exclude_from_search'       => true,
			'private'                   => true,
			'show_in_admin_all_list'    => true,
			'icon'                      => 'la la-refresh',
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Temporary close <span class="count">(%s)</span>', 'Temporary close <span class="count">(%s)</span>', 'wiloke-listing-tools' ),
		),
		'editing' => array(
			'label'                     => esc_html__( 'Editing', 'wiloke-listing-tools' ),
			'public'                    => false,
			'protected'                 => true,
			'exclude_from_search'       => true,
			'private'                   => true,
			'show_in_admin_all_list'    => true,
			'icon'                      => 'la la-refresh',
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Editing close <span class="count">(%s)</span>', 'Temporary close <span class="count">(%s)</span>', 'wiloke-listing-tools' ),
		)
	)
];