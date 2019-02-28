<?php
namespace WilokeListingTools\MetaBoxes;


use WilokeListingTools\Framework\Helpers\General;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\SetSettings;

class ListingPlan {
	public function __construct() {
		add_filter('woocommerce_product_data_tabs', array($this, 'addListingPricingTab'));
		add_action('woocommerce_product_data_panels', array($this, 'addListingPricingContent'));
		add_action('woocommerce_process_product_meta_simple', array($this, 'saveListingPlan'));
		add_action('cmb2_admin_init', array($this, 'renderMetaboxFields'));
		add_action('add_meta_boxes', array($this, 'registerMetaBoxes'), 10, 5);
		add_action('wp_ajax_wilcity_set_plan_directly', array($this, 'setPlanDirectly'));
		add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
		add_filter('wiloke-listing-tools/config/listingplan/listing_plan_settings', array($this, 'addCustomFieldToListingPlans'));
	}

	public static function getMaximumGalleryImagesAllowed(){
	    if ( !current_user_can('administrator') ){
	        return 10;
        }
        if ( !isset($_GET['post']) || empty($_GET['post']) ){
	        return 10;
        }

		$aPlanSettings = GetSettings::getPlanSettings($_GET['post']);
        if ( isset($aPlanSettings['maximumGalleryImages']) ){
            return absint($aPlanSettings['maximumGalleryImages']);
        }

        return 10;
    }

	public static function getMaximumVideosAllowed(){
		if ( !current_user_can('administrator') ){
			return 4;
		}
		if ( !isset($_GET['post']) || empty($_GET['post']) ){
			return 4;
		}

		$aPlanSettings = GetSettings::getPlanSettings($_GET['post']);
		if ( isset($aPlanSettings['maximumVideos']) ){
			return absint($aPlanSettings['maximumVideos']);
		}

		return 4;
	}

	public function enqueueScripts(){
	    if ( !General::isPostType('listing_plan') ){
	        return false;
        }

        wp_enqueue_script('listing-plan', WILOKE_LISTING_TOOL_URL . 'admin/source/js/listing-plan.js', array('jquery'), WILOKE_LISTING_TOOL_VERSION, true);
    }

	public function setPlanDirectly(){
	    if ( !current_user_can('administrator') ){
	        wp_send_json_error(array('msg'=>'ERROR: You do not have permission to access this page.'));
        }

        if ( empty($_POST['postID']) ){
	        wp_send_json_error(array('msg'=>'ERROR: The Plan ID is emptied!'));
        }

        $aConfiguration = GetWilokeSubmission::getAll();

        if ( isset($_POST['belongsTo']) && !empty($_POST['belongsTo']) ){
            $aPlans = GetWilokeSubmission::getAddListingPlans($_POST['belongsTo']);
            $key = array_search($_POST['postID'], $aPlans);
            unset($aPlans[$key]);
	        $aConfiguration[$_POST['belongsTo']] = implode(',', $aPlans);
        }

        $newPlanKey = $_POST['changeBelongsTo'] . '_plans';
        $aChangeBelongsTo = GetWilokeSubmission::getAddListingPlans($newPlanKey);
		$aChangeBelongsTo[] = $_POST['postID'];
		$aConfiguration[$newPlanKey] = implode(',', $aChangeBelongsTo);

		update_option('wiloke_submission_configuration', maybe_serialize($aConfiguration));
		wp_send_json_success(array('msg'=>'Congratulations! This plan has been added to '.$_POST['changeBelongsTo']));
    }

	public function renderBelongsToBox(){
	    $aPostTypes = General::getPostTypes(false, false);
		$belongsTo = '';
	    ?>
        <p>Need help? <a href="https://documentation.wilcity.com/knowledgebase/setting-up-package-setting/" target="_blank">Setting up Plans to Each Directory Type</a></p>

        <p>
            <label for="wilcity-change-plan-belongs-to"><strong>Set this plan to</strong></label>
            <select name="belongs_to" id="wilcity-change-plan-belongs-to">
                <option value="">---</option>
                <?php
                foreach ($aPostTypes as $postType => $aPostType):
                    $aPlanIDs = GetWilokeSubmission::getAddListingPlans($postType.'_plans');
                    if ( isset($_GET['post']) && !empty($_GET['post']) && !empty($aPlanIDs) && in_array($_GET['post'], $aPlanIDs) ) :
                        $belongsTo = $postType . '_plans';
                ?>
                    <option value="<?php echo esc_attr($postType); ?>" selected><?php echo esc_html($aPostType['singular_name'] . ' Plan'); ?></option>
                    <?php else: ?>
                    <option value="<?php echo esc_attr($postType); ?>"><?php echo esc_html($aPostType['singular_name'] . ' Plan'); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <input type="hidden" id="wilcity-plan-belongs-to" value="<?php echo esc_attr($belongsTo); ?>">
            <button id="wilcity-add-plan-to-directory-directly" class="button button-primary button-large">Execute</button>
        </p>
        <?php
    }

	public function registerMetaBoxes(){
		add_meta_box( 'wilcity-listing-plan-belongs-to', 'Belongs To', array($this, 'renderBelongsToBox'), 'listing_plan', 'normal' );
	}

	public function renderMetaboxFields(){
	    foreach (wilokeListingToolsRepository()->get('listingplan') as $aSettings){
		    $aSettings = apply_filters('wiloke-listing-tools/config/listingplan/'.$aSettings['id'], $aSettings);
		    new_cmb2_box($aSettings);
        }
    }

    protected function determineBelongToDirectoryType($planID){
	    $aPostTypes = General::getPostTypeKeys(false, false);
	    foreach ($aPostTypes as $postType){
		    $aPlanIDs   = GetWilokeSubmission::getAddListingPlans($postType.'_plans');
		    if ( !empty($aPlanIDs) && in_array($planID, $aPlanIDs) ){
		        return $postType;
            }
        }

        return false;
    }

    public function addCustomFieldToListingPlans($aSettings){
	    if ( !isset($_GET['post']) || empty($_GET['post']) ){
	        return $aSettings;
        }
	    $planID     = $_GET['post'];
        $belongsTo  = $this->determineBelongToDirectoryType($planID); //postType

        if ( !$belongsTo ){
            return $aSettings;
        }

	    $aUsedSections = General::getCustomFieldsOfPostType($belongsTo);
        $aGroups       = General::getCustomGroupsOfPostType($belongsTo);

        if ( empty($aUsedSections) && empty($aGroups) ){
            return $aSettings;
        }

        $aCustomFields = array();

        if ( !empty($aUsedSections) ){
	        $aCustomFields = $aUsedSections;
        }

	    if ( !empty($aGroups) ){
		    $aCustomFields = array_merge($aCustomFields, $aGroups);
	    }

        foreach ($aCustomFields as $aFieldSettings){
            $aSettings['fields'][] = array(
	            'type'          => 'wiloke_field',
	            'fieldType'     => 'select',
	            'id'            => 'add_listing_plan:toggle_'.$aFieldSettings['key'],
	            'name'          => 'Toggle '.$aFieldSettings['heading'],
	            'options'       => array(
		            'enable' => 'Enable',
		            'disable' => 'Disable'
	            )
            );
        }

        return $aSettings;
    }

	public static function renderProductAlias(){
	    $aPosts = get_posts(
            array(
	            'post_type'         => 'product',
                'posts_per_page'    => -1,
                'post_status'       => 'publish'
            )
        );

	    $aOptions = array(
            '' => '----'
        );

	    foreach ($aPosts as $post){
		    $aOptions[$post->ID] = $post->post_title;
        }
        return $aOptions;
    }

	public function saveListingPlan($postID){
        if ( isset($_POST['listing_plan']) && !empty($_POST['listing_plan'])  ){
            SetSettings::setPostMeta($postID, 'listing_plan', $_POST['listing_plan']);
        }
    }

	public function addListingPricingTab($aProductDataTabs){
		$aProductDataTabs['listing-pricing'] = array(
			'label'  => esc_html__( 'Listing pricing', 'wiloke-listing-tools' ),
			'target' => 'wilcity_listing_pricing',
		);
		return $aProductDataTabs;
	}

	public function getPricing(){
		global $wpdb;
		$aResults = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type='listing_plan' AND post_status='publish' LIMIT 100");

		if ( empty($aResults) || is_wp_error($aResults) ){
			return false;
		}

		$aOptions = array(
			'' => '----'
		);
		foreach ($aResults as $oResult){
			$aOptions[$oResult->ID] = $oResult->post_title;
		}
		return $aOptions;
	}

	public function addListingPricingContent(){
		global $post;
		$id = $post->ID;
		$aOptions = $this->getPricing();
		?>
		<!-- id below must match target registered in above add_my_custom_product_data_tab function -->
		<div id="wilcity_listing_pricing" class="panel woocommerce_options_panel">
			<?php
				if ( empty($aOptions) ){
					?>
					<strong><?php esc_html_e('There is no Listing pricing', 'wiloke-listing-tools'); ?></strong>
					<?php
				}else{
				    $val = GetSettings::getPostMeta($id, 'listing_plan');
					woocommerce_form_field( 'listing_plan', array(
						'type'          => 'select',
						'class'         => array( 'wps-drop' ),
						'label'         => esc_html__( 'Select a Listing Pricing', 'wiloke-listing-tools' ),
						'options'       => $aOptions
					), $val);
				}
			?>
		</div>
		<?php
	}
}