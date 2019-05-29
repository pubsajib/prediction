(function ($) {
  	'use strict';
  	var deleteAnswers = function(event, user, answerid, button) {
        // PREPARE AJAX POST DATA
        var ajaxData = {};
        ajaxData['security'] = object.ajax_nonce;
        ajaxData['action'] = 'delete_answers';
        ajaxData['event'] = event;
        ajaxData['user'] = user;
        ajaxData['answerid'] = answerid;
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
    var runCron = function (button) {
        var ajaxData = {};
        ajaxData['security'] = object.ajax_nonce;
        ajaxData['action'] = 'run_cron';
        ajaxData['type'] = button.attr('cron');
        $.ajax({
            type: 'POST',
            url: object.ajaxurl,
            cache: false,
            data: ajaxData,
            beforeSend: function() { button.attr('disabled', true); },
            success: function(response, status, xhr) {
                console.log(response);
                button.attr('disabled', false);
                if (response != '0') { 
                    jQuery('.msgWrapper').html('<p style="font-weight:bold;color:green;">Successfully updated.</p>');
                    setTimeout(function() {
                        location.reload();
                    }, 3000)
                } else {
                    jQuery('.msgWrapper').html('<p style="font-weight:bold;color:red;">Failed. Please try again.</p>');
                }
            },
            error: function(error) {
                button.attr('disabled', false);
                console.log(error);
            }
        });
    }
    var saveCronOptions = (button) => {
        var tournaments = [];
        $('.tournament:checked').each(function() {
            tournaments.push($(this).val());
        })
        var ajaxData = {};
        ajaxData['security'] = object.ajax_nonce;
        ajaxData['action'] = 'cron_options';
        ajaxData['tournaments'] = tournaments;
        $.ajax({
            type: 'POST',
            url: object.ajaxurl,
            cache: false,
            data: ajaxData,
            beforeSend: function() { button.attr('disabled', true); },
            success: function(response, status, xhr) {
                button.attr('disabled', false);
                if (response != '0') { 
                    jQuery('.msgWrapper').html('<p style="font-weight:bold;color:green;">Successfully saved.</p>');
                    setTimeout(function() {
                        location.reload();
                    }, 3000)
                } else {
                    jQuery('.msgWrapper').html('<p style="font-weight:bold;color:red;">Failed. Please try again.</p>');
                }
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
		var answerid = button.attr('answerid');
        // alert(event+' == '+ user+' == '+ answerid); return false; 
        if (confirm('Are you sure ?')) deleteAnswers(event, user, answerid, button);
	})
    $( 'select[name="role"]' ).closest( '.answer' ).remove();
    $(document).on('click', '.cronBtn', function(e) {
        e.preventDefault();
        var button = $(this);
        runCron(button);
    })
    $(document).on('submit', '.tournamentList', function(e) {
        e.preventDefault();
        var button = $('.tournamentListBtn');
        saveCronOptions(button);
    })
})(jQuery);