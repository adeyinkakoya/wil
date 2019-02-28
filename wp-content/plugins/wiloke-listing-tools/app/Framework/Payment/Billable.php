<?php
namespace WilokeListingTools\Framework\Payment;


use WilokeListingTools\Framework\Routing\Controller;

class Billable extends Controller {
	protected $gateway;
	protected $planID;
	protected $aConfigs;

	public function __construct($aArgs, $isNotDieIfFalse=false) {
		$this->aConfigs = $aArgs;
		$this->gateway = $aArgs['gateway'];
		$this->guard();
	}

	private function guard(){
		$aMiddleware = array('isGatewaySupported');

		if ( !isset($this->aConfigs['category']) || $this->aConfigs['category'] != 'promotion' ){
			$aMiddleware[] = 'isPlanExists';
		}

		$this->middleware($aMiddleware, array(
			'gateway'       => $this->aConfigs['gateway'],
			'planID'        => $this->aConfigs['planID'],
			'listingType'   => $this->aConfigs['listingType'],
			'planType'      => isset($this->aConfigs['planType']) ? $this->aConfigs['planType'] : ''
		));
	}
}