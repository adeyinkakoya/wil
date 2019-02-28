<?php

namespace WilokeListingTools\Framework\Helpers;


class Message {
	public static function error($msg){
		if ( wp_doing_ajax() ){
			wp_send_json_error(
				array(
					'msg' => $msg
				)
			);
		}else{
			throw new \Exception($msg, 403);
		}
	}
}