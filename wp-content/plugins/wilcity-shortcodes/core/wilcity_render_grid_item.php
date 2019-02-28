<?php
use WilokeListingTools\Controllers\ReviewController;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Frontend\BusinessHours;
use WilokeListingTools\Models\ReviewMetaModel;
use WilokeListingTools\Models\UserModel;
use WILCITY_SC\SCHelpers;

function wilcity_render_grid_item($post, $aAtts){
    global $wiloke;
    if ( empty($wiloke) ){
	    $wiloke = Wiloke::getThemeOptions();
    }

	$aAtts['img_size'] = SCHelpers::parseImgSize($aAtts['img_size']);
    $imgUrl     = GetSettings::getFeaturedImg($post->ID, $aAtts['img_size']);
    $logo       = GetSettings::getLogo($post->ID);
	$itemClass  = '';

	if ( !isset($aAtts['isSlider']) ){
		$itemClass = $aAtts['maximum_posts_on_lg_screen'] . ' ' . $aAtts['maximum_posts_on_md_screen'] . ' ' . $aAtts['maximum_posts_on_sm_screen'];
		$itemClass = trim($itemClass);
    }

    if ( isset($aAtts['style']) && $aAtts['style'] == 'list' ){
	    $style = 'js-listing-list listing_module__2EnGq wil-shadow mb-30 js-listing-module';
	    $width = '100%';
    }else{
	    $style = 'listing_module__2EnGq wil-shadow mb-30 js-grid-item js-listing-module';
	    $width = '';
    }
	$style = apply_filters('wilcity/article-class', $style, $aAtts);

	$aAtts['TYPE'] = isset($aAtts['TYPE']) ? $aAtts['TYPE'] : '';

    ?>
    <?php if ( !isset($aAtts['isSlider']) ) : ?>
        <?php if ( empty($width) ) : ?>
            <div class="<?php echo esc_attr($itemClass); ?>">
        <?php else: ?>
            <div class="<?php echo esc_attr($itemClass); ?>" style="width: <?php echo esc_attr($width); ?>">
        <?php endif; ?>

    <?php endif; ?>
        <!-- listing_module__2EnGq wil-shadow -->
        <article class="<?php echo esc_attr($style); ?>">
            <div class="js-grid-item-body listing_firstWrap__36UOZ">
                <header class="listing_header__2pt4D">
	                <?php SCHelpers::renderAds($post, $aAtts['TYPE']); ?>
                    <a href="<?php echo get_permalink($post); ?>">
                        <div class="listing_img__3pwlB pos-a-full bg-cover" style="background-image: url(<?php echo esc_url($imgUrl); ?>);">
                            <img src="<?php echo esc_url($imgUrl); ?>" alt="<?php echo esc_attr($post->post_title); ?>">
                        </div>
	                    <?php SCHelpers::renderAverageReview($post); ?>
                    </a>
                </header>
                <div class="listing_body__31ndf">
                    <?php if ( !empty($logo) ) : ?>
                        <a class="listing_goo__3r7Tj" href="<?php echo get_permalink($post); ?>">
                            <div class="listing_logo__PIZwf bg-cover" style="background-image: url(<?php echo esc_url($logo); ?>);"></div>
                        </a>
                    <?php endif; ?>
	                <?php SCHelpers::renderTitle($post); ?>
	                <?php SCHelpers::renderExcerpt($post, $aAtts); ?>
                    <div class="listing_meta__6BbCG vertical">
                        <?php do_action('wilcity/listing-slider/meta-data', $post); ?>
                    </div>
                </div>
            </div>
            <footer class="js-grid-item-footer listing_footer__1PzMC">
                <div class="text-ellipsis">
                    <div class="icon-box-1_module__uyg5F two-text-ellipsis icon-box-1_style2__1EMOP">
                        <?php
                        SCHelpers::renderListingCat($post);
                        SCHelpers::renderBusinessStatus($post, array(), true);
                        ?>
                    </div>
                </div>
                <div class="listing_footerRight__2398w">
                    <?php
                    SCHelpers::renderGallery($post);
                    SCHelpers::renderFavorite($post);
                    ?>
                </div>
            </footer>

            <?php if ( isset($wiloke->aThemeOptions['isLazyload']) && $wiloke->aThemeOptions['isLazyload'] == 'yes' ) : ?>
            <div class="hidden listing-loading_module__2_Uwh wave-loading">
                <div class="shape-transparent">
                    <div class="shape shape--left"></div>
                    <div class="shape shape--right"></div>
                </div>
                <div class="shape shape--special">
                    <div class="wave-loading"></div>
                </div>
                <div class="shape shape--1"></div>
                <div class="shape shape--2"></div>
                <div class="shape shape--3"></div>
                <div class="shape shape--4"></div>
            </div>
            <?php endif; ?>
        </article>
	<?php if ( !isset($aAtts['isSlider']) ) : ?>
    </div>
	<?php endif; ?>
    <?php
}