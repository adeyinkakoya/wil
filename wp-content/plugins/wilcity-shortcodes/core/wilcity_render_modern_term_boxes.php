<?php
function wilcity_sc_render_modern_term_boxes($atts){
	$atts = \WILCITY_SC\SCHelpers::mergeIsAppRenderingAttr($atts);
	$aArgs = array(
		'taxonomy'      => $atts['taxonomy'],
		'hide_empty'    => false
	);

	$aRawTermIDs = $atts[$atts['taxonomy'].'s'];

	if ( !empty($aRawTermIDs) ){
		$aRawTermIDs = is_array($aRawTermIDs) ? $aRawTermIDs : explode(',', $aRawTermIDs);
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

	$aTerms = get_terms($aArgs);

	if ( empty($aTerms) || is_wp_error($aTerms) ){
		$aArgs['number'] = count($aArgs['include']);
		unset($aArgs['include']);
		$aTerms = get_terms($aArgs);
		if ( empty($aTerms) || is_wp_error($aTerms) ){
			return '';
		}
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
        <div class="row" data-col-xs-gap="<?php echo esc_attr($atts['col_gap']); ?>">
			<?php
			foreach ($aTerms as $oTerm) {
				$boxClass = 'col-sm-6 col-md-6 ' . $atts['items_per_row'];
				wilcity_render_modern_term_box($oTerm, $atts['taxonomy'], $boxClass, $atts);
			} ?>

        </div>
    </div>
	<?php
}