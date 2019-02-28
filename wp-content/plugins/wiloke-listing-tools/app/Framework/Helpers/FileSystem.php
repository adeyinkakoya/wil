<?php

namespace WilokeListingTools\Framework\Helpers;


use WilokeListingTools\Frontend\User;

class FileSystem {
	public static function isWilcityFolderExisted($subFolder=''){
		$aUploadDir  = wp_upload_dir();
		$folder = empty($subFolder) ? $aUploadDir['basedir'] . '/wilcity' : $aUploadDir['basedir'] . '/wilcity/'.$subFolder;
		return is_dir($folder);
	}

	public static function createWilcityFolder(){
		if ( self::isWilcityFolderExisted() ){
			return true;
		}
		$aUploadDir  = wp_upload_dir();
		if (wp_mkdir_p($aUploadDir['basedir'] . '/wilcity')) {
			return true;
		}
		return false;
	}

	public static function getWilcityFolderDir(){
		self::createWilcityFolder();
		$aUploadDir = wp_upload_dir();
		return $aUploadDir['basedir'] . '/wilcity/';
	}

	public static function createUserFolder($userID){
		self::createWilcityFolder();

		$userFolder = User::getField('user_login', $userID);

		if ( self::isWilcityFolderExisted($userFolder) ){
			return true;
		}

		$aUploadDir  = wp_upload_dir();
		if (wp_mkdir_p($aUploadDir['basedir'] . '/wilcity/'.$userFolder)) {
			return true;
		}

		return false;
	}

	public static function getUserFolderUrl($userID){
		$userFolder = User::getField('user_login', $userID);
		$aUploadDir = wp_upload_dir();
		return $aUploadDir['baseurl'] . '/wilcity/' . $userFolder . '/';
	}

	public static function getUserFolderDir($userID){
		$userFolder = User::getField('user_login', $userID);
		$aUploadDir = wp_upload_dir();
		return $aUploadDir['basedir'] . '/wilcity/' . $userFolder . '/';
	}

	public static function getWilcityFolderUrl(){
		self::createWilcityFolder();
		$aUploadDir = wp_upload_dir();
		return $aUploadDir['baseurl'] . '/wilcity/';
	}

	public static function getFileURI($fileName=''){
		$aUploadDir  = wp_upload_dir();
		return $aUploadDir['baseurl'] . '/wilcity/'.$fileName;
	}

	public static function getFileDir($fileName=''){
		$aUploadDir  = wp_upload_dir();
		return $aUploadDir['basedir'] . '/wilcity/'.$fileName;
	}

	public static function createFile($fileName=''){
		if ( !self::createWilcityFolder() ){
			return false;
		}
		$aUploadDir  = wp_upload_dir();
		$fileDir = $aUploadDir['basedir'] . '/wilcity/'.$fileName;

		if ( !function_exists('WP_Filesystem') ){
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}
		WP_Filesystem();
		global $wp_filesystem;

		$wp_filesystem->put_contents(
			$fileDir,
			'',
			FS_CHMOD_FILE // predefined mode settings for WP files
		);
	}

	public static function isFileExists($fileName){
		$aUploadDir  = wp_upload_dir();
		$fileDir = $aUploadDir['basedir'] . '/wilcity/'.$fileName;

		return file_exists($fileDir);
	}

	public static function filePutContents($fileName, $text){
		$aUploadDir  = wp_upload_dir();
		$fileDir = $aUploadDir['basedir'] . '/wilcity/'.$fileName;

		if ( !self::isFileExists($fileName) ){
			self::createFile($fileName);
		}

		if ( !function_exists('WP_Filesystem') ){
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}
		WP_Filesystem();
		global $wp_filesystem;

		return $wp_filesystem->put_contents($fileDir, $text, FS_CHMOD_FILE);
	}

	public static function logPayment($fileName, $text){
		$status = GetWilokeSubmission::getField('toggle_debug');
		if ( $status == 'enable' ){
			self::filePutContents($fileName, $text);
		}
	}

	public static function fileGetContents($fileName, $isCreatedIfNotExists=true){
		$aUploadDir  = wp_upload_dir();
		$fileDir = $aUploadDir['basedir'] . '/wilcity/'.$fileName;

		if ( !file_exists($fileDir) ){
			if ( $isCreatedIfNotExists ){
				self::createFile($fileName);
				return false;
			}
		}

		if ( !function_exists('WP_Filesystem') ){
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}
		WP_Filesystem();
		global $wp_filesystem;
		return $wp_filesystem->get_contents($fileDir);
	}
}