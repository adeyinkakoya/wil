<?php

namespace WilokeListingTools\Controllers;


use Stripe\Util\Set;
use WILCITY_SC\SCHelpers;
use WilokeListingTools\AlterTable\AlterTableBusinessHourMeta;
use WilokeListingTools\AlterTable\AlterTableBusinessHours;
use WilokeListingTools\AlterTable\AlterTableEventsData;
use WilokeListingTools\AlterTable\AlterTableLatLng;
use WilokeListingTools\Framework\Helpers\FileSystem;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\MapHelpers;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Helpers\Time;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Frontend\BusinessHours;
use WilokeListingTools\Frontend\PriceRange;
use WilokeListingTools\Models\ReviewMetaModel;
use WilokeListingTools\Models\UserModel;

class SearchFormController extends Controller {
    public static $aSearchFormSettings;
    private $searchFormVersionKey = 'hero_search_form_version';
    private $searchFormsKey = 'hero_search_form_settings';
    private $isJoinedPostMeta = false;
    private $aCacheObjectTypes = array();
    protected static $aTermsPrinted = array();

	public function __construct() {
		add_filter('query_vars', array($this, 'customQuery'));
		add_action('wp_ajax_wilcity_get_search_fields', array($this, 'getSearchFields'));
		add_action('wp_ajax_nopriv_wilcity_get_search_fields', array($this, 'getSearchFields'));
		add_action('wilcity/render-search', array($this, 'renderSearchResults'));
		add_action('wp_ajax_wilcity_search_listings', array($this, 'searchListings'));
		add_action('wp_ajax_nopriv_wilcity_search_listings', array($this, 'searchListings'));

		add_action('wp_ajax_wilcity_get_json_listings', array($this, 'searchFormJson'));
		add_action('wp_ajax_nopriv_wilcity_get_json_listings', array($this, 'searchFormJson'));

		add_filter('posts_join', array($this, 'joinEvents'), 10, 2);
		add_filter('posts_join', array($this, 'preventListingsThatDoesNotHaveLatLng'), 10, 2);

//		add_filter('posts_fields', array($this, 'addSelectEventStartUTC'), 10, 2);
		add_filter('posts_where', array($this, 'addEventWhen'), 10, 2);
		add_filter('posts_where', array($this, 'addPreventListingsThatDoesNotHaveLatLng'), 10, 2);

		add_filter('posts_where', array($this, 'addEventBetweenDateRange'), 10, 2);
		add_filter('posts_fields', array($this, 'addEventSelection'), 10, 2);
		add_filter('posts_orderby', array($this, 'orderByEventDate'), 10, 2);
//		add_filter('posts_pre_query', array($this, 'checkEventQuery'), 10, 2);

        /* Latlng and map bound query  */
		add_filter('posts_join', array($this, 'joinLatLng'), 10, 2);

		add_filter('posts_pre_query', array($this, 'addHavingDistance'), 10, 2);
//		add_filter('posts_pre_query', array($this, 'preventListingsThatDoesNotHaveLatLng'), 10, 2);
//		add_filter('posts_clauses_request', array($this, 'replaceLatLngDistancePlace'), 10, 2);
		add_filter('posts_orderby', array($this, 'orderByDistance'), 10, 2);

		add_filter('posts_fields', array($this, 'addLatLngSelectionToQuery'), 10, 2);
		add_filter('posts_where', array($this, 'addMapBoundsToQuery'), 10, 2);
        /* End */

		add_filter('posts_join', array($this, 'joinOpenNow'), 50, 2);
		add_filter('posts_where' , array($this, 'addOpenNowToPostsWhere'), 50, 2);

		add_action('wp_ajax_wilcity_fetch_search_cache', array($this, 'getSearchCache'));
		add_action('wp_ajax_nopriv_wilcity_fetch_search_cache', array($this, 'getSearchCache'));

		add_action('wp_ajax_wilcity_search_by_ajax', array($this, 'ajaxSearch'));
		add_action('wp_ajax_nopriv_wilcity_search_by_ajax', array($this, 'ajaxSearch'));

		add_action('wp_ajax_wilcity_fetch_individual_cat_tags', array($this, 'fetchIndividualCatTags'));
		add_action('wp_ajax_nopriv_wilcity_fetch_individual_cat_tags', array($this, 'fetchIndividualCatTags'));

		add_action('wp_ajax_wilcity_get_listings_nearbyme', array($this, 'fetchListingsNearByMe'));
		add_action('wp_ajax_nopriv_wilcity_get_listings_nearbyme', array($this, 'fetchListingsNearByMe'));

		add_action('wp_ajax_wilcity_fetch_terms_suggestions', array($this, 'fetchTermsSuggestions'));
		add_action('wp_ajax_nopriv_wilcity_fetch_terms_suggestions', array($this, 'fetchTermsSuggestions'));

		add_action('wilcity/footer/vue-popup-wrapper', array($this, 'maSearchFormPopup'));

		add_action('wp_ajax_wilcity_fetch_hero_search_fields', array($this, 'fetchHeroSearchFields'));
		add_action('wp_ajax_nopriv_wilcity_fetch_hero_search_fields', array($this, 'fetchHeroSearchFields'));

		add_action('wp_ajax_wilcity_fetch_terms_options', array($this, 'fetchTermOptions'));
		add_action('wp_ajax_nopriv_wilcity_fetch_terms_options', array($this, 'fetchTermOptions'));
	    add_action('wilcity/footer/vue-popup-wrapper', array($this, 'printQuickSearchForm'));
	    add_action('wilcity/saved-hero-search-form', array($this, 'saveSearchFormVersion'), 10, 2);

		add_filter('terms_clauses', array($this, 'modifyTermsClauses'), 99999, 3);
	}

	public function modifyTermsClauses($clauses, $taxonomy, $aArgs){
		global $wpdb;
		if ( isset($aArgs['postTypes']) ){
			$postTypes = $aArgs['postTypes'];
		}else if ( isset($aArgs['post_types']) ){
			$postTypes = $aArgs['post_types'];
		}

		if ( !isset($postTypes) || empty($postTypes) ){
			return $clauses;
		}

		// allow for arrays
		if ( is_array($postTypes) ) {
			$postTypes = array_map(function($type){
			    global $wpdb;
				return $wpdb->_real_escape($type);
            }, $postTypes);
			$postTypes = implode("','", $postTypes);
		}else{
			$postTypes = $wpdb->_real_escape($postTypes);
        }

		$clauses['join'] .= " INNER JOIN $wpdb->term_relationships AS r ON r.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN $wpdb->posts AS p ON p.ID = r.object_id";
		$clauses['where'] .= " AND p.post_type IN ('". $postTypes. "') GROUP BY t.term_id";
		return $clauses;
	}

	public function saveSearchFormVersion($postType, $aFields){
        SetSettings::setOptions($this->searchFormVersionKey, current_time('timestamp'));
    }

	public function printQuickSearchForm(){
		$aQuickSearchForm = GetSettings::getOptions('quick_search_form_settings');
		if ( !empty($aQuickSearchForm) && ( isset($aQuickSearchForm['toggle_quick_search_form']) && $aQuickSearchForm['toggle_quick_search_form'] == 'no' ) ){
            return '';
        }

		$aTerms = GetSettings::getTerms(array(
            'taxonomy' => $aQuickSearchForm['taxonomy_suggestion'],
            'number'   => $aQuickSearchForm['number_of_term_suggestions'],
            'orderby'  => $aQuickSearchForm['suggestion_order_by'],
            'order'    => $aQuickSearchForm['suggestion_order']
        ));
	    ?>
        <div id="wilcity-quick-search-form-popup" class="search-screen_module__q_w-H pos-f-full">
            <div class="wilcity-close-popup search-screen_close__2OSie" data-popup-id="wilcity-quick-search-form-popup"><i class="la la-close"></i></div>
            <div class="container">
                <div class="search-screen_search__15VV1">
                    <input id="wilcity-quick-search-field-popup" type="text" placeholder="<?php esc_html_e('What are you looking for?', 'wiloke-listing-tools'); ?>" @keyup="searchResults" @paste="searchResults" v-model="s"/>
                </div>
            </div>
            <div class="search-screen_box__1jxsb wil-scroll-bar">
                <div class="container">
                    <block-loading position="pos-a-center" :is-loading="isSearching" wrapper-class="full-load bg-none"></block-loading>
                    <?php if ( !empty($aTerms) && !is_wp_error($aTerms) ) : ?>
                    <div v-show="!s.length && isSearching=='no'" id="wilcity-quick-search-form-tax-suggestion-wrapper">
                        <?php if ( isset($aQuickSearchForm['taxonomy_suggestion_title']) && !empty($aQuickSearchForm['taxonomy_suggestion_title']) ) : ?>
                            <h2 class="search-screen_title__oplSb"><?php echo esc_html($aQuickSearchForm['taxonomy_suggestion_title']); ?></h2>
                        <?php endif; ?>
                        <div class="row" data-col-xs-gap="10" data-col-sm-gap="20">
                            <?php
                            foreach ($aTerms as $oTerm) :
	                            $imgUrl = \WilokeHelpers::getTermFeaturedImage($oTerm, array(700, 350));
	                            $aBelongsTo = GetSettings::getTermMeta($oTerm->term_id, 'belongs_to');
	                            $termLink = get_term_link($oTerm->term_id);
	                            if ( !empty($aBelongsTo) ){
		                            $postType = end($aBelongsTo);
		                            $termLink = add_query_arg(
			                            array(
				                            'type' => $postType
			                            ),
			                            $termLink
		                            );
	                            }
                            ?>
                            <div class="col-sm-6 col-md-4 col-lg-4">
                                <!-- image-box_module__G53mA -->
                                <div class="image-box_module__G53mA">
                                    <a href="<?php echo esc_url($termLink); ?>">
                                        <header class="image-box_header__1bT-m">
                                            <div class="wil-overlay"></div>
                                            <div class="image-box_img__mh3A- bg-cover" style="background-image: url('<?php echo esc_url($imgUrl); ?>')">
                                                <img src="<?php echo esc_url($imgUrl); ?>" alt="<?php echo esc_attr($oTerm->name); ?>"/>
                                            </div>
                                        </header>
                                        <div class="image-box_body__Je8Uw">
                                            <h2 class="image-box_title__1PnHo"><?php echo esc_html($oTerm->name); ?></h2><span class="image-box_text__1K_bA"><i class="la la-edit color-primary"></i> <?php echo esc_attr($oTerm->count); ?> <?php esc_html_e('Listings', 'wiloke-listing-tools'); ?></span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div v-show="Object.values(oListings).length && isSearching=='no'">
                        <div class="row" v-for="oGroup in oListings">
                            <div class="col-md-12">
                                <h2 class="search-screen_title__oplSb" v-html="oGroup.groupTitle"></h2>
                            </div>
                            <div class="col-md-6 search-screen_item__1RUWk" v-for="oListing in oGroup.posts">
                                <article class="listing_module__2EnGq wil-shadow listing_list2__2An8C js-listing-module">
                                    <div class="listing_firstWrap__36UOZ">
                                        <header v-if="oListing.thumbnail" class="listing_header__2pt4D">
                                            <a :href="oListing.postLink">
                                                <div class="listing_img__3pwlB pos-a-full bg-cover" :style="{'background-image': 'url('+oListing.thumbnail+')'}"><img :src="oListing.thumbnail" :alt="oListing.postTitle"/></div>
                                            </a>
                                        </header>
                                        <div class="listing_body__31ndf">
                                            <h2 class="listing_title__2920A text-ellipsis"><a :href="oListing.postLink" v-html="oListing.postTitle"></a></h2>
                                            <div class="listing_tagline__1cOB3 text-ellipsis" v-html="oListing.tagLine"></div>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

	public static function parseRequestFromUrl(){
	    $aRequest = array();
	    if ( isset($_REQUEST['date_range']) && !empty($_REQUEST['date_range']) ){
            $dateRange = urldecode($_REQUEST['date_range']);
            $aDateRange = explode(',', $dateRange);
            if ( !empty($aDateRange[0]) && !empty($aDateRange[1]) ){
	            $aRequest['date_range'] = array(
                    'from' => $aDateRange[0],
                    'to' => $aDateRange[1]
                );
            }
        }

        if ( isset($_REQUEST['title']) && !empty($_REQUEST['title']) ){
            $aRequest['title'] = urldecode($_REQUEST['title']);
        }

		if ( isset($_REQUEST['type']) && !empty($_REQUEST['type']) ){
			$aRequest['type'] = urldecode($_REQUEST['type']);
		}else{
		    $aRequest['type'] = 'listing';
        }

		if ( isset($_REQUEST['latLng']) && !empty($_REQUEST['latLng']) ){
			$latLng = urldecode($_REQUEST['latLng']);
            $aParseLatLng = explode(',', $latLng);
			if ( !empty($aParseLatLng[0]) && !empty($aParseLatLng[1]) ){
				$aRequest['oAddress']['lat'] = $aParseLatLng[0];
				$aRequest['oAddress']['lng'] = $aParseLatLng[1];
				$aRequest['oAddress']['address'] = urldecode($_REQUEST['address']);
				$aRequest['oAddress']['radius']  = GetSettings::getSearchFormField($_REQUEST['type'], 'defaultRadius');
				$aRequest['oAddress']['unit']    = GetSettings::getSearchFormField($_REQUEST['type'], 'unit');
			}
		}

		if ( isset($_REQUEST['listing_cat']) && !empty($_REQUEST['listing_cat']) ){
			$listingCats = urldecode($_REQUEST['listing_cat']);
			$aRequest['listing_cat'] = explode(',', $listingCats);
		}

		if ( isset($_REQUEST['listing_location']) && !empty($_REQUEST['listing_location']) ){
			$aRequest['listing_location'] = urldecode($_REQUEST['listing_location']);
		}

		return $aRequest;
    }

    public static function isValidTerm($postType, $oTerm){
	    $aTermBelongsTo = GetSettings::getTermMeta($oTerm->term_id, 'belongs_to');
	    if ( in_array($oTerm->term_id, self::$aTermsPrinted) ){
	        return false;
        }

	    if ( empty($aTermBelongsTo) ){
	        return true;
        }

        return in_array($postType, $aTermBelongsTo);
    }

    public function buildTermItemInfo($oTerm){
	    $aTerm['value'] = $oTerm->slug;
	    $aTerm['name']  = $oTerm->name;
	    $aTerm['parent']= $oTerm->parent;
	    $aIcon = \WilokeHelpers::getTermOriginalIcon($oTerm);
	    if ( $aIcon ){
		    $aTerm['oIcon'] = $aIcon;
	    }else{
		    $featuredImgID = GetSettings::getTermMeta($oTerm->term_id, 'featured_image_id');
		    $featuredImg = wp_get_attachment_image_url($featuredImgID, array(32, 32));
		    $aTerm['oIcon'] = array(
			    'type' => 'image',
			    'url'  => $featuredImg
		    );
	    }
	    return $aTerm;
    }

	public function fetchTermOptions(){
        $at = abs($_POST['at']);
        $savedAt = GetSettings::getOptions('get_taxonomy_saved_at');

        if ( empty($savedAt) ){
            $savedAt = current_time('timestamp', 1);
            SetSettings::setOptions('get_taxonomy_saved_at', $savedAt);
        }

        if ( $at == $savedAt ){
            wp_send_json_success(array(
               'action' => 'used_cache'
            ));
        }

	    if ( isset($_POST['orderBy']) && !empty($_POST['orderBy']) ){
	        $orderBy = $_POST['orderBy'];
        }else{
		    $orderBy = 'count';
        }

		if ( isset($_POST['order']) && !empty($_POST['order']) ){
			$order = $_POST['order'];
		}else{
			$order = 'DESC';
		}

        $isShowParentOnly = isset($_POST['isShowParentOnly']) && $_POST['isShowParentOnly'] == 'yes';
		$aRawTerms = GetSettings::getTaxonomyHierarchy(array(
            'taxonomy' => $_POST['taxonomy'],
            'orderby'  => $orderBy,
            'order'    => $order,
            'parent'   => 0
        ), $_POST['postType'], $isShowParentOnly, true);

		if ( !$aRawTerms ){
			$aTerms = array(
				array(
					-1 => esc_html__('There are no terms', 'wiloke-listing-tools' )
                )
			);
		}else{
			$aTerms = array();
			foreach ($aRawTerms as $oTerm){
			    if ( isset($_POST['postType']) && !self::isValidTerm($_POST['postType'], $oTerm) ){
			        continue;
                }

				$aTerms[] = $this->buildTermItemInfo($oTerm);
			}
		}
		wp_send_json_success(array(
            'terms' => $aTerms,
            'action'=> 'update_new_terms',
            'at'    => $savedAt
        ));
    }

	public function fetchHeroSearchFields(){
	    if ( empty($_POST['postType']) ){
	        wp_send_json_error(array('msg'=>esc_html__('The Directory Type is required.', 'wiloke-listing-tools')));
        }

        $at = abs($_POST['at']);
        $savedAt = GetSettings::getOptions(General::heroSearchFormSavedAt($_POST['postType']));

        if ( empty($savedAt) ){
	        $savedAt = current_time('timestamp', 1);
	        SetSettings::setOptions(General::heroSearchFormSavedAt('heroSearchFormSavedAt'), $savedAt);
        }else{
	        $savedAt = abs($savedAt);
        }

	    if ( $at  == $savedAt ){
		    wp_send_json_success(array(
                'action' => 'use_cache'
            ));
        }

		$aFields = GetSettings::getOptions(General::getHeroSearchFieldsKey($_POST['postType']));
		$aFields = apply_filters('wilcity/filter/hero-search-form/fields', $aFields);

		if ( empty($aFields) ){
			wp_send_json_error(array(
				'msg' => esc_html__('Please go to Wiloke Tools -> Your Directory Type settings -> Add some fields to Hero Search Form', 'wiloke-listing-tools')
			));
		}

        wp_send_json_success(array(
            'oSettings' => $aFields,
            'at'        => $savedAt,
            'action'    => 'update_field'
        ));
    }

	public function maSearchFormPopup(){
	    if ( !wilcityIsMapPage() && !is_tax('listing_cat') && !is_tax('listing_location') && !is_tax('listing_tag') && !wilcityIsNoMapTemplate() ){
            return '';
        }

		global $wiloke, $post;
		$latLng = $address = $taxonomy = $taxID = '';
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

		$isMap = wilcityIsMapPage() ? 'yes' : 'no';

        ?>
        <wiloke-search-form-popup type="<?php echo esc_attr($type); ?>" is-map="<?php echo esc_attr($isMap); ?>" posts-per-page="<?php echo esc_attr($wiloke->aThemeOptions['listing_posts_per_page']); ?>" raw-taxonomies='<?php echo esc_attr(json_encode($aTaxonomies)); ?>' s="<?php echo esc_attr($search); ?>" address="<?php echo esc_attr($address); ?>" lat-lng="<?php echo esc_attr($latLng); ?>" raw-date-range='<?php echo esc_attr(json_encode($aDateRange)); ?>' form-item-class="col-md-6 col-lg-6" popup-title="<?php esc_html_e('Search', 'wiloke-listing-tools'); ?>"  image-size="<?php echo esc_attr($aRequest['image_size']); ?>" raw-taxonomies-options="<?php echo esc_attr(json_encode($aTaxonomiesOption)); ?>"></wiloke-search-form-popup>
        <?php
    }

	public function fetchIndividualCatTags(){
	    if ( !isset($_POST['termSlug']) || empty($_POST['termSlug']) ){
	        wp_send_json_error();
        }else{
	        $oTerm = get_term_by('slug', $_POST['termSlug'], 'listing_cat');
	        $aTagSlugs = GetSettings::getTermMeta($oTerm->term_id, 'tags_belong_to');
	        if ( empty($aTagSlugs) ){
	            wp_send_json_error();
            }

		    $aTags = array();
            foreach ($aTagSlugs as $slug){
	            $oTerm = get_term_by('slug', $slug, 'listing_tag');
	            $aTags[] = array(
                    'value' => $oTerm->slug,
                    'name'  => $oTerm->name,
                    'label' => $oTerm->name
                );
            }

            wp_send_json_success($aTags);
        }
    }

    public static function getSearchFormSettings(){
	    if ( empty(self::$aSearchFormSettings) ){
		    self::$aSearchFormSettings = GetSettings::getOptions('quick_search_form_settings');
        }
        return self::$aSearchFormSettings;
    }

    public function fetchTermsSuggestions(){
        self::getSearchFormSettings();
	    $aRawCategories = GetSettings::getTerms(array(
            'taxonomy'   => self::$aSearchFormSettings['taxonomy_suggestion'],
            'number'     => self::$aSearchFormSettings['number_of_term_suggestions'],
            'orderby'    => self::$aSearchFormSettings['suggestion_order_by'],
            'hide_empty' => false
        ));

	    if ( !empty($aRawCategories) && !is_wp_error($aRawCategories) ){
		    $aCategories = array();
		    foreach ($aRawCategories as $oRawCategory){
			    $aCategories[] = array(
				    'name' => $oRawCategory->name,
				    'slug' => $oRawCategory->slug,
				    'id'   => $oRawCategory->term_id,
				    'link' => get_term_link($oRawCategory),
				    'oIcon'=> \WilokeHelpers::getTermOriginalIcon($oRawCategory)
			    );
		    }

		    wp_send_json_success(array(
			    'aResults'   => $aCategories
		    ));
	    }

	    wp_send_json_error();
    }

    private function setupListingQuickSearchFromSkeleton($post, $oPostTypeObject, $defaultFeaturedImg){
        $postThumbnail = get_the_post_thumbnail_url($post->ID, 'thumbnail');
	    $aSinglePost = array(
		    'postTitle' => get_the_title($post->ID),
		    'postType'  => $post->post_type,
		    'postLink'  => get_post_permalink($post->ID),
		    'thumbnail' => empty($postThumbnail) ? $defaultFeaturedImg : $postThumbnail,
		    'thumbnailLarge' => get_the_post_thumbnail_url($post->ID, 'large'),
		    'logo'      => GetSettings::getLogo($post->ID, 'thumbnail'),
		    'name'      => $oPostTypeObject->labels->name,
		    'singularName'      => $oPostTypeObject->labels->singular_name,
		    'tagLine'    => GetSettings::getTagLine($post)
	    );

	    $oTerm = GetSettings::getLastPostTerm($post->ID, 'listing_cat');
	    if ( $oTerm ){
		    $aSinglePost['oIcon'] = \WilokeHelpers::getTermOriginalIcon($oTerm);
	    }else{
		    $aSinglePost['oIcon'] = false;
	    }
	    return $aSinglePost;
    }

	public function ajaxSearch(){
		$aPostTypes = General::getPostTypeKeys(false);
		$postTypes = implode("','", $aPostTypes);

		global $wpdb;
		$keyword = esc_sql($_POST['s']);
		$aResults = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $wpdb->posts WHERE post_title LIKE %s AND post_status='publish' AND post_type IN ('".$postTypes."') ORDER BY IF(menu_order!=0, menu_order, post_date) LIMIT 20 ",
				'%'.$keyword.'%'
            )
		);

		$total = 0;
		$aPosts = array();

		$aOptions = \Wiloke::getThemeOptions(true);
		$defaultFeaturedImg = '';
		if ( isset($aOptions['listing_featured_image']) && isset($aOptions['listing_featured_image']['id']) && !empty($aOptions['listing_featured_image']['id']) ){
			$defaultFeaturedImg = wp_get_attachment_image_url($aOptions['listing_featured_image']['id'], 'thumbnail');
		}

		$aExcludes = array();
		if ( !empty($aResults) && !is_wp_error($aResults) ){
			$total = count($aResults);
			foreach ($aResults as $oResult){
				if ( isset($this->aCacheObjectTypes[$oResult->post_type]) && !empty($this->aCacheObjectTypes[$oResult->post_type]) ){
					$oPostTypeObject = $this->aCacheObjectTypes[$oResult->post_type];
				}else{
					$oPostTypeObject = get_post_type_object( $oResult->post_type );
					$this->aCacheObjectTypes[$oResult->post_type] = $oPostTypeObject;
				}

				if ( !isset($aPosts[$oResult->post_type]) ){
					$aPosts[$oResult->post_type] = array();
					$aPosts[$oResult->post_type]['groupTitle'] = $oPostTypeObject->labels->name;
					$aPosts[$oResult->post_type]['posts'] = array();
				}

				$aPosts[$oResult->post_type]['posts'][] = $this->setupListingQuickSearchFromSkeleton($oResult,$oPostTypeObject, $defaultFeaturedImg);
				$aExcludes[] = $oResult->ID;
			}
		}

		if ( $total == 20 ){
			wp_send_json_success(array(
				'type' => 'postTitle',
				'aResults' => $aPosts
			));
        }
		if ( $total < 20 ){
			$aArgs = array(
			    'post_type'     => $aPostTypes,
                'post_status'   => 'publish',
                'posts_per_page' => 20 - $total,
                'orderby'       => 'menu_order post_date'
            );
			if ( !empty($aExcludes) ){
			    $aArgs['post__not_in'] = $aExcludes;
            }

            $aRawCategories = GetSettings::getTerms(
                array(
                    'taxonomy'      => 'listing_tag',
                    'hide_empty'    => true,
                    'name__like'    => $_POST['s'],
                    'number'        => 20
                )
            );

            if ( !empty($aRawCategories) && !is_wp_error($aRawCategories) ){
                $aCategories = array();
                foreach ($aRawCategories as $oRawCategory){
                    $aCategories[] = $oRawCategory->term_id;
                }
                $aArgs['tax_query'] = array(
                    array(
                        'taxonomy'  => 'listing_tag',
                        'field'     => 'term_id',
                        'terms'     => $aCategories
                    )
                );
	            $query = new \WP_Query($aArgs);

	            if ( $query->have_posts() ){
		            global $post;
		            while($query->have_posts()){
			            $query->the_post();
			            if ( isset($this->aCacheObjectTypes[$post->post_type]) && !empty($this->aCacheObjectTypes[$post->post_type]) ){
				            $oPostTypeObject = $this->aCacheObjectTypes[$post->post_type];
			            }else{
				            $oPostTypeObject = get_post_type_object( $post->post_type );
				            $this->aCacheObjectTypes[$post->post_type] = $oPostTypeObject;
			            }

			            if ( !isset($aPosts[$post->post_type]) ){
				            $aPosts[$post->post_type] = array();
				            $aPosts[$post->post_type]['groupTitle'] = $oPostTypeObject->labels->name;
				            $aPosts[$post->post_type]['posts'] = array();
			            }

			            $aPosts[$post->post_type]['posts'][] = $this->setupListingQuickSearchFromSkeleton($post, $oPostTypeObject, $defaultFeaturedImg);
		            }
	            }
            }
        }

        if ( empty($aPosts) ){
		    wp_send_json_error();
        }

        wp_send_json_success(array(
            'type' => 'postTitle',
            'aResults' => $aPosts
        ));
    }

	public function getSearchCache(){
	    if ( !FileSystem::isFileExists('search-posts-caching.txt') ){
	        wp_send_json_error();
        }

        $postCache = FileSystem::fileGetContents('search-posts-caching.txt');
        $termCache = FileSystem::fileGetContents('search-terms-caching.txt');

	    wp_send_json_success(array(
            'posts' => json_decode($postCache, true),
            'terms' => json_decode($termCache, true)
        ));
    }

    private function isQueryEvent($that){
	    return $that->query_vars['post_type'] == 'event' && isset($that->query_vars['date_range']) && !empty($that->query_vars['date_range']);
    }

	private function orderByEventConditional($that){
		return !isset($that->query_vars['orderby']) || empty($that->query_vars['orderby']) || (!is_array($that->query_vars['orderby']) && strpos($that->query_vars['orderby'], '_event') === false) ? false : true;
	}

	public function customQuery($aVars){
		$aVars[] = 'geocode';
		$aVars[] = 'map_bounds';
		$aVars[] = 'latLng';
		$aVars[] = 'unit';
		$aVars[] = 'open_now';
		$aVars[] = 'date_range';
		$aVars[] = 'is_map';
		return $aVars;
    }

    public function checkEventQuery($x, $that){
	    if ( $this->orderByEventConditional($that) ){
	        var_export($that->request);die();
        }
    }

	public function addHavingDistance($nothing, $that){
		if ( isset($that->query_vars['geocode']) && !empty($that->query_vars['geocode']) ){
			global $wpdb;
			$radius = $wpdb->_real_escape($that->query_vars['geocode']['radius']);
		    $that->request = str_replace('ORDER BY', 'HAVING wiloke_distance < '. $radius . ' ORDER BY', $that->request);
		}
		return $nothing;
	}

	public function orderByDistance($orderBy, $that){
		if ( isset($that->query_vars['geocode']) && !empty($that->query_vars['geocode']) ){
			return  'wiloke_distance';
		}
        return $orderBy;
	}

	public function addEventSelection($field, $that){
		if ( $this->orderByEventConditional($that) || $this->isQueryEvent($that) ){
			global $wpdb;
			$eventDataTbl = $wpdb->prefix . AlterTableEventsData::$tblName;
			$field .= ", $eventDataTbl.startsOn as wilcity_event_starts_on, $eventDataTbl.endsOn, $eventDataTbl.frequency";

		}
		return $field;
    }

    public function addMapBoundsToQuery($where, $that){
	    if ( isset($that->query_vars['map_bounds']) && !empty($that->query_vars['map_bounds']) ){
		    global $wpdb;
		    $latLngTbl = $wpdb->prefix . AlterTableLatLng::$tblName;
		    $additional = " AND $latLngTbl.lat BETWEEN " . $wpdb->_real_escape($that->query_vars['map_bounds']['aFLatLng']['lat']) . " AND " . $wpdb->_real_escape($that->query_vars['map_bounds']['aSLatLng']['lat']) . " AND $latLngTbl.lng BETWEEN " . $wpdb->_real_escape($that->query_vars['map_bounds']['aFLatLng']['lng']) . " AND " .$wpdb->_real_escape($that->query_vars['map_bounds']['aSLatLng']['lng']);
		    $where .= $additional;
	    }
	    return $where;
    }

	public function addLatLngSelectionToQuery($field, $that){
	    if ( isset($that->query_vars['geocode']) && !empty($that->query_vars['geocode']) ){
	        global $wpdb;
		    $latLngTbl = $wpdb->prefix . AlterTableLatLng::$tblName;

		    $unit = $wpdb->_real_escape($that->query_vars['geocode']['unit']);
		    $aParseLatLng = explode(',', $that->query_vars['geocode']['latLng']);
		    $unit = $unit == 'km' ? 6371 : 3959;
		    $lat = $wpdb->_real_escape(trim($aParseLatLng[0]));
		    $lng = $wpdb->_real_escape(trim($aParseLatLng[1]));

		    $field .= ",( $unit * acos( cos( radians('".$lat."') ) * cos( radians( $latLngTbl.lat ) ) * cos( radians( $latLngTbl.lng ) - radians('".$lng."') ) + sin( radians('".$lat."') ) * sin( radians( $latLngTbl.lat ) ) ) ) as wiloke_distance";
	    }

        return $field;
    }

    public function addEventBetweenDateRange($where, $that){
	    if ( !$this->isQueryEvent($that) ){
		    return $where;
	    }
	    global $wpdb;

	    $eventTbl = $wpdb->prefix . AlterTableEventsData::$tblName;

	    $aDataRange = $that->query_vars['date_range'];
	    $from = Time::mysqlDateTime(strtotime($wpdb->_real_escape($aDataRange['from'])));
	    $to   = Time::mysqlDateTime(strtotime($wpdb->_real_escape($aDataRange['to'])));

	    $where .= " AND ($eventTbl.startsOn <= $eventTbl.endsOn) AND ( 
            ($eventTbl.startsOn >= '".$from."' AND $eventTbl.startsOn <= '".$to."') 
            || (
                $eventTbl.startsOn < '".$from."' 
                AND 
                ($eventTbl.endsOn >= '".$from."' OR $eventTbl.endsOn >= '".$to."')
            )
        )";
	    return $where;
    }

    private function isPassedDateRange($aArgs){
	    if ( isset($aArgs['date_range']) ){
		    if ( empty($aArgs['date_range']['from']) || empty($aArgs['date_range']['to']) || (strtotime($aArgs['date_range']['from']) >  strtotime($aArgs['date_range']['to']))  ){
			    return false;
		    }
	    }
	    return true;
    }

    public function addEventWhen($where, $that){
	    if ( !$this->orderByEventConditional($that) ){
	        return $where;
	    }
	    global $wpdb;

	    $eventTbl = $wpdb->prefix . AlterTableEventsData::$tblName;

	    $now = Time::mysqlDateTime(current_time('timestamp', true));
        if ( $that->query_vars['orderby'] == 'upcoming_event' ){
            $where .= " AND $eventTbl.startsOnUTC > '".$now."'";
        }else if ($that->query_vars['orderby'] == 'ongoing_event'){
	        $where .= " AND $eventTbl.startsOnUTC <= '".$now."' AND $eventTbl.endsOnUTC >= '".$now."'";
        }else if ( $that->query_vars['orderby'] == 'expired_event' ){
	        $where .= " AND ($eventTbl.endsOnUTC <= '".$now."' || $eventTbl.endsOnUTC <= $eventTbl.startsOnUTC)";
        }

        return $where;
    }

    public function addPreventListingsThatDoesNotHaveLatLng($where, $that){
	    global $wpdb;
	    $latLngTbl = $wpdb->prefix . AlterTableLatLng::$tblName;

	    if ( isset($that->query_vars['is_map']) && $that->query_vars['is_map'] == 'yes' ){
		    $where .= " AND ($latLngTbl.lat != '' AND $latLngTbl.lng != '' AND $latLngTbl.lat != $latLngTbl.lng) ";
	    }
	    return $where;
    }

	public function orderByEventDate($orderBy, $that){
		if ( !$this->orderByEventConditional($that) && !$this->isQueryEvent($that) ){
		    return $orderBy;
		}

		return  "wilcity_event_starts_on ASC";
	}

	public function joinEvents($join, $that){
		global $wpdb;
		if ( !$this->orderByEventConditional($that) && !$this->isQueryEvent($that) ){
			return $join;
		}

		$eventsDataTbl = $wpdb->prefix . AlterTableEventsData::$tblName;

		$join .= " LEFT JOIN $eventsDataTbl ON ($eventsDataTbl.objectID = $wpdb->posts.ID)";
		return $join;
	}

    public function preventListingsThatDoesNotHaveLatLng($join, $that){
	    global $wpdb;
	    $latLngTbl = $wpdb->prefix . AlterTableLatLng::$tblName;
		if ( isset($that->query_vars['is_map']) && $that->query_vars['is_map'] == 'yes' ){
			$joinLatLng = " LEFT JOIN $latLngTbl ON ($latLngTbl.objectID = $wpdb->posts.ID)";
			if ( strpos($join, $joinLatLng) === false ){
			    $join .= " " . $joinLatLng;
            }
		}
		return $join;
    }

	public function addSelectEventStartUTC(){

    }

	public function joinLatLng($join, $that){
		global $wpdb;
		if ( ( (isset($that->query_vars['geocode']) && !empty($that->query_vars['geocode']) ) || ( isset($that->query_vars['map_bounds']) && !empty($that->query_vars['map_bounds']) ) ) ){
			$latLngTbl = $wpdb->prefix . AlterTableLatLng::$tblName;
			$joinLatLng = " LEFT JOIN $latLngTbl ON ($latLngTbl.objectID = $wpdb->posts.ID)";
			if ( strpos($join, $joinLatLng) === false ){
				$join .= $joinLatLng;
			}

        }
        return $join;
    }

	public function joinOpenNow($join, $that){
		global $wpdb;
		if ( !isset($that->query_vars['open_now']) || empty($that->query_vars['open_now']) || $that->query_vars['open_now'] == 'no' ){
			return $join;
		}

		$businessHourTbl = $wpdb->prefix . AlterTableBusinessHours::$tblName;
		$bhMeta = $wpdb->prefix . AlterTableBusinessHourMeta::$tblName;

		$join .= " LEFT JOIN $businessHourTbl ON ($businessHourTbl.objectID=$wpdb->posts.ID) LEFT JOIN $bhMeta ON ($bhMeta.objectID=$wpdb->posts.ID) ";
		return $join;
	}

	public function addOpenNowToPostsWhere($where, $that){
		if ( !isset($that->query_vars['open_now']) || empty($that->query_vars['open_now']) || $that->query_vars['open_now'] == 'no' ){
			return $where;
		}
		global $wpdb;

		$businessHourTbl    = $wpdb->prefix.AlterTableBusinessHours::$tblName;
		$businessHourMeta   = $wpdb->prefix.AlterTableBusinessHourMeta::$tblName;

		date_default_timezone_set('UTC');
		$utcTimestampNow    = \time();
		$todayIndex         = date('N', $utcTimestampNow);
		$dayKey             = Time::getDayKey($todayIndex-1);
		$utcHourNow         = date('H:i:s', $utcTimestampNow);
		$where .= " AND ( ($businessHourMeta.meta_key = 'wilcity_hourMode') AND ( ($businessHourMeta.meta_value = 'always_open')  OR ( ($businessHourMeta.meta_value = 'open_for_selected_hours') AND ($businessHourTbl.dayOfWeek='".$wpdb->_real_escape($dayKey)."' AND $businessHourTbl.isOpen='yes' AND
		(
		    ($businessHourTbl.firstOpenHourUTC <= '".$utcHourNow."' AND '".$utcHourNow."' <= $businessHourTbl.firstCloseHourUTC)
		    OR
		    ($businessHourTbl.firstOpenHourUTC <= '".$utcHourNow."' AND '".$utcHourNow."' >= $businessHourTbl.firstCloseHourUTC AND $businessHourTbl.firstCloseHourUTC < $businessHourTbl.firstOpenHourUTC)
		    OR
		    (
		        ($businessHourTbl.secondOpenHourUTC IS NOT NULL AND $businessHourTbl.secondCloseHourUTC IS NOT NULL)
		        AND
		        (
		            ($businessHourTbl.secondOpenHourUTC <= '".$utcHourNow."' AND $businessHourTbl.secondCloseHourUTC >= '".$utcHourNow."')
		            OR
		            ($businessHourTbl.secondOpenHourUTC <= '".$utcHourNow."' AND '".$utcHourNow."' >= $businessHourTbl.secondCloseHourUTC AND $businessHourTbl.secondCloseHourUTC < $businessHourTbl.secondOpenHourUTC)
		        )
            )
		))) )) ";
		return $where;
	}

    public static function buildQueryArgs($aRequest){
	    if ( empty($aRequest['postType']) ){
		    $aRequest['postType'] = General::getFirstPostTypeKey(false, false);
        }
	    $aArgs = array(
		    'post_type'     => $aRequest['postType'],
		    'post_status'   => 'publish',
            'orderby'       => isset($aRequest['orderby']) ? $aRequest['orderby'] : 'menu_order post_date',
	        'is_map'        => isset($aRequest['is_map']) ? $aRequest['is_map'] : 'no',
            'order'         => isset($aRequest['order']) ? $aRequest['order'] : 'DESC'
        );

	    if ( isset($aRequest['aBounds']) && !empty($aRequest['aBounds']) ){
	        unset($aRequest['oAddress']);
		    $aArgs['map_bounds'] = $aRequest['aBounds'];
        }

	    if ( isset($aRequest['oAddress']) && !empty($aRequest['oAddress']) && !empty($aRequest['oAddress']['lat']) ){
	        $aArgs['order'] = 'ASC';

		    $aArgs['geocode'] = array(
			    'latLng' => $aRequest['oAddress']['lat'].','.$aRequest['oAddress']['lng'],
			    'radius' => isset($aRequest['oAddress']['radius']) ? $aRequest['oAddress']['radius'] : GetSettings::getSearchFormField($aRequest['postType'],
                    'defaultRadius'),
			    'unit'   => isset($aRequest['oAddress']['unit']) ? $aRequest['oAddress']['unit'] : GetSettings::getSearchFormField($aRequest['postType'],
				    'unit')
		    );
	    }

	    if ( isset($aRequest['open_now']) && !empty($aRequest['open_now']) && $aRequest['open_now'] !== 'no' ){
		    $aArgs['open_now'] = $aRequest['open_now'];
	    }

	    if ( isset($aRequest['date_range']) ){
		    $aArgs['date_range'] = $aRequest['date_range'];
	    }

	    if ( isset($aRequest['listing_location']) && !empty($aRequest['listing_location']) && $aRequest['listing_location'] != -1 ){
	        $aLocation = array(
		        'taxonomy' => 'listing_location',
		        'field'    => is_numeric($aRequest['listing_location']) ? 'term_id' : 'slug',
		        'terms'    => $aRequest['listing_location']
	        );

	        if ( is_array($aRequest['listing_location']) && count($aRequest['listing_location']) > 1 ){
	            $aLocation['operator'] = 'AND';
            }

		    $aArgs['tax_query'][] = $aLocation;
	    }

	    if ( isset($aRequest['listing_cat']) && !empty($aRequest['listing_cat']) && $aRequest['listing_cat'] != -1 ){
		    $aCat = array(
			    'taxonomy' => 'listing_cat',
			    'field'    => is_numeric($aRequest['listing_cat']) ? 'term_id' : 'slug',
			    'terms'    => $aRequest['listing_cat']
		    );

		    if ( is_array($aRequest['listing_cat']) && count($aRequest['listing_cat']) > 1 ){
			    $aCat['operator'] = 'AND';
		    }

		    $aArgs['tax_query'][] = $aCat;
	    }

	    if ( isset($aRequest['listing_tag']) && !empty($aRequest['listing_tag']) ){
	        if ( is_array($aRequest['listing_tag']) ){
	            $field = is_numeric($aRequest['listing_tag'][0]) ? 'term_id' : 'slug';
            }else{
		        $field = is_numeric($aRequest['listing_tag']) ? 'term_id' : 'slug';
            }

		    $aTags = array(
			    'taxonomy' => 'listing_tag',
			    'field'    => $field,
			    'terms'    => $aRequest['listing_tag']
		    );

		    if ( is_array($aRequest['listing_tag']) && count($aRequest['listing_tag']) > 1 ){
			    $aTags['operator'] = 'AND';
		    }

		    $aArgs['tax_query'][] = $aTags;
	    }

	    if ( isset($aArgs['tax_query']) && count($aArgs['tax_query']) > 1 ){
		    $aArgs['tax_query']['relation'] = 'AND';
	    }

	    if ( isset($aRequest['best_rated']) && $aRequest['best_rated'] == 'yes' ){
		    $aArgs['orderby']   = 'meta_value_num';
		    $aArgs['meta_key']  = 'wilcity_average_reviews';
		    $aArgs['order']     = 'DESC';
	    }

	    if ( isset($aRequest['price_range']) && !empty($aRequest['price_range']) && $aRequest['price_range'] !== 'nottosay'  ){
		    $aArgs['meta_query'][] = array(
			    array(
				    'key'     => 'wilcity_price_range',
				    'value'   => $aRequest['price_range'],
				    'compare' => '='
			    )
            );
	    }

	    if ( isset($aRequest['s']) && !empty($aRequest['s']) ){
		    $aArgs['s'] = $aRequest['s'];
	    }

	    if ( isset($aArgs['meta_query']) && count($aArgs['meta_query']) ){
		    $aArgs['meta_query']['relation'] = 'AND';
	    }

	    $aArgs['posts_per_page'] = isset($aRequest['postsPerPage']) && !empty($aRequest['postsPerPage']) ? absint($aRequest['postsPerPage']) : get_option('posts_per_page');
	    $aArgs['posts_per_page'] = $aArgs['posts_per_page'] > 200 ? 200 : $aArgs['posts_per_page'];

	    if ( isset($aRequest['page']) && !empty($aRequest['page']) ){
		    $aArgs['paged'] = abs($aRequest['page']);
	    }
	    return $aArgs;
    }

    public static function jsonSkeleton($post, $aAtts){
        global $wiloke;
	    $aThemeOptions = \Wiloke::getThemeOptions();

	    $aListing = array();

	    $aListing['postID']  = $post->ID;
	    $aListing['postTitle'] = get_the_title($post->ID);
	    $aListing['permalink'] = get_permalink($post->ID);

	    $aListing['logo'] = GetSettings::getPostMeta($post->ID, 'logo');
	    $tagLine = GetSettings::getPostMeta($post->ID, 'tagline');

	     if ( empty($tagLine) ){
		    $aListing['excerpt'] = \Wiloke::contentLimit($aThemeOptions['listing_excerpt_length'], $post, true, $post->post_content, true);
		     $aListing['excerpt'] = strip_shortcodes($aListing['excerpt']);
	    }else{
		    $aListing['excerpt'] = $tagLine;
	    }
	    $aListing['featuredImage'] = get_the_post_thumbnail_url($post->ID, $aAtts['img_size']);

	    if ( empty($aListing['featuredImage']) ){
	        if ( isset($aThemeOptions['listing_featured_image']) && isset($aThemeOptions['listing_featured_image']['id']) && !empty($aThemeOptions['listing_featured_image']['id']) ){
		        $aListing['featuredImage'] = wp_get_attachment_image_url($aThemeOptions['listing_featured_image']['id'], $aAtts['img_size']);
            }
        }

	    if ( ReviewController::isEnableRating() ){
		    $averageReview = GetSettings::getPostMeta($post->ID, 'average_reviews');
		    if ( empty($averageReview) ){
			    $aListing['oReviews'] = false;
		    }else{
			    $aListing['oReviews'] = array();
			    $aListing['oReviews']['average'] = $averageReview;
			    $aListing['oReviews']['mode'] = ReviewController::getMode($post->post_type);
			    $aListing['oReviews']['quality'] = ReviewMetaModel::getReviewQualityString($averageReview, $post->post_type);
		    }
	    }else{
	        $aListing['oReviews'] = false;
        }
	    $aListingAddress = GetSettings::getListingMapInfo($post->ID);

	    if ( !empty($aListingAddress) && !empty($aListingAddress['lat']) ){
		    $mapPageUrl = add_query_arg(
			    array(
				    'title' => $post->post_title,
				    'lat'   => $aListingAddress['lat'],
				    'lng'   => $aListingAddress['lng']
			    ),
			    $wiloke->aThemeOptions['map_page']
		    );
		    $aListing['oAddress']['mapPageUrl'] = $mapPageUrl;
		    $aListing['oAddress']['address'] = stripslashes($aListingAddress['address']);
		    $aListing['oAddress']['addressOnGGMap'] = GetSettings::getAddress($post->ID, true);
		    $aListing['oAddress']['lat'] = $aListingAddress['lat'];
		    $aListing['oAddress']['lng'] = $aListingAddress['lng'];
		    $aListing['oAddress']['marker'] = MapHelpers::getMapMarker($post->ID);
	    }else{
		    $aListing['oAddress'] = false;
	    }

	    $aListing['phone'] = GetSettings::getPostMeta($post->ID, 'phone');
	    $oListingCat = \WilokeHelpers::getTermByPostID($post->ID, 'listing_cat');
	    if ( !$oListingCat ){
		    $aListing['oListingCat'] = false;
	    }else{
		    $aListing['oListingCat']['name'] = $oListingCat->name;
		    $aListing['oListingCat']['link'] = get_term_link($oListingCat->term_id);
		    $aListing['oListingCat']['oIcon']= \WilokeHelpers::getTermOriginalIcon($oListingCat);
	    }
	    if ( BusinessHours::isEnableBusinessHour($post) ){
		    $aBusinessHours = BusinessHours::getCurrentBusinessHourStatus($post);
		    if ( $aBusinessHours['status'] == 'day_off' ){
			    $aBusinessHours['class'] = ' color-quaternary';
		    }
		    $aListing['oBusinessHours'] = $aBusinessHours;
	    }else{
		    $aListing['oBusinessHours'] = false;
	    }

	    $aImagesID = GetSettings::getPostMeta($post->ID, 'gallery');
	    if ( empty($aImagesID) ){
		    $aListing['gallery'] = false;
	    }else{
		    $aImagesSrc = array();
		    foreach ($aImagesID as $id => $src){
			    $imgSrc = wp_get_attachment_image_url($id, 'large');
			    if ( !$imgSrc ){
				    $aImagesSrc[] = $imgSrc;
                }else{
				    $aImagesSrc[] = $src;
                }
		    }
		    $aListing['gallery'] = implode(',', $aImagesSrc);
	    }

	    $aListing['isMyFavorite'] = UserModel::isMyFavorite($post->ID) ? 'yes' : 'no';
	    $aListing['priceRange'] = PriceRange::getSymbol($post);

	    $aListing = apply_filters('wilcity/filter-listing-slider/meta-data', $aListing, $post);
	    $aListings[] = (object)$aListing;

	    return $aListing;
    }

    public function searchFormJson(){
	    $this->middleware(['isWilokeShortcodeActivated'], array());
	    $aRequest = $_POST['oArgs'];
	    $aRequest['postType'] = isset($_POST['postType']) ? $_POST['postType'] : General::getPostTypeKeys(false, false);
	    $aRequest['page'] = $_POST['page'];
	    $aRequest['postsPerPage'] = $_POST['postsPerPage'];
	    $aRequest['is_map'] = isset($_POST['isMap']) ? $_POST['isMap'] : 'no';

	    $aRequest = apply_filters('wiloke-listing-tools/search-form-controller/search-orderby', $aRequest);
	    $aArgs = self::buildQueryArgs($aRequest);
	    $aArgs = apply_filters('wiloke-listing-tools/search-form-controller/query-args', $aArgs);

	    $errorMsg = '<div class="col-md-12">'. \WilokeMessage::message(array(
			    'status' => 'danger',
			    'msgIcon'=> 'la la-frown-o',
			    'hasMsgIcon' => true,
			    'msg' => esc_html__('Sorry, but We found no posts matched what are you looking for ...', 'wiloke-listing-tools')
		    ), true) . '</div>';

	    if ( !$this->isPassedDateRange($aArgs) ){
		    wp_send_json_error(array(
			    'msg' => $errorMsg,
			    'maxPosts' => 0,
			    'maxPages' => 0
		    ));
        }

	    $query = new \WP_Query($aArgs);


	    if ( !$query->have_posts() ){
		    wp_reset_postdata();
		    wp_send_json_error(array(
			    'msg' => $errorMsg,
			    'maxPosts' => 0,
			    'maxPages' => 0
		    ));
        }

        $aListings = array();

	    global $post;
	    $aAtts = array(
		    'maximum_posts_on_lg_screen'    => 'col-lg-3',
		    'maximum_posts_on_md_screen'    => 'col-md-4',
		    'maximum_posts_on_sm_screen'    => 'col-sm-6',
		    'get_listings_by'   => 'latest',
		    'img_size'          => 'wilcity_360x200',
		    'posts_per_page'    => 6,
		    'listing_cats'      => '',
		    'listing_locations' => '',
		    'listing_tags'      => '',
		    'extra_class'       => ''
        );
        while ($query->have_posts()){
	        $query->the_post();
	        $aListings[] = self::jsonSkeleton($post, $aAtts);
        }
        wp_reset_postdata();

        wp_send_json_success(array(
            'listings' => $aListings,
            'maxPosts' => $query->found_posts,
            'maxPages' => $query->max_num_pages
        ));
    }

	public function fetchListingsNearByMe(){
		if ( empty($_POST['oAddress']) ){
			wp_send_json_error(array(
				'msg' => esc_html__('Sorry, We could not detect your location.', WILOKE_LISTING_DOMAIN)
			));
		}

		$postsPerPage = isset($_POST['postsPerPage']) ? abs($_POST['postsPerPage']) : 10;
		$postsPerPage = $postsPerPage > 100 ? 10 : $postsPerPage;

		$aData['oAddress']      = $_POST['oAddress'];
		$aData['postsPerPage']  = $postsPerPage;
		$aData['postType']      = $_POST['postType'];

		$imgSize = 'wilcity_360x200';
		if ( !empty($_POST['data']) ){
		    $aParseData = unserialize(base64_decode($_POST['data']));
		    $imgSize = $aParseData['img_size'];

		    if ( in_array($aParseData['from'], array('listing_cat', 'listing_location', 'listing_tag')) ){
			    $aTerms = SCHelpers::getAutoCompleteVal($aParseData[$aParseData['from'].'s']);
		        $aData[$aParseData['from']] = $aTerms;
            }
        }

		$aArgs = self::buildQueryArgs($aData);
		$query = new \WP_Query($aArgs);
		$aAtts = array(
			'img_size'  => $imgSize
		);

		if ( $query->have_posts() ){
			$aListings = array();
			while ($query->have_posts()){
				$query->the_post();
				$aListings[] = self::jsonSkeleton($query->post, $aAtts);
			}
			wp_reset_postdata();

			wp_send_json_success(array(
                'aResults' => $aListings
            ));
		}else{
			wp_send_json_error(array(
				'msg' => esc_html__('Sorry, We found no posts near by you.', 'wiloke-listing-tools')
			));
		}
	}

	public function searchListings(){
		$this->middleware(['isWilokeShortcodeActivated'], array());
		$aRequest = $_POST['oArgs'];

		$aRequest['postType'] = $_POST['postType'];

		$aRequest['postsPerPage'] = isset($_POST['postsPerPage']) ? abs($_POST['postsPerPage']) : '';
		$aRequest['page'] = isset($_POST['page']) ? abs($_POST['page']) : '';

        $aArgs = self::buildQueryArgs($aRequest);
		$aArgs = apply_filters('wiloke-listing-tools/search-form-controller/query-args', $aArgs);

		$errorMsg = '<div class="col-md-12">'. \WilokeMessage::message(array(
				'status' => 'danger',
				'msgIcon'=> 'la la-frown-o',
				'hasMsgIcon' => true,
				'msg' => esc_html__('Sorry, but We found no posts matched what are you looking for ...', WILOKE_LISTING_DOMAIN)
			), true) . '</div>';

		if ( !$this->isPassedDateRange($aArgs) ){
			wp_send_json_error(array(
				'msg' => $errorMsg,
				'maxPosts' => 0,
				'maxPages' => 0
			));
		}

		$query = new \WP_Query($aArgs);
		if ( !$query->have_posts() ){
		    wp_reset_postdata();
		    wp_send_json_error(array(
                'msg' => $errorMsg,
                'maxPosts' => 0,
                'maxPages' => 0
            ));
        }

		ob_start();
        while ($query->have_posts()) :
            $query->the_post();

            if ( $aArgs['post_type'] == 'event' ){
                wilcity_render_event_item($query->post, array(
	                'maximum_posts_on_lg_screen'    => '',
	                'maximum_posts_on_md_screen'    => '',
	                'maximum_posts_on_sm_screen'    => 'col-sm-6',
	                'img_size'  => isset($_POST['img_size']) ? $_POST['img_size'] : 'medium',
	                'style'     => isset($_POST['style']) ? $_POST['style'] : 'grid'
                ));
            }else{
	            wilcity_render_grid_item($query->post, array(
		            'maximum_posts_on_lg_screen'    => '',
		            'maximum_posts_on_md_screen'    => '',
		            'maximum_posts_on_sm_screen'    => 'col-sm-6',
		            'img_size'  => isset($_POST['img_size']) ? $_POST['img_size'] : 'medium',
		            'style'     => isset($_POST['style']) ? $_POST['style'] : 'grid'
	            ));
            }

        endwhile; wp_reset_postdata();
        $content = ob_get_contents();
        ob_end_clean();
        wp_send_json_success(array(
            'msg'      => $content,
            'maxPosts'  => $query->found_posts,
            'maxPages' => $query->max_num_pages
        ));
    }

	public function renderSearchResults($aAtts){
		$this->middleware(['isWilokeShortcodeActivated'], array());
        global $wiloke;
		$postsPerPage = isset($wiloke->aThemeOptions['listing_posts_per_page']) ? $wiloke->aThemeOptions['listing_posts_per_page'] : get_option('posts_per_page');
		if ( isset($aAtts['postType']) ){
			$postType = $aAtts['postType'];
		}else if ( isset($aAtts['type']) ){
			$postType = $aAtts['type'];
		}else{
			$postType = 'listing';
		}

		$aAtts['postType'] = $postType;
		$aAtts = apply_filters('wiloke-listing-tools/search-form-controller/search-orderby', $aAtts);
		$aArgs = self::buildQueryArgs($aAtts);
		$aArgs = apply_filters('wiloke-listing-tools/search-form-controller/query-args', $aArgs);
		$aArgs['posts_per_page'] = $postsPerPage;
		$query = new \WP_Query($aArgs);
		$gridID = 'wilcity-search-results';

		if ( !isset($aAtts['aItemsPerRow']) || empty($aAtts['aItemsPerRow']) ){
			$aAtts['aItemsPerRow'] = array(
                'lg' => 'col-lg-6',
                'md' => 'col-md-6',
                'sm' => 'col-sm-6'
            );
        }

		if ( !isset($aAtts['img_size']) || empty($aAtts['img_size']) ){
			$aAtts['img_size'] = 'wilcity_360x200';
		}
		?>
		<div id="<?php echo esc_attr($gridID); ?>" class="row js-listing-grid wilcity-grid">
		<?php
			if ( $query->have_posts() ) :
				while ($query->have_posts()) :
					$query->the_post();
					if ( $postType == 'event' ){
						wilcity_render_event_item($query->post, array(
							'maximum_posts_on_lg_screen'    => $aAtts['aItemsPerRow']['lg'],
							'maximum_posts_on_md_screen'    => $aAtts['aItemsPerRow']['md'],
							'maximum_posts_on_sm_screen'    => $aAtts['aItemsPerRow']['sm'],
							'img_size'  => $aAtts['img_size'],
							'TYPE'      => 'TOP_SEARCH'
						));
                    }else{
						wilcity_render_grid_item($query->post, array(
							'maximum_posts_on_lg_screen'    => $aAtts['aItemsPerRow']['lg'],
							'maximum_posts_on_md_screen'    => $aAtts['aItemsPerRow']['md'],
							'maximum_posts_on_sm_screen'    => $aAtts['aItemsPerRow']['sm'],
							'img_size'  => $aAtts['img_size'],
                            'TYPE'      => 'TOP_SEARCH'
						));
                    }
				endwhile;
			else:
				\WilokeMessage::message(array(
					'status'       => 'danger',
					'hasMsgIcon'   => true,
					'msgIcon'      => 'la la-frown-o',
					'msg'          => esc_html__('No results found', 'wiloke-listing-tools')
				));
			endif; wp_reset_postdata();
		?>
		</div>
        <nav class="mt-20 mb-20">
            <ul id="wilcity-search-pagination" class="wilcity-pagination pagination_module__1NBfW" data-action="wilcity_search_listings" data-gridid="<?php echo esc_attr($gridID); ?>" data-post-type="<?php echo esc_attr($postType); ?>" data-totals="<?php echo esc_attr($query->found_posts); ?>" data-max-pages="<?php echo esc_attr($query->max_num_pages); ?>" data-posts-per-page="<?php echo esc_attr($postsPerPage); ?>" data-current-page="1" data-maximum_posts_on_lg_screen="" data-maximum_posts_on_md_screen="" data-maximum_posts_on_sm_screen="col-sm-6" data-img_size="<?php echo esc_attr($aAtts['img_size']); ?>"></ul>
        </nav>
        <?php
	}

	public function getSearchFields(){
	    $at = abs($_POST['at']);
		$postType = !isset($_POST['postType']) ? 'listing' : sanitize_text_field($_POST['postType']);
	    $savedAt = GetSettings::getOptions(General::mainSearchFormSavedAtKey($postType));

	    if ( empty($savedAt) ){
		    $savedAt = current_time('timestamp', 1);
		    SetSettings::setOptions(General::mainSearchFormSavedAtKey($postType), $savedAt);
        }

        if ( $at == $savedAt ){
	        wp_send_json_success(array(
	           'action' => 'use_cache'
            ));
        }

		$aSearchFields  = GetSettings::getOptions(General::getSearchFieldsKey($postType));

		if ( empty($aSearchFields) ){
			wp_send_json_error(array(
				'msg' => esc_html__('Oops! You have not configured the search fields', WILOKE_LISTING_DOMAIN)
			));
		}else{
			foreach ($aSearchFields as $key => $aSearchField){
			    switch ($aSearchField['key']){
                    case 'price_range':
                        $aOptions = array();
                        foreach (wilokeListingToolsRepository()->get('general:priceRange') as $rangeKey => $rangeName){
	                        $aOptions['name'] = $rangeName;
	                        $aOptions['value'] = $rangeKey;
	                        $aSearchFields[$key]['options'][] = $aOptions;
                        }
                        break;
                    case 'google_place':
	                    $aSearchFields[$key]['address'] = isset($_REQUEST['address']) ? stripslashes($_REQUEST['address']) : '';
                        break;
                    case 'post_type':
	                    $aTypes = General::getPostTypes(false);
	                    foreach ($aTypes as $directoryType => $aType){
		                    $aOption['name']  = $aType['name'];
		                    $aOption['value'] = $directoryType;
		                    $aSearchFields[$key]['options'][] = $aOption;
	                    }
                        break;
                    default:
	                    if (isset($aSearchField['group']) &&  $aSearchField['group'] == 'term' && $aSearchField['isAjax'] == 'no' ){
                            $termOrderBy = isset($aSearchField['orderBy']) ? $aSearchField['orderBy'] :  'count';
                            if ( $aSearchField['key'] == 'listing_tag' && !empty($_POST['catId']) ){
	                            $aTagSlugs = GetSettings::getTermMeta($_POST['catId'], 'tags_belong_to');
	                            if ( !empty($aTagSlugs) ){
		                            foreach ($aTagSlugs as $order => $slug){
			                            $oTerm = get_term_by('slug', $slug, 'listing_tag');
			                            $aSearchField['options'][$order]['label'] = $oTerm->name;
			                            $aSearchField['options'][$order]['name']  = $oTerm->name;
			                            $aSearchField['options'][$order]['value'] = $oTerm->slug;
		                            }

		                            $aSearchFields[$key] = $aSearchField;
	                            }else{
		                            $aTags = GetSettings::getTerms(array(
                                        'taxonomy' => 'listing_tag',
                                        'hide_empty'=> false
                                    ));
		                            if ( empty($aTags) || is_wp_error($aTags) ){
			                            $aSearchFields[$key] = array();
                                    }else{
		                                foreach ($aTags as $order => $oTerm){
			                                $aTagsBelongsTo = GetSettings::getTermMeta($oTerm->term_id, 'belongs_to');
                                            if ( empty($aTagsBelongsTo) || in_array($postType, $aTagsBelongsTo) ){
	                                            $aSearchField['options'][$order]['label'] = $oTerm->name;
	                                            $aSearchField['options'][$order]['name']  = $oTerm->name;
	                                            $aSearchField['options'][$order]['value'] = $oTerm->slug;
                                            }
                                        }
			                            $aSearchFields[$key] = $aSearchField;
                                    }
                                }
                            }else{
	                            $aTerms = GetSettings::getTaxonomyHierarchy(array(
		                            'taxonomy' => $aSearchField['key'],
		                            'orderby'  => $termOrderBy,
		                            'order'    => isset($aSearchField['order']) ? $aSearchField['order'] : 'DESC',
		                            'parent'   => 0
	                            ), $postType, $aSearchField['isShowParentOnly'] == 'yes', false);
	                            if ( empty($aTerms) ){
		                            unset($aSearchFields[$key]);
	                            }else{
		                            foreach ($aTerms as $order => $oTerm){
			                            if ( is_wp_error($oTerm) || empty($oTerm) ){
				                            continue;
			                            }

			                            if ( $aSearchField['key'] == 'listing_tag' ) {
				                            $aTagsBelongsTo = GetSettings::getTermMeta($oTerm->term_id, 'belongs_to');
				                            if ( !empty($aTagsBelongsTo) && !in_array($postType, $aTagsBelongsTo) ) {
                                                continue;
				                            }
			                            }

			                            $aSearchField['options'][$order]['label'] = $oTerm->name;
			                            $aSearchField['options'][$order]['name']  = $oTerm->name;
			                            $aSearchField['options'][$order]['value'] = $oTerm->slug;
		                            }

                                    $aSearchFields[$key] = $aSearchField;
	                            }
                            }
	                    }
                    break;
                }
			}
		}

		wp_send_json_success(
            array(
                'fields'    =>  $aSearchFields,
                'at'        =>  $savedAt,
                'action'    => 'update_search_fields'
            )
        );
	}
}