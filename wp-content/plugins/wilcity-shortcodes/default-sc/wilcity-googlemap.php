<?php
use WilokeListingTools\Framework\Helpers\GetSettings;

add_shortcode('wilcity_sidebar_googlemap', 'wicitySidebarGoogleMap');
function wicitySidebarGoogleMap($aArgs){
	$aAtts = \WILCITY_SC\SCHelpers::decodeAtts($aArgs['atts']);
	$aAtts = wp_parse_args(
		$aAtts,
		array(
			'name'      => '',
			'icon'      => 'la la-map-marker'
		)
	);

	global $post;
	$aLatLng = GetSettings::getLatLng($post->ID);

	if ( empty($aLatLng) || ($aLatLng['lat'] ==  $aLatLng['lng'])  ){
		return '';
	}

	ob_start();
	?>
	<div class="content-box_module__333d9">
		<header class="content-box_header__xPnGx clearfix">
			<div class="wil-float-left">
				<h4 class="content-box_title__1gBHS"><i class="<?php echo esc_attr($aAtts['icon']); ?>"></i>
					<span><?php echo esc_html($aAtts['name']); ?></span>
				</h4>
			</div>
		</header>
		<div class="content-box_body__3tSRB">
			<div id="wilcity-sidebar-map" class="wilcity-single-map" data-latlng="<?php echo esc_attr($aLatLng['lat'] . ',' . $aLatLng['lng']); ?>"></div>
		</div>
	</div>
	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}