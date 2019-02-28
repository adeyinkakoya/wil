<?php

namespace WilokeListingTools\Middleware;


use WilokeListingTools\Framework\Helpers\GetSettings;
use WilokeListingTools\Framework\Routing\InterfaceMiddleware;

class IsAccountConfirmed implements InterfaceMiddleware {
	public $msg = '';

	public function handle( array $aOptions ) {
		if ( !isset($aOptions['userID']) ){
			$aOptions['userID'] = get_current_user_id();
		}

		if ( \WilokeThemeOptions::isEnable('toggle_confirmation', true) ){
			return true;
		}

		if ( current_user_can('administrator') ){
			return true;
		}

		$confirmed = GetSettings::getUserMeta($aOptions['userID'], 'confirmed');
		if ( empty($confirmed) ){
			$this->msg = esc_html__('We have sent an email with a confirmation link to your email address. In order to complete the sign-up process, please click the confirmation link.', 'wiloke-listing-tools');
			return false;
		}
		return true;
	}
}