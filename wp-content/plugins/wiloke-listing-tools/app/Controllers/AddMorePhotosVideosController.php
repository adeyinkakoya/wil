<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Routing\Controller;

class AddMorePhotosVideosController extends Controller {
    public static $aPlanSettings = null;
    protected $aRawGallery;
	protected $listingID;
	protected $aGallery;
	protected $aVideos;

	use InsertGallery;
	use SetVideo;

	public function __construct() {
		add_action('wilcity/footer/vue-popup-wrapper', array($this, 'printFooter'));
		add_action('wp_ajax_fetch_photos_of_listing', array($this, 'fetchPhotos'));
		add_action('wp_ajax_nopriv_fetch_photos_of_listing', array($this, 'fetchPhotos'));
		add_action('wp_ajax_fetch_videos_of_listing', array($this, 'fetchVideos'));
		add_action('wp_ajax_nopriv_fetch_videos_of_listing', array($this, 'fetchVideos'));
		add_action('wp_ajax_update_gallery_and_videos', array($this, 'updateGalleryAndVideos'));
	}

	protected function getPlanSettings(){
		$planID = GetSettings::getPostMeta($this->listingID, 'belongs_to');
		if ( isset(self::$aPlanSettings[$planID]) ){
		    return self::$aPlanSettings[$planID];
        }

        if ( empty($planID) ){
	        self::$aPlanSettings[$planID] = array();
        }else{
	        self::$aPlanSettings[$planID] = GetSettings::getPlanSettings($planID);
        }

		return self::$aPlanSettings[$planID];
    }

	protected function parseVideo(){
		$this->aVideos = $_POST['videos'];
		if ( !current_user_can('edit_theme_options') ){
			$aPlanSettings = $this->getPlanSettings();
			if ( $aPlanSettings && $aPlanSettings['toggle_videos'] == 'disable' ){
				return true;
			}

			if ( !empty($this->aVideos) ){
				if ( !empty($aPlanSettings['maximumVideos']) ){
					$this->aVideos = array_splice($this->aVideos, 0, $aPlanSettings['maximumVideos']);
				}
			}
		}

        $this->setVideos();

		return true;
	}

	protected function parseGallery(){
		$this->aRawGallery = $_POST['gallery'];
		if ( !current_user_can('edit_theme_options') ){
			$aPlanSettings = $this->getPlanSettings();
			if ( !empty($aPlanSettings) && $aPlanSettings['toggle_gallery'] == 'disable' ){
			    return true;
            }

            if ( !empty($this->aRawGallery) ){
			    if ( !empty($aPlanSettings['maximumGalleryImages']) ){
				    $this->aRawGallery = array_splice($this->aRawGallery, 0, $aPlanSettings['maximumGalleryImages']);
                }
            }
		}
		$this->insertGallery();

		return true;
	}

	public function updateGalleryAndVideos(){
		$this->listingID = $_POST['listingID'];

		$this->middleware(['isListingBeingReviewed', 'isPostAuthor'], array(
            'postID' => $this->listingID,
            'passedIfAdmin' => true
        ));

		$this->parseGallery();
		$this->parseVideo();

		wp_send_json_success(array(
			'msg' => esc_html__('Congratulations! Your update has been successfully', 'wiloke-listing-tools')
		));
	}

	public function fetchVideos(){
	    $aReturn = array();
	    $this->listingID = $_POST['listingID'];
        $aPlanSettings = $this->getPlanSettings();

        if ( !empty($aPlanSettings) && $aPlanSettings['toggle_videos'] == 'disable' ){
	        $aReturn = array('msg' => esc_html__('The Add Video is not supported by this plan.', 'wiloke-listing-tools'));
	        wp_send_json_error($aReturn);
        }

		$aRawVideos = GetSettings::getPostMeta($this->listingID, 'video_srcs');

        if ( !empty($aRawVideos) ){
	        $aReturn = array('videos'=>$aRawVideos);
	        $aReturn = $aReturn + array('oPlanSettings'=>$aPlanSettings);
        }
		wp_send_json_success($aReturn);
    }

	public function fetchPhotos(){
		$this->listingID = $_POST['listingID'];
		$aPlanSettings = $this->getPlanSettings();

		if ( $aPlanSettings && $aPlanSettings['toggle_gallery'] == 'disable' ){
			$aReturn = array('msg' => esc_html__('The add photos feature is not supported by this plan.', 'wiloke-listing-tools'));
			wp_send_json_error($aReturn);
		}

	    $aRawPhotos = GetSettings::getPostMeta($this->listingID, 'gallery');

	    $aReturn = array();
		$aPhotos = array();
	    if ( !empty($aRawPhotos) ){
	        foreach ($aRawPhotos as $id => $src){
		        $aPhoto['imgID']  = $id;
		        $aPhoto['src'] = $src;
		        $aPhotos['images'][] = $aPhoto;
            }

		    $aReturn = $aPhotos;
        }

        if ( !current_user_can('edit_theme_options') ){
	        $aReturn = $aReturn + array('oPlanSettings'=>$aPlanSettings);
        }

        wp_send_json_success($aReturn);
    }

	public function printFooter(){
	    $aPostTypes = General::getPostTypeKeys(false, true);
	    if ( !is_singular($aPostTypes) ){
	        return '';
        }
	?>
		<wiloke-add-photos-videos :settings="{}"></wiloke-add-photos-videos>
	<?php
	}
}