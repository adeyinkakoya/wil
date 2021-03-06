<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Helpers\Time;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Frontend\BusinessHours;

class SchemaController extends Controller {
    protected $schemaKey = 'schema_markup';
    protected $schemaMarkupSavedAt = 'schema_markup_saved_at';

	public function __construct() {
		add_action('wp_head', array($this, 'addSchemaMarkupToHeader'));
		add_action('post_updated', array($this, 'savePost'), 10, 3);
	}

	protected function generalSchemaMarkup($post){
		global $post;
		if ( empty($post) ){
		    return false;
        }

		$schemaSettings = GetSettings::getOptions(General::getSchemaMarkupKey($post->post_type));

		if ( empty($schemaSettings) ){
			return '';
		}

		if ( strpos($schemaSettings, '{{listing_location}}') !== false ){
			$aLocations = wp_get_post_terms($post->ID, 'listing_location');
			$location = null;
			if ( !empty($aLocations) && !is_wp_error($aLocations) ){
				$location = $aLocations[0]->name;
            }
			$schemaSettings = str_replace('{{listing_location}}', $location, $schemaSettings);
		}

		$priceRange = null;
		if ( strpos($schemaSettings, 'priceRange') !== false ){
			$aPriceRange = GetSettings::getPriceRange($post->ID, true);

			if ( !$aPriceRange ){
				$priceRange = null;
			}else{
				$priceRange = $aPriceRange['minimumPrice'] . ' - ' . $aPriceRange['maximumPrice'];
				$priceRange = html_entity_decode($priceRange);
			}
			$schemaSettings = str_replace('{{priceRange}}', $priceRange, $schemaSettings);
		}

		$aLatLng = array('lat'=>null, 'lng'=>null);

		if ( strpos($schemaSettings, '{{latitude}}') !== false ){
			$aRawLatLng = GetSettings::getLatLng($post->ID);
			if ( $aRawLatLng ){
				$aLatLng = $aRawLatLng;
			}

			$schemaSettings = str_replace(
				array(
					'{{latitude}}',
					'{{longitude}}'
				),
				array(
					$aLatLng['lat'],
					$aLatLng['lng']
				),
				$schemaSettings
			);
		}

		$featuredImg = null;

		if ( strpos($schemaSettings, '{{featuredImg}}') !== false ){
			$featuredImg = GetSettings::getFeaturedImg($post->ID, 'full');
			$schemaSettings = str_replace('{{featuredImg}}', $featuredImg, $schemaSettings);
		}

		if ( strpos($schemaSettings, '{{eventStartDate}}') !== false ){
			$aEventSettings = GetSettings::getEventSettings($post->ID);
			$eventStartsOn = null;
			$eventEndsOn = null;
			if ( $aEventSettings ){
				$dateFormat = get_option('date_format') . ' ' . get_option('time_format');

				if ( !empty($aEventSettings['timezone']) ){
					$startsOnTimestamp = Time::utcToLocal($aEventSettings['startsOnUTC'], $aEventSettings['timezone']);
					$endsOnTimestamp = Time::utcToLocal($aEventSettings['endsOnUTC'], $aEventSettings['timezone']);
				}else{
					$startsOnTimestamp = strtotime($aEventSettings['startsOn']);
					$endsOnTimestamp = strtotime($aEventSettings['endsOn']);
				}

				$eventStartsOn = date_i18n($dateFormat, $startsOnTimestamp);
				$eventEndsOn = date_i18n($dateFormat, $endsOnTimestamp);
			}

			$schemaSettings = str_replace(
				array(
					'{{eventStartDate}}',
					'{{eventEndDate}}'
				),
				array(
					$eventStartsOn,
					$eventEndsOn
				),
				$schemaSettings
			);
		}

		if ( strpos($schemaSettings, '{{googleAddress}}') !== false ){
			$schemaSettings = str_replace('{{googleAddress}}', GetSettings::getAddress($post->ID, false), $schemaSettings);
		}

		if ( strpos($schemaSettings, '{{streetAddress}}') !== false ){
			$schemaSettings = str_replace('{{streetAddress}}', GetSettings::getAddress($post->ID, false), $schemaSettings);
		}

		if ( strpos($schemaSettings, '{{telephone}}') !== false ){
			$schemaSettings = str_replace('{{telephone}}', GetSettings::getPostMeta($post->ID, 'phone'), $schemaSettings);
		}

		if ( strpos($schemaSettings, '{{website}}') !== false ){
			$schemaSettings = str_replace('{{website}}', GetSettings::getPostMeta($post->ID, 'website'), $schemaSettings);
		}

		if ( strpos($schemaSettings, '{{listingURL}}') !== false ){
			$schemaSettings = str_replace('{{listingURL}}', get_permalink($post->ID), $schemaSettings);
		}

		if ( strpos($schemaSettings, '{{email}}') !== false ){
			$schemaSettings = str_replace('{{email}}', GetSettings::getPostMeta($post->ID, 'email'), $schemaSettings);
		}

		if ( strpos($schemaSettings, '{{averageRating}}') !== false ){
		    $averageRating = GetSettings::getAverageRating($post->ID);
			$averageRating = empty($averageRating) ? 0 : $averageRating;
			$schemaSettings = str_replace('{{averageRating}}', $averageRating, $schemaSettings);
		}

		if ( strpos($schemaSettings, '{{bestRating}}') !== false ){
			$schemaSettings = str_replace('{{bestRating}}', GetSettings::getBestRating($post->post_type), $schemaSettings);
		}

		if ( strpos($schemaSettings, '{{openingHours}}') !== false ){
		    $hourMode = GetSettings::getPostMeta($post->ID, 'hourMode');
		    switch ($hourMode){
                case 'always_open':
                    $schemaAlwaysOpen = apply_filters('wilcity/schema-markup/always_open', esc_html__('Mo-Su Monday through Sunday, all day', 'wiloke-listing-tools'));
                    $schemaSettings = str_replace('{{openingHours}}', $schemaAlwaysOpen, $schemaSettings);
                    break;
                case 'open_for_selected_hours':
	                $aBusinessHours = GetSettings::getBusinessHours($post->ID);
	                $aBusinessHoursSchema = array();
                    $aDayOfWeek = wilokeListingToolsRepository()->get('general:aDayOfWeek');
                    $timeFormat = GetSettings::getTimeFormat($post->ID);
                    foreach ($aBusinessHours as $aBusinessHour){
                        if ( $aBusinessHour['isOpen'] != 'yes' || $aBusinessHour['firstOpenHour'] == $aBusinessHour['firstCloseHour'] || empty($aBusinessHour['firstOpenHour']) || empty($aBusinessHour['firstCloseHour']) ){
                            continue;
                        }

                        $concatBusinessHour = Time::renderTimeFormat(strtotime($aBusinessHour['firstOpenHour']), $timeFormat) . '-' .  Time::renderTimeFormat(strtotime($aBusinessHour['firstCloseHour']), $timeFormat);
	                    $aBusinessHoursSchema[] = $aDayOfWeek[$aBusinessHour['dayOfWeek']] . ' ' .  $concatBusinessHour;
                        if ( ($aBusinessHour['secondCloseHour'] == $aBusinessHour['secondOpenHour']) || empty($aBusinessHour['secondCloseHour']) || empty($aBusinessHour['secondOpenHour']) ){
	                        $concatBusinessHour = Time::renderTimeFormat(strtotime($aBusinessHour['secondOpenHour']), $timeFormat) . '-' .  Time::renderTimeFormat(strtotime($aBusinessHour['secondCloseHour']), $timeFormat);
	                        $aBusinessHoursSchema[] = $aDayOfWeek[$aBusinessHour['dayOfWeek']] . ' ' .  $concatBusinessHour;
                        }
                    }

	                $schemaSettings = str_replace('{{openiopeningHoursngHours}}', json_encode($aBusinessHoursSchema), $schemaSettings);
                    break;
                default:
	                $schemaSettings = str_replace('{{openingHours}}', '', $schemaSettings);
                    break;
            }
        }

		if ( strpos($schemaSettings, '{{photos}}') !== false ){
			$aGallery = GetSettings::getPostMeta($post->ID, 'gallery');
			if ( !$aGallery ){
				$schemaSettings = str_replace('{{photos}}', '', $schemaSettings);
            }else{
			    $aImgSrcs = array();
			    foreach ($aGallery as $imgID => $src){
			        $imgSrc = wp_get_attachment_image_url($imgID, 'large');
			        if ( !$imgSrc ){
				        $aImgSrcs[] = $src;
                    }else{
			            $aImgSrcs[] = $imgSrc;
                    }
                }

				$schemaSettings = str_replace('"{{photos}}"', json_encode($aImgSrcs), $schemaSettings);
            }
		}

		if ( strpos($schemaSettings, '{{coverImg}}') !== false ){
			$coverImg = GetSettings::getPostMeta($post->ID, 'cover_image');
			if ( !$coverImg ){
				$schemaSettings = str_replace('{{coverImg}}', NULL, $schemaSettings);
			}else{
				$schemaSettings = str_replace('{{coverImg}}', $coverImg, $schemaSettings);
			}
		}

		if ( strpos($schemaSettings, '{{logo}}') !== false ){
			$logo = GetSettings::getPostMeta($post->ID, 'logo');
			if ( !$logo ){
				$schemaSettings = str_replace('{{logo}}', NULL, $schemaSettings);
			}else{
				$schemaSettings = str_replace('{{logo}}', $logo, $schemaSettings);
			}
		}

		if ( strpos($schemaSettings, '{{socialNetworks}}') !== false ){
			$aSocialNetworks = GetSettings::getSocialNetworks($post->ID);
			if ( empty($aSocialNetworks) ){
				$schemaSettings = str_replace('{{socialNetworks}}', NULL, $schemaSettings);
            }else{
			    $aSocialUrls = array();
				foreach ($aSocialNetworks as $socialUrl){
				    if ( !empty($socialUrl) ){
					    $aSocialUrls[] = $socialUrl;
                    }
                }
                if ( !empty($aSocialUrls) ){
	                $schemaSettings = str_replace('"{{socialNetworks}}"', json_encode($aSocialUrls), $schemaSettings);
                }
            }
		}

		$schemaSettings = str_replace(
			array(
				'{{postTitle}}',
				'{{featuredImg}}',
				'{{postExcerpt}}',
				'{{worstRating}}'
			),
			array(
				$post->post_title,
				$featuredImg,
				\Wiloke::contentLimit(apply_filters('wilcity/schema_markup/post_excerpt', 100), $post, true, $post->post_content, true),
				1
			),
			$schemaSettings
		);

		SetSettings::setPostMeta($post->ID, $this->schemaKey, $schemaSettings);
		SetSettings::setPostMeta($post->ID, $this->schemaMarkupSavedAt, current_time('timestamp', 1));

		return $schemaSettings;
	}

	public function savePost($postID, $postBefore, $postAfter){
	    if ( $postAfter->post_status !== 'publish' ){
	        return false;
        }
        $this->generalSchemaMarkup($postAfter);
    }

	public function addSchemaMarkupToHeader(){
	    $aPostTypes = General::getPostTypeKeys(false, false);
        if ( !is_singular($aPostTypes) ){
            return false;
        }
		global $post;

		if ( !GetSettings::isPlanAvailableInListing($post->ID, 'toggle_schema_markup') ){
            return false;
		}

        $savedSchemaMarkupAt = GetSettings::getOptions(General::getSchemaMarkupSavedAtKey($post->post_type));
        if ( !$savedSchemaMarkupAt ){
            return false;
        }
        $savedSingleSchemaMarkupAt = GetSettings::getPostMeta($post->ID, $this->schemaMarkupSavedAt);
        if ( !$savedSingleSchemaMarkupAt || $savedSingleSchemaMarkupAt < $savedSchemaMarkupAt ){
	        $schemaSettings = $this->generalSchemaMarkup($post);
        }else{
	        $schemaSettings = GetSettings::getPostMeta($post->ID, $this->schemaKey);
        }
		?>
		<script type="application/ld+json">
			<?php echo $schemaSettings; ?>
		</script>
		<?php
	}
}