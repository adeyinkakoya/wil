<?php
namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\Inc;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Models\InvoiceModel;
use WilokeListingTools\Models\PaymentMetaModel;
use WilokeListingTools\Models\PaymentModel;
use WilokeListingTools\Models\PlanRelationshipModel;

class InvoiceController extends Controller {
	public function __construct() {
		add_action('wiloke-listing-tools/changed-payment-status', array($this, 'update'));
		add_action('wiloke-listing-tools/woocommerce/after-order-succeeded', array($this, 'insertNewInvoiceAfterPayViaWooCommerceSucceeded'), 10, 1);
		add_action('wp_ajax_delete_all_invoices', array($this, 'deleteAllInvoices'));
		add_action('wilcity/paypal/insert-invoice', array($this, 'prepareInsertInvoice'), 10, 2);
		add_action('wilcity/stripe/insert-invoice', array($this, 'prepareInsertInvoice'), 10, 2);
		add_action('wilcity/direct-bank-transfer/insert-invoice', array($this, 'prepareInsertInvoice'), 10, 2);
		add_action('wp_ajax_download_invoice', array($this, 'downloadInvoice'));
	}

	public function downloadInvoice(){
		if ( !isset($_POST['invoiceID']) || empty($_POST['invoiceID']) ){
			header('HTTP/1.0 403 Forbidden');
		}

		$paymentID = InvoiceModel::getField('paymentID', $_POST['invoiceID']);

		$userID = PaymentModel::getField('userID', $paymentID);
		if ( $userID != get_current_user_id() ){
			header('HTTP/1.0 403 Forbidden');
		}

		$aInvoice = InvoiceModel::getInvoiceDetails($_POST['invoiceID']);

		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename=invoices-".date_i18n('Y-m-D', strtotime($aInvoice['created_at'])) . '.csv');
		header("Content-Transfer-Encoding: binary");
		$csv_header = '';

		$csv_header .= esc_html__('ID', 'wiloke-listing-tools').', '.esc_html__('Description', 'wiloke-listing-tools').', '.esc_html__('Total', 'wiloke-listing-tools').', '.esc_html__('Sub Total', 'wiloke-listing-tools').', '. esc_html__('Discount', 'wiloke-listing-tools') . ', ' .  esc_html__('Tax', 'wiloke-listing-tools').', '.esc_html__('Billing At', 'wiloke-listing-tools')."\n";

		$planName = get_the_title($aInvoice['paymentID']);
		if ( empty($planName) ){
			$planName = PaymentMetaModel::get($aInvoice['paymentID'], 'planName');
		}

		$csv_row = $aInvoice['ID'] . ', ' . $planName . ',' . GetWilokeSubmission::renderPrice($aInvoice['total'], $aInvoice['currency'], false) . ', ' . GetWilokeSubmission::renderPrice($aInvoice['subTotal'], $aInvoice['currency'], false) . ', ' . GetWilokeSubmission::renderPrice($aInvoice['discount'], $aInvoice['currency'], false) . ', '  . 0 . ', ' . date('Y-m-d', strtotime($aInvoice['created_at'])) . "\n";

		echo $csv_header . $csv_row;
		die();

	}

	protected function insertInvoice($paymentID, $aData){
		$invoiceID = InvoiceModel::set(
			$paymentID,
			$aData
		);
		if ( $invoiceID ){
			do_action('wilcity/inserted-invoice', array(
				'paymentID'=>$paymentID,
				'total'    =>$aData['total'],
				'subTotal' =>$aData['subTotal'],
				'tax'      =>$aData['tax'],
				'currency' =>$aData['currency'],
				'discount' =>$aData['discount'],
				'invoiceID'=>$invoiceID
			));
		}
	}

	public function prepareInsertInvoice($paymentID, $aData){
		$this->insertInvoice($paymentID, $aData);
	}

	public function deleteAllInvoices(){
		$this->middleware('[isAdministrator]');
		InvoiceModel::deleteAll();

		wp_send_json_success();
	}

	public function insertNewInvoiceAfterPayViaWooCommerceSucceeded($aData){
		$oOrder = new \WC_Order($aData['orderID']);
		$aItems = $oOrder->get_items();

		$packageType = PaymentModel::getPackageTypeByOrderID($aData['orderID']);
		if ( $packageType == 'promotion' ){
			$aPaymentIDs = PaymentModel::getPaymentIDsByWooOrderID($aData['orderID']);
			$order = 0;
			foreach ( $aItems as $aItem ) {
				$paymentID = $aPaymentIDs[$order]['ID'];
				$invoiceID = InvoiceModel::getInvoiceIDByPaymentID($paymentID);

				if ( empty($invoiceID) ){
					InvoiceModel::set(
						$paymentID,
						array(
							'currency'  => $oOrder->get_currency(),
							'subTotal'  => $aItem['subtotal'],
							'discount'  => floatval($aItem['subtotal']) - floatval($aItem['total']),
							'tax'       => $aItem['total_tax'],
							'total'     => $aItem['total']
						)
					);
				}
				$order++;
			}
		}else{
			foreach ( $aItems as $aItem ) {
				$productID = $aItem['product_id'];
				$planID = PlanRelationshipModel::getPlanIDByProductID($productID);
				//$payment = PlanRelationshipModel::getPlanIDByProductID($productID);

				if ( !empty($planID) ){
					$paymentID = PaymentModel::getPaymentIDByOrderIDAndPlanID($aData['orderID'], $planID);
					$invoiceID = InvoiceModel::getInvoiceIDByPaymentID($paymentID);

					if ( empty($invoiceID) ){
						InvoiceModel::set(
							$paymentID,
							array(
								'currency'  => $oOrder->get_currency(),
								'subTotal'  => $aItem['subtotal'],
								'discount'  => floatval($aItem['subtotal']) - floatval($aItem['total']),
								'tax'       => $aItem['total_tax'],
								'total'     => $aItem['total']
							)
						);
					}
				}
			}
		}
	}

	/*
	 * For Direct Bank Transfer Only
	 */
	public function update($aInfo){
		if ( $aInfo['newStatus'] != 'active' && $aInfo['newStatus'] != 'succeeded' && $aInfo['gateway'] != 'banktransfer' ){
			return false;
		}

		$aTransactionInfo = PaymentMetaModel::get($aInfo['paymentID'], wilokeListingToolsRepository()->get('payment:paymentInfo'));

		if ( GetWilokeSubmission::isNonRecurringPayment($aInfo['billingType']) || ( !GetWilokeSubmission::isNonRecurringPayment($aInfo['billingType']) && $aInfo['newStatus'] == 'active' && $aInfo['oldStatus'] == 'processing' ) ){
			InvoiceModel::set(
				$aInfo['paymentID'],
				array(
					'currency'      => $aTransactionInfo['currency'],
					'subTotal'      => $aTransactionInfo['subTotal'],
					'discount'      => $aTransactionInfo['discount'],
					'tax'           => $aTransactionInfo['tax'],
					'total'         => $aTransactionInfo['total']
				)
			);
		}
	}
}