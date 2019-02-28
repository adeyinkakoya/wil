<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\AlterTable\AlterTableMessage;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\Time;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Frontend\User as WilokeUser;
use WilokeListingTools\Frontend\User;
use WilokeListingTools\Models\MessageModel;

class MessageController extends Controller {
	public $aAuthorAvatars = array();
	public $aDisplayName = array();

	public function __construct() {
		add_action('wp_ajax_wilcity_s_users_in_message', array($this, 'fetchAuthorsSendMessage'));
		add_action('wp_ajax_wilcity_fetch_author_messages', array($this, 'fetchAuthorMessages'));
		add_action('wp_ajax_wilcity_fetch_message_authors', array($this, 'fetchAuthorsSendMessage'));
		add_action('wp_ajax_wilcity_fetch_author_msg_info', array($this, 'fetchAuthorMsgInfo'));
		add_action('wp_ajax_wilcity_submit_new_msg', array($this, 'submitNewMsg'));
		add_action('wp_ajax_wilcity_update_read_message', array($this, 'updateReadMsgStatus'));
		add_action('wilcity/footer/vue-popup-wrapper', array($this, 'printFooterCode'));
		add_action('wp_ajax_wilcity_count_messages_to_me', array($this, 'countMessagesToMe'));

		add_action('wilcity/header/after-menu', array($this, 'messageNotifications'), 15);
		add_action('wp_ajax_wilcity_count_new_messages', array($this, 'countNewMessagesOfAuthor'));
		add_action('wp_ajax_wilcity_reset_new_messages', array($this, 'resetNewMessages'));
		add_action('wp_ajax_wilcity_fetch_list_messages', array($this, 'fetchLatestMessagesOfUser'));
		add_action('wilcity/after/created-account', array($this, 'sendWelcomeMessage'));
		add_action('wp_ajax_wilcity_delete_author_message', array($this, 'deleteAuthorMessage'));
		add_action('wp_ajax_wilcity_delete_single_message', array($this, 'deleteSingleMessageInfo'));
	}

	public function deleteSingleMessageInfo(){
        $status = MessageModel::deleteMessageByCurrentID($_POST['ID']);
        if ( $status ){
            wp_send_json_success();
        }else{
            wp_send_json_error(array(
                'msg' => esc_html__('Wrong message ID', 'wiloke-listing-tools')
            ));
        }
    }

	public function deleteAuthorMessage(){
	    MessageModel::deleteAuthorMessages($_POST['authorInfo']['messageAuthorID']);
	    wp_send_json_success(array('authorId'=>$_POST['authorInfo']['messageAuthorID']));
    }

	public function sendWelcomeMessage($userID){
	    $aThemeOptions = \Wiloke::getThemeOptions();
	    if ( isset($aThemeOptions['welcome_message']) && !empty($aThemeOptions['welcome_message']) ){
	        $oFirstSuperAdmin = User::getFirstSuperAdmin();
		    MessageModel::insertNewMessage($userID, $aThemeOptions['welcome_message'], $oFirstSuperAdmin->ID);
        }
    }

	public function fetchLatestMessagesOfUser(){
		$limit = isset($_POST['limit']) || $_POST['limit'] > 100 ? abs($_POST['limit']) : 20;
		$paged = isset($_POST['paged']) ? abs($_POST['paged']) : 1;
		$offset = ($paged-1)*$limit;

        $aRawMessages = MessageModel::getLatestMessageOfUser(get_current_user_id(), $limit, $offset);
        if ( !$aRawMessages ){
            wp_send_json_error(array(
                'msg' => esc_html__('No Messages', 'wiloke-listing-tools')
            ));
        }
		$dashboardUrl = GetWilokeSubmission::getField('dashboard_page', true);
		$dashboardUrl .= '#/messages';

        $aMessages = array();

        foreach ($aRawMessages as $oMessage){
            $aMessages[] = array(
                'avatar'        => User::getAvatar($oMessage->messageAuthorID),
                'displayName'   => User::getField('display_name', $oMessage->messageAuthorID),
                'content'       => \Wiloke::truncateString($oMessage->messageContent, 40),
                'link'          => $dashboardUrl.'?u='.User::getField('user_login', $oMessage->messageAuthorID).'&id='.$oMessage->messageAuthorID,
                'time'          => Time::timeFromNow(strtotime($oMessage->messageDateUTC), true)
            );
        }

        wp_send_json_success(
            array(
                'aInfo' => $aMessages,
                'dashboardUrl' => $dashboardUrl
            )
        );
    }

	public function resetNewMessages(){
		$this->middleware(['isUserLoggedIn'], []);
		MessageModel::resetNewMessages(get_current_user_id());
    }

	public function countNewMessagesOfAuthor(){
        $this->middleware(['isUserLoggedIn'], []);
        $total = MessageModel::countUnReadMessages(get_current_user_id());
        wp_send_json_success($total);
    }

	public function messageNotifications(){
		if ( !is_user_logged_in() || !GetWilokeSubmission::isSystemEnable() ){
			return '';
		}
		?>
        <div id="wilcity-message-notifications" class="header_loginItem__oVsmv">
            <message-notifications></message-notifications>
        </div>
		<?php
	}

	public function countMessagesToMe(){
	    $total = MessageModel::countMessages(get_current_user_id());
	    if ( empty($total) ){
	        wp_send_json_error(esc_html__('No alerts or messages at this time', 'wiloke-listing-tools'));
        }
	    wp_send_json_success($total);
    }

	public function printFooterCode(){
		if ( !is_user_logged_in() ){
			return '';
		}

		if ( is_singular() || is_author() ) :
			global $post;
			$authorID = is_author() ? get_query_var('author') : $post->post_author;
		?>
			<wiloke-message-popup receive-id="<?php echo esc_attr($authorID); ?>" display-name="<?php echo esc_attr(WilokeUser::getField('display_name', $authorID)); ?>"></wiloke-message-popup>
		<?php
		endif;
	}

	public function updateReadMsgStatus(){
		MessageModel::updateReadMessage($_POST['senderID']);
	}

	public function getAuthorDisplayName($userID){
		if ( !empty($this->aDisplayName[$userID]) ){
			return $this->aDisplayName[$userID];
		}

		$this->aDisplayName[$userID] = WilokeUser::getField('display_name', get_current_user_id());
		return $this->aDisplayName[$userID];
	}

	public function getAuthorAvatar($userID){
		if ( isset($this->aAuthorAvatars[$userID]) ){
			return $this->aAuthorAvatars[$userID];
		}

		$this->aAuthorAvatars[$userID] = WilokeUser::getAvatar($userID);
		return $this->aAuthorAvatars[$userID];
	}

	protected function insertInstantMessage($receiverID){
	    $instantMsg = WilokeUser::getInstantMessage($receiverID);
	    if ( !empty($instantMsg) ){
		    MessageModel::insertNewMessage(get_current_user_id(), WilokeUser::getInstantMessage($receiverID), $receiverID);
        }
    }

	public function submitNewMsg(){
		$this->middleware(['beforeSubmitMessage', 'isUserLoggedIn'], array(
			'receiveID' => $_POST['receiveID']
		));

		if ( !isset($_POST['message']) || empty($_POST['message']) ){
			wp_send_json_error(array(
				'msg' => esc_html__('Please enter your message', 'wiloke-listing-tools')
			));
		}

		$msgID = MessageModel::insertNewMessage($_POST['receiveID'], $_POST['message']);

		if ( !$msgID ){
			wp_send_json_error(array(
				'msg' => esc_html__('Oops! We could not send your message, please try it again. ', 'wiloke-listing-tools')
			));
		}else{
			$userID = get_current_user_id();

			if ( !isset($_POST['isChatting']) ){
                $this->insertInstantMessage($_POST['receiveID']);
            }

            do_action('wilcity/action/receive-message', $_POST['receiveID'], get_current_user_id(), $_POST['message']);

			wp_send_json_success(
				array(
					'userAvatar' => $this->getAuthorAvatar($userID),
					'userDisplayName' => $this->getAuthorDisplayName($userID),
					'msg' => array(
						'messageAuthorID' => $userID,
						'messageContent' => stripslashes($_POST['message'])
					),
					'ID'     => $msgID,
					'userID' => $userID,
					'instantMessage' => WilokeUser::getInstantMessage($userID)
				)
			);
		}
	}

	public function fetchAuthorMessages(){
		$authorID       = abs($_POST['authorID']);
		$userID         = get_current_user_id();
		$userDisplayName= $this->getAuthorDisplayName($userID);
		$authorDisplayName= $this->getAuthorDisplayName($authorID);
		$userAvatar     = $this->getAuthorAvatar(get_current_user_id());
		$authorAvatar   = $this->getAuthorAvatar($_POST['authorID']);
		$authorProfile   = get_author_posts_url(get_current_user_id());

		$aExcludes = isset($_POST['excludes']) && !empty($_POST['excludes']) ? array_map(function($id){
			return abs($id);
		}, $_POST['excludes']) : array();

		if ( empty($aExcludes) ){
			$aRawResults = MessageModel::getMyChat($userID, $authorID);
		}else{
            if ( isset($_POST['isFetchNewChat']) ){
	            $aRawResults = MessageModel::getNewestChat($userID, $authorID, $aExcludes);
            }else{
	            $aRawResults = MessageModel::getMyChat($userID, $authorID);
            }
		}

		if ( empty($aRawResults) ){
			wp_send_json_error();
		}

		$aCommonResponse = array(
			'userAvatar'        => $userAvatar,
			'authorAvatar'      => $authorAvatar,
			'userDisplayName'   => $userDisplayName,
			'authorDisplayName' => $authorDisplayName,
			'authorProfile'     => $authorProfile,
			'userID'            => $userID
		);

		$aResponse = array();
		$date = '';

		$aRawResults = array_reverse($aRawResults);

		foreach ($aRawResults as $key => $aResult){
			$newDate = date('Y-m-d', strtotime($aResult['messageDate']));
			if ( $newDate !== $date ){
				array_push($aResponse, array('breakDate'=>$newDate));
			}

			$aResponse[$key] = $aResult;
			$aResponse[$key]['messageAt'] = date_i18n(get_option('time_format'), strtotime($aResult['messageDate']));
			$aResponse[$key]['messageContent'] = stripslashes($aResult['messageContent']);
			$aExcludes[] = $aResult['ID'];

		}
		$aCommonResponse['excludes'] = $aExcludes;
		$aCommonResponse['msg'] = $aResponse;

		if ( count($aRawResults) < 10 ){
			$aCommonResponse['reachedMaximum'] = 'yes';
			wp_send_json_success($aCommonResponse);
		}else{
			wp_send_json_success($aCommonResponse);
		}
	}

	public function fetchAuthorMsgInfo(){
		wp_send_json_success(array(
			'displayName' => WilokeUser::getField('display_name', $_POST['authorID'])
		));
	}

	public static function getMessageAuthors($userID, $aExcludes='', $limit=10){
		global $wpdb;
		$usersTbl = $wpdb->users;
		$msgTbl   = $wpdb->prefix . AlterTableMessage::$tblName;

		if ( empty($aExcludes) ){
			$aRawResults = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT $msgTbl.*, $usersTbl.display_name as displayName FROM $msgTbl LEFT JOIN $usersTbl ON ($usersTbl.ID = $msgTbl.messageAuthorID) WHERE $msgTbl.messageUserReceivedID=%d LIMIT %d",
					$userID, $limit
				), ARRAY_A
			);
		}else{
		    $aExcludes = array_map(function($id){
		        return abs($id);
            }, $aExcludes);
			$aRawResults = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DISTINCT $msgTbl.*, $usersTbl.display_name as displayName FROM $msgTbl LEFT JOIN $usersTbl ON ($usersTbl.ID = $msgTbl.messageAuthorID) WHERE $msgTbl.messageUserReceivedID=%d AND $msgTbl.messageAuthorID NOT IN (".implode(',', $aExcludes).") GROUP BY $msgTbl.messageAuthorID LIMIT %d",
					$userID, $limit
				),
				ARRAY_A
			);
		}

		return $aRawResults;
    }

    public static function searchAuthorMessage($s, $limit=10){
	    global $wpdb;
	    $usersTbl = $wpdb->users;
	    $msgTbl   = $wpdb->prefix . AlterTableMessage::$tblName;

	    $s = '%'.$s.'%';
	    $aRawResults = $wpdb->get_results(
		    $wpdb->prepare(
			    "SELECT DISTINCT $msgTbl.*, $usersTbl.display_name as displayName FROM $msgTbl LEFT JOIN $usersTbl ON ($usersTbl.ID = $msgTbl.messageAuthorID) WHERE $msgTbl.messageUserReceivedID=%d AND ($usersTbl.display_name LIKE %s OR $usersTbl.user_login  LIKE %s) GROUP BY $msgTbl.messageAuthorID LIMIT %d",
			    get_current_user_id(), $s, $s, $limit
		    ), ARRAY_A
	    );

	    return $aRawResults;
    }

	public function fetchAuthorsSendMessage(){
		$aExcludes = array();

		if ( isset($_POST['s']) ){
			if ( empty($_POST['s']) ){
				wp_send_json_error();
			}else{
				$s = sanitize_text_field($_POST['s']);
				$aRawResults = self::searchAuthorMessage($s);
			}
		}else{
			if ( isset($_POST['excludes']) && !empty($_POST['excludes']) ){
				$aExcludes = array_map(function($userID){
					return abs($userID);
				}, $_POST['excludes']);
			}

			$aRawResults = self::getMessageAuthors(get_current_user_id(), $aExcludes);
		}

		if ( empty($aRawResults)  ){
			wp_send_json_error();
		}else{
			$aAuthors = array();
			foreach ($aRawResults  as $key => $aResult){
				$aExcludes[] = $aResult['messageAuthorID'];

				$aAuthors[$aResult['messageAuthorID']] = $aResult;
				$aAuthors[$aResult['messageAuthorID']]['avatar'] = $this->getAuthorAvatar($aResult['messageAuthorID']);
				$aAuthors[$aResult['messageAuthorID']]['phone']  = WilokeUser::getPhone($aResult['messageAuthorID']);
				$aAuthors[$aResult['messageAuthorID']]['position']  = WilokeUser::getPosition($aResult['messageAuthorID']);
				$aAuthors[$aResult['messageAuthorID']]['address']  = WilokeUser::getAddress($aResult['messageAuthorID']);
				$aAuthors[$aResult['messageAuthorID']]['aSocialNetworks']  = WilokeUser::getSocialNetworks($aResult['messageAuthorID']);
				$aAuthors[$aResult['messageAuthorID']]['profileUrl']  = get_author_posts_url($aResult['messageAuthorID']);

				$diffInMinutes = Time::dateDiff(strtotime($aResult['messageDateUTC']), current_time('timestamp', 1), 'minute');
				$aAuthors[$aResult['messageAuthorID']]['messageContent'] = stripslashes($aResult['messageContent']);

				if ( $diffInMinutes < 60  ){
				    if ( empty($diffInMinutes) ){
					    $aAuthors[$aResult['messageAuthorID']]['diff'] = esc_html__('A few seconds ago', 'wiloke-listing-tools');
                    }else{
					    $aAuthors[$aResult['messageAuthorID']]['diff'] = sprintf(_n('%s minute ago', '%s minutes ago', $diffInMinutes, 'wiloke-listing-tools'), $diffInMinutes);
                    }
				}else{
					$diffInHours = Time::dateDiff(strtotime($aResult['messageDateUTC']), current_time('timestamp', 1), 'hour');
					if ( $diffInHours < 24 ){
						$aAuthors[$aResult['messageAuthorID']]['diff'] = sprintf(_n('%s hour ago', '%s hours ago', $diffInHours, 'wiloke-listing-tools'), $diffInHours);
					}elseif (Time::isDateInThisWeek(strtotime($aResult['messageDate']))){
						$aAuthors[$aResult['messageAuthorID']]['diff'] = date_i18n('l', strtotime($aResult['messageDate']));
					}else{
						$aAuthors[$aResult['messageAuthorID']]['diff'] = date_i18n(get_option('date_format'), strtotime($aResult['messageDate']));
					}
				}
			}

			if ( count($aRawResults) >= 10 ){
				wp_send_json_success(array(
					'msg'       => $aAuthors,
					'userID'    => get_current_user_id(),
					'excludes'  => $aExcludes
				));
			}else{
				wp_send_json_success(array(
					'msg'               => $aAuthors,
					'userID'            => get_current_user_id(),
					'reachedMaximum'    => 'yes'
				));
			}
		}
	}
}