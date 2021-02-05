(function($) {

	$('body').on( 'submit', '.lagf-scan-form', function(e){
		e.preventDefault();

		var data = $(this).serialize();

		$('.lagf-scan-form').append( '<span class="spinner is-active"></span><div class="lagf-progress"><div></div></div>' );

		// start the process
		process_step( 1, data, self );
	});

	function process_step( step, data, self ) {

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				form: data,
				action: 'lagf_scan',
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
					process_step( parseInt( response.step ), data, self );
				}

			}
		}).fail(function (response) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});

	}

})(jQuery);
