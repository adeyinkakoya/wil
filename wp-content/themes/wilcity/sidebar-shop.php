<?php
global $wiloke, $wilcitySidebarWrapper;
if ( empty($wilcitySidebarWrapper) ){
	return '';
}

?>
<div class="<?php echo esc_attr($wilcitySidebarWrapper); ?>" style="margin-top: 26px;">
	<aside class="sidebar-1_module__1x2S9">
		<?php if ( is_active_sidebar('wilcity-woocommerce-sidebar') ){
			dynamic_sidebar('wilcity-woocommerce-sidebar');
		} ?>
	</aside>
</div>
