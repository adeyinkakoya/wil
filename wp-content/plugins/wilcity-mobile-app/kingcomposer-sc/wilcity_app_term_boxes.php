<?php
use \WILCITY_SC\SCHelpers;
use \WilokeListingTools\Framework\Helpers\GetSettings;

$atts = shortcode_atts(
	array(
		'TYPE'              => 'MODERN_TERM_BOXES',
		'items_per_row'     => 'col-lg-3',
		'taxonomy'          => 'listing_cat',
		'listing_cats'      => '',
		'col_gap'           => 20,
		'listing_locations' => '',
		'image_size'        => 'wilcity_560x300',
		'listing_tags'      => '',
		'orderby'           => 'count',
		'order'             => 'DESC',
		'extra_class'       => ''
	),
	$atts
);

$atts = SCHelpers::mergeIsAppRenderingAttr($atts);

$aArgs = array(
	'taxonomy'      => $atts['taxonomy'],
	'hide_empty'    => false
);

$aRawTermIDs = $atts[$atts['taxonomy'].'s'];
if ( !empty($aRawTermIDs) ){
	$aRawTermIDs = explode(',', $aRawTermIDs);
	$aTerms = array();

	foreach ($aRawTermIDs as $rawTerm){
		$aParse = explode(':', $rawTerm);
		$aTerms[] = $aParse[0];
	}

	$aArgs['include'] = $aTerms;
}else{
	$aArgs['orderby'] = $atts['orderby'];
	$aArgs['order'] = $atts['order'];
}

$aTerms = get_terms($aArgs);
if ( empty($aTerms) || is_wp_error($aTerms) ){
	return '';
}
$aResponse = array();
foreach ($aTerms as $oTerm){
	$aPostFeaturedImgs = GetSettings::getPostFeaturedImgsByTerm($oTerm->term_id, $atts['taxonomy']);
	$aTerm = get_object_vars($oTerm);

	$featuredImgID = GetSettings::getTermMeta($oTerm->term_id, 'featured_image_id');
	if ( !empty($featuredImgID) ){
		$img = wp_get_attachment_image_url($featuredImgID, 'large');
		$aTerm['featuredImg'] = $img ? $img : WILCITY_APP_IMG_PLACEHOLDER;
	}else{
		$aTerm['featuredImg'] = GetSettings::getTermMeta($oTerm->term_id, 'featured_image');
	}

	$aResponse[] = array(
		'oTerm' => $aTerm,
		'aPostFeaturedImg' => $aPostFeaturedImgs,
		'oCount' => array(
			'number' => $oTerm->count,
			'text'   => $oTerm->count > 1 ? esc_html__('Listings', 'wilcity-shortcodes') : esc_html__('Listing', 'wilcity-shortcodes')
		),
		'oIcon' => WilokeHelpers::getTermOriginalIcon($oTerm)
	);
}

echo '%SC%' . json_encode(array(
		'oSettings' => $atts,
		'TYPE'      => $atts['TYPE'],
		'oResults'  => $aResponse,
	)) . '%SC%';
return '';