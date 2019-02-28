<?php
/*
 * Template Name: User Plan Info
 */

get_header();
	if ( have_posts() ){
		while ( have_posts() ){
			the_post();
			$aUserPlans = \WilokeListingTools\Framework\Helpers\GetSettings::getUserPlans(get_current_user_id());

			echo '<h1>User Plans</h1>';
			echo '<pre>';
				var_export($aUserPlans);
			echo '</pre>';
			echo '<hr>';

			echo '<h1>Next Billing Date</h1>';
			$paymentID = \WilokeListingTools\Models\PaymentModel::getLastPaymentID(get_current_user_id());
			echo \WilokeListingTools\Models\PaymentMetaModel::getNextBillingDateGMT($paymentID);
			echo '<hr>';
		}
	}
get_footer();