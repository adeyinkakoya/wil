(function () {
'use strict';

(function ($) {
	'use strict';

	function printLikedClassToLikedItem(){
		if( WILOKE_GLOBAL.isUserLoggedIn === 'no' ){
			return false;
		}

		$.ajax({
			type: 'POST',
			url: WILOKE_GLOBAL.ajaxurl,
			data: {
				action: 'wilcity_fetch_user_liked'
			},
			success: function (response) {
				if ( response.success ){
					var aItems = Object.values(response.data);
					var activeClass = '';
					var deactiveClass = '';

					for ( var i = 0; i < aItems.length; i++ ){
						var $target = $('.wilcity-js-favorite[data-post-id="'+aItems[i]+'"]');
						if ( !$target.length ){
							continue;
						}

						var $icon = $target.find('.la');

						if ( $target.hasClass('is-event') ){
							activeClass = 'la-star';
							deactiveClass = 'la-star-o';
						}else{
							activeClass = 'la-heart';
							deactiveClass = 'la-heart-o';
						}

						$icon.removeClass(deactiveClass);
						$icon.addClass(activeClass);
						$icon.addClass('color-primary');
					}
				}
			}
		});
	}

	$.fn.wilcityFavoriteStatistic = function () {
		var $this = $(this);
		var postID = $this.data('postId'),
			activeClass = '',
			$icon = $this.find('.la'),
			deactiveClass = '';

		if ( $this.hasClass('is-event') ){
			activeClass = 'la-star';
			deactiveClass = 'la-star-o';
		}else{
			activeClass = 'la-heart';
			deactiveClass = 'la-heart-o';
		}
		postID = postID.toString();

		$this.on('click', function (event) {
			$this.prop('disabled', true);
			event.preventDefault();

			if( WILOKE_GLOBAL.isUserLoggedIn === 'no' ){
				jQuery('body').trigger('onOpenLoginRegisterPopup');
				return false;
			}

			if ( $icon.hasClass(activeClass) ){
				$icon.removeClass(activeClass);
				$icon.addClass(deactiveClass);
				$icon.removeClass('color-primary');
			}else{
				$icon.removeClass(deactiveClass);
				$icon.addClass(activeClass);
				$icon.addClass('color-primary');
			}

			jQuery.ajax({
				type: 'POST',
				url: WILOKE_GLOBAL.ajaxurl,
				data: {
					action: 'wilcity_favorite_statistics',
					postID: postID
				}
			});
		});
	};

	$(document).ready(function () {
		if ( typeof WILCITY_GLOBAL !== 'undefined' ){
			$('.wilcity-js-favorite').each(function () {
				$(this).wilcityFavoriteStatistic();
			});
			printLikedClassToLikedItem();
		}
	});
})(jQuery);

}());
