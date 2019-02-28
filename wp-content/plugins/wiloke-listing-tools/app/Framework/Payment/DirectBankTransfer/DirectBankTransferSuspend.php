<?php

namespace WilokeListingTools\Framework\Payment\Stripe;

use WilokeListingTools\Framework\Payment\SuspendInterface;
use WilokeListingTools\Models\PaymentModel;

class DirectBankTransferSuspend implements SuspendInterface {
	use StripeConfiguration;

	protected $paymentID;
	protected $subscriptionID;

	public function setPaymentID($paymentID){
		$this->paymentID = $paymentID;
	}

	public function suspend(){
		$status = PaymentModel::getField('status', $this->paymentID);
		if ( $status !== 'active' ){
			return true;
		}
		PaymentModel::updatePaymentStatus('suspended', $this->paymentID);
		return true;
	}
}