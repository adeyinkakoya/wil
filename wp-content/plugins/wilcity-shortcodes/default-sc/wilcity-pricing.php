<?php
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Models\UserModel;
use WILCITY_SC\SCHelpers;

function wilcityPricing($aAtts){
	$aArgs = array(
		'post_type'      => 'listing_plan',
		'post_status'    => 'publish',
		'orderby'        => 'post__in',
		'posts_per_page' => -1
	);
	$wrapperClass = 'container ' . $aAtts['extra_class'];

	if ( $aAtts['listing_type'] == 'flexible' ){
		if ( !isset($_REQUEST['listing_type']) ){
			if ( \WilokeListingTools\Frontend\SingleListing::isElementorEditing() ){
				$aAtts['listing_type'] = \WilokeListingTools\Framework\Helpers\General::getFirstPostTypeKey(false, false);
			}else{
//				WilokeMessage::message(array(
//					'msg' => esc_html__('The Listing Type is required', 'wilcity-shortcodes'),
//					'status'       => 'dange',
//					'hasRemoveBtn' => false,
//					'hasMsgIcon'   => false,
//					'msgIcon'      => 'la la-envelope-o'
//				));
				return '';
			}
        }else{
			$aAtts['listing_type'] = $_REQUEST['listing_type'];
        }

		$aIncludes = GetWilokeSubmission::getAddListingPlans($aAtts['listing_type'].'_plans');
		$aArgs['post__in'] = $aIncludes;
	}else{
	    $planKey = $aAtts['listing_type'] . '_plans';
	    $aPlans = GetWilokeSubmission::getField($planKey);
		$aArgs['post__in'] = explode(',', $aPlans);
    }

    if ( empty($aArgs['post__in']) ){
        WilokeMessage::message(
		    array(
			    'status'       => 'warning',
			    'hasRemoveBtn' => false,
			    'hasMsgIcon'   => false,
			    'msgIcon'      => 'la la-envelope-o',
			    'msg'          => sprintf(__('We found no package yet. Please go to Wiloke Submission -> %s: Set 1 package at least to this Directory Type. We recommend reading <a href="https://documentation.wilcity.com/" target="_blank">Wilcity Documentation</a> -> <strong>Add Listing</strong> to know more.', 'wiloke-listing-tools'), ucfirst($aAtts['listing_type']) . ' Plans')
            ),
            false
        );
        return '';
    }

	$postID = isset($_REQUEST['postID']) ? $_REQUEST['postID'] : '';

	if ( isset($_REQUEST['parentID']) && !empty($_REQUEST['parentID']) ){
	    $aAtts['parentID'] = $_REQUEST['parentID'];
    }

	$query = new WP_Query($aArgs);
	?>
	<div class="<?php echo esc_attr($wrapperClass); ?>">
		<div class="row">
			<?php
			if ( $query->have_posts() ) :
				while ($query->have_posts()) : $query->the_post();
					$aPlanSettings = GetSettings::getPlanSettings($query->post->ID);
					$itemWrapperClass = GetSettings::getPostMeta($query->post->ID, 'is_recommended') ? 'pricing_module__2WIXR pricing_features__3Ki80' : 'pricing_module__2WIXR';
					?>
					<div class="<?php echo esc_attr($aAtts['items_per_row']); ?>">
						<div class="<?php echo esc_attr($itemWrapperClass); ?>">
							<header class="pricing_header__1hEFl">
								<?php if ( GetSettings::getPostMeta($query->post->ID, 'is_recommended') ) : ?>
									<div class="pricing_featuresText__1zmFJ"><?php echo esc_html(GetSettings::getPostMeta($query->post->ID, 'recommend_text')); ?></div>
								<?php endif; ?>
								<h2 class="pricing_title__1vXhE"><?php echo get_the_title($query->post->ID); ?></h2>
								<?php
								SCHelpers::renderPlanPrice($aPlanSettings['regular_price']);
								$remainingItems = UserModel::getRemainingItemsOfPlans($query->post->ID);
								if ( !empty($remainingItems) ) :
									?>
									<i class="wilcity-remaining-item-info" style="color: red;"><?php esc_html_e('Remaining Items: ', 'wilcity-shortcodes'); ?><?php echo $remainingItems >= 1000 ? esc_html__('Unlimited', 'wilcity-shortcodes') : esc_html($remainingItems); ?></i>
								<?php endif; ?>
							</header>
							<div class="pricing_body__2-Vq5">
								<div class="pricing_list__KtU8u">
									<?php the_content(); ?>
								</div>
							</div>
							<footer class="pricing_footer__qz3lM">
								<a class="wil-btn wil-btn--primary wil-btn--md wil-btn--round wil-btn--block " href="<?php echo esc_url(apply_filters('wilcity/submission/pricingUrl', $query->post->ID, $postID, $aAtts)); ?>"><i class="la la-check"></i> <?php esc_html_e('Get Now', 'wilcity-shortcodes'); ?>
								</a>
							</footer>
						</div>
					</div>
				<?php endwhile; wp_reset_postdata(); ?>
			<?php else: ?>
				<?php
				WilokeMessage::message(
					array(
						'status' => 'danger',
						'msg'    => esc_html__('You do not have any Add Listing Plan. From the admin sidebar, click on Listing Plans to create one', 'wilcity-shortcodes')
					)
				);
				?>
			<?php endif; ?>
		</div>
	</div>
	<?php
}