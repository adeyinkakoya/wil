<?php
use WilokeListingTools\Framework\Helpers\GetSettings;
use WILCITY_SC\SCHelpers;
use WilokeListingTools\Controllers\ReviewController;

$atts = shortcode_atts(
	array(
		'TYPE'      => 'GRID',
        'post_type' => 'listing',
        'from'  => 'all',
        'maximum_posts_on_lg_screen'    => 'col-lg-3',
        'maximum_posts_on_md_screen'    => 'col-md-4',
        'maximum_posts_on_sm_screen'    => 'col-sm-6',
        'img_size'          => 'wilcity_img_360x200',
        'orderby'    => '',
        'unit'    => 'km',
        'radius'    => 10,
        'tabname'    => '',
        'posts_per_page'    => 6,
		'listing_cats'      => '',
		'listing_locations' => '',
		'listing_tags'      => '',
		'extra_class'       => ''
	),
	$atts
);

wilcity_sc_render_grid($atts);