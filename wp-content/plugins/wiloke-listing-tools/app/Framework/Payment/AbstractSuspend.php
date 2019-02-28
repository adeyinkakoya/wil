<?php

namespace WilokeListingTools\Framework\Payment;


use WilokeListingTools\Framework\Payment\PayPal\PayPalSuspendPlan;
use WilokeListingTools\Framework\Payment\Stripe\DirectBankTransferSuspend;
use WilokeListingTools\Framework\Payment\Stripe\StripeSuspendPlan;
use WilokeListingTools\Framework\Store\Session;
use WilokeListingTools\Models\PaymentModel;

abstract class AbstractSuspend {
	protected $paymentID;
	protected function setPaymentID($paymentID){
		$this->paymentID = $paymentID;
	}

	protected function suspend(){
		$gateway = PaymentModel::getField('gateway', $this->paymentID);
		switch ($gateway){
			case 'paypal':
				$instSuspend = new PayPalSuspendPlan();
				$instSuspend->setPaymentID($this->paymentID);
				break;
			case 'stripe':
				$instSuspend = new StripeSuspendPlan();
				$instSuspend->setPaymentID($this->paymentID);
				break;
			case 'banktransfer':
			case 'free':
				$instSuspend = new DirectBankTransferSuspend();
				$instSuspend->setPaymentID($this->paymentID);
				break;
		}

		if ( isset($instSuspend) ){
			return $instSuspend->suspend();
		}

		return false;
	}
}