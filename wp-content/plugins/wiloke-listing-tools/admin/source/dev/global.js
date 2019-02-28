/**
 * Created by pirates on 7/4/18.
 */
(function ($) {
	'use strict';

	$(document).ready(function () {
		$('#wilcity_business_hourMode').on('change', function () {
			let val = $(this).val();
			if ( val == 'open_for_selected_hours' ){
				$('.wilcity-bh-settings').removeClass('hidden');
			}else{
				$('.wilcity-bh-settings').addClass('hidden');
			}
		});

		$('#frequency').on('change', function () {
			let val = $(this).val();
			if ( val == 'weekly' ){
				$('.cmb-row.cmb2-id-specifyDays').show();
			}else{
				$('.cmb-row.cmb2-id-specifyDays').hide();
			}
		}).trigger('change');
	});

})(jQuery);