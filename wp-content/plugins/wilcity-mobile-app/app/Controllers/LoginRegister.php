<?php

namespace WILCITY_APP\Controllers;

use ReallySimpleJWT\Token;
use WilokeListingTools\Controllers\DashboardController;
use WilokeListingTools\Controllers\RegisterLoginController;
use WilokeListingTools\Controllers\SearchFormController;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Frontend\User;
use ReallySimpleJWT\TokenBuilder;

class LoginRegister extends JsonSkeleton {
	use BuildToken;
	use VerifyToken;
	use ParsePost;

	public function __construct() {
		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX . '/v2', '/auth', array(
				'methods'  => 'POST',
				'callback' => array( $this, 'authentication' ),
			) );
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX . '/v2', '/signup', array(
				'methods'  => 'POST',
				'callback' => array( $this, 'signUp' ),
			) );
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX . '/v2', '/update-password', array(
				'methods'  => 'POST',
				'callback' => array( $this, 'updatePassword' ),
			) );
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX . '/v2', '/is-token-living', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'isTokenLiving' ),
			) );
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX . '/v2', '/get-signup-fields', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getSingupFields' ),
			) );
		} );

		add_action('after_password_reset', array($this, 'afterPasswordReset'), 10);
		add_action('wilcity/user/after_reset_password', array($this, 'afterPasswordReset'), 10);
	}

	public function getSingupFields(){
		$aThemeOptions = \Wiloke::getThemeOptions(true);
		return array(
			'status' => 'success',
			'oFields'=> array(
				array(
					'type' => 'text',
					'key' => 'username',
					'label' => 'username',
					'required' => true,
                    'validationType' => 'username'
				),
				array(
					'type' => 'text',
					'key' => 'email',
					'label' => 'email',
					'required' => true,
					'validationType' => 'email'
				),
				array(
					'type' => 'password',
					'key' => 'password',
					'label' => 'password',
					'required' => true,
					'validationType' => 'password'
				),
				array(
					'type' => 'checkbox2',
					'key' => 'isAgreeToPrivacyPolicy',
					'label' => isset($aThemeOptions['mobile_policy_label']) ? $aThemeOptions['mobile_policy_label'] : 'Agree To our Policy Privacy',
					'required' => true,
					'link'  => get_permalink($aThemeOptions['mobile_policy_page']),
					'validationType' => 'agreeToPolicy'
				),
				array(
					'type' => 'checkbox2',
					'key' => 'isAgreeToTermsAndConditionals',
					'label' => isset($aThemeOptions['mobile_term_label']) ? $aThemeOptions['mobile_term_label'] : 'Agree To our Terms and Conditional',
					'required' => true,
					'link' => get_permalink($aThemeOptions['mobile_term_page']),
					'validationType' => 'agreeToTerms'
				)
			)
		);
	}

	public function signUp(){
		$oToken = $this->verifyToken();
		if ( $oToken ){
			return array(
				'status' => 'error',
				'msg'    => 'youAreLoggedInAlready'
			);
		}

		$aData = $this->parsePost();
		$aData = wp_parse_args($aData, array(
			'email' => '',
			'username' => '',
			'password' => '',
			'isAgreeToPrivacyPolicy' => false,
			'isAgreeToTermsAndConditionals'=>false
		));

		do_action('wilcity/before/register', $aData);

		$aThemeOptions = \Wiloke::getThemeOptions();
		if ( !isset($aThemeOptions['toggle_register']) || $aThemeOptions['toggle_register'] == 'disable' ){
			return array(
				'status' => 'error',
				'msg'    => 'disabledLogin'
			);
		}

		if ( !$aData['isAgreeToPrivacyPolicy'] || !$aData['isAgreeToTermsAndConditionals'] ){
			return array(
				'status' => 'error',
				'msg'    => 'needAgreeToTerm'
			);
		}

		if ( empty($aData['username']) || empty($aData['email']) || empty($aData['password']) ){
			return array(
				'status' => 'error',
				'msg' => 'needCompleteAllRequiredFields'
			);
		}

		if ( !is_email($aData['email']) ){
			return array(
				'status' => 'error',
				'msg' => 'invalidEmail'
			);
		}

		if ( email_exists($aData['email']) ){
			return array(
				'status' => 'error',
				'msg' => 'emailExists'
			);
		}

		if ( username_exists($aData['username']) ){
			return array(
				'status' => 'error',
				'return' => 'usernameExists'
			);
		}

		$aStatus = RegisterLoginController::createNewAccount($aData);
		if ( $aStatus['status'] == 'error' ){
			return array(
				'status' => 'error',
				'return' => 'couldNotCreateAccount'
			);
		}

		if ( $aStatus['status'] == 'success' && !$aStatus['isNeedConfirm'] ){
			$successMsg = 'createdAccountSuccessfully';
		}else{
			$successMsg = $aStatus['msg'];
		}

		$token = $this->buildToken(new \WP_User($aStatus['userID']));

		return array(
			'status' => 'success',
			'msg'    => $successMsg,
			'token'  => $token,
			'oUserInfo' => array(
				'userID'        => $aStatus['userID'],
				'displayName'   => GetSettings::getUserMeta($aStatus['userID'], 'display_name'),
				'avatar'        => User::getAvatar($aStatus['userID'])
			)
		);
	}

	public function isTokenLiving(){
		$oToken = $this->verifyToken();
		if ( !$oToken ){
			return $this->tokenExpiration();
		}

		return array(
			'status' => 'success'
		);
	}

	public function updatePassword(){
		$oToken = $this->verifyToken();
		if ( !$oToken ){
			return $this->tokenExpiration();
		}

		$oToken->getUserID();

		$aData = $this->parsePost();

		if ( isset($aData['new_password']) && !empty($aData['new_password']) ){
			wp_set_password( $aData['new_password'], $oToken->userID );
			$oUser = new \WP_User($this->userID);
			do_action('wilcity/user/after_reset_password', $oUser);

			return array(
				'status' => 'success'
			);
		}
		return array(
			'status' => 'error'
		);
	}

	public function afterPasswordReset($oUser){
		$this->buildToken($oUser, '+1 seconds');
	}

	public function authentication(){
		$oValidate = $this->verifyToken();
		if ( $oValidate !== false ){
			return array(
				'status' => 'loggedIn'
			);
		}

		$aData = $this->parsePost();
		$aError = array(
			'status' => 'error',
			'msg'    => 'invalidUserNameOrPassword'
		);

		if ( empty($aData) ){
			return $aError;
		}

		if (  !isset($aData['username']) || !isset($aData['password']) || empty($aData['username']) || empty($aData['password']) ){
			return array(
				'status' => 'error',
				'msg'    => 'invalidUserNameOrPassword'
			);
		}
		$oUser = wp_authenticate($aData['username'], $aData['password']);

		if ( is_wp_error($oUser) ){
			return array(
				'status' => 'error',
				'msg'    => 'invalidUserNameOrPassword'
			);
		}

		if ( strpos($aData['username'], '@') !== false ){
			$oUser = get_user_by('email', $aData['username']);
		}else{
			$oUser = get_user_by('login', $aData['username']);
		}

		if ( empty($oUser) || is_wp_error($oUser) ){
			return array(
				'status' => 'error',
				'msg'    => 'invalidUserNameOrPassword'
			);
		}

		$token = $this->buildToken($oUser);
		return array(
			'status'    => 'loggedIn',
			'token'     => $token,
			'oUserInfo' => array(
				'userID'        => $oUser->ID,
				'displayName'   => GetSettings::getUserMeta($oUser->ID, 'display_name'),
				'userName'      => $oUser->user_login,
				'avatar'        => User::getAvatar($oUser->ID),
				'position'      => User::getPosition($oUser->ID),
				'coverImg'      => User::getCoverImage($oUser->ID)
			),
			'oUserNav'      => array_values(DashboardController::getNavigation($oUser->ID))
		);
	}
}