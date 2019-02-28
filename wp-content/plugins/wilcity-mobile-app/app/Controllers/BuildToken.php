<?php

namespace WILCITY_APP\Controllers;


use ReallySimpleJWT\TokenBuilder;
use WilokeListingTools\Framework\Helpers\SetSettings;

trait BuildToken {
	private function buildToken($oUser, $expiration=''){
		$builder = new TokenBuilder();

		if ( empty($expiration) ){
			$expiration = $this->getOptionField('wilcity_token_expired_after');
			$expiration = !empty($expiration) ? '+' . $expiration . ' day' : '+30 day';
		}

		$token = $builder->addPayload(['key' => 'userID', 'value' => $oUser->ID])
		               ->setSecret($this->getSecurityAuthKey())
		               ->setExpiration(strtotime($expiration))
		               ->setIssuer(get_option('siteurl'))
		               ->build();
		SetSettings::setUserMeta($oUser->ID, 'app_token', $token);
		return $token;
	}
}