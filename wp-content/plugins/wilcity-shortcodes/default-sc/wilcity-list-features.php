<?php
function wilcityRenderListFeatureItem($aOption){
	?>
	<?php if ( empty($aOption['oIcon']['color']) ) : ?>
		<div class="icon-box-1_icon__3V5c0 rounded-circle">
			<i style="color: #fff" class="<?php echo esc_attr($aOption['oIcon']['icon']); ?>"></i>
		</div>
	<?php else: ?>
		<div class="icon-box-1_icon__3V5c0 rounded-circle" style="background-color: <?php echo esc_attr($aOption['oIcon']['color']); ?>">
			<i style="color: #fff" class="<?php echo esc_attr($aOption['oIcon']['icon']); ?>"></i>
		</div>
	<?php endif;
}

function wilcityListFeaturesSC($atts){
	$aAtts = shortcode_atts(
		array(
			'options'      => '',
			'wrapperClass' => 'col-sm-4  col-sm-4-clear'
		),
		$atts
	);

	if ( empty($aAtts['options']) ){
		return '';
	}

	$aOptions = json_decode($aAtts['options'], true);
	if ( empty($aOptions) ){
		return '';
	}
	?>
	<div class="row">
		<?php foreach ($aOptions as $aOption) : ?>
		<div class="<?php echo esc_attr($aAtts['wrapperClass']); ?>">
			<div class="icon-box-1_module__uyg5F three-text-ellipsis mt-20 mt-sm-15">
				<div class="icon-box-1_block1__bJ25J">
					<?php if ( !empty($aOption['oIcon']['icon']) ) : ?>
						<?php wilcityRenderListFeatureItem($aOption); ?>
					<?php endif; ?>
					<?php if ( !isset($aOption['unChecked']) || $aOption['unChecked'] == 'no' ) : ?>
						<div class="icon-box-1_text__3R39g"><?php echo esc_html($aOption['name']); ?></div>
					<?php else: ?>
						<div class="icon-box-1_text__3R39g" style="text-decoration: line-through"><?php echo esc_html($aOption['name']); ?></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php
}

add_shortcode('wilcity_list_features', 'wilcityListFeaturesSC');