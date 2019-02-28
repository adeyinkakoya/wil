<?php
global $wiloke;
$aFooterIDs = array('wilcity-first-footer', 'wilcity-second-footer', 'wilcity-third-footer', 'wilcity-four-footer');
switch ( $wiloke->aThemeOptions['footer_items'] ){
	case 2:
		$widgetClass = 'col-md-6 col-lg-6';
		break;
	case 3:
		$widgetClass = 'col-md-4 col-lg-4';
		break;
	default:
		$widgetClass = 'col-md-3 col-lg-3';
		break;
}

if ( !is_page_template('dashboard/index.php') && !is_page_template('templates/map.php') && ( wilcityHasCopyright() || wilcityIsHasFooterWidget() ) ) :
	?>
	<footer class="footer_module__1uDav">
		<?php if ( wilcityIsHasFooterWidget() ) : ?>
			<div class="footer_widgets__3FIuV">
				<div class="container">
					<div class="row">
						<?php for ( $i = 0; $i < abs($wiloke->aThemeOptions['footer_items']);  $i ++ ) : ?>
							<div class="<?php echo esc_attr($widgetClass); ?>">
								<?php
								if ( is_active_sidebar($aFooterIDs[$i]) ){
									dynamic_sidebar($aFooterIDs[$i]);
								}
								?>
							</div>
						<?php endfor; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( wilcityHasCopyright() ) : ?>
			<div class="footer_textWrap__Xc_Ht wil-text-center">
				<div class="footer_text__1FkcM"><?php Wiloke::ksesHTML($wiloke->aThemeOptions['copyright']); ?></div>
			</div>
		<?php endif; ?>
	</footer>
	<div class="wil-scroll-top">
		<a href="#" title="To top">
			<i class="la la-angle-up"></i>
		</a>
	</div>
<?php endif; ?>

<div id="wilcity-popup-area">
	<?php do_action('wilcity/before/close_wrapper') ?>
</div>

<div id="wilcity-wrapper-all-popup">
	<?php do_action('wilcity/footer/vue-popup-wrapper'); ?>
</div>