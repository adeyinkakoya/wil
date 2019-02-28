<?php
use WILCITY_SC\SCHelpers;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Controllers\ReviewController;

function wilcity_sc_render_grid($atts){
	$aArgs = SCHelpers::parseArgs($atts);

	if ( $atts['orderby'] !== 'nearbyme' ){
		$query = new WP_Query($aArgs);
		if ( !$query->have_posts() ){
			wp_reset_postdata();
			return '';
		}
    }

	$atts = \WILCITY_SC\SCHelpers::mergeIsAppRenderingAttr($atts);
	if ( SCHelpers::isApp($atts) ){
		$aResponse = array();
		global $post;
		$oSkeleton = new \WILCITY_APP\Controllers\JsonSkeleton();

		if ( $atts['orderby'] !== 'nearbyme' ){
			while ( $query->have_posts() ){
				$query->the_post();
				$aResponse[] = $oSkeleton->listingSkeleton($post, array('oGallery', 'oSocialNetworks', 'oVideos'));
			} wp_reset_postdata();
        }

		echo '%SC%' . json_encode(
				array(
					'oSettings' => $atts,
					'oResults'  => $aResponse,
					'TYPE'      => $atts['TYPE']
				)
			) . '%SC%';
		return '';
	}

	$wrap_class	= apply_filters( 'wilcity-el-class', $atts );
	$wrap_class = implode(' ', $wrap_class) . '  ' . $atts['extra_class'];
	$wrap_class .= ' wilcity-grid';

	?>
	<div class="<?php echo esc_attr($wrap_class); ?>">
        <?php
        if ( $atts['orderby'] == 'nearbyme' ){
            $tabID = '';
            if ( !empty($atts['tabname']) ){
                $tabID = strtolower($atts['tabname']);
                $tabID = preg_replace_callback('/\s+/', function($aMatched){
                    return '-';
                }, $tabID);
            }
            $anchor = $atts['post_type'] == 'event' ? 'events' : 'listings';
            ?>
            <div class="wilcity-<?php echo esc_attr($anchor); ?>-nearbyme">
                <keep-alive>
                    <wiloke-listings-near-by-me :post-type="'<?php echo esc_attr($atts['post_type']); ?>'" :posts-per-page="'<?php echo esc_attr($atts['posts_per_page']); ?>'" :grid-class="'<?php echo esc_attr($atts['maximum_posts_on_md_screen'] . ' ' . $atts['maximum_posts_on_sm_screen'] . ' ' . $atts['maximum_posts_on_lg_screen']); ?>'" :unit="'<?php echo esc_attr($atts['unit']); ?>'" :radius="'<?php echo esc_attr($atts['radius']); ?>'" :tab-id="'<?php echo esc_attr($tabID); ?>'" :o-ajax-data="'<?php echo base64_encode(serialize($atts)); ?>'"></wiloke-listings-near-by-me>
                </keep-alive>
            </div>
            <?php
        }else{
            ?><div class="row"><?php
            if ( $query->have_posts() ){
                while($query->have_posts()){
                    $query->the_post();
                    wilcity_render_grid_item($query->post, $atts);
                }
            }
            ?></div><?php
            wp_reset_postdata();
        }
        ?>
	</div>
	<?php
}