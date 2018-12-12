(function ($) {
  	'use strict';
  	var deleteAnswers = function(event, user, button) {
        // PREPARE AJAX POST DATA
        var ajaxData = {};
        ajaxData['security'] = object.ajax_nonce;
        ajaxData['action'] = 'delete_answers';
        ajaxData['event'] = event;
        ajaxData['user'] = user;
        $.ajax({
            type: 'POST',
            url: object.ajaxurl,
            cache: false,
            data: ajaxData,
            beforeSend: function() { button.attr('disabled', true); },
            success: function(response, status, xhr) {
            	button.attr('disabled', false);
            	if (response) { location.reload(); }
            },
            error: function(error) {
            	button.attr('disabled', false);
                console.log(error);
            }
        });
    }
	$(document).on('click', '.removeAns', function(e) {
		e.preventDefault();
		var button = $(this);
		var event = button.attr('event');
		var user = button.attr('user');
        if (confirm('Are you sure ?')) deleteAnswers(event, user, button);
	})
})(jQuery);
