<?php
$atts = shortcode_atts(
	array(
        'post_type'         => 'event',
        'listing_tags'      => '',
        'listing_cats'      => '',
        'listing_locations' => '',
        'maximum_posts_on_lg_screen'    => 'col-lg-3',
        'maximum_posts_on_md_screen'    => 'col-md-4',
        'maximum_posts_on_sm_screen'    => 'col-sm-6',
        'from'      => 'all',
        'orderby'   => 'post_date',
        'img_size'          => 'wilcity_img_360x200',
        'posts_per_page'    => 6,
		'extra_class'       => ''
	),
	$atts
);

wilcity_sc_render_events_grid($atts);