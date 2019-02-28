<?php
use \WILCITY_SC\SCHelpers;

$atts = shortcode_atts(
	array(
		'TYPE'      => 'EVENTS_SLIDER',
		'maximum_posts'             => 8,
		'maximum_posts_on_extra_lg_screen'=> 6,
		'maximum_posts_on_lg_screen'=> 5,
		'maximum_posts_on_md_screen'=> 5,
		'maximum_posts_on_sm_screen'=> 2,
		'maximum_posts_on_extra_sm_screen'=> 2,
		'heading'                   => '',
		'desc'                      => '',
		'post_type'                 => 'event',
		'from'                      => 'all',
		'listing_tags'              => '',
		'listing_cats'              => '',
		'listing_locations'         => '',
		'orderby'                   => '',
		'is_auto_play'              => 'disable',
		'desktop_image_size'        => '',
		'extra_class'               => '',
		'css_custom'                => ''
	),
	$atts
);

wilcity_render_slider($atts);