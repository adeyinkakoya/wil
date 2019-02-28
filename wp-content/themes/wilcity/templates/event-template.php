<?php
/*
 * Template Name: Wilcity Event Template
 */
use WilokeListingTools\Framework\Helpers\GetSettings;

get_header();
global $wiloke;
    if ( have_posts() ) : while ( have_posts() ) : the_post();
        $sidebarPos = GetSettings::getPostMeta($post->ID, 'sidebar');
	    $sidebarPos = empty($sidebarPos) ? 'right' : $sidebarPos;
	    switch ($sidebarPos){
            case 'right':
                $wrapperClass = 'col-md-8';
                $sidebarClass = 'col-md-4';
                break;
            case 'left':
	            $wrapperClass = 'col-md-8 col-md-push-4';
	            $sidebarClass = 'col-md-4 col-md-pull-8';
                break;
            default:
	            $wrapperClass = 'col-md-12';
	            $sidebarClass = '';
                break;
        }

        $aScreenSize['lg'] = GetSettings::getPostMeta($post->ID, 'maximum_posts_on_lg_screen');
        $aScreenSize['md'] = GetSettings::getPostMeta($post->ID, 'maximum_posts_on_md_screen');
        $aScreenSize['sm'] = GetSettings::getPostMeta($post->ID, 'maximum_posts_on_sm_screen');
        $postsPerPage = GetSettings::getPostMeta($post->ID, 'events_per_page');
?>
        <div id="wilcity-no-map" class="wil-content">
            <section class="wil-section bg-color-gray-2 pt-30">
                <div class="container">
                    <div class="row">
                        <div class="<?php echo esc_attr($wrapperClass); ?> js-sticky">
		                    <?php
		                        do_action('wilcity/render-search', array('type'=>'event', 'aItemsPerRow'=>$aScreenSize, 'postsPerPage'=>$postsPerPage));
		                    ?>
                        </div>
                        <?php if ( !empty($sidebarClass) ) : ?>
                        <div v-if="!isMobile" class="<?php echo esc_attr($sidebarClass); ?> js-sticky">
                            <wiloke-search-form v-on:searching="searching" type="event" is-map="no" posts-per-page="<?php echo esc_attr($wiloke->aThemeOptions['listing_posts_per_page']); ?>" raw-taxonomies="" s="" address="" raw-date-range="" lat-lng="" form-item-class="col-md-12" is-popup="no" raw-taxonomies-options=""></wiloke-search-form>
	                        <?php
	                        if ( is_active_sidebar('wilcity-sidebar-events') ){
		                        dynamic_sidebar('wilcity-sidebar-events');
	                        }
	                        ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
<?php
    endwhile; endif; wp_reset_postdata();
get_footer();