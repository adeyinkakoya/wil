(function ($) {
	'use strict';

	let $lineIconPopup = document.getElementById('wiloke-lineicon-popup'), $lineIconPopupJQ = $('#wiloke-lineicon-popup');

	function searchIcon($modal){
		if ( !$lineIconPopupJQ.length || $lineIconPopupJQ.data('addedScript') ){
			return false;
		}

		let val = '';
		document.getElementById('wiloke-search-icon').addEventListener('keyup', function () {
			val = document.getElementById('wiloke-search-icon').value.toLowerCase();
			if ( val.length ){
				$lineIconPopupJQ.find('.wil-icon').addClass('hidden');
				$lineIconPopupJQ.find('[class*="'+val+'"]').closest('.wil-icon').removeClass('hidden');
			}else{
				$lineIconPopupJQ.find('.wil-icon').removeClass('hidden');
			}

		});
		$lineIconPopupJQ.data('addedScript', true);

		$lineIconPopupJQ.find('.wil-icon').on('click', function (event) {
			event.preventDefault();
			let icon = $(this).find('i').attr('class');
			$lineIconPopupJQ.trigger('onChangedIcon', [icon]);
			$modal.modal('hide');
		})
	}

	function fireEvent(el, event) {
		let evt;
		if ( document.createEventObject ){
			evt = document.createEventObject();
			el.fireEvent(event,evt)
		}else{
			evt = document.createEvent('HTMLEvents');
			evt.initEvent(event, true, true);
			return el.dispatchEvent(evt);
		}
	}

	$(document).ready(function () {
		$('.menu .item').tab();
		$('.ui.checkbox').checkbox();
		$('.ui.dropdown:not(.no-js)').dropdown();
		$('.semantic-tabs .item').tab();

		let $colorPicker = $('.wilcity-color-picker');
		if ( $.fn.spectrum  && $colorPicker.length ){
			$colorPicker.each(function () {
				$(this).spectrum({
					showAlpha: true,
					change: ((color)=>{
						$(this).trigger('change');
					})
				});
			});
		}
		let input = null, self = null;
		// Create the event.
		var event = document.createEvent('Event');
		event.initEvent('click', true, false);

		document.querySelectorAll('.wiloke-form-has-icon').forEach(function (formEl) {
			formEl.addEventListener('click', function (event) {
				if ( event.target && event.target.className !== 'wil-icon-box' && event.target.parentElement.className !== 'wil-icon-box' ){
					return false;
				}

				if ( event.target.className === 'wil-icon-box' ){
					self = event.target.querySelector('i');
					input = event.target.nextElementSibling.querySelector('.wiloke-icon');
				}else{
					self  = event.target;
					input = event.target.parentElement.nextElementSibling.querySelector('.wiloke-icon');
				}

				self.classList.add('heartbeat');

				let oPromise = new Promise((resolve, reject)=>{
					if (  $lineIconPopup.getAttribute('loadedicon') === null ){
						jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {
								action: 'wiloke_load_line_icon'
							},
							success: response =>{
								document.getElementById('wiloke-icon-content').innerHTML = response.data;
								$lineIconPopup.setAttribute('loadedicon', true);
								resolve('success');
							}
						})
					}else{
						resolve('success');
					}
				});

				oPromise.then(()=>{
					self.classList.remove('heartbeat');
					let $modal = $lineIconPopupJQ.modal('show', {
						duration: 0
					});
					$lineIconPopupJQ.on('onChangedIcon', (event, val)=>{
						input.value = val;
						self.setAttribute('class', val);
						fireEvent(input, 'update-icon');
					});
					searchIcon($modal);
				});
			})
		});

	})

})(jQuery);