<?php
namespace WILCITY_APP\Controllers;

use WilokeListingTools\Controllers\ProfileController;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Frontend\User;

class UserController extends JsonSkeleton {
	use VerifyToken;
	use ParsePost;

	public function __construct() {
		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', 'get-profile', array(
				'methods'   => 'GET',
				'callback'  => array($this, 'getProfiles')
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', 'get-short-profile', array(
				'methods'   => 'GET',
				'callback'  => array($this, 'getShortProfile')
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', 'search-users', array(
				'methods'   => 'GET',
				'callback'  => array($this, 'searchUsers')
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', 'list-users', array(
				'methods'   => 'GET',
				'callback'  => array($this, 'getShortUsersInfo')
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', 'users/(?P<id>\d+)', array(
				'methods'   => 'GET',
				'callback'  => array($this, 'getUserShortInfo')
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', 'get-my-profile-fields', array(
				'methods'   => 'GET',
				'callback'  => array($this, 'getProfileFields')
			));
		});

		add_action( 'rest_api_init', function () {
			register_rest_route( WILOKE_PREFIX.'/v2', 'put-my-profile', array(
				'methods'   => 'POST',
				'callback'  => array($this, 'putMyProfile')
			));
		});

//		add_action( 'rest_api_init', function () {
//			register_rest_route( WILOKE_PREFIX.'/v2', 'put-my-profile', array(
//				'methods'   => 'POST',
//				'callback'  => array($this, 'putMyProfile')
//			));
//		});
	}

	private function getQuickUserInformation($oUser){
		return array(
			'userID' => $oUser->ID,
			'displayName'=> $oUser->display_name,
			'avatar' => User::getAvatar($oUser->ID)
		);
	}

	public function getShortUsersInfo(){
		$oToken = $this->verifyToken();
		if ( !$oToken ){
			return $this->tokenExpiration();
		}

		$aMessage = array(
			'status' => 'error',
			'msg'    => 'The user does not exists'
		);

		if ( !isset($_GET['s']) || empty($_GET['s'])){
			return $aMessage;
		}

		$aParsedUsers = explode(',', $_GET['s']);
		$aParsedUsers = array_map(function($userName){
			return trim($userName);
		}, $aParsedUsers);

		if( is_numeric($aParsedUsers[0]) ){
			$by = 'ID';
		}else{
			$by = 'login';
		}

		$aUserInfo = array();
		foreach ($aParsedUsers as $username){
			$oUser = get_user_by($by, $username);
			if ( empty($oUser) || is_wp_error($oUser) ){
				continue;
			}

			$aUserInfo[]  = array(
				'userID' => $oUser->ID,
				'displayName'=> $oUser->display_name,
				'avatar' => User::getAvatar($oUser->ID)
			);
		}

		return array(
			'status' => 'success',
			'aResult' => $aUserInfo
		);
	}

	public function getUserShortInfo($aData){
		$aMessage = array(
			'status' => 'error',
			'msg'    => 'The user does not exists'
		);
		if ( !isset($aData['id']) || empty($aData['id'])){
			return $aMessage;
		}

		if ( is_numeric($aData['id']) ){
			$by = 'ID';
		}else{
			$by = 'login';
		}
		$oUser = get_user_by($by, $aData['id']);
		if ( empty($oUser) || is_wp_error($oUser) ){
			return $aMessage;
		}

		return array(
			'status' => 'success',
			'oInfo'  => $this->getQuickUserInformation($oUser)
		);
	}

	public function searchUsers(){
		$oToken = $this->verifyToken();
		if ( !$oToken ){
			return $this->tokenExpiration();
		}
		$oToken->getUserID();

		$aMessage = array(
			'status' => 'error',
			'msg'    => 'We found no user info'
		);

		if ( !isset($_GET['s']) || empty($_GET['s']) ){
			return $aMessage;
		}

		$q = '*'.esc_attr(trim($_GET['s'])).'*';

		$args = array(
			'search'         => $q,
			'search_columns' => array( 'user_login', 'display_name', 'first_name', 'last_name' ),
			'exclude' => array($oToken->userID)
		);
		$oUserQuery = new \WP_User_Query( $args );
		$aUsers = $oUserQuery->get_results();
		if ( empty($aUsers) ){
			return $aMessage;
		}

		$aInfo = array();
		foreach ($aUsers as $oUser){
			if ( username_exists($oUser->login) ){
				continue;
			}

			$aInfo[] = $this->getQuickUserInformation($oUser);
		}

		return array(
			'status' => 'success',
			'aResults' => $aInfo
		);
	}

	public function putMyProfile(){
		$oToken = $this->verifyToken();
		if ( !$oToken ){
			return $this->tokenExpiration();
		}
		$oToken->getUserID();

		$aFields = $this->parsePost();
		$msg = 'profileHasBeenUpdated';
		if ( isset($aFields['oPassword']) && !empty($aFields['oPassword'])  ){
			$aRawPassword = json_decode(stripslashes($aFields['oPassword']), true);

			if ( !empty($aRawPassword['confirm_new_password']) && !empty($aRawPassword['current_password']) && !empty($aRawPassword['new_password']) ){
				$aPasswordUpdate = array(
					'newPassword' => $aRawPassword['new_password'],
					'confirmNewPassword' => $aRawPassword['confirm_new_password'],
					'currentPassword' => $aRawPassword['current_password']
				);
				$aStatus = ProfileController::updatePassword($aPasswordUpdate, $oToken->userID);
				if ( $aStatus['status'] == 'error' ){
					return array(
						'status' => 'error',
						'msg'    => 'errorUpdatePassword'
					);
				}else{
					$msg = 'passwordHasBeenUpdated';
				}
			}
		}

		if ( isset($aFields['oBasicInfo']) && !empty($aFields['oBasicInfo']) ){
			$aBasicInfo = json_decode(stripslashes($aFields['oBasicInfo']), true);
			foreach ($aBasicInfo  as $key => $aValue){
				if ( $key == 'avatar' ){
					if ( is_array($aBasicInfo['avatar']) ){
						$aBasicInfo['avatar']['value'][0]['src'] = $aBasicInfo['avatar']['base64'];
						$aBasicInfo['avatar']['value'][0]['fileName'] = $aBasicInfo['avatar']['name'];
						$aBasicInfo['avatar']['value'][0]['fileType'] = 'image/jpg';

						unset($aBasicInfo['avatar']['base64']);
						unset($aBasicInfo['avatar']['name']);
						unset($aBasicInfo['avatar']['type']);
						unset($aBasicInfo['avatar']['uri']);
					}else{
						unset($aBasicInfo['avatar']);
					}
				}else if ( $key == 'cover_image' ){
					if ( is_array($aBasicInfo['cover_image']) ){
						$aBasicInfo['cover_image']['value'][0]['src'] = $aBasicInfo['cover_image']['base64'];
						$aBasicInfo['cover_image']['value'][0]['fileName'] = $aBasicInfo['cover_image']['name'];
						$aBasicInfo['cover_image']['value'][0]['fileType'] = 'image/jpg';
						unset($aBasicInfo['cover_image']['base64']);
						unset($aBasicInfo['cover_image']['name']);
						unset($aBasicInfo['cover_image']['type']);
						unset($aBasicInfo['cover_image']['uri']);
					}else{
						unset($aBasicInfo['cover_image']);
					}
				}else{
					unset($aBasicInfo[$key]);
					if ( !empty($aValue) ){
						$aBasicInfo[$key]['value'] = $aValue;
					}
				}
			}

			$aStatus = ProfileController::updateBasicInfo($aBasicInfo, $oToken->userID);
			if ( $aStatus !== true ){
				return array(
					'status' => 'error',
					'msg'    => 'errorUpdateProfile'
				);
			}
		}

		if ( isset($aFields['oFollowAndContact']) && !empty($aFields['oFollowAndContact']) ){
			$aRawFollowAndContact = json_decode(stripslashes($aFields['oFollowAndContact']), true);
			$aFollowAndContact = array();
			foreach ($aRawFollowAndContact as $key => $aVal){
				if ( $key == 'social_networks' ){
					if ( !empty($aVal) && !empty($key) ){
						$aFollowAndContact[$key] = array(
							'value' => array()
						);
						foreach ($aVal as $aSocial){
							$aFollowAndContact[$key]['value'][] = array(
								'name' => $aSocial['id'],
								'url'  => $aSocial['url']
							);
						}
					}
				}else{
					$aFollowAndContact[$key]['value'] = $aVal;
				}
			}
			if ( !empty($aFollowAndContact) ){
				ProfileController::updateFollowAndContact($aFollowAndContact, $oToken->userID);
			}
		}

		$aNewProfiles = $this->getUserProfile($oToken->userID);
		return array(
			'status'    => 'success',
			'msg'       => $msg,
			'oResults'  => $aNewProfiles
		);
	}

	public function getProfileFields(){
		$oToken = $this->verifyToken();
		if ( !$oToken ){
			return $this->tokenExpiration();
		}

		$aFields = array(
			array(
				'heading'   => 'basicInfo',
				'key'       => 'oBasicInfo',
				'aFields'   =>  array(
					array(
						'label' => 'firstName',
						'key'   => 'first_name',
						'type'  => 'text',
						'required' => true,
						'validationType' => 'firstName',
					),
					array(
						'label' => 'lastName',
						'key'   => 'last_name',
						'type'  => 'text',
						'required' => true,
						'validationType' => 'lastName',
					),
					array(
						'label' => 'displayName',
						'key'   => 'display_name',
						'type'  => 'text',
						'required' => true,
						'validationType' => 'displayName',
					),
					array(
						'label' => 'avatar',
						'key'   => 'avatar',
						'type'  => 'file'
					),
					array(
						'label' => 'coverImg',
						'key'   => 'cover_image',
						'type'  => 'file'
					),
					array(
						'label' => 'email',
						'key'   => 'email',
						'type'  => 'text',
						'validationType' => 'email',
						'required' => true
					),
					array(
						'label' => 'position',
						'key'   => 'position',
						'type'  => 'text'
					),
					array(
						'label' => 'introYourSelf',
						'key'   => 'description',
						'type'  => 'textarea'
					),
					array(
						'label' => 'sendAnEmailIfIReceiveAMessageFromAdmin',
						'key'   => 'send_email_if_reply_message',
						'type'  => 'switch'
					)
				)
			),
			array(
				'heading'   => 'followAndContact',
				'key'       => 'oFollowAndContact',
				'aFields'   =>  array(
					array(
						'label' => 'address',
						'key'   => 'address',
						'type'  => 'text'
					),
					array(
						'label' => 'phone',
						'key'   => 'phone',
						'type'  => 'text',
						'required' => true,
						'validationType' => 'phone',
					),
					array(
						'label' => 'website',
						'key'   => 'website',
						'type'  => 'text',
						'validationType' => 'url',
						'required' => true
					),
					array(
						'label'     => 'socialNetworks',
						'key'       => 'social_networks',
						'type'      => 'social_networks',
						'options'   => $this->buildSelectOptions(\WilokeSocialNetworks::getUsedSocialNetworks())
					)
				)
			),
			array(
				'heading'   => 'changePassword',
				'key'       => 'oPassword',
				'aFields'   =>  array(
					array(
						'label' => 'currentPassword',
						'key'   => 'current_password',
						'type'  => 'password'
					),
					array(
						'label' => 'newPassword',
						'key'   => 'new_password',
						'type'  => 'password'
					),
					array(
						'label' => 'confirmNewPassword',
						'key'   => 'confirm_new_password',
						'type'  => 'password'
					)
				)
			)
		);

		return array(
			'status'    => 'success',
			'oResults'  => $aFields
		);
	}

	public function getShortProfile(){
		$oToken = $this->verifyToken();
		if ( !$oToken ){
			return $this->tokenExpiration();
		}
		$oToken->getUserID();

		$oUser = get_user_by('ID', $oToken->userID);
		return array(
			'status'   => 'success',
			'oResult'  => $this->getQuickUserInformation($oUser)
		);
	}

	public function getProfiles(){
		$oToken = $this->verifyToken();

		if ( !$oToken ){
			$userID = isset($_GET['userID']) ? $_GET['userID'] : '';
		}else{
			$oToken->getUserID();
			$userID = $oToken->userID;
		}

		if ( empty($userID) ){
			return array(
				'status' => 'error',
				'msg'    => 'foundNoUser'
			);
		}

		$aUserInfo = $this->getUserProfile($userID);

		return array(
			'status'    => 'success',
			'oResults'  => $aUserInfo
		);
	}
}