/**
 * Created by pirates on 1/16/18.
 */
(function ($) {
	'use strict';
	function checkboxItem() {
		$('.wiloke_checkall').on('change', function (event) {
			if ( $(this).is(':checked') ){
				$('.wiloke_checkbox_item').prop('checked', true);
			}else{
				$('.wiloke_checkbox_item').prop('checked', false);
			}
		})
	}

	function deleteSales() {
		$('.js_delete_sales').on('click', function (event) {
			event.preventDefault();

			let iWantToDelete = confirm('Are you sure that want to delete all checked items?');
			if ( !iWantToDelete ){
				return false;
			}

			let aItems = [];
			$('.wiloke_checkbox_item:checked').each(function () {
				aItems.push($(this).val());
			});

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'wiloke_delete_sales',
					items: aItems
				},
				success: response =>{
					alert(response.data);
					if ( response.success ){
						location.reload();
					}
				}
			})
		})
	}

	function deleteInvoices() {
		$('#js_delete_invoices').on('click', function (event) {
			event.preventDefault();

			let iWantToDelete = confirm('Are you sure that want to delete all checked items?');
			if ( !iWantToDelete ){
				return false;
			}

			let aItems = [];
			$('.wiloke_checkbox_item:checked').each(function () {
				aItems.push($(this).val());
			});

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'wiloke_delete_invoices',
					items: aItems
				},
				success: response =>{
					alert(response.data);
					if ( response.success ){
						location.reload();
					}
				}
			})
		})
	}

	function deleteSubscriptions() {
		$('.js_delete_subscription').on('click', function (event) {
			event.preventDefault();

			let iWantToDelete = confirm('Are you sure that want to delete all checked items?');
			if ( !iWantToDelete ){
				return false;
			}

			let aItems = [];
			$('.wiloke_checkbox_item:checked').each(function () {
				aItems.push($(this).val());
			});

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'wiloke_delete_subcriptions',
					items: aItems
				},
				success: response =>{
					alert(response.data);
					if ( response.success ){
						location.reload();
					}
				}
			})
		})
	}


	$(document).ready(function () {
		deleteInvoices();
		deleteSubscriptions();
		deleteSales();
		checkboxItem();
		checkboxItem();

		if ($().accordion) {
			$('.ui.accordion').accordion();
		}

		if ($().dropdown) {
			$('.ui.dropdown').each(function () {
				$(this).dropdown({
					forceSelection: false
				});
			});

			$('.ui.selection').on('click', 'a.ui.label', function (event) {
				event.preventDefault();
			})
		}

		if ($().tab) {
			$('.menu .item').tab();
		}

		if ( $().datepicker ){
			$('.wiloke_datepicker').datepicker({
				dateFormat: 'yy/mm/dd'
			});
		}

		let $dependency = $('.wiloke-has-dependency');
		if ( $dependency.length ){
			$dependency.each(function (event) {
				let $self = $(this),
					oDependency = $(this).data('dependency'),
					aValues = oDependency.value.split(',');

				$('[name="'+oDependency.name+'"]').on('change', function () {
					let val = $(this).val();
					if ( aValues.indexOf(val) !== -1 ){
						$self.removeClass('hidden');
					}else{
						$self.addClass('hidden');
					}
				}).trigger('change');
			});
		}

		$(document).on('click', '.selection .ui.label', function (event) {
			event.preventDefault();
		});
	})
})(jQuery);