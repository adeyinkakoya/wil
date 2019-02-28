<?php

namespace WilokeListingTools\Models;


use WilokeListingTools\AlterTable\AlterTableMessage;
use WilokeListingTools\Framework\Helpers\Time;

class MessageModel {
	public static function getMyChat($userID, $chatFriendID, $aExcludes=array()){
		global $wpdb;
		$msgTbl = $wpdb->prefix . AlterTableMessage::$tblName;

		if ( empty($aExcludes) ){
			$aChat = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $msgTbl WHERE (messageUserReceivedID=%d AND messageAuthorID=%d) OR (messageUserReceivedID=%d AND messageAuthorID=%d) ORDER BY ID DESC LIMIT 10",
					$userID, $chatFriendID, $chatFriendID, $userID
				),
				ARRAY_A
			);
		}else{
			$aChat = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $msgTbl WHERE ( (messageUserReceivedID=%d AND messageAuthorID=%d) OR (messageUserReceivedID=%d AND messageAuthorID=%d) ) AND $msgTbl.ID NOT IN (".implode(',', $aExcludes).") ORDER BY ID DESC LIMIT 10",
					$userID, $chatFriendID, $chatFriendID, $userID
				),
				ARRAY_A
			);
		}

		return empty($aChat) ? false : $aChat;
	}

	public static function getNewestChat($userID, $chatFriendID, $aPreviousChatIDs){
		global $wpdb;
		$msgTbl = $wpdb->prefix . AlterTableMessage::$tblName;

		$maxID = min($aPreviousChatIDs);
		$messageDateUTC = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT messageDateUTC FROM $msgTbl WHERE ID = %d",
				$maxID
			)
		);
		$aRawResults = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $msgTbl WHERE (messageUserReceivedID=%d AND messageAuthorID=%d) AND $msgTbl.ID NOT IN (".implode(',', $aPreviousChatIDs).") AND messageReceivedSeen = 'no' AND messageDateUTC >= %s ORDER BY ID DESC LIMIT 10",
				$userID, $chatFriendID, $messageDateUTC
			),
			ARRAY_A
		);

		return empty($aRawResults) ? false : $aRawResults;
	}

	public static function getLatestMessageOfUser($receiverID, $limit=10, $offset=0){
		global $wpdb;
		$msgTbl = $wpdb->prefix . AlterTableMessage::$tblName;

		$aMessages = $wpdb->get_results(
			$wpdb->prepare(
			"SELECT * FROM $msgTbl WHERE messageUserReceivedID=%d ORDER BY ID DESC LIMIT $offset,$limit",
				$receiverID
		));
		if ( empty($aMessages) || is_wp_error($aMessages) ){
			return false;
		}

		return $aMessages;
	}

	public static function resetNewMessages($receiverID){
		global $wpdb;
		$msgTbl = $wpdb->prefix . AlterTableMessage::$tblName;
		return $wpdb->update(
			$msgTbl,
			array(
				'messageReceivedSeen' => 'yes'
			),
			array(
				'messageUserReceivedID' => $receiverID,
			),
			array(
				'%s'
			),
			array(
				'%d'
			)
		);
	}

	public static function updateReadMessage($senderID){
		global $wpdb;
		$msgTbl = $wpdb->prefix . AlterTableMessage::$tblName;
		$wpdb->update(
			$msgTbl,
			array(
				'messageReceivedSeen' => 'yes'
			),
			array(
				'messageAuthorID' => get_current_user_id(),
				'messageUserReceivedID' => $senderID,
			),
			array(
				'%s'
			),
			array(
				'%d',
				'%d'
			)
		);
	}

	public static function deleteMessageByCurrentID($messageID){
		$currentUserID = get_current_user_id();
		global $wpdb;
		$tableName = $wpdb->prefix . AlterTableMessage::$tblName;

		$id = $wpdb->get_var($wpdb->prepare(
			"SELECT ID FROM $tableName WHERE (messageUserReceivedID=%d OR messageAuthorID=%d) AND ID = %d",
			$currentUserID, $messageID
		));

		if ( $id ){
			$wpdb->delete(
				$tableName,
				array(
					'ID' => $id
				),
				array(
					'%d'
				)
			);
			return true;
		}
		return false;
	}

	public static function deleteAuthorMessages($authorID){
		$currentUserID = get_current_user_id();
		global $wpdb;
		$authorID = $wpdb->_real_escape(abs($authorID));
		$tableName = $wpdb->prefix . AlterTableMessage::$tblName;

		$wpdb->delete(
			$tableName,
			array(
				'messageUserReceivedID' => $authorID,
				'messageAuthorID'       => $currentUserID
			),
			array(
				'%d',
				'%d'
			)
		);

		$wpdb->delete(
			$tableName,
			array(
				'messageUserReceivedID' => $currentUserID,
				'messageAuthorID'       => $authorID
			),
			array(
				'%d',
				'%d'
			)
		);
		return true;
	}

	public static function countMessages($receiveID){
		global $wpdb;
		$msgTbl = $wpdb->prefix . AlterTableMessage::$tblName;

		$count = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(ID) FROM $msgTbl WHERE messageUserReceivedID=%d",
			 $receiveID
		));
		return abs($count);
	}

	public static function countUnReadMessages($receiveID){
		global $wpdb;
		$msgTbl = $wpdb->prefix . AlterTableMessage::$tblName;

		$count = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(ID) FROM $msgTbl WHERE messageUserReceivedID=%d AND messageReceivedSeen=%s",
			$receiveID, 'no'
		));
		return abs($count);
	}

	public static function insertNewMessage($receiveID, $msg, $senderID=null){
		global $wpdb;
		$msgTbl = $wpdb->prefix . AlterTableMessage::$tblName;

		$senderID = empty($senderID) ? get_current_user_id() : $senderID;

		if ( $receiveID == $senderID ){
			return false;
		}

		$status = $wpdb->insert(
			$msgTbl,
			array(
				'messageUserReceivedID' => $receiveID,
				'messageAuthorID'       => $senderID,
				'messageContent'        => $msg,
				'messageDate'           => Time::mysqlDateTime(),
				'messageDateUTC'        => Time::mysqlDateTime(current_time('timestamp', 1)),
				'messageReceivedSeen'   => 'no'
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s'
			)
		);
		if ( !$status ){
			return false;
		}

		return $wpdb->insert_id;
	}

	public static function getMessage($receiveID){
		global $wpdb;
		$msgTbl = $wpdb->prefix . AlterTableMessage::$tblName;

		return $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $msgTbl WHERE messageAuthorID=%d AND messageUserReceivedID=%d",
			get_current_user_id(), $receiveID
		), ARRAY_A);
	}

	public static function updateField($fieldID, $fieldName, $val){
		global $wpdb;
		$msgTbl = $wpdb->prefix . AlterTableMessage::$tblName;

		return $wpdb->update(
			$msgTbl,
			array(
				$fieldName => $val,
				'messageDate' => Time::mysqlDateTime()
			),
			array(
				'ID' => $fieldID
			),
			array(
				'%s',
				'%s'
			),
			array(
				'%d'
			)
		);
	}
}