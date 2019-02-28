<?php
return [
	'listing' => array(
		'@context' => 'http://schema.org/',
		'@type'    => 'LocalBusiness',
		'name'     => '{{postTitle}}',
		'image'    => '{{coverImg}}',
		'description' => '{{postExcerpt}}',
		'address' => array(
			'addressLocality' => '{{listing_location}}',
			'streetAddress'   => '{{googleAddress}}'
		),
		'review' => array(
			'@type'         => 'Review',
			'reviewRating'  => array(
				'@type'         => 'Rating',
				'bestRating'    => '{{bestRating}}',
				'ratingValue'   => '{{averageRating}}',
				'worstRating'   => '{{worstRating}}'
			)
		),
		'sameAs' => '{{socialNetworks}}',
		'telephone'  => '{{telephone}}',
		'photos' => '{{photos}}',
		'priceRange' => '{{priceRange}}',
		'email' => '{{email}}'
	),
	'event' => array(
		'@context' => 'http://schema.org/',
		'@type'    => 'Event',
		'name'     => '{{postTitle}}',
		'image'    => '{{coverImg}}',
		'description' => '{{postExcerpt}}',
		'address' => array(
			'addressLocality' => '{{listing_location}}',
			'streetAddress'   => '{{googleAddress}}'
		),
		'sameAs' => '{{socialNetworks}}',
		'telephone'  => '{{telephone}}',
		'photos' => '{{photos}}',
		'priceRange' => '{{priceRange}}',
		'email' => '{{email}}',
		'startDate' => '{{eventStartDate}}',
		'endDate' => '{{eventEndDate}}',
	),
];