<?php
function wilcity_sc_render_term_boxes($atts){
	$atts = \WILCITY_SC\SCHelpers::mergeIsAppRenderingAttr($atts);

	$aArgs = array(
		'taxonomy'      => $atts['taxonomy'],
		'hide_empty'    => false
	);

	$aRawTermIDs = $atts[$atts['taxonomy'].'s'];
	if ( !empty($aRawTermIDs) ){
		$aRawTermIDs = explode(',', $aRawTermIDs);
		$aTerms = array();

		foreach ($aRawTermIDs as $rawTerm){
			$aParse = explode(':', $rawTerm);
			$aTerms[] = $aParse[0];
		}

		$aArgs['include'] = $aTerms;
	}else{
		$aArgs['orderby'] = $atts['orderby'];
		$aArgs['order'] = $atts['order'];
	}

	if ( isset($atts['number']) && !empty($atts['number']) ){
		$aArgs['number'] = $atts['number'];
    }

	$aTerms = get_terms($aArgs);

	if ( empty($aTerms) || is_wp_error($aTerms) ){
		return '';
	}

	if ( $atts['isApp'] ){
		$aResponse = array();
		foreach ($aTerms as $oTerm){
			$aPostFeaturedImgs = \WilokeListingTools\Framework\Helpers\GetSettings::getPostFeaturedImgsByTerm($oTerm->term_id, $atts['taxonomy']);

			$aResponse[] = array(
				'oTerm' => $oTerm,
				'aPostFeaturedImg' => $aPostFeaturedImgs,
				'oCount' => array(
					'number' => $oTerm->count,
					'text'   => $oTerm->count > 1 ? esc_html__('Listings', 'wilcity-shortcodes') : esc_html__('Listing', 'wilcity-shortcodes')
				),
				'oIcon' => WilokeHelpers::getTermOriginalIcon($oTerm)
			);
		}

		echo '%SC%' . json_encode(array(
				'oSettings' => $atts,
				'oResults'  => $aResponse,
				'TYPE'      => $atts['TYPE']
			)) . '%SC%';
		return '';
	}

	$wrapper_class = $atts['extra_class'] . ' wilcity-term-boxes';
	?>
	<div class="<?php echo esc_attr($wrapper_class); ?>">
		<div class="row">
			<?php
			foreach ($aTerms as $oTerm) {
				$boxClass = 'col-sm-6 col-md-6 ' . $atts['items_per_row'];
				wilcity_render_term_box($oTerm, $boxClass, $atts);
			} ?>

		</div>
	</div>
	<?php
}