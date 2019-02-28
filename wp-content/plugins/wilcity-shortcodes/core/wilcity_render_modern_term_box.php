<?php
use WilokeListingTools\Framework\Helpers\GetSettings;

function wilcity_render_modern_term_box($oTerm, $taxonomy, $wrapperClass, $aAtts=array()){
    if ( isset($aAtts['image_size']) ){
        if ( strpos($aAtts['image_size'], ',') !== false ){
            $imgSize = explode(',', $aAtts['image_size']);
        }else{
	        $imgSize = $aAtts['image_size'];
        }
    }else{
        $imgSize = 'wilcity_560x300';
    }

	$leftBg = GetSettings::getTermMeta($oTerm->term_id, 'left_gradient_bg');
	$rightBg  = GetSettings::getTermMeta($oTerm->term_id, 'right_gradient_bg');
	$tiltedDegrees  = GetSettings::getTermMeta($oTerm->term_id, 'gradient_tilted_degrees');

	$leftBg = empty($leftBg) ? '#006bf7' : $leftBg;
	$rightBg = empty($rightBg) ? '#f06292' : $rightBg;
	$tiltedDegrees = empty($tiltedDegrees) ? 45 : $tiltedDegrees;
	?>
	<div class="<?php echo esc_attr($wrapperClass); ?>">
		<div class="image-box_module__G53mA">
			<a href="<?php echo esc_url(GetSettings::getTermLink($oTerm)); ?>">
				<header class="image-box_header__1bT-m">
					<div class="wil-overlay"></div>
                    <div class="wil-overlay" style="background-image: linear-gradient(<?php echo esc_attr($tiltedDegrees); ?>deg, <?php echo esc_attr($leftBg) ?> 0%, <?php echo esc_attr($rightBg) ?> 100%)"></div>

					<div class="image-box_img__mh3A- bg-cover" style="background-image: url('<?php echo esc_url(WilokeHelpers::getTermFeaturedImage($oTerm, $imgSize)); ?>')"><img src="<?php echo esc_url(WilokeHelpers::getTermFeaturedImage($oTerm, $imgSize)); ?>" alt="<?php esc_attr($oTerm->name); ?>"/></div>
				</header>
                <div class="image-box_body__Je8Uw">
                    <h2 class="image-box_title__1PnHo"><?php echo esc_html($oTerm->name); ?></h2><span class="image-box_text__1K_bA"><i class="la la-edit color-primary"></i>
                        <?php
                        if ( GetSettings::isTermParent($oTerm->term_id, $oTerm->taxonomy) ) {
                            $numberOfChildren = GetSettings::getNumberOfTermChildren($oTerm);
	                        echo sprintf( _n('%s Sub category', '%s Sub categories', $numberOfChildren, 'wilcity-shortcodes'), number_format_i18n( $numberOfChildren ) );
                        }else{
	                        echo sprintf( _n('%s Listing', '%s Listings', $oTerm->count, 'wilcity-shortcodes'), number_format_i18n( $oTerm->count ) );
                        }
                        ?>
                    </span>
                </div>
                <?php
                $aPostFeaturedImgs = GetSettings::getPostFeaturedImgsByTerm($oTerm->term_id, $taxonomy);
                if ( !empty($aPostFeaturedImgs) ) :
                ?>
                <div class="image-box_right__17b8t">
                    <?php if ( $oTerm->count > 4 ) : ?>
                        <div class="image-box_item__3T3KI"><span class="image-box_count__2ILGP bg-color-primary">+<?php echo esc_attr($oTerm->count); ?></span></div>
                    <?php endif; ?>
                    <?php foreach ($aPostFeaturedImgs as $featuredImg) : ?>
                    <div class="image-box_item__3T3KI">
                        <div class="image-box_logo__3NG5m bg-cover" style="background-image: url(<?php echo esc_url($featuredImg); ?>)"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
			</a>
		</div>
	</div>
	<?php
}