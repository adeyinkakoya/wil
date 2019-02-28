<?php
namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\Message;
use WilokeListingTools\Framework\Payment\Receipt;
use WilokeListingTools\Framework\Payment\WooCommerce\WooCommerceNonRecurringPayment;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Framework\Store\Session;
use WilokeListingTools\Models\PaymentModel;
use WilokeListingTools\Models\PlanRelationshipModel;

class WooCommerceController extends Controller {
	public $planID;
	public $productID;
	public $orderID;
	public $oReceipt;
	protected $aPaymentIDs;
	public $gateway = 'woocommerce';

	public function __construct() {
		add_action('wiloke-listing-tools/before-redirecting-to-cart', array($this, 'removeProductFromCart'), 10, 1);
		add_filter('woocommerce_add_to_cart_validation', array($this, 'cleanEverythingBeforeAddProductToCart'), 0);
		add_action('woocommerce_add_to_cart', array($this, 'removeAssociatePlanItems'), 0);
		add_action('wiloke-listing-tools/payment-via-woocommerce', array($this, 'preparePayment'), 10, 2);

		add_action('woocommerce_checkout_order_processed', array($this, 'newOrderCreated'), 5, 2);
		add_action('woocommerce_order_status_completed', array($this, 'paymentSucceeded'), 5);
		add_action('woocommerce_order_status_failed', array($this, 'paymentFailed'), 5);
		add_action('woocommerce_order_status_on-hold', array($this, 'paymentOnHold'), 5);
		add_action('woocommerce_order_status_refunded', array($this, 'paymentRefunded'), 5);
		add_action('woocommerce_order_status_cancelled', array($this, 'paymentCancelled'), 5);
		add_action('woocommerce_order_status_processing', array($this, 'paymentProcessing'), 5);
//		add_action('woocommerce_thankyou', array($this, 'autoCompleteOrder'), 10, 1 );
		add_action('woocommerce_single_product_summary', array($this, 'removeGalleryOfWooBookingOnTheSidebar'), 1);
		add_action('wilcity/before-close-header-tag', array($this, 'addQuickCart'));
	}

	public function addQuickCart(){
		if ( class_exists( 'woocommerce' ) ) {
			// Is this the cart page?
			if ( is_cart() || WC()->cart->get_cart_contents_count() == 0 ){
				return false;
			}

			?>
			<div class="header_cartWrap__bOA2i active">
				<div class="header_cartBtn__1gAQU">
					<span><?php echo esc_html(WC()->cart->get_cart_contents_count()); ?></span>
					<div class="header_cartIcon__18VjH">
						<i class="la la-shopping-cart"></i>
					</div>
					<div class="header_product__1q6pw product-cart-js">
						<header class="header_cartHeader__2LxzS"><h4 class="header_cartTitle__l46ln"><i class="la la-shopping-cart"></i><?php echo esc_html__('Total Items', 'wiloke-listing-tools'); ?> <?php echo esc_html(WC()->cart->get_cart_contents_count()); ?></h4></header>

							<?php woocommerce_mini_cart(); ?>
							
					</div>
				</div>
			</div>
			<?php
		}
	}

	public function removeGalleryOfWooBookingOnTheSidebar(){
		if ( General::$isBookingFormOnSidebar ){
			remove_action('woocommerce_single_product_summary','woocommerce_template_single_title', 5);
			remove_action('woocommerce_single_product_summary','woocommerce_template_single_rating', 10);
			remove_action('woocommerce_template_single_price','woocommerce_template_single_price', 15);
			remove_action('woocommerce_single_product_summary','woocommerce_template_single_excerpt', 20);
		}
	}

	public function autoCompleteOrder($orderID){
		if ( !$orderID ){
			return;
		}

		$order = wc_get_order($orderID);
		$paymentMethod = get_post_meta($orderID, '_payment_method', true);
		// No updated status for orders delivered with Bank wire, Cash on delivery and Cheque payment methods.

		if ( in_array($paymentMethod, array('basc', 'cod', 'cheque')) ){
			return;
		}else {
			$paymentID = PaymentModel::getPaymentIDsByWooOrderID($orderID, true);

			if ( !empty($paymentID) ){
				$order->update_status( 'completed' );
			}
		}
	}

	public function cleanEverythingBeforeAddProductToCart($cart_item_data){
		if ( !isset($_GET['add-to-cart']) || empty($_GET['add-to-cart']) ){
			return $cart_item_data;
		}
		global $woocommerce;

		$planID = PlanRelationshipModel::getPlanIDByProductID($_GET['add-to-cart']);
		if ( empty($planID) ){
			return $cart_item_data;
		}

		$woocommerce->cart->empty_cart();
		return true;
	}

	public function removeAssociatePlanItems(){
		global $woocommerce;
		if ($woocommerce->cart->get_cart_contents_count() == 0) {
			return false;
		}

		$productID = Session::getSession(wilokeListingToolsRepository()->get('payment:associateProductID'));
		if ( empty($productID) ){
			return false;
		}

		foreach ( $woocommerce->cart->get_cart() as $cartItemKey => $aCardItem ) {
			$planID = PlanRelationshipModel::getPlanIDByProductID($productID);
			if ( empty($planID) ){
				continue;
			}

			if($aCardItem['product_id'] != $productID ){
				$woocommerce->cart->remove_cart_item($cartItemKey);
			}
		}
	}

	public function removeProductFromCart($productIDs){
		global $woocommerce;
		foreach ( $woocommerce->cart->get_cart() as $cartItemKey => $aCardItem ) {
			if ( is_array($productIDs) ){
				if( in_array($aCardItem['product_id'], $productIDs) ){
					$woocommerce->cart->remove_cart_item($cartItemKey);
				}
			}else{
				if( $aCardItem['product_id'] == $productIDs ){
					$woocommerce->cart->remove_cart_item($cartItemKey);
				}
			}
		}
	}

	public function updateStatusOfPaymentViaWooCommerce($orderID, $status){
		$aSessionIDs = PaymentModel::getPaymentIDsByWooOrderID($orderID);

		if ( empty($aSessionIDs) ){
			return false;
		}

		foreach ($aSessionIDs as $aSession){
			$this->aPaymentIDs[] = $aSession['ID'];
			PaymentModel::updatePaymentStatus($status, $aSession['ID']);

			if ( $status == 'pending' || $status == 'onhold' ){
				PostController::migratePostsToExpiredStatus($aSession['ID']);
			}else if ( $status == 'completed' || $status == 'processing'  ){
				PostController::migratePostsToPendingOrPublishStatus($aSession['ID']);
			}else if ( $status == 'refunded' || $status == 'cancelled' ){
				PostController::migratePostsToDraftStatus($aSession['ID']);
			}
		}
	}

	public function updatePaymentStatusByOrderID($orderID, $status){
		$oOrder = new \WC_Order($orderID);
		$aItems = $oOrder->get_items();

		$planID = '';
		foreach ( $aItems as $aItem ) {
			$productID = $aItem['product_id'];
			$planID = PlanRelationshipModel::getPlanIDByProductID($productID);

			do_action('wiloke-listing-tools/woocommerce/order-'.$status, array(
				'planID'    => $planID,
				'status'    => $status,
				'gateway'   => $this->gateway,
				'productID' => $productID,
				'orderID'   => $orderID
			));

			$this->updateStatusOfPaymentViaWooCommerce($orderID, $status);
		}

		do_action('wiloke-listing-tools/woocommerce/after-order-'.$status, array(
			'planID'    => $planID,
			'status'    => $status,
			'gateway'   => $this->gateway,
			'orderID'   => $orderID
		));
	}

	public function paymentOnHold($orderID){
		$this->updatePaymentStatusByOrderID($orderID, 'onhold');
	}

	public function paymentRefunded($orderID){
		$this->updatePaymentStatusByOrderID($orderID, 'refunded');
	}

	public function paymentCancelled($orderID){
		$this->updatePaymentStatusByOrderID($orderID, 'cancelled');
	}

	public function paymentProcessing($orderID){
		$this->updatePaymentStatusByOrderID($orderID, 'processing');
	}

	public function paymentPending($orderID){
		$this->updatePaymentStatusByOrderID($orderID, 'pending');
	}

	public function paymentFailed($orderID){
		$this->updatePaymentStatusByOrderID($orderID, 'failed');
	}

	public function paymentSucceeded($orderID){

		$oOrder = new \WC_Order($orderID);
		$aItems = $oOrder->get_items();
		$planID = '';
		$paymentID = PaymentModel::getPaymentIDsByWooOrderID($orderID, true);

		if ( empty($paymentID) ){
			return false;
		}

		$category = Session::getSession(wilokeListingToolsRepository()->get('payment:category'), true);
		$category = empty($category) ? md5('dadadadad') : $category;
		$claimedID = Session::getSession(wilokeListingToolsRepository()->get('claim:sessionClaimID'), true);

		foreach ( $aItems as $aItem ) {
			$productID = $aItem['product_id'];
			$planID = PlanRelationshipModel::getPlanIDByProductID($productID);

			$this->updateStatusOfPaymentViaWooCommerce($orderID, 'completed');
			/*
			 * @PromotionController:updateWoocommercePostPromotion 10
			 */
			$aInformation = array(
				'planID'    => $planID,
				'status'    => 'succeeded',
				'userID'    => get_current_user_id(),
				'gateway'   => $this->gateway,
				'productID' => $productID,
				'claimID'   => $claimedID
			);
			do_action('wiloke-listing-tools/woocommerce/order-succeeded', $aInformation);
			do_action('wiloke-listing-tools/woocommerce/order-succeeded/'.$category, $aInformation);
		}

		$objectID  = PlanRelationshipModel::getFirstObjectIDByPaymentID($paymentID);
		/*
		 * @PaymentStatusController:updateWooCommercePaymentsStatus 5
		 * @UserController:setUserPlanOfUserBoughtViaWooCommerce 20
		 * @PostController:migrateAllListingsBelongsToWooCommerceToPublish 20
		 */
		$aInformation = array(
			'status'        => 'succeeded',
			'gateway'       => $this->gateway,
			'orderID'       => $orderID,
			'userID'        => get_current_user_id(),
			'postID'        => $objectID,
			'planID'        => $planID,
			'paymentID'     => $paymentID,
			'claimID'       => $claimedID,
			'billingType'   => wilokeListingToolsRepository()->get('payment:billingTypes', true)->sub('nonrecurring')
		);
		do_action('wiloke-listing-tools/woocommerce/after-order-succeeded', $aInformation);
		do_action('wiloke-listing-tools/woocommerce/after-order-succeeded/'.$category, $aInformation);

	}

	public function preparePayment($planID, $productID, $orderID){
		if ( empty($planID) || empty($productID) || empty($orderID) ){
			Message::error(esc_html__('Product ID, Order ID and Plan ID are required', 'wiloke-listing-tools'));
		}

		$this->planID       = $planID;
		$this->orderID      = $orderID;
		$this->productID    = $productID;

		$this->oReceipt = new Receipt(array(
			'planID'    => $this->planID,
			'productID' => $this->productID,
			'userID'    => get_current_user_id(),
			'couponCode'=> '',
			'aRequested'=>''
		));
		$this->oReceipt->setupPlan();
		$instWooPayment = new WooCommerceNonRecurringPayment();
		$instWooPayment->setOrderID($this->orderID);
		$instWooPayment->proceedPayment($this->oReceipt);
	}

	public static function setupReceiptDirectly($aData){
		$aDefault = array(
			'userID'                => get_current_user_id(),
			'productID'             => $aData['productID'],
			'isNonRecurringPayment' => $aData['billingType'] == wilokeListingToolsRepository()->get('payment:billingTypes', true)->sub('nonrecurring'),
		);

		$oReceipt = new Receipt(array_merge($aDefault, $aData));
		$oReceipt->setupPriceDirectly();

		$oPayPalMethod = new WooCommerceNonRecurringPayment();
		$oPayPalMethod->setOrderID($aData['orderID']);
		return $oPayPalMethod->proceedPayment($oReceipt);
	}

	public function newOrderCreated($orderID){
		$oOrder = new \WC_Order($orderID);
		$aItems = $oOrder->get_items();

		foreach ( $aItems as $aItem ) {
			$productID = $aItem['product_id'];
			$planID = PlanRelationshipModel::getPlanIDByProductID($productID);

			if ( !empty($planID) ){
				$this->preparePayment($planID, $productID, $orderID);
			}else{
				do_action('wiloke-listing-tools/woocommerce/order-created', $aItem, $orderID);
			}
		}
	}

}