<?php
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\General;

function wilcityRenderImageField($aAtts){
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

	$imgID = GetSettings::getPostMeta($post->ID, 'custom_'.$aAtts['key']);

	if ( empty($imgID) ){
		return '';
	}

	return wp_get_attachment_image($imgID, 'large');
}

add_shortcode('wilcity_render_image_field', 'wilcityRenderImageField');