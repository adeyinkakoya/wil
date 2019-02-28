<?php
$atts = shortcode_atts(
	array(
		'TYPE'      => 'TERM_BOXES',
		'items_per_row' => 'col-lg-3',
		'taxonomy'      => 'listing_cat',
		'listing_cats'  => '',
		'listing_locations' => '',
		'listing_tags'  => '',
		'orderby'       => 'count',
		'toggle_box_gradient' => 'enable',
		'left_gradient_color' => '#006bf7',
		'right_gradient_color' => '#f06292',
		'order'       => 'DESC',
		'number'       => '',
		'extra_class'   => ''
	),
	$atts
);

wilcity_sc_render_term_boxes($atts);