<?php

namespace WILCITY_APP\Controllers;


trait ParsePost {
	public function parsePost(){
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
		$rawData = file_get_contents('php://input');

		if ( $method == 'DELETE' ){
			parse_str($rawData, $aData);
		}else{
			if ( !empty($rawData) ){
				$aData = json_decode($rawData, true);
			}else{
				$aData = $_POST;
			}
		}

		return $aData;
	}
}