<?php
$atts = shortcode_atts(
    array(
        'include'       => '',
        'items_per_row' => 'col-md-4',
        'extra_class'   => '',
        'listing_type'  => 'flexible',
        'css'           => ''
    ),
	$atts
);

wilcityPricing($atts);