<?php
namespace WILCITY_SC;


use WilokeListingTools\Controllers\ReviewController;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Frontend\BusinessHours;
use WilokeListingTools\MetaBoxes\Review;
use WilokeListingTools\Models\FavoriteStatistic;
use WilokeListingTools\Models\ReviewMetaModel;
use WilokeListingTools\Models\UserModel;

class SCHelpers {
	public static $isApp = false;
	private static $listingID = '';

	public static function getPostMetaData($post, $metaKey){
	    switch ($metaKey){
            case 'date':
                return date_i18n(get_option('date_format'), strtotime($post->post_date));
                break;
		    case 'category':
		        $aCategories = get_the_category($post->ID);
		        if ( empty($aCategories) ){
		            return false;
                }
                $aCatNames = array_map(function($oTerm){
                    return $oTerm->name;
                }, $aCategories);
		        return implode(', ', $aCatNames);
			    break;
		    case 'comment':
		        $comments = get_comments_number($post->ID);
		        if ( empty($comments) ){
		            return esc_html__('No Comment', 'wilcity-shortcodes');
                }else if ( $comments  == 1 ){
			        return esc_html__('1 Comment', 'wilcity-shortcodes');
                }else if ( $comments  == 2 ){
			        return esc_html__('2 Comments', 'wilcity-shortcodes');
		        }else{
			        return sprintf(esc_html__('%d Comments', 'wilcity-shortcodes'), $comments);
		        }
			    break;
            default:
                return apply_filters('wilcity/post/meta-data/item/'.$metaKey, '', $post);
                break;
        }
    }

	public static function decodeAtts($atts){
		return $atts ? json_decode(utf8_decode($atts), true) : array();
	}

	public static function parseImgSize($size){
	    if ( empty($size) ){
	        return '';
        }

        if ( strpos($size, ',') !== false ){
	        return explode(',', $size);
        }

        return $size;
    }

	public static function prepareCustomSC($customSC, $postID='', $isApp=false){
		$customSC = str_replace(array('{{', '}}'), array('"', '"'), $customSC);
		if ( strpos($customSC, 'post_id') === false ){
			if ( !empty($postID) ){
				self::$listingID = $postID;
				$customSC = preg_replace_callback('/\s/', function($matched){
					return ' post_id="'.self::$listingID.'" is_grid="yes" ';
				}, $customSC, 1);
			}
		}

		if ( $isApp ){
			$customSC = preg_replace_callback('/\s/', function($matched){
				return ' is_mobile="yes" ';
			}, $customSC, 1);
		}

		return $customSC;
	}

	public static function getFeaturedImage($postID, $size=''){
		$thumbnailURL = get_the_post_thumbnail_url($postID, $size);

		if ( empty($thumbnailURL) ){
			global $wiloke;
			if ( isset($wiloke->aThemeOptions['listing_featured_image']['id']) ){
				$thumbnailURL = wp_get_attachment_image_url($wiloke->aThemeOptions['listing_featured_image']['id'], $size);
			}

			if ( empty($thumbnailURL) ){
				$thumbnailURL = $wiloke->aThemeOptions['listing_featured_image']['url'];
			}
		}

		return $thumbnailURL;
	}

	public static function getPost(){
		if ( wp_doing_ajax() ){
			$post = get_post($_POST['postID']);
		}else{
			global $post;
		}
		return $post;
	}

	public static function mergeIsAppRenderingAttr($aAtts){
		if ( isset($_POST['post_ID']) ){
			$pageTemplate = get_page_template_slug($_POST['post_ID']);
			if ( $pageTemplate == 'templates/mobile-app-homepage.php' ){
				self::$isApp = true;
			}
		}
		$aAtts['isApp'] = self::$isApp;
		return $aAtts;
	}

	public static function isApp($aAtts){
		if ( isset($aAtts['isApp']) && $aAtts['isApp'] ){
			return true;
		}
		return false;
	}

	public static function renderPlanPrice($price){
		$currencyPosition   = GetWilokeSubmission::getField('currency_position');
		$currencyCode       = GetWilokeSubmission::getField('currency_code');
		$currencySymbol     = GetWilokeSubmission::getSymbol($currencyCode);

		switch ($currencyPosition){
			case 'left':
				?>
                <span class="pricing_price__2vtrC color-primary"><sup class="pricing_currency__2bkpj"><?php echo esc_html($currencySymbol); ?></sup><span class="pricing_amount__34e-B"><?php echo esc_html($price); ?></span></span>
				<?php
				break;
			case 'right:':
				?>
                <span class="pricing_price__2vtrC color-primary"><span class="pricing_amount__34e-B"><?php echo esc_html($price); ?></span><sup class="pricing_currency__2bkpj"><?php echo esc_html($currencySymbol); ?></sup></span>
				<?php
				break;
			case 'left_space':
				?>
                <span class="pricing_price__2vtrC color-primary"><sup class="pricing_currency__2bkpj"><?php echo esc_html($currencySymbol); ?></sup> <span class="pricing_amount__34e-B"><?php echo esc_html($price); ?></span></span>
				<?php
				break;
			case 'right_space':
				?>
                <span class="pricing_price__2vtrC color-primary"><span class="pricing_amount__34e-B"><?php echo esc_html($price); ?></span> <sup class="pricing_currency__2bkpj"><?php echo esc_html($currencySymbol); ?></sup></span>
				<?php
				break;
		}
	}

	public static function renderIconAndLink($link, $icon, $content, $aArgs=array()){
		$wrapperClass = isset($aArgs['wrapperClass']) ? 'icon-box-1_module__uyg5F one-text-ellipsis ' . $aArgs['wrapperClass'] : 'icon-box-1_module__uyg5F one-text-ellipsis';
		$style = isset($aArgs['style']) ? $aArgs['style'] : 'icon-box-1_block1__bJ25J';
		$iconWrapperClass = isset($aArgs['iconWrapperClass']) ? 'icon-box-1_icon__3V5c0 ' . $aArgs['iconWrapperClass'] : 'icon-box-1_icon__3V5c0';
		?>
        <div class="<?php echo esc_attr($wrapperClass); ?>">
            <div class="<?php echo esc_attr($style); ?>">
				<?php if ( is_email($link) ): ?>
                <a href="mailto:<?php echo esc_attr($link); ?>">
					<?php elseif(isset($aArgs['isPhone'])): ?>
                    <a href="tel:<?php echo esc_attr($link); ?>">
						<?php elseif(isset($aArgs['isGoogle'])):
						$link = str_replace('/', '%2F', $link);
						?>
                        <a target="_blank" href="<?php echo esc_url('https://www.google.com/maps/search/'.esc_attr($link)); ?>">
							<?php else: ?>
                            <a target="_blank" href="<?php echo esc_url($link); ?>">
								<?php endif;?>
                                <div class="<?php echo esc_attr($iconWrapperClass); ?>"><i class="<?php echo esc_attr($icon); ?>"></i></div>
                                <div class="icon-box-1_text__3R39g"><?php echo esc_html(stripslashes($content)); ?></div>
                            </a>
            </div>
        </div>
		<?php
	}

	public static function parseAutoComplete($val){
		if ( empty($val) ){
			return false;
		}
		if ( is_array($val) ){
			$aTerms = array_filter($val, function($val){
				return !empty($val);
			});
			return $aTerms;
		}
		return explode(',', $val);
	}

	public static function getPostTypeKeys($isExcludeEvents=true){
		if ( !class_exists('WilokeListingTools\Framework\Helpers\General') ){
			return array('listing'=>'listing');
		}
		$aRawPostTypes  = General::getPostTypeKeys(true, $isExcludeEvents);

		$aPostTypes  = array();
		foreach ($aRawPostTypes as $postType){
			$aPostTypes[$postType] = $postType;
		}

		return $aPostTypes;
	}

	public static function getAutoCompleteVal($val){
		if ( empty($val) ){
			return false;
		}
		$aParse = self::parseAutoComplete($val);

		if ( !$aParse ){
			return false;
		}

		$aValues = array();
		foreach ($aParse as $val){
			$aInfo = explode(':', $val);
			$aValues[] = $aInfo[0];
		}
		return $aValues;
	}

	public static function parseArgs($atts){
		$aArgs = array(
			'post_type'         => $atts['post_type'],
			'posts_per_page'    => isset($atts['posts_per_page']) ? $atts['posts_per_page'] : $atts['maximum_posts'],
			'post_status'       => 'publish'
		);

		$aTaxQuery = array();
		if ( isset($atts['listing_locations']) && !empty($atts['listing_locations']) ){
			$aTaxQuery[] = array(
				'taxonomy' => 'listing_location',
				'field'    => 'term_id',
				'terms'    => self::getAutoCompleteVal($atts['listing_locations'])
			);
		}

		if ( isset($atts['listing_cats']) && !empty($atts['listing_cats']) ){
			$aTaxQuery[] = array(
				'taxonomy' => 'listing_cat',
				'field'    => 'term_id',
				'terms'    => self::getAutoCompleteVal($atts['listing_cats'])
			);
		}

		if ( isset($atts['listing_tags']) && !empty($atts['listing_tags']) ){
			$aTaxQuery[] = array(
				'taxonomy' => 'listing_tag',
				'field'    => 'term_id',
				'terms'    => self::getAutoCompleteVal($atts['listing_tags'])
			);
		}

		if ( !empty($aTaxQuery) ){
			$aArgs['tax_query'] = array($aTaxQuery);
		}

		if ( count($aTaxQuery) > 1 ){
			$aArgs['tax_query']['relation'] = 'AND';
		}

		switch ($atts['orderby']){
			case 'best_rated':
				$aArgs['orderby']   = 'meta_value_num';
				$aArgs['meta_key']  = 'wilcity_average_reviews';
				$aArgs['order']     = 'DESC';
				break;
			case 'best_viewed':
				$aArgs['orderby']   = 'meta_value_num';
				$aArgs['meta_key']  = 'wilcity_count_viewed';
				$aArgs['order']     = 'DESC';
				break;
			case 'best_shared':
				$aArgs['orderby']   = 'meta_value_num';
				$aArgs['meta_key']  = 'wilcity_count_shared';
				$aArgs['order']     = 'DESC';
				break;
			case 'premium_listings':
				if ( $atts['TYPE'] == 'LISTINGS_SLIDER' ){
					$aArgs['orderby']   = 'meta_value_num';
					$aArgs['meta_key']  = 'wilcity_promote_listing_slider_sc';
					$aArgs['order']     = 'DESC';
				}else{
					$aArgs['orderby']   = 'meta_value_num';
					$aArgs['meta_key']  = 'wilcity_promote_listing_grid_sc';
					$aArgs['order']     = 'DESC';
				}
				break;
			default:
				$aArgs['orderby'] = $atts['orderby'];
				break;
		}

		return $aArgs;
	}


	public static function renderAds($post, $type=''){
	    $isAds = false;
		switch ($type){
			case 'LISTINGS_SLIDER':
				$val = GetSettings::getPostMeta($post->ID, 'promote_listing_slider_sc');
				if ( !empty($val) ){
					$isAds = true;
                }
				break;
			case 'GRID':
				$val = GetSettings::getPostMeta($post->ID, 'promote_listing_grid_sc');
				if ( !empty($val) ){
					$isAds = true;
				}
				break;
			case 'TOP_SEARCH':
				$val = GetSettings::getPostMeta($post->ID, 'wilcity_promote_top_of_search');
				if ( !empty($val) ){
					$isAds = true;
				}
				break;
		}

		if ( $isAds ){
            ?>
            <span class="wil-ads"><?php esc_html_e('Ads', 'wilcity-shortcodes'); ?></span>
            <?php
		}
	}

	public static function renderInterested($post){
		$total = FavoriteStatistic::countFavorites($post->ID);
		if ( empty($total) ){
			return '';
		}
		$total = abs($total);
		?>
        <li class="event_metaList__1bEBH text-ellipsis">
            <span><?php echo sprintf( _n('%d person interested', '%d people interested', $total, 'wilcity-shortcodes'), $total ); ?></span>
        </li>
		<?php
	}

	public static function renderFavorite($post, $aAtts=array()){
		if ( self::isApp($aAtts) ){
			return UserModel::isMyFavorite($post->ID)  ? 'yes' : 'no';
		}
		if ( $post->post_type == 'event' ){
			$favoriteIconClass = UserModel::isMyFavorite($post->ID) ? 'la la-star color-primary' : 'la la-star-o';
		}else{
			$favoriteIconClass = UserModel::isMyFavorite($post->ID) ? 'la la-heart color-primary' : 'la la-heart-o';
		}

		?>
        <a class="wilcity-js-favorite" data-post-id="<?php echo esc_attr($post->ID); ?>" href="#" data-tooltip="<?php esc_html_e('Save to my favorites', 'wilcity-shortcodes'); ?>" data-tooltip-placement="top"><i class="<?php echo esc_attr($favoriteIconClass); ?>"></i></a>
		<?php
	}
	public static function renderGallery($post, $aAttts=array()){
		$aImagesID = GetSettings::getPostMeta($post->ID, 'gallery');

		if ( !empty($aImagesID) ) :
			$aImagesSrc = array();
			foreach ($aImagesID as $id => $imgSrc){
				$largeImg = wp_get_attachment_image_url($id, 'large');
				if ( !$largeImg ){
					$aImagesSrc[] = $imgSrc;
				}else{
					$aImagesSrc[] = $largeImg;
				}
			}

			if ( self::isApp($aAttts) ){
				return $aImagesSrc;
			}
			?>
            <a class="wilcity-preview-gallery" href="#" data-tooltip="<?php esc_html_e('Gallery', 'wilcity-shortcodes'); ?>" data-tooltip-placement="top" data-gallery="<?php echo esc_attr(implode(',', $aImagesSrc)); ?>"><i class="la la-search-plus"></i></a>
		<?php endif;
		return '';
	}
	public static function renderListingCat($post, $aAtts=array()){
		if ( is_tax('listing_cat') ){
			$oListingCat = get_term_by( 'slug', get_query_var('term'), 'listing_cat');
		}else{
			$oListingCat = \WilokeHelpers::getTermByPostID($post->ID, 'listing_cat');
		}

		if ( $oListingCat ){
			if ( self::isApp($aAtts) ){
				return array(
					'oTerm' => $oListingCat,
					'oIcon' => \WilokeHelpers::getTermOriginalIcon($oListingCat)
				);
			}
			echo '<div class="icon-box-1_block1__bJ25J">'.\WilokeHelpers::getTermIcon($oListingCat, 'icon-box-1_icon__3V5c0 rounded-circle', true). '</div>';
		}
		return '';
	}

	public static function renderBusinessStatus($post, $aAtts=array(), $isGridItem=false){
		if ( BusinessHours::isEnableBusinessHour($post) ) :
			$aBusinessHours = BusinessHours::getCurrentBusinessHourStatus($post);

			if ( self::isApp($aAtts) ){
				return $aBusinessHours['text'];
			}

			if ( $isGridItem ){
				if ( $aBusinessHours['status'] == 'day_off' ){
					$aBusinessHours['class'] = ' color-quaternary';
				}
			}
			?>
            <div class="icon-box-1_block2__1y3h0 wilcity-listing-hours"><span class="<?php echo esc_attr($aBusinessHours['class']); ?>"><?php echo esc_html($aBusinessHours['text']); ?></span></div>
		<?php endif;
		return '';
	}


	public static function renderTitle($post){
		?>
        <h2 class="listing_title__2920A text-ellipsis">
            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>"><?php echo get_the_title($post->ID); ?></a>
        </h2>
		<?php
	}

	public static function renderPhone($post, $aAtts=array(), $isReturn=false){
		if ( !isset($aAtts['icon']) ){
			$aAtts['icon'] = 'la la-phone';
		}

		$phone = GetSettings::getPostMeta($post->ID, 'phone');
		if ( self::isApp($aAtts) || $isReturn ){
			return array(
				'value' => $phone,
				'icon'  => $aAtts['icon'],
				'type'  => 'phone'
			);
		}

		if ( !empty($phone) ) :
			?>
            <a class="text-ellipsis phone-number" href="tel:<?php echo esc_attr($phone); ?>">
                <i class="<?php echo esc_attr($aAtts['icon']); ?> color-primary"></i><?php echo esc_html($phone); ?>
            </a>
		<?php endif;
	}

	public static function renderSinglePrice($post, $aAtts=array(), $isReturn=false){
		if ( !isset($aAtts['icon']) ){
			$aAtts['icon'] = 'la la-money';
		}

		$price = GetSettings::getPostMeta($post->ID, 'single_price');
		if ( self::isApp($aAtts) || $isReturn ){
			return array(
				'value' => $price,
				'icon'  => $aAtts['icon'],
				'type'  => 'single_price'
			);
		}

		if ( !empty($price) ) :
			?>
            <a class="text-ellipsis single-price" href="<?php echo esc_attr($price); ?>">
                <i class="<?php echo esc_attr($aAtts['icon']); ?> color-primary"></i><?php echo GetWilokeSubmission::renderPrice($price); ?>
            </a>
		<?php endif;
	}

	public static function renderWebsite($post, $aAtts=array(), $isWebsite=false){
		$website = GetSettings::getPostMeta($post->ID, 'website');
		if ( !isset($aAtts['icon']) ){
			$aAtts['icon'] = 'la la-link';
		}

		if ( self::isApp($aAtts) || $isWebsite){
			return array(
				'value' => $website,
				'icon'  => $aAtts['icon'],
				'type'  => 'website'
			);
		}

		if ( !empty($website) ) :
			?>
            <a class="text-ellipsis website" href="<?php echo esc_attr($website); ?>" target="_blank">
                <i class="<?php echo esc_attr($aAtts['icon']); ?> color-primary"></i><?php echo esc_html($website); ?>
            </a>
		<?php endif;
	}

	public static function renderEmail($post, $aAtts=array(), $isReturn=false){
		$email = GetSettings::getPostMeta($post->ID, 'email');
		if ( !isset($aAtts['icon']) ){
			$aAtts['icon'] = 'la la-envelope';
		}

		if ( self::isApp($aAtts) || $isReturn){
			return array(
				'value' => $email,
				'icon'  => $aAtts['icon'],
				'type'  => 'email'
			);
		}

		if ( !empty($email) && is_email($email) ) :
			?>
            <a class="text-ellipsis mail-address" href="mailto:<?php echo esc_attr($email); ?>">
                <i class="<?php echo esc_attr($aAtts['icon']); ?> color-primary"></i><?php echo esc_html($email); ?>
            </a>
		<?php endif;
	}

	public static function scNotHasIcon($aContent) {
		foreach ($aContent as $aSc){
			if ( !isset($aSc['oIcon']) || !empty($aSc['oIcon']['icon']) ){
				return true;
			}
		}
		return false;
	}

	public static function renderCustomField($post, $aAtts=array(), $isReturn=false){
		if ( empty($aAtts['content']) ){
			return '';
		}
		$sc = self::prepareCustomSC($aAtts['content'], $post->ID, $isReturn);
		if ( $isReturn ){
			return do_shortcode($sc);
		}

		$parsedSc = do_shortcode($sc);

		if ( empty($parsedSc) ){
			return '';
		}

		if ( isJson($parsedSc) ){
			if ( strpos($aAtts['content'], 'select') !== false || strpos($aAtts['content'], 'checkbox') !== false ){
				$aParsedSc = json_decode($parsedSc, true);
				$aScHasIcon = array();
				$aScName  = array();

				foreach ($aParsedSc as $aSc){
					if ( !empty($aSc['oIcon']['icon']) ){
						$sc = do_shortcode("[wilcity_render_box_icon1 icon='".$aSc['oIcon']['icon']."' name='".$aSc['name']."' color='".$aSc['oIcon']['color']."']");
						if ( !empty($sc) ){
							$aScHasIcon[] = $sc;
						}
					}else{
						$aScName[] = $aSc['name'];
					}
				}

				if ( empty($aScHasIcon) ){
					if ( !empty($aScName) ) {
						?>
                        <a class="text-ellipsis custom-content"
                           href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
                            <i class="<?php echo esc_attr( $aAtts['icon'] ); ?> color-primary"></i> <?php echo implode( ', ', $aScName ); ?>
                        </a>
						<?php
					}
				}else{
					echo implode("\r\n", $aScHasIcon);
				}
			}
		}else{
			if ( empty($parsedSc) ){
				return '';
			}
			?>
            <a class="text-ellipsis custom-content" href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                <i class="<?php echo esc_attr($aAtts['icon']); ?> color-primary"></i> <?php echo $parsedSc; ?>
            </a>
			<?php
		}
	}

	public static function renderPriceRange($post, $aAtts=array(), $isReturn=false){
		$aPriceRange  = GetSettings::getPriceRange($post->ID);
		if ( !isset($aAtts['icon']) ){
			$aAtts['icon'] = 'la la-money';
		}

		$symbol = !empty($aPriceRange) ? GetWilokeSubmission::getSymbol($aPriceRange['currency']) : '';
		$symbol = apply_filters('wilcity/price-range/currencySymbol', $symbol);

		if ( self::isApp($aAtts) || $isReturn){
			if ( empty($aPriceRange) || ($aPriceRange['mode'] == 'nottosay') || ($aPriceRange['minimumPrice'] == $aPriceRange['maximumPrice']) ){
				return array(
					'value' => $aPriceRange,
					'icon'  => $aAtts['icon'],
					'type'  => 'price_range',
					'symbol'=> $symbol
				);
			}else{
				$aPriceRange['minimumPrice'] = GetWilokeSubmission::renderPrice($aPriceRange['minimumPrice'], '', false, $symbol);
				$aPriceRange['maximumPrice'] = GetWilokeSubmission::renderPrice($aPriceRange['maximumPrice'], '', false, $symbol);
				return array(
					'value' => $aPriceRange,
					'icon'  => $aAtts['icon'],
					'type'  => 'price_range',
					'symbol'=> $symbol
				);
			}
		}

		if ( empty($aPriceRange) || ($aPriceRange['mode'] == 'nottosay') || ($aPriceRange['minimumPrice'] == $aPriceRange['maximumPrice']) ){
			return '';
		}

		?>
        <a class="text-ellipsis price-range" href="<?php echo esc_url(get_permalink($post->ID)); ?>">
            <i class="<?php echo esc_attr($aAtts['icon']); ?> color-primary"></i><?php echo GetWilokeSubmission::renderPrice($aPriceRange['minimumPrice'], '', false, $symbol) . ' - ' . GetWilokeSubmission::renderPrice($aPriceRange['maximumPrice'], '', false, $symbol); ?>
        </a>
		<?php
	}

	public static function renderTextType($post, $key, $aAtts){
		$val = GetSettings::getPostMeta($post->ID, $key);

		if ( !isset($aAtts['icon']) ){
			$aAtts['icon'] = 'la la-refresh';
		}

		if ( self::isApp($aAtts) ){
			return array(
				'type'  => 'text',
				'value' => $val,
				'icon'  => $aAtts['icon']
			);
		}

		if ( empty($val) ){
			return '';
		}

		?>
        <a class="text-ellipsis text-type" href="<?php echo esc_url(get_permalink($post->ID)); ?>">
            <i class="<?php echo esc_attr($aAtts['icon']); ?> color-primary"></i><?php echo esc_html($val); ?>
        </a>
		<?php
	}

	public static function renderSelectType($post, $aAtts=array()){

	}

	public static function renderAddress($post, $aAtts=array(), $isReturn = false){
		$aThemeOptions = \Wiloke::getThemeOptions();
		$aListingAddress = GetSettings::getListingMapInfo($post->ID);

		if ( !empty($aListingAddress) && !empty($aListingAddress['lat']) && ($aListingAddress['lat'] != $aListingAddress['lng']) ) :
			$mapPageUrl = add_query_arg(
				array(
					'title' => urlencode($post->post_title),
					'lat'   => $aListingAddress['lat'],
					'lng'   => $aListingAddress['lng']
				),
				get_permalink($aThemeOptions['map_page'])
			);

			if ( !isset($aAtts['icon']) ){
				$aAtts['icon'] = 'la la-map-marker';
			}

			if ( self::isApp($aAtts) || $isReturn ){
				return array(
					'type'    => 'google_address',
					'value'   => array(
						'address' => $aListingAddress['address'],
						'mapUrl'  => $mapPageUrl,
						'googleMapAddress' => 'https://www.google.com/maps/search/' . urlencode($aListingAddress['address'])
					),
					'icon'    => $aAtts['icon']
				);
			}

			?>
            <a class="text-ellipsis google-address" href="<?php echo esc_url($mapPageUrl); ?>" data-tooltip="<?php echo esc_html(stripslashes($aListingAddress['address'])); ?>">
                <i class="<?php echo esc_attr($aAtts['icon']); ?> color-primary"></i><?php echo esc_html(stripslashes($aListingAddress['address'])); ?>
            </a>
			<?php
		endif;

		if ( self::isApp($aAtts) ){
			return '';
		}
	}

	public static function renderExcerpt($post, $aAtts=array(), $isReturn = false){
		$aThemeOptions = \Wiloke::getThemeOptions();
		$tagLine = GetSettings::getPostMeta($post->ID, 'tagline');

		if ( (isset($aAtts['isApp']) && $aAtts['isApp']) || $isReturn ){
			if ( !empty($tagLine) ){
				return $tagLine;
			}
			return \Wiloke::contentLimit($aThemeOptions['listing_excerpt_length'], $post, true, $post->post_content, true);
		}
		if ( !empty($tagLine) ) :
			?>
            <div class="listing_tagline__1cOB3 text-ellipsis"><?php \Wiloke::ksesHTML($tagLine); ?></div>
		<?php else:
			?>
            <div class="listing_tagline__1cOB3 text-ellipsis"><?php \Wiloke::contentLimit($aThemeOptions['listing_excerpt_length'], $post, true, $post->post_content, false); ?></div>
		<?php endif;
	}

	public static function renderAverageReview($post, $aAtts=array(), $isReturn = false){
		if ( ReviewController::isEnableRating() ) :
			$averageReview = GetSettings::getPostMeta($post->ID, 'average_reviews');
			$mode = ReviewController::getMode($post->post_type);

			if ( self::isApp($aAtts) || $isReturn ){
				return array(
					'mode' => $mode,
					'average' => $averageReview
				);
			}

			if ( !empty($averageReview) ) :
				?>
                <div class="listing_rated__1y7qV">
                    <div class="rated-small_module__1vw2B rated-small_style-2__3lb7d">
                        <div class="rated-small_wrap__2Eetz" data-rated="<?php echo esc_html($averageReview); ?>" data-tenmode="<?php echo ReviewController::toTenMode($averageReview, $mode); ?>">
                            <div class="rated-small_overallRating__oFmKR"><?php echo esc_html(number_format($averageReview, 1)); ?></div>
                            <div class="rated-small_ratingWrap__3lzhB">
                                <div class="rated-small_maxRating__2D9mI"><?php echo ReviewController::getMode($post->post_type); ?></div>
                                <div class="rated-small_ratingOverview__2kCI_"><?php echo esc_html(ReviewMetaModel::getReviewQualityString($averageReview, $post->post_type)); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
			<?php endif;
		endif;
	}
}