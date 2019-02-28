<?php
$prefix = 'wilcity_';
return array(
	'listing_location_settings' =>  array(
		'id'            => 'listing_location_settings',
		'title'         => esc_html__('Settings', 'wiloke-listing-tools'),
		'object_types'  => array('term'),
		'taxonomies'    => array('listing_location'),
		'context'       => 'normal',
		'priority'      => 'low',
		'show_names'    => true, // Show field names on the left
		'fields'        => array(
			array(
				'type'      => 'taxonomy_multicheck',
				'taxonomy'  => 'listing_tag',
				'id'        => $prefix.'tags_children',
				'name'      => esc_html__('Set Tags belong to this Locations', 'wiloke-listing-tools'),
				'description' => 'Leave empty means belongs to all tags'
			),
			array(
				'type'      => 'text',
				'taxonomy'  => 'icon',
				'id'        => $prefix.'icon',
				'name'      => 'LineIcon',
			),
			array(
				'type'      => 'colorpicker',
				'id'        => $prefix.'icon_color',
				'name'      => 'Icon Image',
			),
			array(
				'type'      => 'file',
				'id'        => $prefix.'featured_image',
				'name'      => 'Featured Image'
			),
			array(
				'type'      => 'colorpicker',
				'id'        => $prefix.'left_gradient_bg',
				'name'      => 'Left Gradient Background',
				'desc'      => 'This setting is for Term Boxes shortcode'
			),
			array(
				'type'      => 'colorpicker',
				'id'        => $prefix.'right_gradient_bg',
				'name'      => 'Right Gradient Background',
				'desc'      => 'This setting is for Term Boxes shortcode'
			),
			array(
				'type'      => 'text',
				'id'        => $prefix.'gradient_tilted_degrees',
				'name'      => 'Gradient tilted degrees',
				'desc'      => 'Eg: A gradient tilted 45 degrees, starting Left Background and finishing Right Background',
				'default'   => -10
			),
			array(
				'type'      => 'multicheck_inline',
				'id'        => $prefix.'belongs_to',
				'name'      => esc_html__('Belongs To', 'wiloke-listing-tools'),
				'desc'      => 'Enter in your icon name you want to use. You can find the icon at <a href="https://icons8.com/line-awesome" target="_blank">https://icons8.com</a>',
				'description'       => 'Select Listing Types that this term should belong to. Leave empty to set the category for all',
				'options_cb' => array('WilokeListingTools\MetaBoxes\Listing', 'setListingTypesOptions')
			),
		)
	)
);