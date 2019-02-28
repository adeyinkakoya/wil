<?php

namespace WilokeListingTools\Framework\Helpers;


class Validation {
	public static function isUrl($url){
		return filter_var($url, FILTER_VALIDATE_URL);
	}

	public static function isEmail($url){
		return filter_var($url, FILTER_VALIDATE_EMAIL);
	}
}