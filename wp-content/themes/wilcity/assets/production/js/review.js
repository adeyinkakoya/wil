(function () {
'use strict';

(function ($) {
	function toggleLiked(postID, isLiked) {
		if (typeof Storage === 'undefined') {
			return false;
		}
		var myLiked = localStorage.getItem('wilcity_my_liked');
		var aParseLiked = !myLiked ? [] : myLiked.split(',');
		var findIt = aParseLiked.indexOf(postID);

		if (!isLiked) {
			if ( findIt !== -1 ){
				aParseLiked.splice(findIt, 1);
				if ( aParseLiked.length ){
					localStorage.setItem('wilcity_my_liked', aParseLiked.join(','));
				}else{
					localStorage.removeItem('wilcity_my_liked');
				}
			}
		} else {
			aParseLiked.push(postID);
			localStorage.setItem('wilcity_my_liked', aParseLiked.join(','));
		}
	}

	function myLiked() {
		var myLiked = localStorage.getItem('wilcity_my_liked');
		if ( myLiked ){
			var aParseLiked = myLiked.split(',');
			for ( var i in aParseLiked ){
				var $target = $('.wilcity-i-like-it[data-id="'+aParseLiked[i]+'"]');
				$target.addClass('liked');
				$target.html('<i class="la la-thumbs-up"></i> '+WILCITY_I18.like);
			}
		}
	}

	function likeIt() {
		var xhr = null;
		$('.wilcity-i-like-it').on('click', function (event) {
			event.preventDefault();
			var $target = $(event.currentTarget), reviewID = $target.data('id'), $countLikedPrint = $('.wilcity-count-liked-'+reviewID), countLiked = parseInt($countLikedPrint.data('countLiked'), 10), countLikedText = $countLikedPrint.html();

			if ( xhr !== null && xhr.status !== 200 ){
				xhr.abort();
			}

			if ( $target.hasClass('liked') ){
				$target.html('<i class="la la-thumbs-up"></i> '+WILCITY_I18.like);
				countLiked -= 1;
				toggleLiked(reviewID, false);
			}else{
				countLiked += 1;
				$target.html('<i class="la la-thumbs-up"></i> '+WILCITY_I18.liked);
				toggleLiked(reviewID, true);
			}

			$target.toggleClass('liked');
			$target.toggleClass('color-primary');

			$countLikedPrint.html(countLikedText.replace(new RegExp("[0-9]", "g"), countLiked));
			$countLikedPrint.data('countLiked', countLiked);

			xhr = $.ajax({
				type: 'POST',
				url: WILOKE_GLOBAL.ajaxurl,
				data: {
					action: 'wilcity_review_is_update_like',
					reviewID: reviewID
				}
			});
		});
	}

	function focusWriteDiscussion() {
		$('.wilcity-add-new-discussion').on('click', function(event){
			event.preventDefault();
			$(this).closest('.comment-review_module__-Z5tr').find('.wilcity-write-a-new-comment-box').focus();
		});
	}

	$(document).ready(function () {
		myLiked();
		likeIt();
		focusWriteDiscussion();
	});

})(jQuery);

}());
