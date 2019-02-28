<?php
namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Upload\Upload;
use WilokeListingTools\Frontend\User;

trait InsertImg {
	protected function isImgData($aRawImage){
		if ( !is_array($aRawImage) ){
			return false;
		}

		if ( isset($aRawImage[0])  && isset($aRawImage[0]['imgID'])){
			return true;
		}

		if ( isset($aRawImage[0])  && isset($aRawImage[0]['id'])){
			return true;
		}

		if ( isset($aRawImage[0])  && isset($aRawImage[0]['fileType']) ){
			return true;
		}

		return false;
	}

	protected function insertImg($aRawImage){
		if ( empty($aRawImage) || (isset($aRawImage[0]['imgID']) && !empty($aRawImage[0]['imgID'])) ){
			return false;
		}

		if ( isset($aRawImage[0])  && isset($aRawImage[0]['id']) ){
			if ( get_post_field('post_author', $aRawImage[0]['id']) !=  User::getCurrentUserID() ){
				return false;
			}

			return array(
				'src' => wp_get_attachment_image_url($aRawImage[0]['id'], 'large'),
				'id'  => $aRawImage[0]['id']
			);
		}


		$instUploadImg = new Upload();

		$instUploadImg->userID = get_current_user_id();
		$instUploadImg->aData['imageData']  = $aRawImage[0]['src'];
		$instUploadImg->aData['fileName']   = $aRawImage[0]['fileName'];
		$instUploadImg->aData['fileType']   = $aRawImage[0]['fileType'];
		$instUploadImg->aData['uploadTo']   = $instUploadImg::getUserUploadFolder();

		$id = $instUploadImg->image();
		return array(
			'src' => wp_get_attachment_image_url($id, 'large'),
			'id'  => $id
		);
	}
}