<?php
$atts = shortcode_atts(
	array(
		'TYPE'              => 'LISTINGS',
		'post_type'         => 'listing',
		'from'              => 'all',
		'orderby'           => '',
		'posts_per_page'    => 6,
		'listing_cats'      => '',
		'listing_locations' => '',
		'listing_tags'      => '',
		'extra_class'       => ''
	),
	$atts
);

wilcity_sc_render_listings_on_mobile($atts);