(function () {
'use strict';

(function ($) {
	'use strict';

	$.fn.wilcityInitMagnific = function () {
		$(this).find('.wilcity-magnific-via-jquery').each(function () {
			$(this).removeData('magnificPopup');
			$(this).magnificPopup({
				delegate: 'a',
				gallery: {
					enabled: true
				},
				type: 'image' // this is default type
			});
		});
	};

	function copyPostLink() {
		$(document).on('click', '.wilcity-copy-link', function () {
			window.prompt($(this).data('desc'), $(this).data('shortlink'));
		});
	}

	function countShared() {
		$('[data-toggle-content="share"]').on('click', '.wilcity-social-sharing', function () {
			var postID = $(this).closest('.wilcity-social-sharing-wrapper').data('postid');
			$.ajax({
				type: 'POST',
				url: WILOKE_GLOBAL.ajaxurl,
				data: {
					action: 'wilcity_count_shares',
					postID: postID
				},
				success: (function (response){
					if ( typeof response.data.countShared !== 'undefined' ){
						$('.wilcity-count-shared-'+postID).html(response.data.countShared + ' ' + response.data.text);
					}
				})
			});
		});
	}

	$(document).ready(function () {
		$('.wilcity-magnific-wrapper').each(function () {
			$(this).wilcityInitMagnific();
		});
		copyPostLink();
		countShared();
	});

})(jQuery);

}());
