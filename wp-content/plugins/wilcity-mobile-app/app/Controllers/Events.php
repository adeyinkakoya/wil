<?php

namespace WILCITY_APP\Controllers;


use WilokeListingTools\Controllers\EventController;
use WilokeListingTools\Controllers\SearchFormController;
use WilokeListingTools\Framework\Helpers\GetSettings;

class Events extends JsonSkeleton {
	private $postType = 'event';

	public function __construct() {
		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', 'events', array(
				'methods'   => 'GET',
				'callback'  => array($this, 'getEvents')
			));
		});
	}

	public function getEvents($aData){
		$page = isset($aData['page']) ? abs($aData['page']) : 1;
		$aQuery = $aData;
		$aQuery['postType'] = $this->postType;
		$aQuery['page'] = $page;
		if ( !isset($aData['postsPerPage']) || (abs($aData['postsPerPage']) > 100) ){
			$aData['postsPerPage'] = 18;
		}
		if ( isset($aData['lat']) && !empty($aData['lat']) && isset($aData['lng']) && !empty($aData['lng']) ){
			$aQuery['oAddress'] = array(
				'lat'    =>  $aData['lat'],
				'lng'    =>  $aData['lng'],
				'address'=> isset($aData['radius']) ? $aData['radius'] : 10,
				'unit'   => isset($aData['unit']) ? $aData['unit'] : 'km',
			);
		}

		$aArgs = SearchFormController::buildQueryArgs($aQuery);
		$query = new \WP_Query($aArgs);

		if ( $query->have_posts() ){
			$aPosts = array();
			while ($query->have_posts()){
				$query->the_post();
				$aPost = $this->listingSkeleton($query->post);
				$aPosts[] = $aPost;
			}

			$aReturn['status'] = 'success';
			if ( $page < $query->max_num_pages ){
				$aReturn['next'] = $nextPage = $page+1;
			}else{
				$aReturn['next'] = false;
			}
			$aReturn['oResults'] = $aPosts;
			return $aReturn;
		}else{
			return array(
				'status' => 'error',
				'msg'    => esc_html__('No posts found', WILCITY_MOBILE_APP)
			);
		}
	}
}