<?php
use WILCITY_SC\SCHelpers;
function wilcity_sc_render_events_grid($atts){
	$atts  = SCHelpers::mergeIsAppRenderingAttr($atts);
	$aArgs = SCHelpers::parseArgs($atts);

	$query = new WP_Query($aArgs);
	if ( !$query->have_posts() ){
		wp_reset_postdata();
		return '';
	}

	$wrap_class	= apply_filters( 'wilcity-el-class', $atts );
	$wrap_class = implode(' ', $wrap_class) . '  ' . $atts['extra_class'];
	$wrap_class .= ' wilcity-event-grid wilcity-grid';

?>
	<div class="<?php echo esc_attr($wrap_class); ?>">
		<div class="row">
			<?php
			if ( $query->have_posts() ){
				while($query->have_posts()){
					$query->the_post();
					wilcity_render_event_item($query->post, $atts);
				}
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
<?php
}