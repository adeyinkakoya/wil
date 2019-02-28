<?php

namespace WilokeListingTools\Controllers;


use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Helpers\GetWilokeSubmission;
use WilokeListingTools\Framework\Helpers\SetSettings;

trait SetPostDuration {
	private function setDuration($billingType, $postID, $planID, $isTrial=false){
		if ( GetWilokeSubmission::isNonRecurringPayment($billingType) ){
			$isTrial = false;
		}

		$aPlanSettings = GetSettings::getPlanSettings($planID);

		if ( $isTrial ){
			$duration = $aPlanSettings['trial_period'];
		}else{
			$duration = $aPlanSettings['regular_period'];
		}

		SetSettings::setPostMeta($postID, 'duration', $duration);
	}
}