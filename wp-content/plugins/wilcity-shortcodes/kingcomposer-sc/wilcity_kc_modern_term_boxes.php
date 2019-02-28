<?php
$atts = shortcode_atts(
	array(
		'TYPE'      => 'MODERN_TERM_BOXES',
		'items_per_row' => 'col-lg-3',
		'taxonomy'      => 'listing_cat',
		'listing_cats'  => '',
		'col_gap'  => 20,
		'listing_locations' => '',
		'image_size' => 'wilcity_560x300',
		'listing_tags'  => '',
		'orderby'       => 'count',
		'order'         => 'DESC',
		'extra_class'   => ''
	),
	$atts
);

wilcity_sc_render_modern_term_boxes($atts);