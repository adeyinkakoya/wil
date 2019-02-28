<?php
namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\DebugStatus;
use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\HTML;
use WilokeListingTools\Framework\Helpers\SetSettings;
use WilokeListingTools\Framework\Helpers\Submission;
use WilokeListingTools\Framework\Payment\FreePlan\FreePlan;
use WilokeListingTools\Framework\Routing\Controller;
use WilokeListingTools\Framework\Store\Session;
use WilokeListingTools\Frontend\SingleListing;
use WilokeListingTools\Frontend\User;
use WilokeListingTools\MetaBoxes\Listing;
use WilokeListingTools\Models\UserModel;

final class AddListingController extends Controller {
	use Validation;

    use GetWilokeToolSettings;
    use SetPostDuration;
    use SetCustomSections;
    use SetProductsToListing;
    use SetMyRoom;

	protected $isJustInserted=true;
	protected $aData;
	protected $listingID;
	protected $listingType;
	protected $planID;
	protected $aSections;
	protected $postStatus;

	protected $aSectionSettings;
	protected $aGetTerms = array();

	public $aConvertToRealBusinessHourKeys = array(
		array(
			'from' => 'firstOpenHour',
			'to'   => 'firstCloseHour'
		),
		array(
			'from' => 'secondOpenHour',
			'to'   => 'secondCloseHour'
		)
	);

	protected $aPlanSettings = array();

	use PrintSidebarItems;
	use InsertGallery;
	use SetSinglePrice;
	use InsertLogo;
	use InsertImg;
	use InsertCoverImage;
	use PrintAddListingSettings;
	use PrintAddListingFields;
	use MergingSettingValues;
	use BelongsToCategories;
	use BelongsToTags;
	use SetPriceRange;
	use SetVideo;
	use SetGeneral;
	use SetContactInfo;
	use SetSocialNetworks;
	use InsertAddress;
	use BelongsToLocation;
	use SetPlanRelationship;
	use InsertFeaturedImg;
	use SetGroupData;
	use AddBookingComBannerCreator;
	use HandleSubmit;

	public function __construct(){
		add_action('wiloke/wilcity/addlisting/print-fields', array($this, 'printAddListingFields'));
		add_action('wiloke/wilcity/addlisting/print-sidebar-items', array($this, 'printSidebarItems'));
		add_action('wp_enqueue_scripts', array($this, 'printAddListingSettings'));
		add_action('wp_ajax_wilcity_handle_review_listing', array($this, 'handlePreview'));
		add_action('wp_ajax_nopriv_wilcity_handle_review_listing', array($this, 'handlePreview'));
		add_action('wp_ajax_wilcity_handle_submit_listing', array($this, 'handleSubmit'));
		add_action('wp_ajax_nopriv_wilcity_select2_fetch_term', array($this, 'fetchTerms'));
		add_action('wp_ajax_wilcity_select2_fetch_term', array($this, 'fetchTerms'));
		add_filter('woocommerce_add_to_cart_redirect', array($this, 'straightGoToCheckoutPage'));
		add_action('woocommerce_order_status_completed', array($this, 'paymentCompleted'), 10, 1);
		add_action('wp_ajax_fetch_tags_of_listing_cat', array($this, 'fetchTagsOfListingCat'), 10, 1);
		add_action('wp_ajax_nopriv_fetch_tags_of_listing_cat', array($this, 'fetchTagsOfListingCat'), 10, 1);
		add_action('wp_ajax_wilcity_fetch_post', array($this, 'fetchPosts'), 10, 1);
		add_action('wp_ajax_nopriv_wilcity_fetch_post', array($this, 'fetchPosts'), 10, 1);
		add_action('wilcity/can-not-submit-listing', array($this, 'printCannotSubmitListingMsg'));
		add_filter('the_content', array($this, 'theContent'));
//		add_action('wilcity/checkout/render-checkout-button', array($this, 'renderAddListingButton'));
	}

	public function renderAddListingButton(){
	    HTML::renderPaymentButtons();
    }

    public function theContent($content){
        if ( !is_page_template() || SingleListing::isElementorEditing() ){
            return $content;
        }
        global $post;
        if ( is_page_template('wiloke-submission/pricing.php') ){
	        $aPostTypeSupported = Submission::getSupportedPostTypes();
	        if( !User::canSubmitListing() ) {
		        ob_start();
		        do_action( 'wilcity/can-not-submit-listing' );
		        $content = ob_get_contents();
		        ob_end_clean();
		        return $content;
	        }else {
		        if ( !isset( $_REQUEST['listing_type'] ) || empty( $_REQUEST['listing_type'] ) ) {
			        $aCustomPostTypes = GetSettings::getFrontendPostTypes();
			        $packageURL       = GetWilokeSubmission::getField( 'package', true );
			        $additionalClass  = '';
			        $order            = 1;

			        $boxClass = count($aPostTypeSupported) < 3 ? 'col-md-6 col-lg-6' : 'col-md-4 col-ld-4';

			        ob_start();
			        foreach ( $aCustomPostTypes as $aInfo ) :
				        if ( !in_array($aInfo['key'], $aPostTypeSupported) ){
					        continue;
				        }

				        $url = apply_filters('wilcity/wiloke-submission/box-listing-type-url', $packageURL, $aInfo);
				        ?>
                        <div class="<?php echo esc_attr( $additionalClass . ' ' . $boxClass ); ?>">
                            <div class="icon-box-2_module__AWd3Y wil-text-center"
                                 style="background-color: <?php echo esc_attr( $aInfo['addListingLabelBg'] ); ?>">
                                <a href="<?php echo esc_url( $url ); ?>">
                                    <div class="icon-box-2_icon__ZqobK"><i class="<?php echo esc_attr( $aInfo['icon'] ); ?>"></i>
                                    </div>
                                    <h2 class="icon-box-2_title__2cgba"><?php echo esc_html( $aInfo['addListingLabel'] ); ?></h2>
                                </a>
                            </div>
                        </div>
				        <?php
				        $order ++;
			        endforeach;
			        $listingType = ob_get_contents();
			        ob_end_clean();

			        return '<div class="wilcity-choose-listing-types">'.$listingType.'</div>';
		        } else {
			        $isPrintPricing = true;

			        if ( !in_array($_REQUEST['listing_type'], $aPostTypeSupported) ){
				        return \WilokeMessage::message( array(
					        'msg'        => sprintf( __( 'Oops! %s type is not supported.', 'wilcity' ), $_REQUEST['listing_type'] ),
					        'msgIcon'    => 'la la-bullhorn',
					        'status'     => 'danger',
					        'hasMsgIcon' => true
				        ), true );
			        }else {
				        if ( !GetWilokeSubmission::isNonRecurringPayment() ) {
					        $isPrintPricing = ! UserModel::isExceededRecurringPaymentPlan( $_REQUEST['listing_type'] . '_plan' );
				        }

				        if ( $isPrintPricing || DebugStatus::status( 'WILOKE_ALWAYS_PAY' ) ) {
					        return $content;
				        } else {
					        $errMsg = \WilokeMessage::message( array(
						        'msg'        => sprintf( __( 'You have exceeded your number of items quota in this plan. Please go to <a href="%s">Dashboard</a>, then click on Billings to upgrade to higher plan', 'wilcity' ), GetWilokeSubmission::getField( 'dashboard_page', true ) ),
						        'msgIcon'    => 'la la-bullhorn',
						        'status'     => 'info',
						        'hasMsgIcon' => true
					        ), true );
					        return $content . $errMsg;
				        }
			        }
		        }
	        }
        }else if ( is_page_template('templates/confirm-account.php') ){
	        if ( !isset($_REQUEST['action']) || $_REQUEST['action'] != 'confirm_account' ){
		        return \WilokeMessage::message(
			        array(
				        'status' => 'danger',
				        'msgIcon'=> 'la la-frown-o',
				        'msg'    => esc_html__('Invalid confirmation link.', 'wilcity')
			        ),
                    true
		        );
	        }else{
		        $userName = urldecode($_REQUEST['userName']);
		        $oUser = get_user_by('login', $userName);

		        if ( empty($oUser) || is_wp_error($oUser) ){
			        return \WilokeMessage::message(
				        array(
					        'status' => 'danger',
					        'msgIcon'=> 'la la-frown-o',
					        'msg'    => esc_html__('This account does not exist.', 'wilcity')
				        ),
                        true
			        );
		        }else{
			        $activationKey  = urldecode($_REQUEST['activationKey']);
			        $userActivationKey = User::getField('user_activation_key', $oUser->ID);
			        if ( $activationKey != $userActivationKey ){
				        return \WilokeMessage::message(
					        array(
						        'status' => 'danger',
						        'msgIcon'=> 'la la-frown-o',
						        'msg'    => esc_html__('Invalid activation key.', 'wilcity')
					        ),
                            true
				        );
			        }else{
				        SetSettings::setUserMeta($oUser->ID, 'confirmed', true);
			        }
		        }
	        }
        }

        return $content;
    }

	public static function saveListingIDToSession(){
	    if ( isset($_GET['postID']) && !empty($_GET['postID']) ){
		    Session::setSession(wilokeListingToolsRepository()->get('payment:sessionObjectStore'), $_GET['postID']);
        }
    }

	public function printCannotSubmitListingMsg(){
		?>
		<div class="col-md-12">
			<?php if ( is_user_logged_in() ): ?>
                <?php if ( !User::isAccountConfirmed() ) : ?>
					<?php do_action('wilcity/print-need-to-verify-account-message'); ?>
                <?php elseif ( GetWilokeSubmission::isEnable('toggle_become_an_author') ) : ?>
				<div class="alert_module__Q4QZx alert_success__1nkos">
					<div class="alert_icon__1bDKL"><i class="la la-smile-o"></i></div>
					<div class="alert_content__1ntU3"><?php \Wiloke::ksesHTML( sprintf(__('Just one more step to submit the listing. Please click on <a href="%s">Become An Author</a> to complete it.', 'wiloke-listing-tools'), GetWilokeSubmission::getField('become_an_author_page', true)), false ); ?></div>
				</div>
                <?php else: ?>
                    <div class="alert_module__Q4QZx alert_danger__2ajVf">
                        <div class="alert_icon__1bDKL"><i class="la la-frown-o"></i></div>
                        <div class="alert_content__1ntU3"><?php esc_html_e('You do not have permission to access this page!', 'wiloke-listing-tools'); ?></div>
                    </div>
                <?php endif; ?>
			<?php else: ?>
				<div class="alert_module__Q4QZx alert_success__1nkos">
					<div class="alert_icon__1bDKL"><i class="la la-smile-o"></i></div>
					<div class="alert_content__1ntU3"><?php esc_html_e('You must be logged in to submit a listing', 'wiloke-listing-tools'); ?></div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	public function fetchTagsOfListingCat(){
		$aTags = array();
	    if ( is_array($_POST['termID']) ) {
            foreach ($_POST['termID'] as $termID) {
	            $aMaybeTag = maybe_unserialize(GetSettings::getTermMeta($termID, 'tags_belong_to'));
	            if ( !empty($aMaybeTag) ){
	                $aTags = array_merge($aMaybeTag, $aTags);
	            }
            }
	    }else {
		    $aTags = maybe_unserialize(GetSettings::getTermMeta($_POST['termID'], 'tags_belong_to'));
	    }

		if ( empty($aTags) ){
			wp_send_json_error();
		}

		$aTags = array_unique($aTags);
		$aTerms = array();
		foreach ($aTags as $slug){
			$oTerm = GetSettings::getTermBy('slug', $slug, 'listing_tag');
			$aTerm['name']  = $oTerm->name;
			$aTerm['label'] = $oTerm->name;
			$aTerm['value'] = $oTerm->term_id;
			$aTerms[]       = $aTerm;
		}
		wp_send_json_success($aTerms);
	}

	public function generatePayAndPublishURL(){
		$planID     = Session::getSession(wilokeListingToolsRepository()->get('payment:storePlanID'));
		$productID  = GetSettings::getPostMeta($planID, 'product_alias');

		if ( !empty($productID) ){
			/*
			* @hooked: WooCommerceController:preparePayment
			*/
			do_action('wiloke-listing-tools/payment-via-woocommerce', $planID, $productID);
			$redirectTo = GetWilokeSubmission::getAddToCardUrl($productID);
		}else{
			$redirectTo = GetWilokeSubmission::getField('checkout', true);
		}

		wp_send_json_success(
			array(
				'redirectTo' => $redirectTo
			)
		);
	}

	public function paymentCompleted($orderID){
		SetSettings::setOptions('testfat', $orderID);
	}

	public function straightGoToCheckoutPage($url){
		if ( isset( $_POST['add-to-cart'] ) ) {
			$product_id = (int) apply_filters( 'woocommerce_add_to_cart_product_id', $_POST['add-to-cart'] );
//Check if product ID is in the proper taxonomy and return the URL to the redirect product
			if ( has_term( 'posters', 'product_cat', $product_id ) ){
				return get_permalink( 83 );
			}
		}
		return $url;
	}

	public function deleteImg($id){
		$this->middleware(
			'isPassedPostAuthor',
			array(
				'id' => $id
			)
		);
		wp_delete_post($id, true);
	}

	public function fetchPosts(){
		if ( !is_user_logged_in() || !isset($_GET['postTypes']) || empty($_GET['postTypes']) ){
			wp_send_json_error();
		}
        $aPostTypes = explode(',', $_GET['postTypes']);
		foreach ($aPostTypes as $key => $postType) {
            $aPostTypes[$key] = esc_sql(trim($postType));
		}

		$postTypes = implode("','", $aPostTypes);
		global $wpdb;

		if ( current_user_can('edit_theme_options') ){
			$aResults = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_title FROM $wpdb->posts WHERE post_title LIKE %s AND post_type In ('".$postTypes."') AND post_status='publish' ORDER BY ID DESC LIMIT 50",
					'%'.esc_sql($_GET['search']).'%'
                )
			);
		}else{
			$aResults = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_title FROM $wpdb->posts WHERE post_title LIKE %s AND post_type In ('".$postTypes."') AND post_author=".esc_sql(get_current_user_id())." AND post_status='publish' ORDER BY ID DESC LIMIT 50",
					'%'.esc_sql($_GET['search']).'%'
                )

			);
		}

		if ( empty($aResults) ){
			wp_send_json_error();
		}

		$aResponse = array();
		foreach ($aResults as $oResult){
			$aTemporary['id'] = $oResult->ID;
			$aTemporary['text']  = $oResult->post_title;
			$aResponse['results'][] = $aTemporary;
		}

		wp_send_json_success(
			array(
				'msg' => $aResponse
			)
		);
	}

	public function buildTermItemInfo($oTerm){
		$aTerm['value'] = $oTerm->slug;
		$aTerm['name']  = $oTerm->name;
		$aTerm['parent']= $oTerm->parent;
		$aIcon = \WilokeHelpers::getTermOriginalIcon($oTerm);
		if ( $aIcon ){
			$aTerm['oIcon'] = $aIcon;
		}else{
			$featuredImgID = GetSettings::getTermMeta($oTerm->term_id, 'featured_image_id');
			$featuredImg = wp_get_attachment_image_url($featuredImgID, array(32, 32));
			$aTerm['oIcon'] = array(
				'type' => 'image',
				'url'  => $featuredImg
			);
		}
		return $aTerm;
	}

	public function fetchTerms(){
		$aArgs = array(
			'name__like' => $_GET['search'],
			'taxonomy'   => $_GET['taxonomy'],
			'hide_empty' => false
		);

		if ( isset($_GET['postType']) && !empty($_GET['postType']) ){
			$aArgs['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'       => 'wilcity_belongs_to',
					'compare'   => 'NOT EXISTS'
				),
				array(
					'key'       => 'wilcity_belongs_to',
					'compare'   => 'LIKE',
					'value'     => $_GET['postType']
				)
			);
		}

		$aRawTerms = GetSettings::getTerms($aArgs);

		if ( !$aRawTerms ){
			wp_send_json_error(array(
				'msg' => esc_html__('Nothing found', 'wiloke-listing-tools')
			));
		}else{
			$aTerms = array();
			foreach ($aRawTerms as $oTerm){
			    $aTerm = $this->buildTermItemInfo($oTerm);
				$aTerm['id'] = isset($_GET['get']) && $_GET['get'] == 'slug' ? $oTerm->slug : $oTerm->term_id;
				$aTerm['text'] = $oTerm->name;
				$aTerms['results'][] = $aTerm;
			}

			wp_send_json_success(array(
				'msg' => $aTerms
			));
		}
	}

	protected function updateBusinessHours(){
		if ( !empty($this->aBusinessHours) ){
			Listing::saveBusinessHours($this->listingID, $this->aBusinessHours);
		}
	}

	protected function setListingBelongsTo(){
	    $oldPlan = GetSettings::getPostMeta($this->listingID, 'belongs_to');
	    if ( $oldPlan != $this->planID ){
	        SetSettings::setPostMeta($this->listingID, 'belongs_to', $this->planID);
        }
    }

	public function handleSubmit(){
		$this->_handleSubmit();
	}

	protected function setMenuOrder(){
		$this->aListingData['menu_order'] = !empty($this->aPlanSettings['menu_order']) ?  abs($this->aPlanSettings['menu_order']) : 0;
    }

    private function _handlePreview(){
	    $this->planID     = $_POST['planID'];
	    $this->listingID  = $_POST['listingID'];

	    $this->middleware(['isSupportedPostTypeAddListing', 'isAccountConfirmed', 'canSubmissionListing', 'isExceededFreePlan'], array(
		    'userID'    => get_current_user_id(),
		    'planID'    => $this->planID,
		    'listingID' => $this->listingID,
		    'postType'  => $_POST['listingType']
	    ));

	    if ( !empty($this->listingID) ) {
		    $this->middleware(['isPostAuthor'], array(
			    'postID' => $this->listingID
		    ));
	    }

	    $this->aData        = $_POST['data'];
	    $this->listingType  = $_POST['listingType'];

	    $aMiddleware = $aMiddlewareArgs = array();
	    $aMiddleware[] = 'verifyDirectBankTransfer';
	    $aMiddleware[] = 'isSupportedPostTypeAddListing';

	    $aMiddlewareArgs = array(
		    'userID' => get_current_user_id(),
		    'planID' => $this->planID,
		    'postType' => $this->listingType
	    );

	    if ( !empty($this->listingID) ){
		    $this->postStatus = get_post_status($this->listingID);
		    $aMiddleware[] = 'isListingBeingReviewed';
		    $aMiddlewareArgs['postStatus'] = $this->postStatus;
	    }
	    $this->aPlanSettings = GetSettings::getPlanSettings($this->planID);

	    $this->middleware($aMiddleware, $aMiddlewareArgs);
	    $this->validation();
	    Session::setSession(wilokeListingToolsRepository()->get('payment:storePlanID'), $this->planID);

	    if ( empty($this->listingID) ){
		    $this->isJustInserted = true;
		    $this->aListingData['post_type'] = $this->listingType;
		    $this->listingID = wp_insert_post($this->aListingData);
		    $this->setMenuOrder();

		    if ( empty($this->listingID) ){
			    wp_send_json_error(
				    array(
					    'msg' => esc_html__('Oops! Something went wrong. We could not create a new listing', 'wiloke-listing-tools')
				    )
			    );
		    }
	    }else{
		    $this->isJustInserted = false;
		    $this->aListingData['ID'] = $this->listingID;
		    $postStatus = get_post_status($this->listingID);
		    if ( $postStatus == 'expired' ){
			    $this->setMenuOrder();
		    }

		    if ( (GetSettings::getListingBelongsToPlan($this->listingID) == $this->planID) && ($this->postStatus == 'publish' || $postStatus == 'pending') ){
			    $this->aListingData['post_status'] = 'editing';
		    }

		    wp_update_post($this->aListingData);
	    }

	    $this->belongsToCategories();
	    $this->belongsToLocation();
	    $this->belongsToTags();
	    $this->insertAddress();
	    $this->setSocialNetworks();
	    $this->setContactInfo();
	    $this->setPriceRange();
	    $this->setGroup();
	    $this->setVideos();
	    $this->setSinglePrice();
	    $this->insertLogo();
	    $this->insertFeaturedImg();
	    $this->insertCoverImg();
	    $this->insertGallery();
	    $this->setGeneralSettings();
	    $this->updateBusinessHours();
	    $this->setCustomSections();
	    $this->setListingBelongsTo();
	    $this->addBookingComBannerCreator();
	    $this->setProductsToListing();
	    $this->setMyRoom();

	    do_action('wiloke-listing-tools/addlisting', $this);

	    // Save Session
	    Session::setSession(wilokeListingToolsRepository()->get('payment:storePlanID'), $this->planID);
	    Session::setSession(wilokeListingToolsRepository()->get('payment:sessionObjectStore'), $this->listingID);

	    if ( current_user_can('contributor') ) {
		    SetSettings::setPostMeta($this->listingID, 'claim_status','claimed');
	    }

	    // Maybe Skip Preview Step
        if ( \WilokeThemeOptions::isEnable('addlisting_skip_preview_step', false) ){
            $this->_handleSubmit();
        }

	    wp_send_json_success(array(
		    'redirectTo' => add_query_arg(
			    array(
				    'mode' => 'preview'
			    ),
			    get_permalink($this->listingID)
		    )
	    ));
    }

	public function handlePreview(){
		$this->_handlePreview();
	}

	final public function getPartial($fileName, $isRequired=false){
		if ( !is_file(WILOKE_LISTING_TOOL_DIR . 'views/addlisting/'.$fileName) ){
			if ( WP_DEBUG ){
				return new \WP_Error( 'broke', 'The {'.$fileName.'} does not exits' );
			}else{
				return '';
			}
		}

		if (  $isRequired ){
			include WILOKE_LISTING_TOOL_DIR . 'views/addlisting/'.$fileName . '.php';
		}else{
			require WILOKE_LISTING_TOOL_DIR . 'views/addlisting/'.$fileName . '.php';
		}
	}
}