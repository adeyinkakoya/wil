<?php

namespace WilokeListingTools\Models;



use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;

class PostModel {
	/*
	 * @postID: int
	 * @menuOrder: int
	 */
	public static function setMenuOrder($postID, $menuOrder){
		global $wpdb;
		$wpdb->update(
			$wpdb->posts,
			array(
				'menu_order' => $menuOrder
			),
			array(
				'ID' => $postID
			),
			array(
				'%d'
			),
			array(
				'%d'
			)
		);
	}

	public static function countAllPosts(){
		$aPostKeys = General::getPostTypeKeys(false, true);
		$totalPosts = 0;

		foreach ($aPostKeys as $postType){
			$oCountPosts = wp_count_posts($postType);
			$totalPosts += $oCountPosts->publish;
		}
		return $totalPosts;
	}

	public static function isClaimed($postID){
		$claimStatus = GetSettings::getPostMeta($postID, 'claim_status');
		return $claimStatus == 'claimed';
	}
}