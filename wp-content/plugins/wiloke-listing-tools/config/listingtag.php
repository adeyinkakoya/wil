<?php
$prefix = 'wilcity_';
return array(
	'listing_tag_settings' =>  array(
		'id'            => 'listing_tag_settings',
		'title'         => esc_html__('Settings', 'wiloke-listing-tools'),
		'object_types'  => array('term'),
		'taxonomies'    => array('listing_tag'),
		'context'       => 'normal',
		'priority'      => 'low',
		'show_names'    => true, // Show field names on the left
		'fields'        => array(
			array(
				'type'      => 'text',
				'id'        => $prefix.'icon',
				'name'      => 'LineAwesome Icon',
				'desc'      => 'Enter in your icon name you want to use. You can find the icon at <a href="https://icons8.com/line-awesome" target="_blank">https://icons8.com</a>',
			),
			array(
				'type'      => 'colorpicker',
				'id'        => $prefix.'icon_color',
				'name'      => 'Icon Color'
			),
			array(
				'type'      => 'file',
				'id'        => $prefix.'icon_img',
				'name'      => esc_html__('Upload Your Icon', 'wiloke-listing-tools'),
				'desc'      => esc_html__('The icon image will get higher priority than LineAwesome Icon', 'wiloke-listing-tools'),
				'preview_size' => 'full'
			),
			array(
				'type'      => 'file',
				'taxonomy'  => 'featured_image',
				'id'        => $prefix.'featured_image',
				'name'      => 'Featured Image'
			),
			array(
				'type'      => 'multicheck_inline',
				'id'        => $prefix.'belongs_to',
				'name'      => esc_html__('Belongs To', 'wiloke-listing-tools'),
				'description'       => 'Select Listing Types that this term should belong to. Leave empty to set the tag for all',
				'options_cb' => array('WilokeListingTools\MetaBoxes\Listing', 'setListingTypesOptions')
			)
		)
	)
);