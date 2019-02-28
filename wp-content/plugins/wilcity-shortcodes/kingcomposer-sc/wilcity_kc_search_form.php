<?php
$atts = shortcode_atts(
	array(
		'TYPE'                  => 'SEARCH_FORM',
		'is_using_tab'          => 'no',
		'items'                 => array(),
		'extra_class'           => ''
	),
	$atts
);

wilcity_sc_render_hero_search_form($atts);