/**
 * Created by pirates on 2/6/18.
 */
;(function ($) {
	let $msg  = $('#wiloke-submission-message-after-update'),
		paymentID = $('[name="paymentID"]').val();

	function handleResult($btn, response) {
		let $elID = '';

		$btn.removeClass('loading');
		$btn.prop('disabled', false);
		if ( response.success ){
			$msg.removeClass('hidden');
			if ( typeof response.data !== 'undefined' ){
				for( elID in response.data.msg ){
					$elID = $('#'+elID);
					if ( $elID.length ){
						$elID.val(response.data.msg[elID]);
					}
				}
			}
		}else{
			$msg.addClass('hidden');
			alert(response.data.msg.replace(/<{1}[^<>]{1,}>{1}/g," "));
		}
	}

	function updateNextBillingDate() {
		let $createInvoiceBtn = $('#wiloke-charge-the-next-billing-date');

		$createInvoiceBtn.on('click', function (event) {
			event.preventDefault();

			let iWantDoThat = confirm('Do you want extend the next billing date?');

			if ( !iWantDoThat ){
				return false;
			}

			$createInvoiceBtn.addClass('loading');
			$createInvoiceBtn.prop('disabled', true);

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action: 'extend_next_billing_date',
					paymentID: paymentID,
					planID: $('[name="planID"]').val()
				},
				success: function (response) {
					handleResult($createInvoiceBtn, response);
				}
			})
		})
	}

	function refundSale() {
		let $btn = $('#wiloke-submission-refund-sale');

		$btn.on('click', (event=>{
			event.preventDefault();

			let iWantToDoThat = confirm('This is a permanent change and all posts belong to this plan will be moved to trash. You still want to that?');

			if ( !iWantToDoThat ){
				return false;
			}
			$btn.addClass('loading');
			$btn.prop('disabled', true);

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action: 'refund_sale',
					paymentID:paymentID,
					gateway: $('#gateway').val()
				},
				success: (response=>{
					handleResult($btn, response);
				})
			})
		}))
	}

	function cancelSubscription(){
		let $btn = $('#wiloke-submission-cancel-subscription');
		$btn.on('click', (event=>{
			event.preventDefault();

			let iWantToDoThat = confirm('This is a permanent change and all posts belong to this plan will be moved to trash. You still want to that?');

			if ( !iWantToDoThat ){
				return false;
			}
			$btn.addClass('loading');
			$btn.prop('disabled', true);

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action: 'cancel_subscription',
					paymentID:paymentID,
					gateway: $('#gateway').val()
				},
				success: (response=>{
					handleResult($btn, response);
				})
			})
		}))
	}

	function changeDirectBankTransferNonRecurringPaymentStatus() {
		let $btn = $('#wiloke-submission-change-banktransfer-nonrecurring-payment-status');

		$btn.on('click', (event=>{
			event.preventDefault();

			let newStatus       = $('#change_to_new_order_status').val(),
				$orderStatus    = $('#order_status'),
				currentStatus   = $orderStatus.val();

			if (newStatus === '' ||  newStatus === currentStatus ){
				alert('Please select another status');
				return false;
			}

			if ( newStatus === 'cancelled' || newStatus === 'processing'   ){
				let iWantToDoThat = confirm('All posts belong to this plan will be hidden. You still want to that?');

				if ( !iWantToDoThat ){
					return false;
				}
			}else if ( newStatus === 'refunded' ){
				let iWantToDoThat = confirm('This is a permanent change and all posts belong to this plan will be hidden. You still want to that?');

				if ( !iWantToDoThat ){
					return false;
				}
			}

			$btn.addClass('loading');
			$btn.prop('disabled', true);

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action: 'change_banktransfer_order_status_'+$('#billingType').val(),
					paymentID:paymentID,
					newStatus:newStatus,
					oldStatus:currentStatus,
					gateway: $('#gateway').val()
				},
				success: (response=>{
					handleResult($btn, response);
				})
			})
		}))
	}

	function changeDirectBankTransferRecurringPaymentStatus() {
		let $btn = $('#wiloke-submission-change-banktransfer-recurring-payment-status');

		$btn.on('click', (event=>{
			event.preventDefault();

			let newStatus       = $('#change_to_new_order_status').val(),
				$orderStatus    = $('#order_status'),
				currentStatus   = $orderStatus.val();

			if (newStatus === '' ||  newStatus === currentStatus ){
				alert('Please select another status');
				return false;
			}

			if ( newStatus === 'cancelled' || newStatus === 'processing'   ){
				let iWantToDoThat = confirm('All posts belong to this plan will be hidden. You still want to that?');

				if ( !iWantToDoThat ){
					return false;
				}
			}else if ( newStatus === 'refunded' ){
				let iWantToDoThat = confirm('This is a permanent change and all posts belong to this plan will be hidden. You still want to that?');

				if ( !iWantToDoThat ){
					return false;
				}
			}

			$btn.addClass('loading');
			$btn.prop('disabled', true);

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{
					action: 'change_banktransfer_order_status_'+$('#billingType').val(),
					paymentID:paymentID,
					newStatus:newStatus,
					oldStatus:currentStatus,
					gateway: $('#gateway').val()
				},
				success: (response=>{
					handleResult($btn, response);
				})
			})
		}))
	}
	
	function deleteInvoices() {
		let $btn = $('#wilcity-delete-all-invoices');
		$btn.on('click', function (event) {
			event.preventDefault();

			let iWantToDoIt = confirm('All Invoices will be deleted permanently, You still want to do it?');
			if ( !iWantToDoIt ){
				return false;
			}

			$btn.removeClass('loading');
			$btn.prop('disabled', false);
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'delete_all_invoices'
				},
				success: (function (response) {
					alert(response);
					if ( response.success ){
						window.reload();
					}
				})
			})
		})
	}

	$(document).ready(function () {
		// changeSaleStatus();
		updateNextBillingDate();
		cancelSubscription();
		refundSale();
		changeDirectBankTransferNonRecurringPaymentStatus();
		changeDirectBankTransferRecurringPaymentStatus();
		deleteInvoices();
	});

})(jQuery);