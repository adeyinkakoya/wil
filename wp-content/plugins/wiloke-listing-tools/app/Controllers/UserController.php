<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Frontend\User;

class UserController extends Controller {
	public function __construct() {
		add_action('wp_ajax_wilcity_fetch_user_profile', array($this, 'fetchUserProfile'));
	}

	public function fetchUserProfile(){
		$this->middleware(array('isUserLoggedIn'), array());
		$userID = get_current_user_id();

		$aThemeOptions = \Wiloke::getThemeOptions();

		wp_send_json_success(array(
			'display_name'  => User::getField('display_name', $userID),
			'avatar'        => User::getAvatar($userID),
			'position'      => User::getPosition($userID),
			'profile_description' => isset($aThemeOptions['dashboard_profile_description']) ? $aThemeOptions['dashboard_profile_description'] : '',
			'author_url'    => get_author_posts_url($userID)
		));
	}
}