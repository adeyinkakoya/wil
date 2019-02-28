<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Routing\Controller;

class GuardController extends Controller {
	public function __construct() {
		add_action('admin_init', array($this, 'preventAccessAdmin'));
	}

	public function preventAccessAdmin(){
		if ( defined('DOING_AJAX') && DOING_AJAX ){
			return true;
		}

		if ( is_user_logged_in() ){
			if ( !current_user_can('edit_theme_options') ){
				wp_redirect(home_url('/'));
				exit();
			}
		}
	}
}