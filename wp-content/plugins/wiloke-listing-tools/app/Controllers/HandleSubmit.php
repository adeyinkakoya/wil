<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\DebugStatus;
use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Payment\FreePlan\FreePlan;
use WilokeListingTools\Framework\Store\Session;
use WilokeListingTools\Models\UserModel;

trait HandleSubmit {
	private function _handleSubmit(){
		$this->listingID = Session::getSession(wilokeListingToolsRepository()->get('payment:sessionObjectStore'));
		$this->postStatus = get_post_status($this->listingID);
		$this->planID = Session::getSession(wilokeListingToolsRepository()->get('payment:storePlanID'));
		$this->aPlanSettings = GetSettings::getPlanSettings($this->planID);

		$this->middleware(['isUserLoggedIn', 'canSubmissionListing', 'isPassedPostAuthor', 'isExceededFreePlan', 'isPlanExists'], array(
			'postID' => $this->listingID,
			'planID' => $this->planID,
			'userID' => get_current_user_id(),
			'listingType'   => get_post_type($this->listingID)
		));

		$aUpdatePost = array(
			'ID' => $this->listingID
		);

		if ( !defined('WILOKE_LISTING_TOOLS_CHECK_EVEN_ADMIN') && current_user_can('edit_theme_options') ){
			$aUpdatePost['post_status'] = 'publish';
			wp_update_post($aUpdatePost);
			wp_send_json_success(
				array(
					'redirectTo' => GetWilokeSubmission::getField('thankyou', true)
				)
			);
		}

		if ( $this->postStatus == 'editing' ){
			if ( GetWilokeSubmission::getField('published_listing_editable') == 'allow_trust_approved' ){
				$aUpdatePost['post_status'] = 'publish';
			}else{
				$aUpdatePost['post_status'] = 'pending';
			}

			do_action('wiloke/submitted-listing', get_post_field('post_author', $this->listingID), $this->listingID);

			wp_update_post($aUpdatePost);
			wp_send_json_success(
				array(
					'redirectTo' => GetWilokeSubmission::getField('thankyou', true)
				)
			);
		}

		if ( in_array($this->postStatus, array('unpaid', 'expired')) ){
			$aUserPlan = UserModel::getSpecifyUserPlanID($this->planID, get_current_user_id(), true);
			$aInfo = array(
				'planID'    => $this->planID,
				'objectID'  => $this->listingID,
				'userID'    => get_current_user_id()
			);

			$planRelationship = $this->setPlanRelationship($aUserPlan, $aInfo);
			if ( !$planRelationship ){
				wp_send_json_error( array(
					'msg' => esc_html__('ERROR: We could not insert plan relationship.', 'wiloke-listing-tools')
				) );
			}

			if ( !empty($aUserPlan) ){
				if ( !empty($aUserPlan['remainingItems']) && (absint($aUserPlan['remainingItems']) > 0) ){
					$instUserModel = new UserModel();
					$instUserModel->updateRemainingItemsUserPlan($this->planID);
					$isTrial = isset($aUserPlan['isTrial']) && $aUserPlan['isTrial'];

					$this->setDuration(GetWilokeSubmission::getBillingType(), $this->listingID, $this->planID, $isTrial);

					if ( GetWilokeSubmission::getField('approved_method') == 'manual_review' ){
						$aUpdatePost['post_status'] = 'pending';
					}else{
						$aUpdatePost['post_status'] = 'publish';
					}

					wp_update_post($aUpdatePost);
					do_action('wiloke/submitted-listing', get_post_field('post_author', $this->listingID), $this->listingID);

					if ( !DebugStatus::status('WILOKE_ALWAYS_PAY') ){
						wp_send_json_success(
							array(
								'redirectTo' => GetWilokeSubmission::getField('thankyou', true)
							)
						);
					}
				}else if(GetWilokeSubmission::isNonRecurringPayment($aUserPlan['billingType'])){
					$this->middleware(['isExceededMaximumListing'], array('aUserPlan'=>$aUserPlan));
				}
			}
		}

		// Free Add Listing
		if ( empty($this->aPlanSettings['regular_price']) ){
			$oFreePlan = new FreePlan($this->planID);
			$aStatus = $oFreePlan->proceedPayment();
			if ( $aStatus['status'] == 'success' ){
				do_action('wiloke/submitted-listing', get_post_field('post_author', $this->listingID), $this->listingID);
				$redirectTo = GetWilokeSubmission::getField('thankyou', true);
				wp_send_json_success(
					array(
						'redirectTo' => $redirectTo
					)
				);
			}else{
				wp_send_json_error(array(
					'msg' => esc_html__('ERROR: We could not create Free Plan', 'wiloke-listing-tools')
				));
			}
		}

		$redirectTo = GetWilokeSubmission::getField('checkout', true);
		// If paying via WooCoomerce, we need to get rid of this product id from the cart
		$productID  = GetSettings::getPostMeta($this->planID, 'woocommerce_association');

		if ( !empty($productID) ){
			$wooCommerceCartUrl = GetSettings::getCartUrl($this->planID);
			/*
			* @hooked WooCommerceController:removeProductFromCart
			*/
			do_action('wiloke-listing-tools/before-redirecting-to-cart', $productID);
			$redirectTo = $wooCommerceCartUrl;
			Session::setSession(wilokeListingToolsRepository()->get('payment:associateProductID'), $productID);
		}

		do_action('wiloke/submitted-listing', get_post_field('post_author', $this->listingID), $this->listingID);

		wp_send_json_success(
			array(
				'redirectTo' => $redirectTo
			)
		);
	}
}