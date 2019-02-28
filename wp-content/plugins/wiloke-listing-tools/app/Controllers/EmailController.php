<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\FileSystem;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\QRCodeGenerator;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Frontend\User;
use WilokeListingTools\Models\PaymentMetaModel;
use WilokeListingTools\Models\PaymentModel;

class EmailController extends Controller {
	private $aConfiguration;
	private $aBankAccounts;
	private $aBankAccountFields;
	private $aThemeOptions;
	private $customerID;

	public function __construct() {
		add_action('user_register', array($this, 'sayWelcome'));
		add_action('wilcity/became-an-author', array($this, 'becameAnAuthor'));
		add_action('wiloke/submitted-listing', array($this, 'submittedListing'), 10, 2);
		add_action('post_updated', array($this, 'listingApproved'), 10, 3);
		add_action('post_almost_expiry', array($this, 'almostExpired'));
		add_action('post_updated', array($this, 'listingExpired'), 10, 3);
		add_action('wiloke/free-claim/submitted', array($this, 'claimSubmitted'), 10, 2);
		add_action('wiloke/free-claim/submitted', array($this, 'notifyClaimToAdmin'), 10, 3);
		add_action('wilcity/claim-listing/approved', array($this, 'notifyCustomerClaimedListingToAdmin'), 10);
		add_action('wilcity/claim-listing/approved', array($this, 'notifyClaimHasBeenApprovedToCustomer'), 10, 3);
		add_action('wiloke/claim/approved', array($this, 'claimApproved'), 10, 2);
		add_action('updated_post_meta', array($this, 'claimRejected'), 10, 4);
		add_action('wiloke-listing-tools/payment-pending', array($this, 'orderProcessing'), 10);
		add_action('wiloke-listing-tools/subscription-created', array($this, 'subscriptionCreated'), 10);
		add_action('wiloke-listing-tools/subscription-created', array($this, 'changedPlan'), 10);
		add_action('wiloke/promotion/submitted', array($this, 'promotionCreated'), 10, 2);
		add_action('post_updated', array($this, 'promotionApproved'), 10, 3);

		add_action('mailtpl/sections/test/before_content', array($this, 'addTestMailTarget'));
		add_action('customize_controls_enqueue_scripts', array($this, 'enqueueCustomizeScripts'), 99);
		add_action('wp_ajax_wiloke_mailtpl_send_email', array($this, 'testMail'));
		add_action('wilcity/after/created-account', array($this, 'sendConfirmation'), 10, 3);
		add_action('wilcity/submitted-new-review', array($this, 'sendReviewNotification'), 10, 3);
		add_action('wilcity/submitted-report', array($this, 'sendReportNotificationToAdmin'), 10, 3);
		add_filter('wilcity/theme-options/configurations', array($this, 'addEmailTemplateSettingsToThemeOptions'));
		add_action('wilcity/action/receive-message', array($this, 'sendMessageToEmail'), 10, 3);
		add_action('wilcity/inserted-invoice', array($this, 'sendEmailInvoice'));

		add_action('woocommerce_email_attachments', array($this, 'dokanAddQrcodeToAttachment'), 10, 3);
		add_action('woocommerce_email_customer_details', array($this, 'dokanSendQRCodeToCustomer'), 100);
	}

	public function dokanAddQrcodeToAttachment($aAttachments, $id, $oOrder){
	    if (  $id != 'customer_completed_order' ){
	        return $aAttachments;
        }
		$aTickets = QRCodeGenerator::generateTicket($oOrder->get_id());
	    if ( empty($aTickets) ){
	        return $aAttachments;
        }

        foreach ($aTickets as $aTicket){
	        $aAttachments[] = $aTicket['dir'];
        }

        return $aAttachments;
    }

    private function sendQRCodeToCustomers($aProducts){
	    foreach ($aProducts as $productID){
	        if ( QRCodeGenerator::isSendQRCodeToEmail($productID) ){
	            return true;
            }
        }
        return false;
    }

	public function dokanSendQRCodeToCustomer($oOrder){
	    if ( empty($oOrder) || is_wp_error($oOrder) ){
	        return false;
        }

		if ( $oOrder->get_status() !== 'completed' ){
            return false;
        }
		$orderID = $oOrder->get_id();

		$aProducts = GetSettings::getDokanProductIDsByOrderID($orderID);
        if ( empty($aProducts) || !$this->sendQRCodeToCustomers($aProducts) ){
            return false;
        }

		$emailContent = GetSettings::getPostMeta($aProducts[0], 'qrcode_description');
        if ( empty($emailContent) ){
	        $aThemeOptions = \Wiloke::getThemeOptions(true);
	        $emailContent = $aThemeOptions['email_qr_code'];
        }
		$emailContent = $this->generateReplace($emailContent);
        echo $emailContent;
    }

	public function addEmailTemplateSettingsToThemeOptions($aOptions){
        if ( !defined('MAILTPL_VERSION') ){
            return $aOptions;
        }

		$aOptions[] =  array(
			'title'            => esc_html__('Email Contents', 'wilcity'),
			'id'               => 'listing_settings',
			'desc'              => 'In order to use this feature, please go to Appearance -> Install Plugins -> Install and Active Email Template plugin.',
			'icon'             => 'dashicons dashicons-email',
			'subsection'       => false,
			'customizer_width' => '500px',
			'fields'           => array(
				array(
					'id'        => 'email_from',
					'type'      => 'text',
					'title'     => 'Admin Email',
					'default'   => get_option('admin_email')
				),
				array(
					'id'        => 'email_brand',
					'type'      => 'text',
					'title'     => 'Brand',
					'default'   => 'Wiloke'
				),
				array(
					'id'        => 'email_welcome_subject',
					'type'      => 'text',
					'title'     => 'Welcome Subject',
					'default'   => 'Welcome to %brand%!'
				),
				array(
					'id'        => 'email_welcome',
					'type'      => 'textarea',
					'title'     => 'Welcome',
					'subtitle'  => 'Say Welcome when a new account is created.',
					'default'   => 'Welcome to %brand%!'
				),
				array(
					'id'        => 'email_confirm_account',
					'type'      => 'textarea',
					'title'     => 'Confirmation',
					'default'   => 'Confirm your email address to complete your @%userName% account. It\'s easy - just click this link %confirmationLink%.'
				),
				array(
					'id'        => 'email_become_an_author_subject',
					'type'      => 'text',
					'title'     => 'Became an author Subject',
					'default'   => ''
				),
				array(
					'id'        => 'email_become_an_author',
					'type'      => 'textarea',
					'title'     => 'Became an author',
					'default'   => 'Hi %customerName%! Thank for being an author. Go %websiteUrl% and submit a listing now ;).'
				),
				array(
					'id'        => 'email_review_notification',
					'type'      => 'textarea',
					'title'     => 'Review Notification',
					'default'   => '%customerName% just left an review on %postLink%. The review title is %reviewTitle%.'
				),
				array(
					'id'        => 'email_report_notification',
					'type'      => 'textarea',
					'title'     => 'Report Notification',
					'default'   => '%customerName% just report %postTitle% on your site %brand%. The report title is %reportTitle%.'
				),
				array(
					'id'        => 'email_listing_submitted',
					'type'      => 'textarea',
					'title'     => 'After submitting',
					'subtitle'  => 'This email will be sent after a customer submits a listing to your site.',
					'default'   => 'Congratulations! Your article - %postTitle% - on %brand% has been submitted successfully. Our staff will review it and contact you shortly.'
				),
				array(
					'id'        => 'email_listing_approved_subject',
					'type'      => 'text',
					'title'     => 'Listing Approved Subject',
					'default'   => ''
				),
				array(
					'id'        => 'email_listing_approved',
					'type'      => 'textarea',
					'title'     => 'Listing Approved',
					'subtitle'  => 'This email will be sent to Listing Author after his/her listing is approved.',
					'default'   => 'Congratulations! Your article - %postTitle% - on %brand% has been submitted approved. You can view your article at: %postLink%.%breakDown% Thanks for your submission!'
				),
				array(
					'id'        => 'email_listing_almost_expired_subject',
					'type'      => 'text',
					'title'     => 'Listing Almost Expired Subject',
					'default'   => ''
				),
				array(
					'id'        => 'email_listing_almost_expired',
					'type'      => 'textarea',
					'title'     => 'Listing Almost Expired',
					'default'   => 'Hi %customerName%! Your article - %postTitle% - on %brand% is almost expired. Please renew it to keep the article is published on the site.'
				),
				array(
					'id'        => 'email_listing_expired_subject',
					'type'      => 'text',
					'title'     => 'Listing Expired Subject',
					'default'   => ''
				),
				array(
					'id'        => 'email_listing_expired',
					'type'      => 'textarea',
					'title'     => 'Listing Expired',
					'default'   => 'Hi %customerName%! Your article - %postTitle% - on %brand% is almost expired. Luckily, you can renew right on the front-end dashboard. Please go to %websiteUrl% -> Log into the website -> Click on Dashboard -> Listings -> And Review it.'
				),
				array(
					'id'        => 'email_claim_submitted_subject',
					'type'      => 'text',
					'title'     => 'Claim Submitted Subject',
					'default'   => ''
				),
				array(
					'id'        => 'email_claim_submitted',
					'type'      => 'textarea',
					'title'     => 'Claim Submitted',
					'default'   => 'Hi %customerName%! Thank for your claiming on %postTitle%. Our staff is reviewing your request. We will contact you as soon as possible.'
				),
				array(
					'id'        => 'email_claim_approved_subject',
					'type'      => 'text',
					'title'     => 'Claim Approved Subject',
					'default'   => ''
				),
				array(
					'id'        => 'email_claim_approved',
					'type'      => 'textarea',
					'title'     => 'Claim Approved',
					'default'   => 'Congratulations! Your claim - %postTitle% - on %brand% has been approved. The claim url: %postLink%'
				),
				array(
					'id'        => 'email_claim_rejected_subject',
					'type'      => 'text',
					'title'     => 'Claim Approved Subject',
					'default'   => ''
				),
				array(
					'id'        => 'email_claim_rejected',
					'type'      => 'textarea',
					'title'     => 'Claim Rejected',
					'default'   => 'Hi %customerName%!%breakDown%Thank you for using %brand%. We regret to inform you that the listing %postTitle% you have claimed on %brand% has been rejected.%breakDown%Please do keep sending in your suggestions and feedback to %adminEmail% and let us know if there’s anything else we can help with.'
				),
				array(
					'id'        => 'email_order_processing_subject',
					'type'      => 'text',
					'title'     => 'Order is processing Subject',
					'default'   => ''
				),
				array(
					'id'        => 'email_order_processing',
					'type'      => 'textarea',
					'title'     => 'Order is processing',
					'default'   => 'Dear %customerName%!%breakDown%Your order has been received and now being processed. Your order details are show below for your reference:%breakDown% %orderDetails%%breakDown%To complete this plan, please transfer your payment to the following bank accounts:%breakDown% %adminBankAccount%.'
				),
				array(
					'id'        => 'email_subscription_created_subject',
					'type'      => 'text',
					'title'     => 'Subscription Created Subject',
					'default'   => ''
				),
				array(
					'id'        => 'email_subscription_created',
					'type'      => 'textarea',
					'title'     => 'Subscription Created',
					'default'   => 'Congratulations! Your subscription on %brand% has been created. The subscription details:%breakDown%%subscriptionDetails%%breakDown%Thank for using our service!'
				),
				array(
					'id'        => 'email_subscription_cancelled',
					'type'      => 'textarea',
					'title'     => 'Subscription Created',
					'default'   => 'Dear %customerName%. Unfortunately, Your subscription #%subscriptionNumber% on %brand% has been failed.%breakDown% If you have any question, Please feel free contact us at %adminEmail%. %breakDown%Thank for using our service!'
				),
				array(
					'id'        => 'email_changed_plan_subject',
					'type'      => 'text',
					'title'     => 'Changed Plan Subject',
					'default'   => ''
				),
				array(
					'id'        => 'email_changed_plan',
					'type'      => 'textarea',
					'title'     => 'Changed Plan',
					'default'   => 'Congratulations. Your plan has been changed from %oldPlan% to %newPlan%!'
				),
				array(
					'id'        => 'email_promotion_submitted_subject',
					'type'      => 'text',
					'title'     => 'Promotion Created Subject',
					'default'   => ''
				),
				array(
					'id'        => 'email_promotion_submitted',
					'type'      => 'textarea',
					'title'     => 'Promotion Created',
					'default'   => 'Hi %customerName%. We got your request about promote %postTitle% on %brand%. Our staff is reviewing it, We will contact you shortly.'
				),
				array(
					'id'        => 'email_promotion_approved_subject',
					'type'      => 'text',
					'title'     => 'Promotion Approved Subject',
					'default'   => ''
				),
				array(
					'id'        => 'email_promotion_approved',
					'type'      => 'textarea',
					'title'     => 'Promotion Approved',
					'default'   => 'Congratulations. Your promotion campain:%promotionTitle% on %brand% has been completed.'
				),
				array(
					'id'        => 'email_when_reply_to_customer',
					'type'      => 'textarea',
					'title'     => 'After You reply a message to customer',
					'default'   => '[%brand%] replied on your inbox.'
				),
				array(
					'id'        => 'email_send_invoice_subject',
					'type'      => 'text',
					'title'     => 'Invoice Subject',
					'default'   => '%brand% Sales'
				),
				array(
					'id'        => 'email_send_invoice',
					'type'      => 'textarea',
					'title'     => 'Invoice Email',
					'default'   => ''
				),
                array(
                    'id'    => 'email_qr_code',
                    'type'  => 'textarea',
                    'title' => 'QRcode Email',
                    'default'=> '%h2%Check for this Event%close_h2%%breakDown%Please show us QRCode below when visiting the Event.'
                )
			)
		);
        return $aOptions;
    }

	public function testMail(){
		$subject = __( 'Wilcity Test Mail', 'email-templates');

	    if ( !isset($_POST['target']) || empty($_POST['target']) ){
		    ob_start();
		    include_once( MAILTPL_PLUGIN_DIR . '/admin/templates/partials/default-message.php');
		    $message = ob_get_contents();
		    ob_end_clean();
        }else{
		    $this->customerID = get_current_user_id();
		    switch ($_POST['target']){
                case 'email_subscription_created':
                    $this->subscriptionCreated(array(
                        'planTitle' => 'Test Plan',
                        'paymentID' => 1,
                        'gateway'   => 'stripe',
                        'isTrial'   => 'yes'
                    ));
	                die();
                case 'email_changed_plan':
	                $this->changedPlan(array(
		                'oldPlan' => 'Test Mail 1',
		                'newPlan' => 'Test Mail 2'
	                ));
                    die();
			    case 'email_confirm_account':
				    $this->sendConfirmation($this->customerID, 'test', true, true);
				    die();
			    case 'email_review_notification':
				    $aThemeOptions = $this->getOptions();
				    $this->customerID = get_current_user_id();
				    $message = $aThemeOptions['email_review_notification'];
				    $message = $this->generateReplace($message, '');
				    $message = str_replace(
					    '%reviewTitle%',
					    'Test Review Notification',
					    $message
				    );
				    wp_mail($this->to(), $aThemeOptions['email_from'], $message);
				    die();
			    case 'email_report_notification':
				    $aThemeOptions = $this->getOptions();
				    $this->customerID = get_current_user_id();
				    $message = $aThemeOptions['email_report_notification'];
				    $message = $this->generateReplace($message, '');
				    $message = str_replace(
					    '%reportTitle%',
					    'Test Report Notification',
					    $message
				    );
				    wp_mail($this->to(), $aThemeOptions['email_from'], $message);
				    die();
			    case 'email_order_processing':
			        $this->aBankAccountFields = array(
				        'bank_transfer_account_name'    =>  esc_html__('Bank Account Name', 'wiloke-listing-tools'),
				        'bank_transfer_account_number'  =>  esc_html__('Bank Account Number', 'wiloke-listing-tools'),
				        'bank_transfer_name'            => esc_html__('Bank Name', 'wiloke-listing-tools'),
				        'bank_transfer_short_code'      => esc_html__('Shortcode', 'wiloke-listing-tools'),
				        'bank_transfer_iban'            => esc_html__('IBAN', 'wiloke-listing-tools'),
				        'bank_transfer_swift'           => esc_html__('Swift', 'wiloke-listing-tools')
                    );

				    $this->orderProcessing(array(
					    'planTitle' => 'Test Mail 1',
					    'paymentID' => 1,
                        'gateway'   => 'stripe',
                        'billingType' => 'Recurring Payment'
				    ));
				    die();
                case 'email_receive_message':
	                $subject = '[%brand] You receive an message from A';
	                $subject = $this->generateReplace($subject);
	                $message = 'This is an Test Mail';
                    break;
                default:
				    $aThemeOptions = $this->getOptions();
				    $message = $aThemeOptions[$_POST['target']];
				    $message = $this->generateReplace($message);
				    break;
            }
        }

		echo wp_mail( get_bloginfo('admin_email'), $subject, $message);

    }

	public function enqueueCustomizeScripts(){
	    wp_dequeue_script('mailtpl-js');
	    wp_enqueue_script( 'wiloke-mailtpl-js', WILOKE_LISTING_TOOL_URL . 'admin/source/js/mailtpl-admin.js', array('jquery'), WILOKE_LISTING_TOOL_VERSION, true );
    }

	public function getOptions(){
        if ( empty($this->aThemeOptions) ){
            $this->aThemeOptions = \Wiloke::getThemeOptions(true);
        }
        return $this->aThemeOptions;
    }

	public function addTestMailTarget($wp_customize){
		$wp_customize->add_setting( 'mailtpl_opts[send_mail_target]', array(
			'type'                  => 'option',
			'default'               => '',
			'transport'             => 'postMessage',
			'capability'            => 'edit_theme_options',
			'sanitize_callback'     => '',
			'sanitize_js_callback'  => '',
		) );

		$wp_customize->add_control( 'mailtpl_opts[send_mail_target]', array(
			'type'                  => 'select',
			'default'               => '',
			'section'       => 'section_mailtpl_test',
			'transport'             => 'postMessage',
			'capability'            => 'edit_theme_options',
			'choices' => array(
				'email_welcome'                 => 'Welcome Message',
				'email_confirm_account'         => 'Confirm account',
				'email_become_an_author'        => 'Became An Author Message',
				'email_review_notification'     => 'Review Notification',
				'email_report_notification'     => 'Report Notification',
				'email_listing_submitted'       => 'Listing Submitted Message',
				'email_listing_approved'        => 'Listing Approved Message',
				'email_listing_almost_expired'  => 'Listing Almost Expired Message',
				'email_listing_expired'         => 'Listing Expired Message',
				'email_claim_submitted'         => 'Claim Submitted Message',
				'email_claim_approved'          => 'Claim Approved Message',
				'email_claim_rejected'          => 'Claim Rejected Message',
				'email_order_processing'        => 'Email Order Processing Message',
				'email_subscription_created'    => 'Subscription Created Message',
				'email_subscription_cancelled'  => 'Subscription Cancelled Message',
				'email_changed_plan'            => 'Plan Changed Message',
				'email_promotion_submitted'     => 'Promotion Submitted Message',
				'email_promotion_approved'      => 'Email Approved Message',
                'email_receive_message'         => 'Email Receive Message From Customer'
			),
			'sanitize_callback'     => '',
			'sanitize_js_callback'  => '',
		) );
    }

	private function to(){
		return User::getField('user_email', $this->customerID);
	}

	public function generateReplace($content, $postID=''){
		$aThemeOptions  = $this->getOptions();
		$displayName    = User::getField('display_name', $this->customerID);
		$postTitle      = '';
		$postLink       = '';

		if ( !empty($postID) ){
			$postTitle  = apply_filters('wiloke/mail/postTitle', get_the_title($postID));
			$postLink   = apply_filters('wiloke/mail/postLink', get_permalink($postID));
		}

		return str_replace(
			array(
				'%customerName%',
				'%brand%',
				'%breakDown%',
				'%postTitle%',
				'%postLink%',
				'%websiteUrl%',
				'%adminEmai%',
                '%close_h1%',
                '%close_h2%',
                '%close_h3%',
                '%close_h4%',
                '%close_h5%',
                '%close_h6%',
                '%h1%',
                '%h2%',
                '%h3%',
                '%h4%',
                '%h5%',
                '%h6%',
			),
			array(
				$displayName,
				$aThemeOptions['email_brand'],
				'<br />',
				$postTitle,
				$postLink,
				home_url('/'),
				$aThemeOptions['email_from'],
                '</h1>',
                '</h2>',
                '</h3>',
                '</h4>',
                '</h5>',
                '</h6>',
                '<h1>',
                '<h2>',
                '<h3>',
                '<h4>',
                '<h5>',
                '<h6>'
			),
			$content
		);
	}

	public function createMailSubject($aThemeOptions, $key){
		if ( !isset($aThemeOptions[$key]) || empty($aThemeOptions[$key]) ){
			$subject = $this->generateReplace($aThemeOptions[$key]);
		}else{
			$subject = $aThemeOptions['email_from'];
		}
		return $subject;
    }

	public function sayWelcome($userID){
		$aThemeOptions = $this->getOptions();
		if ( !isset($aThemeOptions['email_welcome']) || empty($aThemeOptions['email_welcome']) ){
		    return false;
        }
		$welcome = $aThemeOptions['email_welcome'];

		if ( empty($welcome) ){
			return false;
		}
		$this->customerID = $userID;

		$welcome = $this->generateReplace($welcome);
		$subject = $this->createMailSubject($aThemeOptions, 'email_welcome_subject');
		wp_mail($this->to(), $subject, $welcome);
	}

	public function sendMessageToEmail($receiveID, $senderID, $content){
	    $allow = GetSettings::getUserMeta($receiveID, 'send_email_if_reply_message');
	    if ( $allow == 'no' ){
	        return '';
        }
		$aThemeOptions = \Wiloke::getThemeOptions(true);

	    if ( user_can($receiveID,'administrator') ){
            $subject = '[%brand%]' . sprintf(__(' You receive an message from %s', 'wiloke-listing-tools'), User::getField('display_name', $senderID));
            $to = GetSettings::adminEmail();
        }else{
	        if ( !isset($aThemeOptions['email_when_reply_to_customer']) ){
		        $subject = '[%brand%] replied on your inbox';
            }else{
		        $subject = $aThemeOptions['email_when_reply_to_customer'];
            }
		    $to = User::getField('user_email', $receiveID);
        }

		$subject = $this->generateReplace($subject);

        $dashboardURL = GetWilokeSubmission::getField('dashboard_page', true);
		$dashboardURL .= '#messages?u=' . urlencode(User::getField('user_login', $senderID));
	    $content .= "\r\n" . sprintf(__('To reply this message, please click on %s', 'wiloke-listing-tools'), $dashboardURL);

        wp_mail($to, $subject, $content);
    }

    private function toAdmin(){
	    $aThemeOptions = $this->getOptions();
	    if ( isset($aThemeOptions['email_from']) ){
		    $adminEmail = $aThemeOptions['email_from'];
	    }else {
		    $adminEmail = get_option('admin_email');
	    }
	    return $adminEmail;
    }

	public function becameAnAuthor($userID){
		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_become_an_author'];

		if ( empty($message) ){
			return false;
		}

		$this->customerID = $userID;

		$message = $this->generateReplace($message);
		$subject = $this->createMailSubject($aThemeOptions, 'email_become_an_author_subject');
		wp_mail($this->to(), $subject, $message);
	}

	public function submittedListing($userID, $postID){
		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_listing_submitted'];
		if ( empty($message) ){
			return false;
		}

		$this->customerID = $userID;
		$message = $this->generateReplace($message, $postID);
		wp_mail($this->to(), $aThemeOptions['email_from'], $message);

		if ( isset($aThemeOptions['email_from']) ){
			$adminEmail = $aThemeOptions['email_from'];
        }else {
		    $adminEmail = get_option('admin_email');
        }

        $notification = sprintf(__('%s just submitted %s to your website. Submitted At: %s', 'wiloke-listing-tools'), User::getField('display_name', $this->customerID), get_the_title($postID), get_post_field('post_date', $postID));
		$notificationTitle =  '[%brand%] ' . sprintf(__('%s just submitted an Listing to your website', 'wiloke-listing-tools'), User::getField('display_name', $userID));
		$notificationTitle = $this->generateReplace($notificationTitle);

		wp_mail($adminEmail, $notificationTitle, $notification);
	}

	public function listingApproved($postID, $oAfter, $oBefore){
		if ( $oAfter->post_status !== 'publish' || ($oBefore->post_before != 'pending' && $oBefore->post_before != 'expired') ){
			return false;
		}

		$aListingKeys = General::getPostTypeKeys(true, false);
		if ( !in_array($oAfter->post_type, $aListingKeys) ){
			return false;
		}

		$planID = GetSettings::getPostMeta($postID, 'belongs_to');
		if ( empty($planID) ){
			return false;
		}

		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_listing_approved'];
		if ( empty($message) ){
			return false;
		}

		$this->customerID = $oAfter->post_author;
		$message = $this->generateReplace($message, $oAfter->ID);

		$subject = $this->createMailSubject($aThemeOptions, 'email_listing_approved_subject');
		wp_mail($this->to(), $subject, $message);
	}

	public function almostExpired($postID){
		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_listing_almost_expired'];
		if ( empty($message) ){
			return false;
		}

		$this->customerID = get_post_field('post_author', $postID);
		$message = $this->generateReplace($message, $postID);

		$subject = $this->createMailSubject($aThemeOptions, 'email_listing_almost_expired_subject');
		wp_mail($this->to(), $subject, $message);
	}

	public function listingExpired($postID, $oAfter, $oBefore){
		if ( $oAfter->post_status !== 'expired' ){
			return false;
		}

		$aListingKeys = General::getPostTypeKeys(true, false);
		if ( !in_array($oAfter->post_type, $aListingKeys) ){
			return false;
		}

		$planID = GetSettings::getPostMeta($postID, 'belongs_to');
		if ( empty($planID) ){
			return false;
		}

		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_listing_expired'];
		if ( empty($message) ){
			return false;
		}

		$this->customerID = $oAfter->post_author;
		$message = $this->generateReplace($message, $oAfter->ID);
		$subject = $this->createMailSubject($aThemeOptions, 'email_listing_expired_subject');
		wp_mail($this->to(), $subject, $message);
	}

	public function notifyCustomerClaimedListingToAdmin($aInfo){
		$message = sprintf(
		        __('%s claimed %s on your site %s.<a href="%s">View Claim Detail</a>', 'wiloke-listing-tools'),
                GetSettings::getUserMeta($aInfo['userID'], 'display_name'),
			    get_the_title($aInfo['postID']),
                get_option('siteurl'),
                add_query_arg(
                    array(
                        'action' => 'edit',
                        'post' => $aInfo['claimID']
                    ),
                    admin_url('post.php')
		        )
        );

		wp_mail($this->toAdmin(), get_the_title($aInfo['claimID']), $message);
    }

	public function notifyClaimToAdmin($userID, $postID, $claimID){
		$message = sprintf(
            __('%s wants to claim %s on your site %s.<a href="%s">View Claim Detail</a>', 'wiloke-listing-tools'),
            GetSettings::getUserMeta($userID, 'display_name'),
			get_the_title($postID),
            get_option('siteurl'),
            add_query_arg(
                array(
                    'action' => 'edit',
                    'post' => $claimID
                ),
                admin_url('post.php')
            )
        );

		wp_mail($this->toAdmin(), get_the_title($claimID), $message);
    }

	public function claimSubmitted($userID, $postID){
		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_claim_submitted'];
		if ( empty($message) ){
			return false;
		}

		$this->customerID = $userID;
		$message = $this->generateReplace($message, $postID);

		$subject = $this->createMailSubject($aThemeOptions, 'email_claim_submitted_subject');
		wp_mail($this->to(), $subject, $message);
	}

	public function notifyClaimHasBeenApprovedToCustomer($aInfo){
        $this->claimApproved($aInfo['userID'], $aInfo['postID']);
    }

	public function claimApproved($userID, $postID){
		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_claim_approved'];
		if ( empty($message) ){
			return false;
		}

		$this->customerID = $userID;
		$message = $this->generateReplace($message, $postID);
		$subject = $this->createMailSubject($aThemeOptions, 'email_claim_approved_subject');
		wp_mail($this->to(), $subject, $message);
	}

	public function claimRejected($metaID, $objectID, $metaKey, $metaValue){
		if ( $metaKey !== 'wilcity_claim_status' ){
			return false;
		}

		if ( $metaValue != 'cancelled' ){
			return false;
		}

		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_claim_rejected'];
		if ( empty($message) ){
			return false;
		}

		$this->customerID = GetSettings::getPostMeta($objectID, 'wilcity_claimer_id');
		$postID = GetSettings::getPostMeta($objectID, 'wilcity_claimed_listing_id');
		$message = $this->generateReplace($message, $postID);

		$subject = $this->createMailSubject($aThemeOptions, 'email_claim_rejected_subject');
		wp_mail($this->to(), $subject, $message);
	}

	private function getBankAccount(){
		$this->aConfiguration = GetWilokeSubmission::getAll();

		$aInfo = array(
			'bank_transfer_account_name'    =>  esc_html__('Bank Account Name', 'wiloke-listing-tools'),
			'bank_transfer_account_number'  =>  esc_html__('Bank Account Number', 'wiloke-listing-tools'),
			'bank_transfer_name'            => esc_html__('Bank Name', 'wiloke-listing-tools'),
			'bank_transfer_short_code'      => esc_html__('Shortcode', 'wiloke-listing-tools'),
			'bank_transfer_iban'            => esc_html__('IBAN', 'wiloke-listing-tools'),
			'bank_transfer_swift'           => esc_html__('Swift', 'wiloke-listing-tools')
		);

		for ($i=1; $i<=4; $i++){
			if ( !empty($this->aConfiguration['bank_transfer_account_name_'.$i]) && !empty($this->aConfiguration['bank_transfer_account_number_'.$i]) && !empty($this->aConfiguration['bank_transfer_name_'.$i]) ){
				foreach ($aInfo as $bankInfo => $name){
					if ( !empty($this->aConfiguration[$bankInfo.'_'.$i]) ){
						$this->aBankAccountFields[$bankInfo] = $name;
						$this->aBankAccounts[$i][$bankInfo] = $this->aConfiguration[$bankInfo.'_'.$i];
					}
				}
			}
		}
	}

	public function sendEmailInvoice($aData){
	    $planName = get_the_title($aData['paymentID']);
	    if ( empty($planName) ){
	        $planName = PaymentMetaModel::get($aData['paymentID'], 'planName');
        }
        $aOptions = \Wiloke::getThemeOptions(true);
		ob_start();
		?>
        <h3><?php esc_html_e('INVOICE', 'wiloke-listing-tools'); ?></h3>
        <p><strong><?php echo esc_html(GetWilokeSubmission::getField('brandname')); ?></strong></p>
        <?php
        if ( !empty($aOptions['email_send_invoice']) ):
	        $aOptions['email_send_invoice'] = $this->generateReplace($aOptions['email_send_invoice']);
        ?>
        <p><?php \Wiloke::ksesHTML($aOptions['email_send_invoice']);  ?></p>
        <?php endif; ?>
        <p><strong><?php esc_html_e('Invoice ID', 'wiloke-listing-tools'); ?>:</strong> <?php echo esc_html($aData['invoiceID']); ?></p>
        <p><strong><?php esc_html_e('Invoice date', 'wiloke-listing-tools'); ?>:</strong> <?php echo date_i18n(get_option('date_format'), current_time('timestamp')); ?></p>

        <table width="100%">
            <thead>
                <tr>
                    <th><?php esc_html_e('Description', 'wiloke-listing-tools'); ?></th>
                    <th><?php esc_html_e('Total', 'wiloke-listing-tools'); ?></th>
                    <th><?php esc_html_e('Sub Total', 'wiloke-listing-tools'); ?></th>
                    <th><?php esc_html_e('Discount', 'wiloke-listing-tools'); ?></th>
                    <th><?php esc_html_e('Tax', 'wiloke-listing-tools'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo esc_html($planName); ?></td>
                    <td><?php echo GetWilokeSubmission::renderPrice($aData['total'], $aData['currency']); ?></td>
                    <td><?php echo GetWilokeSubmission::renderPrice($aData['subTotal'], $aData['currency']); ?></td>
                    <td><?php echo GetWilokeSubmission::renderPrice($aData['discount'], $aData['currency']); ?></td>
                    <td><?php echo GetWilokeSubmission::renderPrice(0, $aData['currency']); ?></td>
                </tr>
            </tbody>
        </table>
		<?php
		$message = ob_get_contents();
		ob_end_clean();
		$this->customerID = PaymentModel::getField('userID', $aData['paymentID']);

		$subject = $this->createMailSubject($aOptions, 'email_send_invoice_subject');
		wp_mail($this->to(), $subject, $message);
    }

	private function getBankAccounts(){
		$this->getBankAccount();
		$bankAccount = '';
		if ( !empty($this->aBankAccounts) ):
            $total = count($this->aBankAccountFields);
			ob_start();
			?>
			<table width="100%">
				<tr>
					<?php foreach ($this->aBankAccountFields as $class => $name) : ?>
						<th class="<?php echo esc_attr($class); ?>" width="(100/<?php echo esc_attr($total); ?>)"><?php echo esc_html($name); ?></th>
					<?php endforeach; ?>
				</tr>
				<?php foreach ($this->aBankAccounts as $aBankAccount) : ?>
					<tr>
						<td width="(100/<?php echo esc_attr($total); ?>)"><?php echo esc_html($aBankAccount['bank_transfer_account_name']); ?></td>
						<td width="(100/<?php echo esc_attr($total); ?>)"><?php echo esc_html($aBankAccount['bank_transfer_account_number']); ?></td>
						<td width="(100/<?php echo esc_attr($total); ?>)"><?php echo esc_html($aBankAccount['bank_transfer_name']); ?></td>
						<?php if ( !empty($aBankAccount['bank_transfer_short_code']) ) : ?>
							<td width="(100/<?php echo esc_attr($total); ?>)"><?php echo esc_html($aBankAccount['bank_transfer_short_code']); ?></td>
						<?php endif; ?>
						<?php if ( !empty($aBankAccount['bank_transfer_iban']) ) : ?>
							<td width="(100/<?php echo esc_attr($total); ?>)"><?php echo esc_html($aBankAccount['bank_transfer_iban']); ?></td>
						<?php endif; ?>
						<?php if ( !empty($aBankAccount['bank_transfer_swift']) ) : ?>
							<td width="(100/<?php echo esc_attr($total); ?>)"><?php echo esc_html($aBankAccount['bank_transfer_swift']); ?></td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php
			$bankAccount = ob_get_contents();
			ob_end_clean();
		endif;
		return $bankAccount;
	}

	public function orderProcessing($aOrderInfo){
		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_order_processing'];
		if ( empty($message) ){
			return false;
		}

		$billingType = GetWilokeSubmission::isNonRecurringPayment($aOrderInfo['billingType']) ? esc_html__('Non Recurring Payment', 'wiloke-listing-tools') : esc_html__('Recurring Payment', 'wiloke-listing-tools');

		$this->customerID = $aOrderInfo['userID'];
		ob_start();
		do_action('wilcity/email/banktransfer/order-processing/before-table', $aThemeOptions, $this->customerID);
		?>
        <table width="100%">
			<tr>
				<th width="100/5"><?php esc_html_e('Payment ID', 'wiloke-listing-tools'); ?></th>
				<th width="100/5"><?php esc_html_e('Billing Type', 'wiloke-listing-tools'); ?></th>
				<th width="100/5"><?php esc_html_e('Plan Name', 'wiloke-listing-tools'); ?></th>
				<th width="100/5"><?php esc_html_e('Gateway', 'wiloke-listing-tools'); ?></th>
				<th width="100/5"><?php esc_html_e('Created At', 'wiloke-listing-tools'); ?></th>
			</tr>
			<tr>
				<td width="100/5"><?php echo esc_html($aOrderInfo['paymentID']); ?></td>
				<td width="100/5"><?php echo esc_html($billingType); ?></td>
				<td width="100/5"><?php echo isset($aOrderInfo['planTitle']) ? esc_html($aOrderInfo['planTitle']) : esc_html(get_the_title($aOrderInfo['planID'])); ?></td>
				<td width="100/5"><?php echo esc_html__('Bank Transfer', 'wiloke-listing-tools'); ?></td>
				<td width="100/5"><?php echo date_i18n(get_option('date_format'), current_time('timestamp')); ?></td>
			</tr>
		</table>
		<?php
		do_action('wilcity/email/banktransfer/order-processing/after-table', $aThemeOptions, $this->customerID);
		$orderDetail = ob_get_contents();
		ob_end_clean();

		$message = str_replace(
			array(
				'%orderDetails%',
				'%adminBankAccount%',
			),
			array(
				$orderDetail,
				$this->getBankAccounts()
			),
			$message
		);

		$message = $this->generateReplace($message);

		$subject = $this->createMailSubject($aThemeOptions, 'email_order_processing_subject');
		wp_mail($this->to(), $subject, $message);
	}

	public function subscriptionCreated($aData){
		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_subscription_created'];
		if ( empty($message) ){
			return false;
		}

		$this->customerID = PaymentModel::getField('userID', $aData['paymentID']);
		$message = $this->generateReplace($message);

		ob_start();
        ?>
        <table width="100%">
            <tr>
                <th width="100/5"><?php esc_html_e('Subscription ID', 'wiloke-listing-tools'); ?></th>
                <th width="100/5"><?php esc_html_e('Gateway', 'wiloke-listing-tools'); ?></th>
                <th width="100/5"><?php esc_html_e('Is Trial?', 'wiloke-listing-tools'); ?></th>
                <th width="100/5"><?php esc_html_e('Plan Name', 'wiloke-listing-tools'); ?></th>
                <th width="100/5"><?php esc_html_e('Created At', 'wiloke-listing-tools'); ?></th>
            </tr>
            <tr>
                <td width="100/5"><?php echo esc_html($aData['paymentID']); ?></td>
                <td width="100/5">
                <?php
                if ( $aData['gateway'] == 'banktransfer' ){
	                esc_html_e('Bank Transfer', 'wiloke-listing-tools');
                }else{
	                echo esc_html($aData['gateway']);
                }
                ?>
                </td>
                <td width="100/5">
                    <?php
                        if ( isset($aData['isTrial']) && $aData['isTrial'] ){
                            esc_html_e('Yes', 'wiloke-listing-tools');
                        }else{
                            esc_html_e('No', 'wiloke-listing-tools');
                        }
                    ?>
                </td>
                <td width="100/5"><?php echo isset($aData['planTitle']) ? esc_html($aData['planTitle']) : get_the_title($aData['planID']); ?></td>
                <td width="100/5"><?php echo date_i18n(get_option('date_format'), current_time('timestamp')); ?></td>
            </tr>
        </table>
        <?php
		$content = ob_get_contents();
		ob_end_clean();

		$message = str_replace(
			array(
				'%subscriptionDetails%',
				'%subscriptionNumber%',
			),
			array(
				$content,
				$aData['paymentID']
			),
			$message
		);

		$message = $this->generateReplace($message);
		$subject = $this->createMailSubject($aThemeOptions, 'email_subscription_created_subject');
		wp_mail($this->to(), $subject, $message);
	}

	public function changedPlan($aData){
		if ( !isset($aData['onChangedPlan']) || $aData['onChangedPlan'] !== 'yes' ){
			return false;
		}

		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_changed_plan'];
		if ( empty($message) ){
			return false;
		}

		$message = str_replace(
			array(
				'%subscriptionNumber%',
				'%oldPlan%',
				'%newPlan%'
			),
			array(
				$aData['paymentID'],
				get_the_title($aData['oldPlanID']),
				get_the_title($aData['planID']),
			),
			$message
		);

		$message = $this->generateReplace($message);
		$subject = $this->createMailSubject($aThemeOptions, 'email_changed_plan_subject');
		wp_mail($this->to(), $subject, $message);
	}

	public function promotionCreated($userID, $postID){
		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_promotion_submitted'];
		if ( empty($message) ){
			return false;
		}
		$this->customerID = $userID;
		$message = $this->generateReplace($message, $postID);
		$subject = $this->createMailSubject($aThemeOptions, 'email_promotion_submitted_subject');
		wp_mail($this->to(), $subject, $message);
	}

	public function promotionApproved($postID, $oPostAfter, $oPostBefore){
	    if ( $oPostAfter->post_status != 'promotion' || $oPostAfter->post_status != 'publish' ){
            return false;
        }
		$aThemeOptions = $this->getOptions();
		$message = $aThemeOptions['email_promotion_approved'];
		if ( empty($message) ){
			return false;
		}

        $this->customerID = $oPostAfter->post_author;
		$listingID = GetSettings::getPostMeta($postID, 'listing_id');
		$message = $this->generateReplace($message, $listingID);
		$message = str_replace(
            '%promotionTitle%',
            get_the_title($listingID),
            $message
        );
		$subject = $this->createMailSubject($aThemeOptions, 'email_promotion_approved_subject');
		wp_mail($this->to(), $subject, $message);
	}

	public function sendConfirmation($userID, $userName, $needConfirm, $isFocusing=false){
	    if ( !$needConfirm ){
			return false;
		}

		$aThemeOptions = $this->getOptions();

		if ( !$isFocusing && ( !isset($aThemeOptions['confirmation_page']) || empty($aThemeOptions['confirmation_page']) ) ){
		    return false;
        }

		$this->customerID = $userID;
		$message = $aThemeOptions['email_confirm_account'];

		$message = $this->generateReplace($message);
		$redirectTo = get_permalink($aThemeOptions['confirmation_page']);

		$confirmationLink = add_query_arg(
            array(
                'action'        => 'confirm_account',
                'activationKey' => urlencode(User::getField('user_activation_key', $this->customerID)),
                'userName'      => urlencode($userName)
            ),
			$redirectTo
        );

		$message = str_replace(
			array(
				'%confirmationLink%',
                '%userName%'
            ),
			array(
				$confirmationLink,
				$userName
            ),
			$message
		);

		wp_mail($this->to(), $aThemeOptions['email_from'], $message);
	}

	public function sendReviewNotification($reviewID, $parentID, $reviewerID){
		$aThemeOptions = $this->getOptions();
		$this->customerID = get_post_field('post_author', $parentID);
		$message = $aThemeOptions['email_review_notification'];
		$message = $this->generateReplace($message, $parentID);
		$message = str_replace(
			'%reviewTitle%',
			get_the_title($reviewID),
			$message
		);
		wp_mail($this->toAdmin(), $aThemeOptions['email_from'], $message);
    }

    public function sendReportNotificationToAdmin($postID, $reportedID){
	    $aThemeOptions = $this->getOptions();
	    $this->customerID = get_post_field('post_author', $postID);
	    $message = $aThemeOptions['email_report_notification'];
	    $message = $this->generateReplace($message, $postID);
	    $message = str_replace(
		    '%reportTitle%',
		    get_the_title($reportedID),
		    $message
	    );
	    $subject = sprintf(esc_html__('%s reported an issue on your site', 'wiloke-listing-tool'), GetSettings::getUserMeta($this->customerID, 'display_name'));
	    wp_mail($this->toAdmin(), $subject, $message);
    }
}