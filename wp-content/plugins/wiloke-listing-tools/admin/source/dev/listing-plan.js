(function ($) {
	'use strict';

	$(document).ready(function () {
		let $btn = $('#wilcity-add-plan-to-directory-directly'), $belongsTo = $('#wilcity-plan-belongs-to');
		$btn.on('click', (event=>{
			event.preventDefault();
			let changeTo = $('#wilcity-change-plan-belongs-to').val();

			$btn.html('Executing ... ');

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'wilcity_set_plan_directly',
					postID: $('#post_ID').val(),
					changeBelongsTo: changeTo,
					belongsTo: $belongsTo.val()
				},
				success: response => {
					if ( response.success ){
						$belongsTo.val(changeTo+'_plans');
					}
					$btn.html('Execute');
					alert(response.data.msg);
				}
			})
		}))
	});

})(jQuery);