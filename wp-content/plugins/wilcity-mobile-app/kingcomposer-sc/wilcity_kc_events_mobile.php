<?php
use WILCITY_SC\SCHelpers;
$atts = shortcode_atts(
	array(
		'post_type'         => 'event',
		'orderby'           => 'post_date',
		'img_size'          => 'wilcity_img_360x200',
		'posts_per_page'    => 6,
		'extra_class'       => ''
	),
	$atts
);

$aArgs = SCHelpers::parseArgs($atts);

$query = new WP_Query($aArgs);

if ( !$query->have_posts() ){
	wp_reset_postdata();
	return '';
}

global $post;
$aResponse = array();
while ( $query->have_posts() ){
	$query->the_post();
	$aListing = apply_filters('wilcity/mobile/render_event_on_mobile', $atts, $post);
	$aResponse[] = $aListing;
} wp_reset_postdata();

echo '%SC%' . json_encode(
		array(
			'oSettings' => $atts,
			'TYPE'      => 'EVENTS',
			'oResults'  => $aResponse
		)
	) . '%SC%';
return '';