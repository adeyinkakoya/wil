(function($) {
	'use strict';
	function activeLi() {
		$('.js_wilcity_list_item').on('click', function() {
			$(this).addClass('active')
		})
	}
	$(document).ready(function() {
		activeLi();
	})
})(jQuery);