<?php
use WILCITY_SC\SCHelpers;
function wilcity_render_slider($atts){
	$aArgs = SCHelpers::parseArgs($atts);
	$query = new WP_Query($aArgs);

	if ( !$query->have_posts() ){
		echo '';
		wp_reset_postdata();
		return false;
	}

	$atts = SCHelpers::mergeIsAppRenderingAttr($atts);

	if ( SCHelpers::isApp($atts) ){
		$aResponse = apply_filters('wilcity/mobile/render_slider_sc', $atts, $query);

		echo '%SC%' . json_encode(
				array(
					'oSettings'=> $atts,
					'oResults' => $aResponse,
					'TYPE'     => $atts['TYPE']
				)
			) . '%SC%';
		return '';
	}

	$wrap_class	= apply_filters( 'wilcity-el-class', $atts );
	$wrap_class = implode(' ', $wrap_class) . '  ' . $atts['extra_class'];

	$wrap_class = 'swiper__module swiper-container swiper--button-pill swiper--button-abs-outer swiper--button-mobile-disable ' . $wrap_class;

	if ( !empty($atts['heading']) || !empty($atts['desc']) ){
		wilcity_render_heading(array(
			'TYPE'              => 'HEADING',
			'blur_mark'         => '',
			'blur_mark_color'   => '',
			'heading'           => $atts['heading'],
			'heading_color'     => '#252c41',
//			'description'       => base64_encode($atts['desc']),
			'description'       => $atts['desc'],
			'description_color' => '#70778b',
			'alignment'         => 'wil-text-center',
			'extra_class'       => ''
		));
	}

	if ( wp_is_mobile() ){
		$imgSize = 'wilcity_380x215';
    }else{
	    if ( !empty($atts['desktop_image_size']) ){
		    $imgSize = $atts['desktop_image_size'];
        }else{
		    if ( $atts['maximum_posts_on_lg_screen'] <= 2 ){
			    $imgSize = 'large';
		    }else if($atts['maximum_posts_on_lg_screen'] == 3){
			    $imgSize = 'medium';
		    }else{
			    $imgSize = 'wilcity_380x215';
		    }
        }
    }
?>
	<div class="<?php echo esc_attr($wrap_class); ?>" data-options='{"autoplay": <?php echo $atts['is_auto_play'] == 'enable'  ? "true" : "false"; ?>,"slidesPerView":<?php echo esc_attr($atts['maximum_posts_on_lg_screen']); ?>,"spaceBetween":30,"breakpoints":{"640":{"slidesPerView":<?php echo esc_attr($atts['maximum_posts_on_extra_sm_screen']); ?>,"spaceBetween":10},"992":{"slidesPerView":<?php echo esc_attr($atts['maximum_posts_on_sm_screen']); ?>,"spaceBetween":10},"1200":{"slidesPerView":<?php echo esc_attr($atts['maximum_posts_on_md_screen']); ?>,"spaceBetween":10},"1400":{"slidesPerView":<?php echo esc_attr($atts['maximum_posts_on_lg_screen']); ?>, "spaceBetween":20}, "1600":{"slidesPerView":<?php echo esc_attr($atts['maximum_posts_on_extra_lg_screen']); ?>, "spaceBetween":20}}}' style="min-height: 285px;">
		<div class="swiper-wrapper" data-lazy-load="yes">
			<?php
			while ($query->have_posts()){
				$query->the_post();
				if ( $query->post->post_type == 'event' ){
					wilcity_event_slider_item($query->post, $imgSize);
                }else{
					wilcity_feature_slider_item($query->post, $imgSize);
                }
			}
			wp_reset_postdata();
			?>
		</div>

		<div class="swiper-button-custom">
			<div class="swiper-button-prev-custom"><i class='la la-angle-left'></i></div>
			<div class="swiper-button-next-custom"><i class='la la-angle-right'></i></div>
		</div>
	</div>
<?php
}