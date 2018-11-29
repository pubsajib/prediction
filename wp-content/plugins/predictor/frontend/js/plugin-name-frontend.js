(function($) {
    'use strict';

    /**
     * Prints Hello world! to the console
     */
    var helloWorld = function() {
        // console.log('Hello world!');
    };
    var pNotify = function() {
        var fx = "wobble", //wobble shake
            $modal = $(this).closest('.iziModal');

        if (!$modal.hasClass(fx)) {
            $modal.addClass(fx);
            setTimeout(function() {
                $modal.removeClass(fx);
            }, 1500);
        }
    }

    $(document).ready(function() {
        helloWorld();
        /* Instantiating iziModal */
        $("#modal-custom").iziModal({
            overlayClose: false,
            overlayColor: 'rgba(0, 0, 0, 0.6)'
        });

        $(document).on('click', '.custom-login', function(event) {
            event.preventDefault();
            $('#modal-custom').iziModal('open');
        });

        /* JS inside the modal */

        $("#modal-custom").on('click', 'header a', function(event) {
            event.preventDefault();
            var index = $(this).index();
            $(this).addClass('active').siblings('a').removeClass('active');
            $(this).parents("div").find("section").eq(index).removeClass('hide').siblings('section').addClass('hide');

            if ($(this).index() === 0) {
                $("#modal-custom .iziModal-content .icon-close").css('background', '#ddd');
            } else {
                $("#modal-custom .iziModal-content .icon-close").attr('style', '');
            }
        });
        $('.owl-carousel').owlCarousel({
            loop: true,
            margin: 10,
            nav: true,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 3
                },
                1000: {
                    items: 5
                }
            }
        });
        $("#modal-custom").on('click', '.submit', function(event) {
            event.preventDefault();
            var email = jQuery('#p_user').val()
            var pass = jQuery('#P_pass').val()
            var remember = jQuery('#remember').is(':checked')

            if (email != '' && pass != '') {
                jQuery.ajax({
                    type: 'POST',
                    url: object.ajaxurl + '?action=user_login',
                    cache: false,
                    data: {
                        email: email,
                        pass: pass,
                        remember: remember,
                        security: object.ajax_nonce
                    },
                    success: function(response, status, xhr) {
                        if (response == true) {
                            window.location.reload()
                        } else {
                            $('.pLoginMessage').html('<p> Invalid credentials </p>');
                            var fx = "wobble", //wobble shake
                                $modal = $(this).closest('.iziModal');

                            if (!$modal.hasClass(fx)) {
                                $modal.addClass(fx);
                                setTimeout(function() {
                                    $modal.removeClass(fx);
                                }, 1500);
                            }
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            } else {
                var fx = "wobble", //wobble shake
                    $modal = $(this).closest('.iziModal');

                if (!$modal.hasClass(fx)) {
                    $modal.addClass(fx);
                    setTimeout(function() {
                        $modal.removeClass(fx);
                    }, 1500);
                }
            }
        });
        $(document).on('click', '.saveQAns', function(event) {
            event.preventDefault();
            var button = $(this);
            var teamID = $(this).parents('.teamQuestionContainer').addClass('box').attr('id');
            // PREPARE AJAX POST DATA
            var ajaxData = {};
            ajaxData['security'] = object.ajax_nonce;
            ajaxData['eventID'] = $('#eventID').val();
            ajaxData['userID'] = $('#userID').val();
            ajaxData['action'] = 'save_answers';
            // GIVEN ANSWERS ARRAY
            var radioValue = {};
            var Questions = $(this).parents('.teamQuestionContainer').find('.predictionContainer');
            Questions.each(function(event) {
                var radioName = $(this).prop('id');
                radioValue[radioName] = $("input[name='"+ radioName +"']:checked").val();
            });
            if (radioValue && teamID) {
                radioValue[teamID] = 1;
                ajaxData['answers'] = radioValue;
            }

            if (radioValue) {
                jQuery.ajax({
                    type: 'POST',
                    url: object.ajaxurl,
                    cache: false,
                    data: ajaxData,
                    success: function(response, status, xhr) {
                        // console.log(response == 1);
                        // if (response == 1) $('#'+ teamID).remove();
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            } else {
                alert('You didn\'t select any value.');
            }
            return false;
        });
    });
})(jQuery);