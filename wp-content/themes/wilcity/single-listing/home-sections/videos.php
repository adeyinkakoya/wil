<?php
global $post, $wilcityaVideo, $wilcityArgs;
$aRawVideos = \WilokeListingTools\Framework\Helpers\GetSettings::getPostMeta($post->ID, 'video_srcs');

if ( empty($aRawVideos) ){
	return '';
}
$aRawVideos = \WilokeListingTools\Frontend\SingleListing::parseVideos($aRawVideos, $post->ID);
?>
<div class="content-box_module__333d9">
	<wiloke-single-header icon="<?php echo esc_attr($wilcityArgs['icon']); ?>" heading="<?php echo esc_attr($wilcityArgs['name']); ?>"></wiloke-single-header>
    <div class="content-box_body__3tSRB">
        <div class="gallery_module__2AbLA">
            <div class="row" data-col-xs-gap="5" data-col-sm-gap="10">
                <?php
                foreach ($aRawVideos as $wilcityaVideo){
                    get_template_part('single-listing/partials/video');
                }
                ?>
            </div>
        </div>
    </div>
	<footer class="content-box_footer__kswf3">
        <wiloke-switch-tab-btn tab-key="videos" v-on:on-switch-tab="switchTab" wrapper-class="list_link__2rDA1 text-ellipsis color-primary--hover wil-text-center">
            <template slot="insideTab">
				<?php esc_html_e('See All', 'wilcity'); ?>
            </template>
        </wiloke-switch-tab-btn>
    </footer>
</div>
