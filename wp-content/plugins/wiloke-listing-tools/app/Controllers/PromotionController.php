<?php

namespace WilokeListingTools\Controllers;


use Stripe\Util\Set;
use WilokeListingTools\Framework\Helpers\FileSystem;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Payment\WooCommerce\WoocommerceNonRecurringPayment;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Framework\Store\Session;
use WilokeListingTools\Models\PaymentMetaModel;
use WilokeListingTools\Models\PaymentModel;

class PromotionController extends Controller {
	protected $aPromotionPlans;
	protected $aWoocommercePlans;
	protected $expirationHookName = 'expiration_promotion';
	private $belongsToPromotionKey = 'belongs_to_promotion';

	public function __construct() {
		add_action('wp_ajax_wilcity_fetch_promotion_plans', array($this, 'fetchPromotions'));
		add_action('wp_ajax_wilcity_get_payment_gateways', array($this, 'getPaymentGateways'));
		add_action('wp_ajax_wilcity_boost_listing', array($this, 'boostListing'));
		add_action('wiloke-listing-tools/payment-succeeded/promotion', array($this, 'updatePayPalPostPromotion'));
		add_action('wiloke-listing-tools/woocommerce/order-created', array($this, 'updateWooCommercePromotion'), 10, 2);
		add_action('wiloke-listing-tools/woocommerce/after-order-succeeded', array($this, 'updateWoocommercePostPromotion'), 10);

		add_action('update_post_meta', array($this, 'maybeChangeListingID'), 10, 4);
		add_action('updated_post_meta', array($this, 'updatedListingPromotion'), 10, 4);
		add_action('added_post_meta', array($this, 'updatedListingPromotion'), 10, 4);
		add_action('post_updated', array($this, 'onChangedPromotionStatus'), 10, 3);
		add_action('before_delete_post', array($this, 'deletePromotion'), 100);

		$aPromotionPlans = GetSettings::getOptions('promotion_plans');
		if ( !empty($aPromotionPlans) ){
			foreach ($aPromotionPlans as $aPlanSetting){
				add_action($this->generateScheduleKey($aPlanSetting['position']), array($this, 'deletePromotionValue'), 10, 2);
			}
		}
		add_action('wiloke-listing-tools/payment-succeeded/promotion', array($this, 'updatePromotionPageStatusToPublish'));
//		add_action('wiloke-listing-tools/after-changed-to-new-payment-status', array($this, 'afterChangedToNewPaymentStatus'));
		add_action('wiloke-listing-tools/after-changed-payment-status/promotion', array($this, 'updateChangedPaymentStatus'));

		add_action('wilcity/single-listing/sidebar-promotion', array($this, 'printOnSingleListing'));
	}

	public function printOnSingleListing(){
		global $post;
		$belongsTo = GetSettings::getPostMeta($post->ID, 'belongs_to');
		if ( !empty($belongsTo) && !GetSettings::isPlanAvailableInListing($post->ID, 'toggle_promotion') ){
			return '';
		}

		$aArgs = array(
			'post_type'         => $post->post_type,
			'posts_per_page'    => -1,
			'post_status'       => 'publish',
			'meta_key'          => 'wilcity_promote_listing_sidebar',
			'orderby'           => 'meta_value_num',
			'order'             => 'DESC'
		);

		$query = new \WP_Query($aArgs);

		if ( !$query->have_posts() ){
			wp_reset_postdata();
			return '';
		}
		?>
		<div class="content-box_module__333d9">
			<header class="content-box_header__xPnGx clearfix">
				<div class="wil-float-left"><h4 class="content-box_title__1gBHS">
					<i class="la la-star"></i><span><?php esc_html_e('You might interested', 'wiloke-listing-tools'); ?></span></h4>
				</div>
			</header>
	        <div class="content-box_body__3tSRB">
	            <div class="swiper__module swiper-container swiper--button-abs" data-options='{"slidesPerView":"auto","spaceBetween":10,speed:1000,"autoplay":true,"loop":true}'>
	                <div class="swiper-wrapper">
	                    <?php
	                    while ( $query->have_posts() ){
	                        $query->the_post();
	                        wilcity_render_grid_item($query->post, array('img_size'=>'wilcity_290x165', 'isSlider'=>true));
	                    } wp_reset_postdata();
	                    ?>
	                </div>
	                <div class="swiper-button-custom">
	                    <div class="swiper-button-prev-custom"><i class='la la-angle-left'></i></div>
	                    <div class="swiper-button-next-custom"><i class='la la-angle-right'></i></div>
	                </div>
	            </div>
	        </div>
		</div>
		<?php
	}

	public function deletePromotion($postID){
		if ( get_post_type($postID) != 'promotion' ){
			return false;
		}

		$listingID = GetSettings::getPostMeta($postID, 'listing_id');
		if ( empty($listingID) ){
			return false;
		}
		SetSettings::deletePostMeta($listingID, $this->belongsToPromotionKey);
	}

	private function focusUpdatePromotionStatus($postID, $status){
		global $wpdb;
		$wpdb->update(
			$wpdb->posts,
			array(
				'post_status'=>$status
			),
			array(
				'ID' => $postID
			),
			array(
				'%s'
			),
			array(
				'%s'
			)
		);
	}

	public function updateChangedPaymentStatus($aData){
		if ( $aData['newStatus'] !== 'pending' ){
			$aBoostPostData = PaymentMetaModel::get($aData['paymentID'], 'boost_post_data');
			SetSettings::deletePostMeta($aBoostPostData['postID'], 'promotion_wait_for_bank_transfer');
		}else{
			$aBoostPostData = PaymentMetaModel::get($aData['paymentID'], 'boost_post_data');
			SetSettings::setPostMeta($aBoostPostData['postID'], 'promotion_wait_for_bank_transfer', $aData['paymentID']);
		}
	}

	public function deleteWaitForBankTransferStatus($aData){
        if ( PaymentMetaModel::get($aData['paymentID'], 'packageType') !== 'promotion' ){
            return false;
        }
    }

	public function updatePromotionPageStatusToPublish($aData){
		$promotionPageID = PaymentMetaModel::get($aData['paymentID'], 'promotion_page_id');
		if ( empty($promotionPageID) ){
			return false;
		}

		wp_update_post(array(
			'ID' => $promotionPageID,
			'post_status' => 'draft'
		));

		wp_update_post(array(
			'ID' => $promotionPageID,
			'post_status' => 'publish'
		));
	}

	public function deletePromotionValue($listingID, $position){
		SetSettings::deletePostMeta($listingID, 'promote_'.$position);

		if ( $position == 'top_of_search' ){
			$this->updateMenuOrder($listingID, false);
		}

		$promotionID = GetSettings::getPostMeta($listingID, $this->belongsToPromotionKey);
		if ( empty($promotionID) ){
			return false;
		}
		$aRawPromotionPlans = GetSettings::getOptions('promotion_plans');

		$isExpiredAll = true;
		$now = current_time('timestamp');
		foreach ($aRawPromotionPlans as $aPlanSetting){
			$val = GetSettings::getPostMeta($promotionID, 'promote_'.$aPlanSetting['position']);
			if ( !empty($val) ){
				$val = abs($val);
				if ( $val > $now ){
					$isExpiredAll = false;
				}
			}
		}

		if ( $isExpiredAll ){
			$this->focusUpdatePromotionStatus($promotionID, 'draft');
		}
	}

	private function deleteAllPlansOfListing($listingID){
		$aRawPromotionPlans = GetSettings::getOptions('promotion_plans');
		foreach ($aRawPromotionPlans as $aPlanSetting){
			SetSettings::deletePostMeta($listingID, 'promote_'.$aPlanSetting['position']);
			if ( $aPlanSetting['position'] ==  'top_of_search' ){
				$this->updateMenuOrder($listingID, false);
			}
		}
	}

	private function generateScheduleKey($position){
		return 'trigger_promote_'.$position.'_expired';
	}

	protected function getPromotionPlans(){
		if ( !empty($this->aPromotionPlans) ){
			return $this->aPromotionPlans;
		}

		$aRawPromotionPlans = GetSettings::getOptions('promotion_plans');
		foreach ($aRawPromotionPlans as $aPromotion){
			$this->aPromotionPlans[$aPromotion['position']] = $aPromotion;
		}
		return $this->aPromotionPlans;
	}

	public function getPromotionField($field){
		$aPromotionPlans = $this->getPromotionPlans();

		return isset($aPromotionPlans[$field]) ? $aPromotionPlans[$field] : false;
	}

	private function updateMenuOrder($listingID, $isPlus=true){
		$aTopOfSearchSettings = $this->getPromotionField('top_of_search');
		if ( $aTopOfSearchSettings ){
			$menuOrder = get_post_field('menu_order', $listingID);
			if ( $isPlus ){
				$menuOrder = abs($menuOrder) + abs($aTopOfSearchSettings['menu_order']);
			}else{
				$menuOrder = abs($menuOrder) - abs($aTopOfSearchSettings['menu_order']);
			}

			global $wpdb;
			$wpdb->update(
				$wpdb->posts,
				array(
					'menu_order' => $menuOrder
				),
				array(
					'ID' => $listingID
				),
				array(
					'%d'
				),
				array(
					'%d'
				)
			);
		}
	}

	public function updatedListingPromotion($metaID, $objectID, $metaKey, $metaValue){
		if ( (get_post_type($objectID) !== 'promotion') || GetSettings::getPostMeta($objectID, 'canIgnoreHim') ){
			return false;
		}

		if ( strpos($metaKey, 'wilcity_promote_') !== false && is_numeric($metaValue) && !empty($metaValue) ){
			$listingID = GetSettings::getPostMeta($objectID, 'listing_id');

			$position = str_replace('wilcity_promote_',  '',$metaKey);
			$this->clearExpirationPromotion($position, $listingID);

			wp_schedule_single_event($metaValue, $this->generateScheduleKey($position), array($listingID, $position));
			update_post_meta($listingID, $metaKey, $metaValue);

			if ( strpos($metaKey, 'top_of_search') !== false ){
				$this->updateMenuOrder($listingID);
			}
		}else if ( $metaKey == 'wilcity_listing_id' ){
			SetSettings::setPostMeta($metaValue, $this->belongsToPromotionKey, $objectID);
		}
	}

	protected function getWooCommercePlanSettings($productID){
		$aPromotionPlans = $this->getPromotionPlans();
		foreach ($aPromotionPlans as $aPromotion){
			if ( $aPromotion['productAssociation'] == $productID ){
				return $aPromotion;
			}
		}
	}

	protected function getWooCommercePlans(){
		if ( !empty($this->aWoocommercePlans) ){
			return $this->aWoocommercePlans;
		}

		$aPromotionPlans = $this->getPromotionPlans();
		foreach ($aPromotionPlans as $aPromotion){
			$this->aWoocommercePlans[] = $aPromotion['productAssociation'];
		}
		return $this->aWoocommercePlans;
	}

	public function updateWooCommercePromotion($aItem, $orderID){
		$aWooCommercePlans = $this->getWooCommercePlans();
		if ( !in_array($aItem['product_id'], $aWooCommercePlans) ){
			return false;
		}

		$aResponse = WooCommerceController::setupReceiptDirectly(array(
			'packageType'   => 'promotion',
			'orderID'       => $orderID,
			'planName'      => get_the_title($aItem['product_id']),
			'productID'     => $aItem['product_id'],
			'billingType'   => wilokeListingToolsRepository()->get('payment:billingTypes', true)->sub('nonrecurring')
		));

		if ( !isset($aResponse['paymentID']) || empty($aResponse['paymentID']) ){
			wp_send_json_error(array(
				'msg' => esc_html__('We could not insert the payment id', 'wiloke-listing-tools')
			));
		}

		PaymentMetaModel::set($aResponse['paymentID'], 'boost_post_data', array(
			'postID' => Session::getSession('woocommerce_boost_listing_id'),
			'plans'  => array($this->getWooCommercePlanSettings($aItem['product_id'])),
            'productID' => $aItem['product_id']
		));
	}

	public function cancelPostPromotion($aInfo){
		$aBoostPostData = PaymentMetaModel::get($aInfo['paymentID'], 'boost_post_data');
		if ( empty($aBoostPostData) ){
			return true;
		}
		$this->decreasePostPromotion($aBoostPostData);
	}

	protected function clearExpirationPromotion($position, $postID){
		wp_clear_scheduled_hook($this->generateScheduleKey($position), array($postID, $position));
	}

	public function decreasePostPromotion($aBoostPostData){
		foreach ($aBoostPostData['plans'] as $aInfo){
			$this->clearExpirationPromotion($aInfo['position'], $aBoostPostData['postID']);
			SetSettings::deletePostMeta($aBoostPostData['postID'], $aInfo['position']);
		}
	}

	public function updatePostPromotion($aBoostPostData, $isSucceeded=false){
		$promotionID = GetSettings::getPostMeta($aBoostPostData['postID'], $this->belongsToPromotionKey);

		if ( empty($promotionID) ){
			$promotionID = wp_insert_post(array(
				'post_title' => 'Promote ' . get_the_title($aBoostPostData['postID']),
				'post_type'  => 'promotion',
				'post_status'=> 'draft',
				'post_author'=> get_current_user_id()
			));
		}

		foreach ($aBoostPostData['plans'] as $aInfo){
			SetSettings::setPostMeta($promotionID, 'promote_'.$aInfo['position'], strtotime('+ ' . $aInfo['duration'] . ' days'));
		}

		SetSettings::setPostMeta($aBoostPostData['postID'], $this->belongsToPromotionKey, $promotionID);
		SetSettings::setPostMeta($promotionID, 'listing_id', $aBoostPostData['postID']);

		if ( $isSucceeded ){
			wp_update_post(array(
				'ID' => $promotionID,
				'post_status' => 'publish'
			));
		}
		return $promotionID;
	}

	public function updateWoocommercePostPromotion($aData){
		Session::destroySession('woocommerce_boost_listing_id');
		$aPaymentIDs = PaymentModel::getPaymentIDsByWooOrderID($aData['orderID']);

		if ( empty($aPaymentIDs) ){
			return true;
		}

		$i = 0;
		foreach ($aPaymentIDs as $aPaymentID){
			$aBoostPostData = PaymentMetaModel::get($aPaymentID['ID'], 'boost_post_data');
			$i++;
			if ( !empty($aBoostPostData) ){
				$this->updatePostPromotion($aBoostPostData, true);
			}
		}
	}

	public function cancelWooCommercePostPromotion($aData){
		$aPaymentIDs = PaymentModel::getPaymentIDsByWooOrderID($aData['orderID']);
		if ( empty($aPaymentIDs) ){
			return true;
		}

		foreach ($aPaymentIDs as $aPaymentID){
			$aBoostPostData = PaymentMetaModel::get($aPaymentID['ID'], 'boost_post_data');
			if ( !empty($aBoostPostData) ){
				$this->decreasePostPromotion($aBoostPostData);
			}
		}
	}

	public function updatePayPalPostPromotion($aInfo){
		if ( $aInfo['gateway'] != 'paypal' && $aInfo['gateway'] != 'banktransfer' ){
			return false;
		}

		$aBoostPostData = PaymentMetaModel::get($aInfo['paymentID'], 'boost_post_data');
		if ( empty($aBoostPostData) ){
			$msg = 'The promotion plans are emptied. Please contact the theme author to solve this issue';
			if ( wp_doing_ajax() ){
				wp_send_json_error(array('msg'=>$msg));
			}
			throw new \Exception($msg);
		}

		SetSettings::deletePostMeta($aBoostPostData['postID'], 'promotion_wait_for_bank_transfer');
		$this->updatePostPromotion($aBoostPostData, true);
	}

	public function boostListing(){
		$this->middleware(['isPublishedPost'], array(
			'postID' => $_POST['postID']
		));

		$noPlanMsg = esc_html__('You have to select 1 plan at least', 'wiloke-listing-tools');

		if ( !isset($_POST['aPlans']) || empty($_POST['aPlans']) ){
			wp_send_json_error(array(
				'msg' => $noPlanMsg
			));
		}

		$aSelectedPlans = array();
		$aSelectedPlanKeys = array();

		$aPromotionPlans = GetSettings::getOptions('promotion_plans');

		foreach ($_POST['aPlans'] as $aPlan){
			if ( isset($aPlan['value']) && $aPlan['value'] == 'yes' ){
				$aSelectedPlanKeys[] = $aPlan['position'];
			}
		}

		if ( empty($aSelectedPlanKeys) ){
			wp_send_json_error(array(
				'msg' => $noPlanMsg
			));
		}

		$total = 0;

		foreach ($aPromotionPlans as $aPlan ){
			if ( in_array($aPlan['position'], $aSelectedPlanKeys) ){
				$total += floatval($aPlan['price']);
				$aSelectedPlans[] = $aPlan;
			}
		}

		if ( empty($total) ){
			wp_send_json_error(array(
				'msg' => $noPlanMsg
			));
		}

		Session::setSession(wilokeListingToolsRepository()->get('payment:category'), 'promotion');

		if ( isset($_POST['gateway']) && !empty($_POST['gateway']) ){
			$this->middleware(['isGatewaySupported'], array(
				'gateway' => $_POST['gateway']
			));

			$aPlanSettings = array(
				'total'         => $total,
				'planName'      => esc_html__('Promotion - ', 'wiloke-listing-tools') . get_the_title($_POST['postID']),
				'billingType'   => wilokeListingToolsRepository()->get('payment:billingTypes', true)->sub('nonrecurring'),
				'packageType'   => 'promotion',
                'oStripeData'   => $_POST['oStripeData']
			);
			$aBoostPostData = array(
				'postID' => $_POST['postID'],
				'plans'  => $aSelectedPlans
			);

			switch ($_POST['gateway']){
				case 'paypal':
					$aResponse = PayPalController::setupReceiptDirectly($aPlanSettings);

					if ( $aResponse['status'] !== 'success' ){
						Session::destroySession(wilokeListingToolsRepository()->get('payment:category'));
						wp_send_json_error(array(
							'msg' => esc_html__('Oops! Something went wrong. The PayPal gateway could not execute', 'wiloke-listing-tools')
						));
					}else{
						PaymentMetaModel::set($aResponse['paymentID'], 'boost_post_data', array(
							'postID' => $_POST['postID'],
							'plans'  => $aSelectedPlans
						));
						wp_send_json_success($aResponse);
					}
					break;

				case 'stripe':
					$aResponse = StripeController::setupReceiptDirectly($aPlanSettings);

					if ( $aResponse['status'] == 'success' ){
						PaymentMetaModel::set($aResponse['paymentID'], 'boost_post_data', $aBoostPostData);
						$this->updatePostPromotion($aBoostPostData, true);
						wp_send_json_success(array(
							'msg' => esc_html__('Congratulations! Your post has been boosted successfully', 'wiloke-listing-tools')
						));
					}else{
						Session::destroySession(wilokeListingToolsRepository()->get('payment:category'));
						wp_send_json_error(array(
							'msg' => $aResponse['msg']
						));
					}

					break;

				case 'banktransfer':
					if ( GetSettings::getPostMeta($_POST['postID'], 'promotion_wait_for_bank_transfer') ){
						wp_send_json_error(array(
							'msg' => esc_html__('You have already submitted a request via Bank Transfer before, please complete the payment to boost your post.', 'wiloke-listing-tools')
						));
					}else{
						$aResponse = DirectBankTransferController::setupReceiptDirectly($aPlanSettings);
						if ( $aResponse['status'] == 'error' ){
							Session::destroySession(wilokeListingToolsRepository()->get('payment:category'));
							wp_send_json_error(array(
								'msg' => $aResponse['msg']
							));
						}else{
							PaymentMetaModel::set($aResponse['paymentID'], 'boost_post_data', $aBoostPostData);
							$promotionPageID = $this->updatePostPromotion($aBoostPostData, false);
							PaymentMetaModel::set($aResponse['paymentID'], 'promotion_page_id', $promotionPageID);
							do_action('wiloke/promotion/submitted', get_current_user_id(), $_POST['postID']);

							wp_send_json_success(array(
								'msg' => esc_html__('Congratulations! Your submission has been approved. Please complete the payment to boost your post.', 'wiloke-listing-tools')
							));
						}
					}
					break;
			}
		}else{
			// Pay via WooCommerce
			$aProductIDs = array_map(function($aPlan){
				return $aPlan['productAssociation'];
			}, $aSelectedPlans);

			/*
			 * @WooCommerceController:removeProductFromCart
			 */
			global $woocommerce;
			do_action('wiloke-listing-tools/before-redirecting-to-cart', $aProductIDs);

			Session::setSession('woocommerce_boost_listing_id', $_POST['postID']);

			global $woocommerce;
			wp_send_json_success(array(
				'productIDs'=> $aProductIDs,
				'cartUrl'   => $woocommerce->cart->get_cart_url()
			));
		}
	}

	public function getPaymentGateways(){
		$aPromotions = GetSettings::getOptions('promotion_plans');
		if ( empty($aPromotions) ){
			wp_send_json_error();
		}

		foreach ($aPromotions as $aPromotion){
			if ( isset($aPromotion['productAssociation']) && !empty($aPromotion['productAssociation']) ){
				wp_send_json_error();
			}
		}

		$gateways = GetWilokeSubmission::getField('payment_gateways');
		if ( empty($gateways) ){
			wp_send_json_error(array(
				'msg' => esc_html__('You do not have any gateways. Please go to Wiloke Submission to set one.')
			));
		}

		wp_send_json_success($gateways);
	}

	public function fetchPromotions(){
		$aPromotions    = GetSettings::getOptions('promotion_plans');
		$currency       = GetWilokeSubmission::getField('currency_code');
		$symbol         = GetWilokeSubmission::getSymbol($currency);
		$position       = GetWilokeSubmission::getField('currency_position');

		$promotionID = GetSettings::getPostMeta($_POST['postID'], $this->belongsToPromotionKey);

		if ( !empty($promotionID) && get_post_status($promotionID) === 'publish' ){
			$now = current_time('timestamp');
			foreach ($aPromotions as $key => $aPlanSetting){
				$val = GetSettings::getPostMeta($promotionID, 'promote_'.$aPlanSetting['position']);
				if ( !empty($val) ){
					$val = abs($val);
					if ( $now < $val ){
						$aPromotions[$key]['isUsing'] = 'yes';
					}
				}
			}
		}

		wp_send_json_success(
			array(
				'plans'     => $aPromotions,
				'position'  => $position,
				'symbol'    => $symbol
			)
		);
	}

	public function onChangedPromotionStatus($postID, $oPostAfter, $oPostBefore){
		if ( $oPostAfter->post_type !== 'promotion' || $oPostAfter->post_status == $oPostBefore->post_status ){
			return false;
		}

		$listingID = GetSettings::getPostMeta($postID, 'listing_id');
		if ( empty($listingID) ){
			return false;
		}

		$aPromotionPlans = $this->getPromotionPlans();
		if ( empty($aPromotionPlans) ){
			return false;
		}

		$aPlanKeys = array_keys($aPromotionPlans);

		if ( $oPostAfter->post_status == 'publish' ){
		    if ( $oPostBefore->post_status == 'publish' ){
                return false;
            }

			foreach ($aPlanKeys as $position){
				$this->clearExpirationPromotion($position, $listingID);
				$newVal = GetSettings::getPostMeta($postID, 'promote_'.$position);

				if ( !empty($newVal) ){
					wp_schedule_single_event($newVal, $this->generateScheduleKey($position), array($listingID, $position));
					SetSettings::setPostMeta($listingID, 'promote_'.$position, $newVal);

					if ( $position == 'top_of_search' ){
						$this->updateMenuOrder($listingID, true);
					}
				}
			}

			do_action('wiloke/promotion/approved', $listingID);
			SetSettings::setPostMeta($listingID, $this->belongsToPromotionKey, $postID);
		}else{
			foreach ($aPlanKeys as $position){
				$this->clearExpirationPromotion($position, $listingID);
				SetSettings::deletePostMeta($listingID, 'promote_'.$position);

				if ( $position == 'top_of_search' ){
					$promotionExists = GetSettings::getPostMeta($listingID, 'promote_'.$position);
					if ( !empty($promotionExists) ){
						$this->updateMenuOrder($listingID, false);
                    }
				}
			}
			SetSettings::deletePostMeta($listingID, $this->belongsToPromotionKey);
		}
	}

	public function maybeChangeListingID($metaID, $objectID, $metaKey, $metaValue){
		if ( (get_post_type($objectID) !== 'promotion') || get_post_status($objectID) != 'publish' ){
			return false;
		}

		if ( $metaKey == 'wilcity_listing_id' ){
			$currentListing = GetSettings::getPostMeta($metaValue, $this->belongsToPromotionKey);
			if ( $metaValue != $currentListing ){
				SetSettings::setPostMeta($objectID, 'canIgnoreHim', true);
				$this->deleteAllPlansOfListing($currentListing);
				SetSettings::deletePostMeta($objectID, 'canIgnoreHim');
			}
		}
	}
}