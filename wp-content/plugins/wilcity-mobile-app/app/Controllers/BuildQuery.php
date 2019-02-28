<?php

namespace WILCITY_APP\Controllers;


trait BuildQuery {
	private function buildSingleQuery($aData){
		$aArgs = array(
			'post_status'   => 'publish',
			'post_type'		=> get_post_type($aData['target']),
			'post_per_page' => 1
		);

		if ( is_numeric($aData['target']) ){
			$aArgs['p'] = abs($aData['target']);
		}else{
			$aArgs['name'] = $aData['target'];
		}
		return $aArgs;
	}
}