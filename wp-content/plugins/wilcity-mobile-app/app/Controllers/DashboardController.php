<?php

namespace WILCITY_APP\Controllers;

use WilokeListingTools\Controllers\DashboardController as ThemeDashboardController;


class DashboardController extends JsonSkeleton {
	use VerifyToken;
	use ParsePost;

	public function __construct() {
		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX . '/v2', '/get-dashboard-navigator', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getDashboardNavigator' ),
			) );
		} );
	}

	public function getDashboardNavigator(){
		$oToken = $this->verifyToken();
		if ( !$oToken ){
			return $this->tokenExpiration();
		}
		$oToken->getUserID();

		$aNavigator = ThemeDashboardController::getNavigation($oToken->userID);
		unset($aNavigator['dashboard']);
		unset($aNavigator['billings']);
		$aNavigator = array_values($aNavigator);

		return array(
			'status'    => 'success',
			'oResults'  => $aNavigator
		);
	}
}