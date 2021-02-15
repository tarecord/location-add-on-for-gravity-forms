(function($) {

	$('body').on( 'submit', '.lagf-scan-form', function(e){
		e.preventDefault();

		var form = $('.lagf-scan-form');

		form.append( '<span class="spinner is-active"></span><div class="lagf-progress"><div></div></div>' );
		form.find('#submit').prop('disabled', true);

		// start the process
		process_step( 1, self );
	});

	function process_step( step, self ) {

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				nonce: lagf_scan_obj.nonce,
				action: 'lagf_scan_for_forms',
				step: step,
			},
			dataType: "json",
			success: function( response ) {
				if( 'done' == response.step ) {

					var form = $('.lagf-scan-form');

					form.find('.spinner').remove();
					form.find('.lagf-scan-progress').remove();

					window.location = response.url;

				} else {

					$('.lagf-scan-progress div').animate({
						width: response.percentage + '%',
					}, 50, function() {
						// Animation complete.
					});
					process_step( parseInt( response.step ), self );
				}

			}
		}).fail(function (response) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});

	}

})(jQuery);
