<?php
return [
	'allSections' => array(
		'header'  => array(
			'isDefault' => true,
			'type'      => 'header',
			'key'       => 'header',
			'icon'      => 'la la-certificate',
			'heading'	=> esc_html__('Header', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Listing Name', 'wiloke-listing-tools'),
					'type' 		=> 'text',
					'desc'      => '',
					'key' 		=> 'listing_title',
					'fields'    => array(
						array(
							'heading'   => esc_html__('Label name', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'desc'      => '',
							'key'       => 'label',
							'label'     => 'Listing Title'
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'yes'
						)
					)
				),
				array(
					'heading' 	=> esc_html__('Tagline', 'wiloke-listing-tools'),
					'type' 		=> 'text',
					'desc'      => '',
					'toggle'    => 'enable',
					'key' 		=> 'tagline',
					'fields'    => array(
						array(
							'heading'   => esc_html__('Label name', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'desc'      => '',
							'key'       => 'label',
							'label'     => 'Tagline'
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'no'
						)
					)
				),
				array(
					'heading' 	=> esc_html__('Logo', 'wiloke-listing-tools'),
					'type' 		=> 'single_image',
					'desc'      => '',
					'toggle'    => 'enable',
					'key' 		=> 'logo',
					'fields'    => array(
						array(
							'heading'   => esc_html__('Label name', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'desc'      => '',
							'key'       => 'label',
							'label'     => 'Logo'
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'yes'
						)
					)
				),
				array(
					'heading' 	=> esc_html__('Cover Image', 'wiloke-listing-tools'),
					'type' 		=> 'single_image',
					'desc'      => '',
					'toggle'    => 'enable',
					'key' 		=> 'cover_image',
					'fields'    => array(
						array(
							'heading'   => esc_html__('Label name', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'desc'      => '',
							'key'       => 'label',
							'label'     => 'Cover Image'
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'yes'
						)
					)
				)
			)
		),
		'featured_image' => array(
			'isDefault' => true,
			'type'      => 'featured_image',
			'icon'      => 'la la-image',
			'key'       => 'featured_image',
			'heading'	=> esc_html__('Featured Image', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Settings', 'wiloke-listing-tools'),
					'type' 		=> 'single_image',
					'desc'      => '',
					'key' 		=> 'featured_image',
					'fields'    => array(
						array(
							'heading'   => esc_html__('Label name', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'desc'      => '',
							'key'       => 'label',
							'label'     => 'Featured Image'
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'yes'
						)
					)
				)
			)
		),
		'contact_info'  => array(
			'isDefault' => true,
			'type'      => 'contact_info',
			'key'       => 'contact_info',
			'icon'      => 'la la-phone-square',
			'heading'	=> esc_html__('Contact Information', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Email', 'wiloke-listing-tools'),
					'type' 		=> 'email',
					'desc'      => '',
					'key' 		=> 'email',
					'fields'    => array(
						array(
							'heading' 	=> esc_html__('Is Enable?', 'wiloke-listing-tools'),
							'type' 		=> 'checkbox',
							'desc'      => '',
							'key'       => 'isEnable',
							'isEnable' 	=> 'yes'
						),
						array(
							'heading' 	=> esc_html__('Label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'key'       => 'label',
							'label' 	=> 'Email'
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'no'
						)
					)
				),
				array(
					'heading' 	=> esc_html__('Phone', 'wiloke-listing-tools'),
					'type' 		=> 'text',
					'desc'      => '',
					'key' 		=> 'phone',
					'fields'    => array(
						array(
							'heading' 	=> esc_html__('Is Enable?', 'wiloke-listing-tools'),
							'type' 		=> 'checkbox',
							'desc'      => '',
							'key'       => 'isEnable',
							'isEnable' 	=> 'yes'
						),
						array(
							'heading' 	=> esc_html__('Label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'key'       => 'label',
							'label' 	=> 'Phone'
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'no'
						)
					)
				),
				array(
					'heading' 	=> esc_html__('Website', 'wiloke-listing-tools'),
					'type' 		=> 'url',
					'desc'      => '',
					'key' 		=> 'website',
					'fields'    => array(
						array(
							'heading' 	=> esc_html__('Is Enable?', 'wiloke-listing-tools'),
							'type' 		=> 'checkbox',
							'desc'      => '',
							'key'       => 'isEnable',
							'isEnable' 	=> 'yes'
						),
						array(
							'heading' 	=> esc_html__('Label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'key'       => 'label',
							'label' 	=> 'Website'
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'no'
						)
					)
				),
				array(
					'heading' 	=> esc_html__('Social Networks', 'wiloke-listing-tools'),
					'type' 		=> 'social_networks',
					'desc'      => '',
					'key' 		=> 'social_networks',
					'fields'    => array(
						array(
							'heading' 	=> esc_html__('Is Enable?', 'wiloke-listing-tools'),
							'type' 		=> 'checkbox',
							'desc'      => '',
							'key'       => 'isEnable',
							'isEnable' 	=> 'yes'
						),
						array(
							'heading' 	=> esc_html__('Excluding Social networks', 'wiloke-listing-tools'),
							'type' 		=> 'select',
							'isMultiple'=> 'yes',
							'desc'      => esc_html__('Those socials in this field will not shown on Add Listing page', 'wiloke-listing-tools'),
							'key'       => 'excludingSocialNetworks',
							'options'   => class_exists('WilokeSocialNetworks') ? WilokeSocialNetworks::$aSocialNetworks : array(),
							'excludingSocialNetworks' 	=> array()
						),
						array(
							'heading' 	=> esc_html__('Social name label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'key'       => 'socialNameLabel',
							'socialNameLabel' 	=> 'Social Networks'
						),
						array(
							'heading' 		=> esc_html__('Social Link Label', 'wiloke-listing-tools'),
							'type' 			=> 'text',
							'desc'          => '',
							'key'           => 'socialLinkLabel',
							'socialLinkLabel' => 'Social URL'
						),
						array(
							'heading' 	=> esc_html__('Add Social Button Name', 'wiloke-listing-tools'),
							'type' 	    => 'text',
							'desc'      => '',
							'key'       => 'btnName',
							'btnName'  => 'Add Social'
						)
					)
				)
			)
		),
		'category'  => array(
			'isDefault' => true,
			'type'      => 'category',
			'key'       => 'category',
			'icon'      => 'la la-file-text',
			'heading'	=> esc_html__('Category', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Category Setting', 'wiloke-listing-tools'),
					'type' 		=> 'category',
					'desc'      => '',
					'ajaxAction'=> 'wilcity_select2_fetch_term',
					'ajaxArgs'  => array(
						'taxonomy' => 'listing_cat'
					),
					'key' 		=> 'listing_cat',
					'fields'    => array(
						array(
							'heading' 	=> esc_html__('Is Enable?', 'wiloke-listing-tools'),
							'type' 		=> 'checkbox',
							'desc'      => '',
							'key'       => 'isEnable',
							'isEnable' 	=> 'yes'
						),
						array(
							'heading' 	=> esc_html__('Label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'key'       => 'label',
							'label'     => 'Categories'
						),
						array(
							'heading' 	=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 		=> 'checkbox',
							'desc'      => '',
							'key'       => 'isRequired',
							'isRequired' => 'yes'
						),
						array(
							'heading' 	=> esc_html__('Searching for Categories using the Ajax?', 'wiloke-listing-tools'),
							'type' 		=> 'checkbox',
							'desc'      => esc_html__('We recommend using this feature if your site has more than 100 Listing Locations', 'wiloke-listing-tools'),
							'key'       => 'isAjax',
							'isAjax'    => 'no'
						),
						array(
							'heading' 		=> esc_html__('Order by', 'wiloke-listing-tools'),
							'desc' 			=> esc_html__('Get all tags ordered by'),
							'type' 			=> 'select',
							'key'           => 'orderBy',
							'orderBy'       => 'term_id',
							'options'       => array(
								'name'       => 'name',
								'count'      => 'count',
								'slug'       => 'slug',
								'term_id'    => 'term_id',
							)
						),
						array(
							'heading' 		=> 'Maximum Categories',
							'type' 			=> 'text',
							'key'           => 'maximum',
							'maximum'       => 1
						)
					)
				)
			)
		),
		'listing_tag'  => array(
			'isDefault' => true,
			'type'      => 'listing_tag',
			'icon'      => 'la la-tag',
			'key'       => 'listing_tag',
			'heading'	=> esc_html__('Tags', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Settings', 'wiloke-listing-tools'),
					'type' 		=> 'listing_tag',
					'desc'      => '',
					'fileName'  => 'tag.php',
					'key' 		=> 'listing_tag',
					'fields'    => array(
						array(
							'heading' 	=> esc_html__('Label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'key'       => 'label',
							'label'     => 'Listing Features'
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'key'           => 'isRequired',
							'isRequired'    => 'no'
						),
						array(
							'heading' 		=> esc_html__('Maximum Tags', 'wiloke-listing-tools'),
							'desc' 		    => esc_html__('Maximum tags can be used for 1 listing', 'wiloke-listing-tools'),
							'type' 			=> 'text',
							'key'           => 'maximum',
							'maximum'       => 4
						),
						array(
							'heading' 		=> esc_html__('Over Maximum Warning', 'wiloke-listing-tools'),
							'type' 			=> 'text',
							'key'           => 'overMaximumTagsWarning',
							'overMaximumTagsWarning'  => 'You must specify 4 or less'
						),
						array(
							'heading' 		=> esc_html__('Order by', 'wiloke-listing-tools'),
							'desc' 			=> esc_html__('Get all tags ordered by'),
							'type' 			=> 'select',
							'key'           => 'orderBy',
							'orderBy'       => 'term_id',
							'options'       => array(
								'name'       => 'name',
								'count'      => 'count',
								'slug'       => 'slug',
								'term_id'    => 'term_id',
							)
						)
					)
				)
			)
		),
		'business_hours'  => array(
			'isDefault' => true,
			'type'      => 'business_hours',
			'key'       => 'business_hours',
			'icon'      => 'la la-clock-o',
			'heading'	=> esc_html__('Business Hours', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Hour Options', 'wiloke-listing-tools'),
					'type' 		=> 'business_hours',
					'desc'      => '',
					'key' 		=> 'business_hours',
					'fields'    => array(
						array(
							'heading' 	=> esc_html__('Open for selected hours Label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'desc'      => '',
							'key'       => 'openForSelectedHoursLabel',
							'openForSelectedHoursLabel' 	=> 'Open for selected hours'
						),
						array(
							'heading' 	=> esc_html__('Always Open Label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'desc'      => '',
							'key'       => 'alwaysOpenLabel',
							'alwaysOpenLabel' 	=> 'Always open'
						),
						array(
							'heading' 	=> esc_html__('No Hours Available Label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'desc'      => '',
							'key'       => 'noHoursAvailableLabel',
							'noHoursAvailableLabel' 	=> 'No Hours Available'
						),
						array(
							'heading' 	=> esc_html__('Default Opening Time', 'wiloke-listing-tools'),
							'type' 		=> 'select',
							'desc'      => '',
							'key'       => 'stdOpeningTime',
							'stdOpeningTime' => '',
							'options'   => \WilokeListingTools\Framework\Helpers\General::generateBusinessHours()
						),
						array(
							'heading' 	=> esc_html__('Default Closed Time', 'wiloke-listing-tools'),
							'type' 		=> 'select',
							'desc'      => '',
							'key'       => 'stdClosedTime',
							'stdClosedTime' => '',
							'options'   => \WilokeListingTools\Framework\Helpers\General::generateBusinessHours()
						)
					)
				)
			)
		),
		'single_price'  => array(
			'isDefault' => true,
			'type'      => 'single_price',
			'key'       => 'single_price',
			'icon'      => 'la la-money',
			'heading'	=> 'Single Price',
			'fields'    => array(
				array(
					'heading' 	=> 'Single Price',
					'type' 		=> 'single_price',
					'desc'      => '',
					'key' 		=> 'single_price',
					'fields'    => array(
						array(
							'heading' 	 => 'Label',
							'type' 		 => 'text',
							'key'        => 'label',
							'label'      => 'Price'
						),
						array(
							'heading' 	=> 'Is Required?',
							'type' 		=> 'checkbox',
							'desc'      => '',
							'key'       => 'isRequired',
							'isRequired' => 'no'
						),
					)
				)
			)
		),
		'price_range'  => array(
			'isDefault' => true,
			'type'      => 'price_range',
			'key'       => 'price_range',
			'icon'      => 'la la-money',
			'heading'	=> esc_html__('Price Range', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Price Range', 'wiloke-listing-tools'),
					'type' 		=> 'price_range',
					'desc'      => '',
					'key' 		=> 'price_range',
					'fields'    => array(
						array(
							'heading' 	=> esc_html__('Is Enable?', 'wiloke-listing-tools'),
							'type' 		=> 'checkbox',
							'desc'      => '',
							'key'       => 'isEnable',
							'isEnable' 	=> 'yes'
						),
						array(
							'heading' 	 => esc_html__('Range Label', 'wiloke-listing-tools'),
							'type' 		 => 'text',
							'key'        => 'rangeLabel',
							'rangeLabel' => 'Price Range'
						),
						array(
							'heading' 		=> 'Description',
							'type' 			=> 'text',
							'desc'          => '',
							'key'           => 'priceDescLabel',
							'priceDescLabel'=> 'Description'
						),
						array(
							'heading' 		=> esc_html__('Minimum Price', 'wiloke-listing-tools'),
							'type' 			=> 'text',
							'desc'          => '',
							'key'           => 'minimumPriceLabel',
							'minimumPriceLabel' => 'Minimum Price'
						),
						array(
							'heading' 		=> esc_html__('Maximum Price', 'wiloke-listing-tools'),
							'type' 			=> 'text',
							'desc'          => '',
							'key'           => 'maximumPriceLabel',
							'maximumPriceLabel' => 'Maximum Price'
						)
					)
				)
			)
		),
		'listing_address'  => array(
			'isDefault' => true,
			'type'      => 'listing_address',
			'key'       => 'listing_address',
			'icon'      => 'la la-globe',
			'heading'	=> esc_html__('Listing Address', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Region', 'wiloke-listing-tools'),
					'type' 		=> 'select2',
					'desc'      => '',
					'key' 		=> 'listing_location',
					'fields'    => array(
						array(
							'heading' 	=> esc_html__('Is Enable?', 'wiloke-listing-tools'),
							'type' 		=> 'checkbox',
							'desc'      => '',
							'key'       => 'isEnable',
							'isEnable' 	=> 'yes'
						),
						array(
							'heading' 	=> esc_html__('Label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'key'       => 'label',
							'label'     => 'Region'
						),
						array(
							'heading' 	=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 		=> 'checkbox',
							'desc'      => '',
							'key'       => 'isRequired',
							'isRequired' => 'yes'
						),
						array(
							'heading' 		=> esc_html__('Order by', 'wiloke-listing-tools'),
							'desc' 			=> esc_html__('Get all tags ordered by', 'wiloke-listing-tools'),
							'type' 			=> 'select',
							'key'           => 'orderBy',
							'orderBy'       => 'term_id',
							'options'       => array(
								'name'       => 'name',
								'count'      => 'count',
								'slug'       => 'slug',
								'term_id'    => 'term_id',
							)
						),
						array(
							'heading' 		=> 'Maximum Regions',
							'type' 			=> 'text',
							'key'           => 'maximum',
							'maximum'       => 1
						)
					)
				),
				array(
					'heading' 	=> esc_html__('Google Address', 'wiloke-listing-tools'),
					'type' 		=> 'map',
					'desc'      => '',
					'key' 		=> 'address',
					'fields'    => array(
						array(
							'heading' 	=> esc_html__('Label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'key'       => 'label',
							'label' 	=> 'Google Address'
						),
						array(
							'heading' 		    => esc_html__('Set Default Starting Location', 'wiloke-listing-tools'),
							'type' 			    => 'text',
							'desc'              => esc_html__('Leave empty to use visitor\'s location as the default', 'wiloke-listing-tools'),
							'key'               => 'defaultLocation',
							'defaultLocation' 	=> ''
						),
						array(
							'heading' 		    => esc_html__('Default Zoom', 'wiloke-listing-tools'),
							'type' 			    => 'text',
							'key'               => 'defaultZoom',
							'defaultZoom' 	    => 8
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'yes'
						)
					)
				)
			)
		),
		'listing_title'  => array(
			'isDefault' => true,
			'type'      => 'listing_title',
			'key'       => 'listing_title',
			'icon'      => 'la la-file-text',
			'heading'	=> esc_html__('Listing Title', 'wiloke-listing-tools'),
			'desc'	    => 'You should remove this field if you are using Header block field already.',
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Settings', 'wiloke-listing-tools'),
					'type' 		=> 'text',
					'desc'      => '',
					'key' 		=> 'listing_title',
					'fields'    => array(
						array(
							'heading' 	=> esc_html__('Label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'key'       => 'label',
							'label' 	=> 'Listing Title'
						),
					)
				)
			)
		),
		'listing_content'  => array(
			'isDefault' => true,
			'type'      => 'listing_content',
			'key'       => 'listing_content',
			'icon'      => 'la la-file-text',
			'heading'	=> esc_html__('Listing Content', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Listing Content', 'wiloke-listing-tools'),
					'type' 		=> 'textarea',
					'desc'      => '',
					'key' 		=> 'listing_content',
					'fields'    => array(
						array(
							'heading' 	=> esc_html__('Label', 'wiloke-listing-tools'),
							'type' 		=> 'text',
							'key'       => 'label',
							'label' 	=> 'Description'
						),
					)
				)
			)
		),
		'my_products'  => array(
			'isDefault' => true,
			'type'      => 'my_products',
			'key'       => 'my_products',
			'icon'      => 'la la-shopping-cart',
			'heading'	=> 'My Products',
			'fields'    => array(
				array(
					'heading' 	=> 'Settings',
					'type' 		=> 'select2',
					'desc'      => '',
					'key' 		=> 'my_products',
					'fields'    => array(
						array(
							'heading'   => 'Label',
							'type' 		=> 'text',
							'desc'      => '',
							'key'       => 'label',
							'label'     => 'Showing Products on the Listing'
						),
						array(
							'heading' 		=> '',
							'type' 			=> 'hidden',
							'desc'          => '',
							'key'           => 'ajaxAction',
							'ajaxAction'    => 'wilcity_fetch_dokan_products'
						),
						array(
							'heading' 		=> '',
							'type' 			=> 'hidden',
							'desc'          => '',
							'key'           => 'isAjax',
							'isAjax' 	    => 'yes'
						),
						array(
							'heading' 		=> 'Is Multiple Select?',
							'type' 			=> 'checkbox',
							'component' 	=> 'wiloke-checkbox',
							'desc'          => '',
							'key'           => 'isMultiple',
							'isMultiple' 	=> 'yes'
						)
					)
				)
			)
		),
		'my_room'  => array(
			'isDefault' => true,
			'type'      => 'my_room',
			'key'       => 'my_room',
			'icon'      => 'la la-hotel',
			'heading'	=> 'My Room',
			'fields'    => array(
				array(
					'heading' 	=> 'Settings',
					'type' 		=> 'select2',
					'desc'      => '',
					'key' 		=> 'my_room',
					'fields'    => array(
						array(
							'heading'   => 'Label',
							'type' 		=> 'text',
							'desc'      => '',
							'key'       => 'label',
							'label'     => 'Showing Products on the Listing'
						),
						array(
							'heading' 		=> '',
							'type' 			=> 'hidden',
							'desc'          => '',
							'key'           => 'ajaxAction',
							'ajaxAction'    => 'wilcity_fetch_my_room'
						),
						array(
							'heading' 		=> '',
							'type' 			=> 'hidden',
							'desc'          => '',
							'key'           => 'isAjax',
							'isAjax' 	    => 'yes'
						)
					)
				)
			)
		),
		'bookingcombannercreator'  => array(
			'isDefault' => true,
			'type'      => 'bookingcombannercreator',
			'key'       => 'bookingcombannercreator',
			'icon'      => 'la la-hotel',
			'heading'	=> 'Booking.com Banner Creator',
			'fields'    => array(
				array(
					'heading' 	=> 'Button Name Label',
					'type' 		=> 'text',
					'desc'      => '',
					'toggle'    => 'enable',
					'key' 		=> 'bookingcombannercreator_buttonName',
					'fields'    => array(
						array(
							'heading' 	=> 'Label',
							'type' 		=> 'text',
							'key'       => 'label',
							'label'     => 'Button Name',
						)
					)
				),
				array(
					'heading' 	=> 'Button Name Color Label',
					'type' 		=> 'colorpicker',
					'desc'      => '',
					'toggle'    => 'enable',
					'key' 		=> 'bookingcombannercreator_buttonColor',
					'fields'    => array(
						array(
							'heading' 	=> 'Label',
							'type' 		=> 'text',
							'key'       => 'label',
							'label' 	=> 'Button Color'
						),
					)
				),
				array(
					'heading' 	=> 'Button Background Label',
					'type' 		=> 'colorpicker',
					'desc'      => '',
					'toggle'    => 'enable',
					'key' 		=> 'bookingcombannercreator_buttonBg',
					'fields'    => array(
						array(
							'heading' 	=> 'Label',
							'type' 		=> 'text',
							'key'       => 'label',
							'label' 	=> 'Button Background Color'
						),
					)
				),
				array(
					'heading' 	=> 'Banner Image Settings',
					'type' 		=> 'single_image',
					'desc'      => '',
					'key' 		=> 'bookingcombannercreator_bannerImg',
					'fields'    => array(
						array(
							'heading' 	=> 'Label',
							'type' 		=> 'text',
							'key'       => 'label',
							'label' 	=> 'Banner Image (1920px wide suggested)'
						),
					)
				),
				array(
					'heading' 	=> 'Banner Copy',
					'type' 		=> 'text',
					'desc'      => '',
					'key' 		=> 'bookingcombannercreator_bannerCopy',
					'fields'    => array(
						array(
							'heading' 	=> 'Label',
							'type' 		=> 'text',
							'key'       => 'label',
							'label' 	=> 'Banner Copy'
						),
					)
				),
				array(
					'heading' 	=> 'Banner Copy Color Setting',
					'type' 		=> 'colorpicker',
					'desc'      => '',
					'toggle'    => 'enable',
					'key' 		=> 'bookingcombannercreator_bannerCopyColor',
					'fields'    => array(
						array(
							'heading' 	=> 'Label',
							'type' 		=> 'text',
							'key'       => 'label',
							'label' 	=> 'Banner Copy Color'
						),
					)
				),
				array(
					'heading' 	=> 'Banner Link',
					'type' 		=> 'text',
					'desc'      => '',
					'key' 		=> 'bookingcombannercreator_bannerLink',
					'fields'    => array(
						array(
							'heading' 	=> 'Label',
							'type' 		=> 'text',
							'key'       => 'label',
							'label' 	=> 'Banner Link'
						)
					)
				)
			)
		),
		'video'  => array(
			'isDefault' => true,
			'type'      => 'video',
			'key'       => 'video',
			'icon'      => 'la la-video-camera',
			'heading'	=> esc_html__('Video Urls', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Settings', 'wiloke-listing-tools'),
					'type' 		=> 'video',
					'desc'      => esc_html__('You can define the maximum videos user can add for each plan', 'wiloke-listing-tools'),
					'key' 		=> 'videos',
					'fields'    => array(
						array(
							'heading' 		=> esc_html__('Add Video Button Name', 'wiloke-listing-tools'),
							'type' 			=> 'text',
							'desc'          => '',
							'key'           => 'addMoreBtnName',
							'addMoreBtnName'=> 'Add More'
						),
						array(
							'heading' 		=> esc_html__('Placeholder', 'wiloke-listing-tools'),
							'type' 			=> 'text',
							'desc'          => '',
							'key'           => 'placeholder',
							'placeholder'   => 'Video Link'
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'yes'
						)
					)
				)
			)
		),
		'gallery'  => array(
			'isDefault' => true,
			'type'      => 'gallery',
			'key'       => 'gallery',
			'icon'      => 'la la-image',
			'heading'	=> esc_html__('Gallery', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Upload Images', 'wiloke-listing-tools'),
					'type' 		=> 'gallery',
					'desc'      => '',
					'key' 		=> 'gallery',
					'fields'    => array(
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'yes'
						)
					)
				)
			)
		),
		'image' => array(
			'isDefault' => false,
			'isCustomSection'=> 'yes',
			'type'      => 'image',
			'key'       => 'image',
			'icon'      => 'la la-image',
			'heading'	=> 'Image',
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Settings', 'wiloke-listing-tools'),
					'type' 		=> 'single_image',
					'desc'      => '',
					'key' 		=> 'image',
					'fields'    => array(
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'no'
						),
						array(
							'heading'   => 'Link To',
							'type'      => 'hidden',
							'desc'      => '',
							'key'       => 'isLinkTo',
							'isLinkTo'  => 'yes'
						)
					)
				)
			)
		),
		'text'  => array(
			'isCustomSection'=> 'yes',
			'type'      => 'text',
			'key'       => 'my_text_field',
			'icon'      => 'la la-magic',
			'heading'	=> esc_html__('Text Field', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Settings', 'wiloke-listing-tools'),
					'type' 		=> 'text',
					'desc'      => '',
					'key' 		=> 'settings',
					'fields'    => array(
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'component' 	=> 'wiloke-checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'no'
						)
					)
				)
			)
		),
		'textarea'  => array(
			'isCustomSection'=> 'yes',
			'type'      => 'textarea',
			'key'       => 'my_textarea_field',
			'icon'      => 'la la-wikipedia-w',
			'heading'	=> esc_html__('Textarea Field', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Settings', 'wiloke-listing-tools'),
					'type' 		=> 'textarea',
					'desc'      => '',
					'key' 		=> 'settings',
					'fields'    => array(
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'component' 	=> 'wiloke-checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'no'
						)
					)
				)
			)
		),
		'date_time'  => array(
			'isCustomSection'=> 'yes',
			'type'      => 'date_time',
			'key'       => 'date_time',
			'icon'      => 'la la-clock-o',
			'heading'	=> esc_html__('Date Time', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Settings', 'wiloke-listing-tools'),
					'type' 		=> 'date_time',
					'desc'      => '',
					'key' 		=> 'settings',
					'fields'    => array(
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'component' 	=> 'wiloke-checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'yes'
						)
					)
				)
			)
		),
		'select'  => array(
			'isCustomSection'=> 'yes',
			'type'      => 'select',
			'key'       => 'my_select_field',
			'icon'      => 'la la-eyedropper',
			'heading'	=> esc_html__('Select Field', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Settings', 'wiloke-listing-tools'),
					'desc'      => '',
					'type'      => 'select',
					'key' 		=> 'settings',
					'fields'    => array(
						array(
							'heading' 		=> esc_html__('Label', 'wiloke-listing-tools'),
							'type' 	        => 'text',
							'component' 	=> 'wiloke-text',
							'key'           => 'label',
							'label'         => 'Select'
						),
						array(
							'heading' 		=> esc_html__('Options', 'wiloke-listing-tools'),
							'type' 			=> 'textarea',
							'component' 	=> 'wiloke-textarea',
							'isOptionField' => true,
							'desc' 		    => esc_html__('Each option is separated by a comma', 'wiloke-listing-tools'),
							'key' 			=> 'options',
							'options'       => 'Option A, Option B'
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'component' 	=> 'wiloke-checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'no'
						),
						array(
							'heading' 		=> esc_html__('Is Multiple Select?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'component' 	=> 'wiloke-checkbox',
							'desc'          => '',
							'key'           => 'isMultiple',
							'isMultiple' 	=> 'yes'
						)
					)
				)
			)
		),
		'checkbox2'  => array(
			'isCustomSection'=> 'yes',
			'type'      => 'checkbox2',
			'key'       => 'my_checkbox_field',
			'icon'      => 'la la-check',
			'heading'	=> esc_html__('Checkbox Field', 'wiloke-listing-tools'),
			'fields'    => array(
				array(
					'heading' 	=> esc_html__('Settings', 'wiloke-listing-tools'),
					'type' 		=> 'checkbox2',
					'desc'      => '',
					'key' 		=> 'settings',
					'fields'    => array(
						array(
							'heading' 		=> esc_html__('Options', 'wiloke-listing-tools'),
							'type' 			=> 'textarea',
							'component' 	=> 'wiloke-textarea',
							'isOptionField' => true,
							'key' 			=> 'options',
							'options'       => 'Option A, Option B'
						),
						array(
							'heading' 		=> esc_html__('Is Required?', 'wiloke-listing-tools'),
							'type' 			=> 'checkbox',
							'component' 	=> 'wiloke-checkbox',
							'desc'          => '',
							'key'           => 'isRequired',
							'isRequired' 	=> 'no'
						)
					)
				)
			)
		),
		'group'  => array(
			'isGroup'   => 'yes',
			'type'      => 'group',
			'key'       => 'my_group_field',
			'icon'      => 'la la-check',
			'heading'	=> 'Group Field',
			'fields'    => array(
				array(
					array(
						'heading'   => 'Label',
						'key'       => 'label',
						'type'      => 'label',
						'value'     => '',
						'component' => 'wiloke-input'
					),
					array(
						'heading' 		=> 'Key',
						'type' 			=> 'name',
						'component' 	=> 'wiloke-input',
						'desc'          => '',
						'value'         => '',
						'key'           => 'name'
					),
					array(
						'heading' 		=> 'Field Type',
						'type' 			=> 'customField',
						'component' 	=> 'wiloke-select-field-type',
						'desc'          => '',
						'key'           => 'customField',
						'value'         => array(
							'type'      => 'text',
							'options'   => 'Option A, Option B'
						)
					)
				)
			)
		)
	)
];
