<?php

namespace WilokeListingTools\Frontend;


use WilokeListingTools\Framework\Helpers\GetSettings;

class Gallery {
	public static function parseGallery($postID, $size='medium'){
		$aRawGallery = GetSettings::getPostMeta($postID, 'gallery');

		if ( empty($aRawGallery) ){
			return false;
		}

		$aGallery = array();

		foreach ($aRawGallery as $galleryID => $link){
			$aItem['title']      = get_the_title($galleryID);
			$aItem['link']       = $link;
			$aItem['thumbnail']  = wp_get_attachment_image_url($galleryID, 'thumbnail');
			$aItem[$size]        = wp_get_attachment_image_url($galleryID, $size);
			$aItem['full']       = $link;
			$aGallery[] = $aItem;
		}

		return $aGallery;
	}
}