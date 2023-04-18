jQuery(document).ready(function(){
	jQuery('#order_feedback_form').submit(function(e) {
		e.preventDefault();
		var formData = $(this).serialize();
		jQuery.ajax({
			url: jQuery(this).attr('action'), 
			type: 'POST',
			data: formData,
			async: false,
			success: function(result) {
				jQuery(".feedback_message").html(result);
				jQuery(".feedback_form").hide();
			},
			error: function() {
				jQuery(".feedback_message").html('There is some problem, Please try again!');
			}
		});
	});
});	