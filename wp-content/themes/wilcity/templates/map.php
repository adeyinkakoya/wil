<?php
/*
 * Template Name: Wilcity Map
 */

use WilokeListingTools\Controllers\SearchFormController;
use \WilokeListingTools\Framework\Helpers\GetSettings;

get_header();
    global $wiloke;
    $search = $latLng = $address = $taxonomy = $taxID = '';
    $type = 'listing';
    $aTaxonomies = array();
    $aDateRange = array();
    $aRequest = SearchFormController::parseRequestFromUrl();

    if ( is_tax() ){
        $taxSlug = get_query_var('term');
        $taxonomy = get_query_var('taxonomy');

        $taxID = get_queried_object_id();
        if ( $taxonomy == 'listing_cat' || $taxonomy == 'listing_tag' ){
            $aRequest[$taxonomy] = array($taxSlug);
            $aTaxonomies[$taxonomy] = array($taxSlug);
        }else{
            $aRequest[$taxonomy] = $taxSlug;
            $aTaxonomies[$taxonomy] = $taxSlug;
        }

        if ( isset($_REQUEST['type']) ){
            $type = $_REQUEST['type'];
            $aRequest['postType'] = $type;
        }else{
            if ( isset($taxID) ){
                $aBelongsTo = GetSettings::getTermMeta($taxID, 'belongs_to');
                if ( !empty($aBelongsTo) ){
                    $type = $aBelongsTo[0];
                    $aRequest['type'] = $type;
                }
            }
        }
        if ( isset($wiloke->aThemeOptions['taxonomy_image_size']) && !empty($wiloke->aThemeOptions['taxonomy_image_size']) ){
            $aRequest['img_size'] = $wiloke->aThemeOptions['taxonomy_image_size'];
        }

        $taxTitle = get_queried_object()->name;
    }else{
        if ( isset($aRequest['listing_cat']) ){
            $aTaxonomies['listing_cat'] = $aRequest['listing_cat'];
        }

        if ( isset($aRequest['listing_location']) ){
            $aTaxonomies['listing_location'] = $aRequest['listing_location'];
        }

        $imgSize = GetSettings::getPostMeta($post->ID, 'search_img_size');
        if ( !empty($imgSize) ){
            $aRequest['img_size'] = $imgSize;
        }
    }

    $aTaxonomiesOption = array();

    if ( !empty($aTaxonomies) ){
        foreach ($aTaxonomies as $tax => $rawSlug){
            $slug = is_array($rawSlug) ? $rawSlug[0] : $rawSlug;
            $oTermInfo = get_term_by('slug', $slug, $tax);
            if ( !empty($oTermInfo) && !is_wp_error($oTermInfo) ){
                $aTaxonomiesOption[$tax] = array(
                    array(
                        'name' => $oTermInfo->name,
                        'value'=> $slug
                    )
                );
            }
        }
    }
    if ( isset($aRequest['oAddress']) ){
        $address = $aRequest['oAddress']['address'];
        $latLng  = $aRequest['oAddress']['lat'] . ','.$aRequest['oAddress']['lng'];
    }

    if ( isset($aRequest['type']) ){
        $type = $aRequest['type'];
        $aRequest['postType'] = $type;
    }

    if ( !empty($aRequest['date_range']) ){
        $aDateRange = $aRequest['date_range'];
    }

    $search = isset($aRequest['title']) ? $aRequest['title'] : '';

    if ( isset($aRequest['title']) ){
        $aRequest['s'] = $aRequest['title'];
    }

    if ( !isset($aRequest['image_size']) ){
        $aRequest['image_size'] = '';
    }

    $aRequest = wp_parse_args(
        $aRequest,
        array(
            'postType' => 'listing',
            'image_size' => ''
        )
    );
?>
	<div class="wil-content">
		<section id="wilcity-map-wrapper" style="min-height: 500px;" class="wil-section bg-color-gray-1 pd-0">
            <div v-show="!isInitialized" class="full-load"><div class="pill-loading_module__3LZ6v pos-a-center"><div class="pill-loading_loader__3LOnT"></div></div></div>
			<div class="listing-map_left__1d9nh js-listing-map-content">
                <div class="listing-bar_module__2BCsi js-listing-bar-sticky">
                    <div class="container">
                        <div class="listing-bar_resuilt__R8pwY">
                            <span v-show="foundPosts!=0"><?php esc_html_e('Showing', 'wilcity'); ?> <span v-html="showingListingDesc"></span></span>
                            <a class="wil-btn wil-btn--border wil-btn--round wil-btn--xs" @click.prevent="resetSearchForm" href="#"><i class="color-primary la la-share"></i> <?php esc_html_e('Reset', 'wilcity'); ?>
                            </a>
                        </div>
                        <div class="listing-bar_layout__TK3vH">
                            <a class="listing-bar_item__266Xo js-grid-button color-primary" href="#" data-tooltip="<?php echo esc_attr__('Grid Layout', 'wilcity'); ?>" @click.prevent="switchLayoutTo('grid')" data-tooltip-placement="bottom"><i class="la la-th-large"></i></a>
                            <a class="listing-bar_item__266Xo js-list-button" href="#" @click.prevent="switchLayoutTo('list')" data-tooltip="<?php echo esc_attr__('List Layout', 'wilcity'); ?>" data-tooltip-placement="bottom"><i class="la la-list"></i></a><a class="listing-bar_item__266Xo js-map-button" href="#"><i class="la la-map-marker"></i><i class="la la-close"></i></a>
                            <a class="wil-btn js-listing-search-button wil-btn--primary wil-btn--round wil-btn--xs " href="#"><i class="la la-search"></i> <?php esc_html_e('Search', 'wilcity'); ?>
                            </a>
                            <a class="wil-btn js-listing-search-button-mobile wil-btn--primary wil-btn--round wil-btn--xs " href="#" @click.prevent="toggleSearchFormPopup"><i class="la la-search"></i> <?php esc_html_e('Search', 'wilcity'); ?>
                            </a>
                        </div>
                    </div>
                </div><!-- End / listing-bar_module__2BCsi -->
				<div v-if="!isMobile" class="content-box_module__333d9 content-box_lg__3v3a- listing-map_box__3QnVm mb-0 js-listing-search">
                    <wiloke-search-form v-on:searching="togglePreloader" type="<?php echo esc_attr($type); ?>" is-map="yes" posts-per-page="<?php echo esc_attr($wiloke->aThemeOptions['listing_posts_per_page']); ?>" raw-taxonomies='<?php echo esc_attr(json_encode($aTaxonomies)); ?>' s="<?php echo esc_attr($search); ?>" address="<?php echo esc_attr($address); ?>" raw-date-range='<?php echo esc_attr(json_encode($aDateRange)); ?>' lat-lng="<?php echo esc_attr($latLng); ?>" form-item-class="col-md-6 col-lg-6" is-popup="no" is-mobile="no" v-on:fetch-listings="triggerFetchListing" taxonomy="<?php echo esc_attr($taxonomy); ?>" image-size="<?php echo esc_attr($imgSize); ?>" raw-taxonomies-options="<?php echo esc_attr(json_encode($aTaxonomiesOption)); ?>"></wiloke-search-form>
				</div>

				<div class="content-box_module__333d9 content-box_lg__3v3a- listing-map_box__3QnVm bg-color-gray-2">
					<div class="content-box_body__3tSRB">
						<wiloke-listings posts-per-page="<?php echo esc_attr($wiloke->aThemeOptions['listing_posts_per_page']); ?>"></wiloke-listings>
					</div>
				</div>
			</div>
			<wiloke-map mode="multiple" map-id="wilcity-map" is-using-mapcluster="yes" grid-size="<?php echo esc_attr($gridSize); ?>" marker-svg="<?php echo esc_url(get_template_directory_uri() . "/assets/img/marker.svg"); ?>"></wiloke-map>
		</section>
	</div>
<?php
get_footer();
