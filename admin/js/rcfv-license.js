(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	jQuery(document).ready(function($){
		$('#manage_license').on('submit',function(e){
			e.preventDefault();
			var button = $('#raopress-license-submit');
        	button.attr("disabled",true);
			var form_data = $(this).serializeArray();
			console.log(form_data);
			$('.rcfv-notice').fadeOut();
			$('.rcfv-notice').removeClass("notice-error notice-success");
			
			$.ajax({
				url: licenseConfig.ajaxurl,
				type: "POST",
				dataType: "JSON",
				data: form_data,
				success: function( ret_data ) {
					button.attr("disabled",false);
					console.log(ret_data);
					if(ret_data.success)
					{

						
						$('.rcfv-notice p').html(ret_data.data);
						$('.rcfv-notice').addClass("notice-success");
						$('.rcfv-notice').fadeIn();
						var current_action = $('#current_action').val();
						if( current_action === "activate") {
						button.val("Deactivate");
						$('#current_action').val("deactivate");
						} else {
							button.val("Activate");
							$('#current_action').val("activate");
						}
					} else {
						
						$('.rcfv-notice p').html(ret_data.data);
						$('.rcfv-notice').addClass("notice-error");
						$('.rcfv-notice').fadeIn();
						$('#current_action').val("activate");
					}
				}
			});

		});
	});

})( jQuery );
