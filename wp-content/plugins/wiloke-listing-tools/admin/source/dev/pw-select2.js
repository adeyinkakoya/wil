(function ($) {
	'use strict';

	$('.pw_select').each(function () {
		let $this = $(this);
		$this.select2({
			ajax: {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: $this.data('action'),
					args: $this.data('args')
				},
				processResults: function (data, params) {

					if ( !data.success ){
						return false;
					}else{
						return typeof data.msg !== 'undefined' ? data.msg : data;
					}
				},
			},
			allowClear: true,
			placeholder: '',
			minimumInputLength: 1
		});
	});

	$('.pw_multiselect').each(function () {
		let $this = $(this);
		$this.select2({
			ajax: {
				url: ajaxurl,
				type: 'POST',
				data: function (params) {
					return {
						action: $this.data('action'),
						args: $this.data('args'),
						s: params.term
					}
				},
				processResults: function (data, params) {
					if ( !data.success ){
						return false;
					}else{
						return typeof data.data.msg !== 'undefined' ? data.data.msg : data.data;
					}
				},
				cache: true
			},
			allowClear: true,
			placeholder: '',
			minimumInputLength: 1
		});
	});
})(jQuery);