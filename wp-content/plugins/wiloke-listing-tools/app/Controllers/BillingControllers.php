<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Models\InvoiceModel;
use WilokeListingTools\Models\PaymentMetaModel;
use WilokeListingTools\Models\UserModel;

class BillingControllers extends Controller {
	public $limit = 4;
	public function __construct() {
		add_action('wp_ajax_wilcity_fetch_my_billings', array($this, 'fetchBillings'));
		add_action('wp_ajax_wilcity_fetch_my_billing_details', array($this, 'fetchBillingDetails'));
		add_action('wp_ajax_wilcity_fetch_my_plan', array($this, 'fetchMyPlan'));
		add_action('wp_ajax_wilcity_post_type_plans', array($this, 'fetchPostTypePlans'));
	}

	public function fetchPostTypePlans(){
		$aGateways = GetWilokeSubmission::getGatewaysWithName(true);
		$noGateway = esc_html__('There are Payment Gateways', 'wiloke-listing-tools');

		if ( empty($aGateways) ){
			wp_send_json_error(array(
				'msg' => $noGateway
			));
		}

		if ( !isset($_POST['postType']) || empty($_POST['postType']) ){
			wp_send_json_error(array(
				'msg' => esc_html__('The post type is required', 'wiloke-listing-tools')
			));
		}

		$aPlanIDs = GetWilokeSubmission::getAddListingPlans($_POST['postType'].'_plans');
		if ( empty($aPlanIDs) ){
			wp_send_json_error(array(
				'msg' => esc_html__('We found no plans in this listing type.', 'wiloke-listing-tools')
			));
		}

		$query = new \WP_Query(
			array(
				'post_type' => 'listing_plan',
				'post_status' => 'publish',
				'post__in' => $aPlanIDs,
				'orderby'  => 'post__in'
			)
		);

		if ( !$query->have_posts() ){
			wp_send_json_error(array(
				'msg' => esc_html__('We found no plans in this listing type.', 'wiloke-listing-tools')
			));
		}

		global $post;
		$aPlans = array();
		while ($query->have_posts()){
			$query->the_post();
			$aPlanSettings = GetSettings::getPlanSettings($post->ID);
			ob_start();
			\WILCITY_SC\SCHelpers::renderPlanPrice($aPlanSettings['regular_price']);
			$price = ob_get_contents();
			ob_end_clean();

			$aPlans[] = array(
				'postTitle' => get_the_title($post->ID),
				'content'   => $post->post_content,
				'ID'        => $post->ID,
				'price'     => $price
			);
		}
		wp_reset_postdata();

		$aGatewayOptions = array();
		foreach ($aGateways as $gateway => $name){
			$aGatewayOptions[] = array(
				'name' => $name,
				'value'=> $gateway
			);
		}

		wp_send_json_success(array(
			'aPlans'    => $aPlans,
			'aGateways' => $aGatewayOptions
		));
	}

	public function fetchMyPlan(){
		$aRawUserPlans = UserModel::getAllPlans(get_current_user_id());

		if ( empty($aRawUserPlans) ){
			wp_send_json_error(array(
				'msg' => esc_html__('You have not used any plan.', 'wiloke-listing-tools')
			));
		}

		$aUserPlans = array();
		foreach ($aRawUserPlans as $aPlans){
			$order = 0;
			foreach ($aPlans as $planID => $aPlan){
				$aUserPlans[$order] = $aPlan;

				$planTitle = get_the_title($planID);
				$planTitle = empty($planTitle) ? PaymentMetaModel::get($aPlan['paymentID'], 'planName') : $planTitle;

				$aUserPlans[$order]['planName'] = !empty($planTitle) ? $planTitle : esc_html__('This plan might have been deleted.', 'wiloke-listing-tools');
				$aUserPlans[$order]['planID'] = $planID;
				if ( GetWilokeSubmission::isNonRecurringPayment($aPlan['billingType']) ){
					$aUserPlans[$order]['nextBillingDate'] = esc_html__('No', 'wiloke-listing-tools');
				}else{
					if ( empty($aUserPlans[$order]['nextBillingDateGMT']) ){
						$aUserPlans[$order]['nextBillingDate'] = esc_html__('Updating', 'wiloke-listing-tools');
					}else{
						$aUserPlans[$order]['nextBillingDate'] = date_i18n(get_option('date_format'), $aUserPlans[$order]['nextBillingDateGMT']);
					}
				}
				if ( !isset($aPlan['postType']) ){
					$aUserPlans[$order]['postType'] = '';
				}
				$aUserPlans[$order]['isNonRecurringPayment'] = GetWilokeSubmission::isNonRecurringPayment($aPlan['billingType']) ? 'yes' : 'no';
				$order++;
			}
 		}

		wp_send_json_success($aUserPlans);
	}

	public function fetchBillingDetails(){
		$aResult = InvoiceModel::getInvoiceDetails($_POST['invoiceID']);
		if ( empty($aResult) ){
			wp_send_json_error(array(
				'msg' => esc_html__('This plan might have been deleted', 'wiloke-listing-tools')
			));
		}

		wp_send_json_success($aResult);
	}

	public function fetchBillings(){
		$offset = (abs($_POST['page'])-1)*$this->limit;

		$aInvoices = InvoiceModel::getMyInvoices($this->limit, $offset);
		if ( empty($aInvoices) ){
			if ( $_POST['page'] > 1 ){
				wp_send_json_error(array(
					'reachedMaximum' => 'yes'
				));
			}else{
				wp_send_json_error(array('msg'=>esc_html__('There are no invoices', 'wiloke-listing-tools')));
			}
		}
		wp_send_json_success($aInvoices);
	}
}