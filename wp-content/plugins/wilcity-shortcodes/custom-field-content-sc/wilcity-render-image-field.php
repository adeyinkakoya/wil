<?php
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\General;

function wilcityRenderTextField($aAtts){
	$aAtts = shortcode_atts(
		array(
			'post_id'     => '',
			'key'         => '',
			'is_mobile'   => '',
			'description' => '',
			'title'       => ''
		),
		$aAtts
	);

	if ( !empty($aAtts['post_id']) ){
		$post = get_post($aAtts['post_id']);
	}else{
		$post = \WILCITY_SC\SCHelpers::getPost();
	}

	if ( empty($aAtts['key']) || !class_exists('WilokeListingTools\Framework\Helpers\GetSettings') || empty($post)){
		return '';
	}

	if ( !GetSettings::isPlanAvailableInListing($post->ID, $aAtts['key']) ){
		return '';
	}

	$content = GetSettings::getPostMeta($post->ID, 'custom_'.$aAtts['key']);
	return do_shortcode($content);
}

add_shortcode('wilcity_render_text_field', 'wilcityRenderTextField');