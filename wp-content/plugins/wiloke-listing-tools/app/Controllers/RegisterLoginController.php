<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Frontend\User;
use WilokeListingTools\Register\WilokeSubmissionConfiguration;

class RegisterLoginController extends Controller {
    protected static $canRegister = null;
    private static $fbMetaKey = 'facebook_user_id';

	public function __construct() {
		add_action('wilcity/header/after-menu', array($this, 'printRegisterLoginButton'), 20);
		add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
		add_action('wilcity/footer/vue-popup-wrapper', array($this, 'printFooterCode'));
		add_filter('logout_redirect', array($this, 'modifyLogoutRedirectUrl'), 10);

		//Ajax
		add_action('wp_ajax_nopriv_wilcity_login', array($this, 'handleLogin'));
		add_action('wp_ajax_nopriv_wilcity_register', array($this, 'handleRegister'));
		add_action('wp_ajax_nopriv_wilcity_reset_password', array($this, 'resetPassword'));
		add_action('wp_ajax_wilcity_agree_become_to_author', array($this, 'handleBecomeAnAuthorSubmission'));

		add_action('show_admin_bar', array($this, 'hideAdminBar'));
//		add_action('init', array($this, 'handleRegister'));
        add_action('wilcity/print-need-to-verify-account-message', array($this, 'printNeedToVerifyAccount'));
        add_action('wp_ajax_nopriv_wilcity_send_retrieve_password', array($this, 'sendRetrievePassword'));
        add_action('wp_ajax_nopriv_wilcity_update_password', array($this, 'updatePassword'));
		add_filter('lostpassword_redirect', array($this, 'modifyLostPasswordUrl'), 10, 2);
		add_action('wiloke/claim/approved', array($this, 'addClaimerToWilokeSubmissionGroup'));
        add_filter('wilcity-login-with-social/after_login_redirect_to', array($this, 'afterLoggedInWithSocialWillRedirectTo'), 10, 2);
        add_action('wp_ajax_nopriv_wilcity_facebook_login', array($this, 'loginWithFacebook'));
	}

	public static function createNewAccount($aData, $isSocialLogin=false){
		$aThemeOptions = \Wiloke::getThemeOptions();
		$userID = wp_create_user($aData['username'], $aData['password'], $aData['email']);
		if ( empty($userID) || is_wp_error($userID) ){
			return array(
				'status' => 'error',
				'msg'    => 'couldNotCreateAccount'
			);
		}

		update_user_meta($userID, 'agree_with_privacy', $aData['isAgreeToPrivacyPolicy']);
		update_user_meta($userID, 'agree_with_terms', $aData['isAgreeToTermsAndConditionals']);

		update_user_meta($userID, 'user_ip', General::clientIP());

		wp_new_user_notification($userID, null, 'admin');
		if ( GetWilokeSubmission::getField('toggle_become_an_author') == 'disable' ){
			self::addSubmissionRole($userID, true);
		}else if (GetWilokeSubmission::getField('toggle_become_an_author') == 'enable'){
			$oSubscriber = (object)array(
				'ID' => $userID,
				'role' => 'subscriber'
			);
			wp_update_user($oSubscriber);
		}

		$needConfirm = !$isSocialLogin &&  ( isset($aThemeOptions['toggle_confirmation']) && $aThemeOptions['toggle_confirmation'] == 'enable' );
		if ( $needConfirm ){
			SetSettings::setUserMeta($userID, 'confirmed', false);
			$successMsg  = $aThemeOptions['confirmation_notification'];
			$oUser = get_user_by('id', $userID);
			self::insertActivationKey($oUser);
			return array(
				'status' => 'success',
				'isNeedConfirm' => true,
				'userID' => $userID,
				'msg'=>$successMsg
			);
		}else{
			SetSettings::setUserMeta($userID, 'confirmed', true);
			return array(
				'status' => 'success',
				'userID' => $userID,
				'isNeedConfirm' => false
			);
		}
	}

	public function afterLoggedInWithSocialWillRedirectTo($redirectTo, $isFirstTimeLoggedIn){
		$aThemeOptions = \Wiloke::getThemeOptions(true);
	    if ( $isFirstTimeLoggedIn ){
		    $redirectTo = isset($aThemeOptions['created_account_redirect_to']) && !empty($aThemeOptions['created_account_redirect_to']) && $aThemeOptions['created_account_redirect_to'] != 'self_page' ? urlencode(get_permalink($aThemeOptions['created_account_redirect_to'])) : 'self';
        }else{
		    $redirectTo = isset($aThemeOptions['login_redirect_type']) && !empty($aThemeOptions['login_redirect_type']) && $aThemeOptions['login_redirect_type'] !== 'self_page' ? urlencode(get_permalink($aThemeOptions['login_redirect_type'])) : 'self';
        }

        return $redirectTo;
    }

	private function getUserBy( $aUser ) {

		// if the user is logged in, pass curent user
		if( is_user_logged_in() ){
			return wp_get_current_user();
		}

		$user_data = get_user_by('email', $aUser['email']);

		if( !$user_data ) {
			$users = get_users(
				array(
					'meta_key'    => self::$fbMetaKey,
					'meta_value'  => $aUser['fb_user_id'],
					'number'      => 1,
					'count_total' => false
				)
			);
			if( is_array( $users ) ){
				$user_data = reset( $users );
			}
		}
		return $user_data;
	}

	/**
	 * Simple pass sanitazing functions to a given string
	 * @param $username
	 *
	 * @return string
	 */
	private function cleanUsername( $username ) {
		return sanitize_title( str_replace('_','-', sanitize_user(  $username  ) ) );
	}

	/**
	 * Generated a friendly username for facebook users
	 * @param $user
	 *
	 * @return string
	 */
	private function generateUsername( $user ) {
		global $wpdb;

		$username = '';
		if( !empty( $user['first_name'] ) && !empty( $user['last_name'] ) ){
			$username = $this->cleanUsername( trim( $user['first_name'] ) .'-'. trim( $user['last_name'] ) );
		}

		if( !validate_username( $username ) ) {
			$username = '';
			// use email
			$email    = explode( '@', $user['email'] );
			if( validate_username( $email[0] ) ){
				$username = $this->cleanUsername( $email[0] );
			}
		}

		// User name can't be on the blacklist or empty
		$illegal_names = get_site_option( 'illegal_names' );
		if ( empty( $username ) || in_array( $username, (array) $illegal_names ) ) {
			// we used all our options to generate a nice username. Use id instead
			$username = 'fbl_' . $user['id'];
		}

		// "generate" unique suffix
		$suffix = $wpdb->get_var( $wpdb->prepare(
			"SELECT 1 + SUBSTR(user_login, %d) FROM $wpdb->users WHERE user_login REGEXP %s ORDER BY 1 DESC LIMIT 1",
			strlen( $username ) + 2, '^' . $username . '(-[0-9]+)?$' ) );

		if( !empty( $suffix ) ) {
			$username .= "-{$suffix}";
		}

		return $username;
	}

	public function loginWithFacebook(){
        if ( !\WilokeThemeOptions::isEnable('fb_toggle_login') ){
            wp_send_json_error(
                array(
                    'msg' => esc_html__('We do not support this feature.', 'wiloke-listing-tools')
                )
            );
        }

        $aThemeOptions = \Wiloke::getThemeOptions(true);

		$access_token = isset( $_POST['fb_response']['authResponse']['accessToken'] ) ? $_POST['fb_response']['authResponse']['accessToken'] : '';

		// Get user from Facebook with given access token
		$fb_url = add_query_arg(
			array(
				'fields'            =>  'id,first_name,last_name,email,link,picture',
				'access_token'      =>  $access_token,
			),
			'https://graph.facebook.com/v2.4/'.$_POST['fb_response']['authResponse']['userID']
		);
		//
		if( ! empty( $aThemeOptions['fb_app_secret'] ) ) {
			$appsecret_proof = hash_hmac('sha256', $access_token, $aThemeOptions['fb_app_secret'] );
			$fb_url = add_query_arg(
				array(
					'appsecret_proof' => $appsecret_proof
				),
				$fb_url
			);
		}

		$fb_response = wp_remote_get( esc_url_raw( $fb_url ), array( 'timeout' => 30 ) );

		if( is_wp_error( $fb_response ) ){
			wp_send_json_error(
				array(
					'message' => $fb_response->get_error_message()
				)
			);
		}

		$fb_user = apply_filters( 'wiloke-login-with-social/facebook/auth_data',json_decode( wp_remote_retrieve_body( $fb_response ), true ) );

		//check if user at least provided email
		if( empty( $fb_user['email'] ) ){
			wp_send_json_error(
				array(
					'message' => esc_html__('We need your email in order to continue. Please try loging again.', 'wiloke-listing-tools')
				)
			);
		}

		// Map our FB response fields to the correct user fields as found in wp_update_user
		$user = apply_filters( 'wiloke-login-with-social/facebook/user_data_login', array(
			'fb_user_id' => $fb_user['id'],
			'first_name' => $fb_user['first_name'],
			'last_name'  => $fb_user['last_name'],
			'email' => $fb_user['email'],
			'user_url'   => '',
			'password'  => wp_generate_password(),
		));

		do_action( 'wiloke-login-with-social/facebook/before_login', $user);

		if ( empty( $user['fb_user_id'] ) ){
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Invalid User', 'login-with-social' )
				)
			);
		}

		$user_obj = $this->getUserBy( $user );

		$isFirstTimeLogin = false;
		if ( $user_obj ){
			$user_id = $user_obj->ID;
			if( empty( $user_obj->user_email ) && !email_exists($user['user_email']) ){
				wp_update_user(
                    (object)array( 'ID' => $user_id, 'user_email' => $user['user_email'] )
                );
			}
		}else{
			$user['username'] = $this->generateUsername($fb_user);
			$user['isAgreeToPrivacyPolicy'] = 'yes';
			$user['isAgreeToTermsAndConditionals'] = 'yes';
			$aStatus = self::createNewAccount($user, true);

			if( $aStatus['status'] == 'success' ) {
				SetSettings::setUserMeta($aStatus['userID'], self::$fbMetaKey, $user['fb_user_id']);
				do_action('wilcity-login-with-social/after_insert_user', $aStatus['userID'], $fb_user, 'facebook');
				$isFirstTimeLogin = true;
				$user_id = $aStatus['userID'];
				if ( isset($fb_user['picture']) ){
					SetSettings::setUserMeta($user_id, 'avatar', $fb_user['picture']['data']['url']);
                }

				$aSocialNetworks['facebook'] =  esc_url('https://www.facebook.com/profile.php?id='.$fb_user['id']);
				SetSettings::setUserMeta($user_id, 'social_networks', $aSocialNetworks);
				do_action('wilcity/after/created-account', $aStatus['userID'], $user['username'], false);
			}else{
			    wp_send_json_error(
			        array(
                        'msg' => esc_html__('Sorry, We could not create your account. Please try it later', 'wiloke-listing-tools')
                    )
                );
            }
		}

		$user_id = absint($user_id);
		if( $user_id ) {
			wp_set_auth_cookie( $user_id, true );
		}

		wp_send_json_success(
			array(
				'redirectTo' => apply_filters('wilcity-login-with-social/after_login_redirect_to', '', $isFirstTimeLogin)
			)
		);
    }

	public static function canRegister(){
	    if ( self::$canRegister !== null ){
	        return self::$canRegister;
        }

	    self::$canRegister = get_option('users_can_register');
		self::$canRegister = self::$canRegister == 0 ? false : true;

	    return self::$canRegister;
    }

	public function addClaimerToWilokeSubmissionGroup($claimerID){
		$user_meta  =   get_userdata($claimerID);
		$aUserRoles =   $user_meta->roles;
        if ( in_array('subscriber', $aUserRoles) ){
	        self::addSubmissionRole($claimerID);
        }
    }

    public function modifyLostPasswordUrl($url){
	    global $wiloke;
	    if ( isset($wiloke->aThemeOptions['reset_password_page']) && !empty($wiloke->aThemeOptions['reset_password_page']) ){
	        if ( get_post_status($wiloke->aThemeOptions['reset_password_page']) == 'publish' ){
		        return get_permalink($wiloke->aThemeOptions['reset_password_page']);
            }
        }
	    return $url;
    }

	public function updatePassword(){
		if ( !isset( $_POST['newPassword'] ) ) {
			wp_send_json_error(esc_html__('Please enter your new password', 'wiloke-listing-tools'));
		}
		$aCheckResetPWStatus = check_password_reset_key( $_POST['rpKey'], $_POST['user_login'] );

        if( is_wp_error($aCheckResetPWStatus) || !$aCheckResetPWStatus){
	        wp_send_json_error(esc_html__('The reset key has been expired', 'wiloke-listing-tools'));
        }

		$oUser = get_user_by( 'login', sanitize_text_field($_POST['user_login']) );

	    if ( is_wp_error($oUser) || empty($oUser) ){
		    wp_send_json_error(esc_html__('This username does not exist.', 'wiloke-listing-tools'));
        }
		wp_set_password( $_POST['newPassword'], $oUser->ID );

		wp_send_json_success(esc_html__('Congratulations! The new password has been updated successfully. Please click on Login button to Log into the website', 'wiloke-listing-tools'));
	}

	public function sendRetrievePassword(){
		$errors = new \WP_Error();

		if ( empty( $_POST['user_login'] ) || ! is_string( $_POST['user_login'] ) ) {
			$errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or email address.', 'wiloke-listing-tools'));
		} elseif ( strpos( $_POST['user_login'], '@' ) ) {
			$user_data = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
			if ( empty( $user_data ) ){
				$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.', 'wiloke-listing-tools'));
            }
		} else {
			$login = trim($_POST['user_login']);
			$user_data = get_user_by('login', $login);
		}

		do_action( 'lostpassword_post', $errors );

		if ( $errors->get_error_code() ){
		    wp_send_json_error($errors->get_error_message());
        }

		if ( !$user_data ) {
			$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or email.', 'wiloke-listing-tools'));
			wp_send_json_error($errors->get_error_message());
		}

		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		$key = get_password_reset_key( $user_data );

		if ( is_wp_error( $key ) ) {
			wp_send_json_error(esc_html__('Oops! We could not generate reset key. Please contact the administrator to report this issue', 'wiloke-listing-tools'));
		}

		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$aThemeOptions = \Wiloke::getThemeOptions(true);
		if ( isset($aThemeOptions['reset_password_page']) || !empty($aThemeOptions['reset_password_page']) || get_post_status($aThemeOptions['reset_password_page']) == 'publish' ){
			$resetPasswordURL = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' );
		}else{
			$resetPasswordURL = get_permalink($aThemeOptions['reset_password_page']);
			$resetPasswordURL = add_query_arg(
                array(
                    'action' => 'rp',
                    'key'    => $key,
                    'login'  => rawurlencode( $user_login )
                ),
				$resetPasswordURL
            );
        }

		$message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
		/* translators: %s: site name */
		$message .= sprintf( __( 'Site Name: %s'), $site_name ) . "\r\n\r\n";
		/* translators: %s: user login */
		$message .= sprintf( __( 'Username: %s'), $user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		$message .= '<' . $resetPasswordURL . ">\r\n";

		/* translators: Password reset email subject. %s: Site name */
		$title = sprintf( __( '[%s] Password Reset' ), $site_name );
		$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );
		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

		if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ){
			wp_send_json_error( __('The email could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.', 'wiloke-listing-tools') );
        }

        wp_send_json_success(esc_html__('We sent an email to you with a link to get back into your account. Please check your mailbox and click on the reset link.', 'wiloke-listing-tools'));
    }

	public function printNeedToVerifyAccount(){
		\WilokeMessage::message(
		    array(
                'msg'   => esc_html__('We have sent an email with a confirmation link to your email address. In order to complete the sign-up process, please click the confirmation link.
If you do not receive a confirmation email, please check your spam folder. Also, please verify that you entered a valid email address in our sign-up form.', 'wiloke-listing-tools'),
                'status' => 'danger',
                'msgIcon'   => 'la la-bullhorn',
                'hasMsgIcon'=> true
            )
        );
    }

	public function verifyConfirmation(){
        if ( !isset($_REQUEST['confirm_account']) ){
            return false;
        }
    }

	private static function addSubmissionRole($userID, $isUseRoleDefault=false){
		$oGetUser = new \WP_User($userID);
		$defaultRole = get_option('default_role');

		if ( $isUseRoleDefault ){
		    if ( $defaultRole == 'administrator' ){
			    $oGetUser->remove_role('administrator');
			    $oGetUser->add_role('subscriber');
            }

            return true;
        }

		$oGetUser->remove_role('subscriber');

		if ( $defaultRole == 'seller' ){
		    $oGetUser->add_role('seller');
		    $oGetUser->add_role('contributor');
		    if ( dokan_get_option( 'new_seller_enable_selling', 'dokan_selling' ) != 'off' ){
			    update_user_meta( $userID, 'dokan_enable_selling', 'yes' );
            }
        }else{
			$oGetUser->add_role('contributor');
        }
    }

	public function handleBecomeAnAuthorSubmission(){
	    $this->middleware(['iAgreeToPrivacyPolicy', 'iAgreeToTerms'], array(
            'agreeToTerms' => $_POST['agreeToTerms'],
            'agreeToPrivacyPolicy'  => $_POST['agreeToPrivacyPolicy']
        ));

	    if ( User::canSubmitListing() ){
	        wp_send_json_success();
        }

        self::addSubmissionRole(get_current_user_id());

	    do_action('wilcity/became-an-author', get_current_user_id());
	    wp_send_json_success();
    }

	public function hideAdminBar($status){
	    if ( !current_user_can('edit_theme_options') ){
	        return false;
        }

        return $status;
    }

	public function modifyLogoutRedirectUrl($logout_url){
		return home_url('/');
    }

    public function resetPassword(){
	    do_action('wilcity/before/register', $_POST);
	    if ( empty($_POST['username']) ) {
		    wp_send_json_error(array(
		       'msg' => esc_html__('Please provide your username or email address.', 'wiloke-listing-tools')
            ));
	    } else if ( strpos( $_POST['username'], '@' ) ) {
	        $email = trim($_POST['username']);
		    $oUserData = get_user_by( 'email', $email);
		    if ( empty( $oUserData ) ){
			    wp_send_json_error(array(
				    'msg' => esc_html__('Sorry, We found no account matched this email.', 'wiloke-listing-tools')
			    ));
            }

	    } else {
		    $login = trim($_POST['username']);
		    $oUserData = get_user_by('login', $login);

		    if ( empty( $oUserData ) ){
			    wp_send_json_error(array(
				    'msg' => esc_html__('Sorry, We found no account matched this username.', 'wiloke-listing-tools')
			    ));
		    }
	    }

	    $userEmail = $oUserData->user_email;
	    $userLogin = $oUserData->user_login;

	    $key = get_password_reset_key($oUserData);

	    if ( is_wp_error($key) ) {
		    return $key;
	    }

	    $aThemeOptions = \Wiloke::getThemeOptions(true);
	    if ( isset($aThemeOptions['reset_password_page']) && !empty($aThemeOptions['reset_password_page']) && get_post_status($aThemeOptions['reset_password_page']) == 'publish' ){
		    $resetURL = get_permalink($aThemeOptions['reset_password_page']);
		    $resetURL = add_query_arg(
                array(
                    'key'   => $key,
                    'login' => rawurlencode($userLogin),
                    'action'=> 'rp'
                ),
			    $resetURL
            );
        }else{
            $resetURL = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($userLogin), 'login');
        }

	    $message = esc_html__('Someone has requested a password reset for the following account:', 'wiloke-listing-tools') . "\r\n\r\n";
	    $message .= network_home_url( '/' ) . "\r\n\r\n";
	    $message .= sprintf(__('Username: %s'), $userLogin) . "\r\n\r\n";
	    $message .= esc_html__('If this was a mistake, just ignore this email and nothing will happen.', 'wiloke-listing-tools') . "\r\n\r\n";
	    $message .= esc_html__('To reset your password, visit the following address:', 'wiloke-listing-tools') . "\r\n\r\n";
	    $message .= '<' . $resetURL . ">\r\n";

	    if ( is_multisite() ) {
		    $blogname = get_network()->site_name;
	    } else {
		    /*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
		    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	    }

	    /* translators: Password reset email subject. 1: Site name */
	    $title = sprintf( __('[%s] Password Reset', 'wiloke-listing-tools'), $blogname );
	    if ( $message && !wp_mail( $userEmail, wp_specialchars_decode( $title ), $message ) ){
		    wp_send_json_error(
                array(
                    'msg' => __('The email could not be sent.', 'wiloke-listing-tools') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.', 'wiloke-listing-tools')
                )
            );
	    }
        $aParseMail = explode('@', $userEmail);
        $mailDomain = end($aParseMail);
	    $totalLength = count($aParseMail[0]);

        if ( $totalLength > 5 ){
            $truncateIndex = 4;
        }else{
	        $truncateIndex = $totalLength - 2;
        }

	    $escapeEmail = substr($aParseMail[0],0,$truncateIndex) . '***' . '@' .$mailDomain;
	    wp_send_json_success(
		    array(
			    'msg' => sprintf(esc_html__('We just mailed a reset link to %s. Please check your mail box / spam box and click on that link.', 'wiloke-listing-tools'), $escapeEmail),
                'isFocusHideForm' => true
		    )
	    );
    }

    protected static function generateHashPassword(){
	    $key = wp_generate_password( 20, false );
	    if ( empty( $wp_hasher ) ) {
		    require_once ABSPATH . WPINC . '/class-phpass.php';
		    $wp_hasher = new \PasswordHash( 8, true );
	    }
	    $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
	    if ( strpos($hashed, '.') === false ){
	        return $hashed;
        }

        return self::generateHashPassword();
    }

    protected static function insertActivationKey($oUser){
        global $wpdb;
        $hashed = self::generateHashPassword();
	    $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $oUser->user_login ) );
    }

    public function handleRegister(){
	    $this->middleware(['canRegister'], array());

	    do_action('wilcity/before/register', $_POST);

	    $aThemeOptions = \Wiloke::getThemeOptions();
	    if ( !isset($aThemeOptions['toggle_register']) || $aThemeOptions['toggle_register'] == 'disable' ){
	        wp_send_json_error(array(
                'msg' => esc_html__('ERROR: Sorry, We are temporary disable registration feature', 'wiloke-listing-tools')
            ));
        }

        if ( $_POST['isAgreeToPrivacyPolicy'] == 'no' || $_POST['isAgreeToTermsAndConditionals'] == 'no' ){
	        wp_send_json_error(array(
		        'msg' => esc_html__('ERROR: Sorry, To create an account on our site, you have to agree to our team conditionals and our privacy policy.', 'wiloke-listing-tools')
	        ));
        }

        if ( empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']) ){
	        wp_send_json_error(array(
		        'msg' => esc_html__('ERROR: Please complete all required fields.', 'wiloke-listing-tools')
	        ));
        }

        if ( !is_email($_POST['email']) ){
	        wp_send_json_error(array(
		        'msg' => esc_html__('ERROR: Invalid email address.', 'wiloke-listing-tools')
	        ));
        }

	    if ( email_exists($_POST['email']) ){
		    wp_send_json_error(array(
			    'msg' => esc_html__('ERROR: An account with this email already exists on the website.', 'wiloke-listing-tools')
		    ));
	    }

	    if ( username_exists($_POST['username']) ){
		    wp_send_json_error(array(
			    'msg' => esc_html__('ERROR: Sorry, The username is not available. Please with another username.', 'wiloke-listing-tools')
		    ));
	    }

        $aStatus = self::createNewAccount($_POST);

	    if ($aStatus['status'] == 'error'){
            wp_send_json_error(array(
                'msg' => esc_html__('ERROR: Something went wrong', 'wiloke-listing-tools')) );
        }

	    if ( $aStatus['status'] == 'success' && !$aStatus['isNeedConfirm'] ){
		    $successMsg = esc_html__('Congratulations! Your account has been created successfully.', 'wiloke-listing-tools');
        }else{
		    $successMsg = $aStatus['msg'];
        }

	    $ssl = is_ssl() ? true : false;
	    wp_signon(array(
		    'user_login'    =>  $_POST['email'],
		    'user_password' =>  $_POST['password'],
		    'remember'      =>  false
	    ), $ssl);

	    $redirectTo = isset($aThemeOptions['created_account_redirect_to']) ? urlencode(get_permalink($aThemeOptions['created_account_redirect_to'])) : '';

	    do_action('wilcity/after/created-account', $aStatus['userID'], $_POST['username'], $aStatus['isNeedConfirm']);

	    wp_send_json_success(array(
            'redirectTo' => $redirectTo,
            'msg'        => $successMsg
        ));
	}

	public function handleLogin(){
		$aData = array(
			'user_login'    => $_POST['username'],
			'user_password' => $_POST['password'],
			'remember'      => isset($_POST['isRemember']) && $_POST['isRemember'] == 'yes'
		);
		do_action('wilcity/before/login', $aData);

		$oUser = wp_signon($aData, is_ssl());

		if ( is_wp_error($oUser) ) {
			wp_send_json_error(array(
			   'msg' => esc_html__('ERROR: Invalid username or password', 'wiloke-listing-tools')
            ));
		}

		$aThemeOption = \Wiloke::getThemeOptions();

		wp_send_json_success(array(
		    'msg' => sprintf(esc_html__('Hi %s! Nice to see you back.', 'wiloke-listing-tools'), $_POST['username']),
            'redirectTo' => isset($aThemeOption['login_redirect_type']) && $aThemeOption['login_redirect_type'] == 'specify_page' ? urlencode(get_permalink($aThemeOption['login_redirect_to'])) : 'self'
        ));
    }

    public function printFooterCode(){
	    if ( is_user_logged_in() ){
		    return false;
	    }
	    ?>
        <login-register-popup></login-register-popup>
        <?php
    }

	public function enqueueScripts(){
		if ( is_user_logged_in() ){
			return false;
		}
		global $wiloke;

		wp_localize_script('jquery-migrate', 'WILCITY_REGISTER_LOGIN', array(
            'toggleRegister'      => \WilokeThemeOptions::isEnable('toggle_register'),
			'togglePrivacyPolicy' => \WilokeThemeOptions::isEnable('toggle_privacy_policy'),
			'privacyPolicyDesc'   => $wiloke->aThemeOptions['privacy_policy_desc'],
			'toggleTermsAndConditionals' => \WilokeThemeOptions::isEnable('toggle_terms_and_conditionals'),
			'termsAndConditionals'   => $wiloke->aThemeOptions['terms_and_conditionals_desc']
		));
	}

	public function printRegisterLoginButton(){
		?>
		<div id="wilcity-login-register-controller" class="header_login__1sQ6w">
			<div class="header_btnGroup__3L61P">
                <?php if ( !is_user_logged_in() ) : ?>
                    <login-btn btn-name="<?php esc_html_e('Login', 'wiloke-listing-tools'); ?>"></login-btn>
                    <?php if ( self::canRegister() ) : ?>
                        <register-btn btn-name="<?php esc_html_e('Register', 'wiloke-listing-tools'); ?>"></register-btn>
                    <?php endif; ?>
                <?php endif; ?>
			</div>
		</div>
		<?php
	}
}