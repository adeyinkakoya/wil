(function( $ ) {

	$(window).load(function () {

		wp.customize.panel( 'mailtpl' ).focus();
		$('.mailtpl_range').on('input',function(){
			var val = $(this).val();
			$(this).parent().find('.font_value').html(val);
			$(this).val(val);
		});
		$('#mailtpl-send_mail').on('click', function(e){
			e.preventDefault();
			$('#mailtpl-spinner').fadeIn();

			$.ajax({
				type: 'POST',
				url     : ajaxurl,
				data    : { action: 'wiloke_mailtpl_send_email', target: $('select[data-customize-setting-link="mailtpl_opts[send_mail_target]"]').val() }
			}).done(function(data) {
				$('#mailtpl-spinner').fadeOut();
				$('#mailtpl-success').fadeIn().delay(3000).fadeOut();
			});
		});
		if( $('#customize-control-mailtpl_template select').val() != 'boxed' ) {
			$('#customize-control-mailtpl_body_size').hide();
		}

		$('#customize-control-mailtpl_template select').on('change', function () {
			if( $(this).val() == 'boxed' ) {
				$('#customize-control-mailtpl_body_size').fadeIn();
			} else {
				$('#customize-control-mailtpl_body_size').fadeOut();
			}
		});
	});

})( jQuery );
