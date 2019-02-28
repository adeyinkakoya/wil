<?php
global $post, $wilcityArgs;
use WilokeListingTools\Framework\Helpers\GetSettings;

$aRawGallery = GetSettings::getPostMeta($post->ID, 'gallery');
if ( empty($aRawGallery) ){
	return '';
}

$aGallery = array();

foreach ($aRawGallery as $galleryID => $link){
	$aItem['title']      = get_the_title($galleryID);
	$aItem['link']       = $link;
	$aItem['thumbnail']  = wp_get_attachment_image_url($galleryID, 'thumbnail');
	$aItem['full']       = wp_get_attachment_image_url($galleryID, 'large');
	$aItem['src']        = $link;
	$aGallery[] = $aItem;
}
$numberOfPhotos = isset($wilcityArgs['maximumItemsOnHome']) && !empty($wilcityArgs['maximumItemsOnHome']) ? $wilcityArgs['maximumItemsOnHome'] : 4;
?>
<!-- content-box_module__333d9 -->
<div class="content-box_module__333d9">
	<wiloke-single-header icon="<?php echo esc_attr($wilcityArgs['icon']); ?>" heading="<?php echo esc_attr($wilcityArgs['name']); ?>"></wiloke-single-header>
	<div class="content-box_body__3tSRB">
		<wiloke-gallery item-class="col-xs-6 col-sm-3" gallery-id="wilcity-home-page-section" :number-of-items="<?php echo abs($numberOfPhotos); ?>" size="<?php echo esc_attr(apply_filters('wilcity/single-listing/gallery/size', 'thumbnail')); ?>" raw-gallery="<?php echo esc_attr(json_encode($aGallery)); ?>" is-show-on-home="yes"></wiloke-gallery>
	</div>
	<footer class="content-box_footer__kswf3">
        <wiloke-switch-tab-btn wrapper-class="wil-text-center list_link__2rDA1 text-ellipsis color-primary--hover" tab-key="photos" v-on:on-switch-tab="switchTab">
            <template slot="insideTab">
	            <?php esc_html_e('See All', 'wilcity'); ?>
            </template>
        </wiloke-switch-tab-btn>
    </footer>
</div><!-- End / content-box_module__333d9 -->