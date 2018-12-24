(function($) {
    'use strict';
     var owlCarousel = function(ID, end) {
        $('.notWorking').owlCarousel({
            loop:true,
            margin: 10,
            nav: true,
            autoplay:true,
            autoplayTimeout:10000,
            URLhashListener:true,
            autoplayHoverPause:true,
            startPosition: 'URLHash',
            responsive: {
                0: {items: 1 }, 
                600: {items: 1 }, 
                1000: {items: 2 }
            }
        })
    };
    //  Counter
    var timeCounter = function(ID, end) {
       var time = new Date(end);
       $(ID).timeTo({
           timeTo: new Date(time),
           displayDays: 2,
       }, function(){ 
           removeCurrentItem();
       });
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
    };
    var removeCurrentItem = function() {
        $('.endTime').each(function(event) {
            var ID  = '#'+ $(this).prop('id');
            var end = $(this).text();
            if (new Date() >= new Date(end)) {
                $(ID).parents('.teamQuestionContainer').remove();
            }
        });
    };
    var progress = function(progress=0) {
        return '<progress max="100" value="'+ progress +'"></progress>';
    }
    var loader = function() {
        var html = '';
        html += '<div class="text-center loaderText"> Loading please wait </div>';
        html += '<div class="spinner">';
          html += '<div class="bounce1"></div>';
          html += '<div class="bounce2"></div>';
          html += '<div class="bounce3"></div>';
        html += '</div>';
        return html;
    }
    var loadAnswers = function(ID, ditems) {
        var answersWrapper = $('#answersWrapper_'+ ID);
        // PREPARE AJAX POST DATA
        var ajaxData = {};
        ajaxData['security'] = object.ajax_nonce;
        ajaxData['action'] = 'load_answers';
        ajaxData['ID'] = ID;
        ajaxData['ditems'] = ditems;
        $.ajax({
            type: 'POST',
            url: object.ajaxurl,
            cache: false,
            data: ajaxData,
            beforeSend: function() { answersWrapper.html(loader()); },
            success: function(response, status, xhr) {
                if (response != null) {
                    setTimeout(function() {
                        answersWrapper.html(response);
                        owlCarousel();
                    }, 500);
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }
    var cofirmBox = function(warnings, teamID) {
        var modal = $("<div>").attr("class", "modalWrapper confirm-modal");
        var footer = "<footer><button type=\"button\" class=\"confirmed fusion-button button-default button-small btn-green\" team="+ teamID +">SubmitSubmit</button> <button data-iziModal-close class=\"fusion-button button-default button-small\">Cancel</button></footer>";
        modal.html("<div class=\"iziModal-header\" style=\"background: rgb(136, 160, 185); padding-right: 78px;\"><i class=\"iziModal-header-icon icon-home\"></i><h2 class=\"iziModal-header-title\">Welcome to the iziModal</h2><p class=\"iziModal-header-subtitle\">Elegant, responsive, flexible and lightweight modal plugin with jQuery.</p><div class=\"iziModal-header-buttons\"><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-close\" data-izimodal-close=\"\"></a><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-fullscreen\" data-izimodal-fullscreen=\"\"></a></div></div>");
        modal.iziModal({
            title: "Prediction confirmation",
            // subtitle: 'SUB',
            autoOpen: 1,
            onOpening: function(modal) {
                modal.startLoading();
                $(".modalWrapper .iziModal-content").html('<div class="content">'+ warnings +'</div>'+ footer);
                modal.stopLoading();
            }
        });
    }
    var saveQAns = function(teamID) {
        // PREPARE AJAX POST DATA
        var ajaxData = {};
        ajaxData['security'] = object.ajax_nonce;
        ajaxData['eventID'] = $('#eventID').val();
        ajaxData['userID'] = $('#userID').val();
        ajaxData['action'] = 'save_answer';
        // GIVEN ANSWERS ARRAY
        var radioValue = {};
        var radioValueCount = false;
        var Question = $('#'+teamID);
        var radioTitle = Question.find('.title').text();
        var radioName = Question.prop('id');
        var radioVal = $("input[name='"+ radioName +"']:checked").val();
        radioValue[radioName] = radioVal;
        if (radioVal) { radioValueCount = true; }

        if (radioValue) {
            ajaxData['answer'] = radioValue;
            jQuery.ajax({
                type: 'POST',
                url: object.ajaxurl,
                cache: false,
                data: ajaxData,
                success: function(response, status, xhr) {
                    // console.log(response == 1);
                    // $('.modalWrapper').html('');
                    $('.modalWrapper').iziModal('destroy');
                    if (response == 1) {
                        var teamWrapper = Question.parents('.teamQuestionContainer').attr('id');
                        $('#'+ teamID).remove();
                        removeEmptyQuestionWrapper(teamWrapper);
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
        return false;
    }
    var loadTournament = function(tournamentID, userID) {
        var tournamentWrapper = $('.tournamentWrapper');
        // PREPARE AJAX POST DATA
        var ajaxData = {};
        ajaxData['security'] = object.ajax_nonce;
        ajaxData['action'] = 'load_tournament';
        ajaxData['tournamentID'] = tournamentID;
        ajaxData['userID'] = userID;
        $.ajax({
            type: 'POST',
            url: object.ajaxurl,
            cache: false,
            data: ajaxData,
            beforeSend: function() { tournamentWrapper.html(loader()); },
            success: function(response, status, xhr) {
                // alert(response);
                if (response != null) {
                    tournamentWrapper.html(response);
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }
    var removeEmptyQuestionWrapper = function(teamWrapperID) {
        var teamWrapper = $('#'+ teamWrapperID);
        var isEmptyWrapper = teamWrapper.find('.predictionContainer').length;
        if (!isEmptyWrapper) { teamWrapper.remove(); }
    }
    $(document).ready(function() {
        $(document).on('change', '#tournaments', function(event) {
            event.preventDefault();
            var tournamentID = $(this).val();
            var userID = $(this).attr('user');
            loadTournament(tournamentID, userID);
        });
        $('.endTime').each(function(event) {
            var ID = '#'+ $(this).prop('id');
            var end = $(this).text();
            timeCounter(ID, end);
        });
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
        // USER LOGIN
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
        //ProgressBar
        $(".progress-bar").loading(); 
        // SAVE PREDICTIONS
        $(document).on('click', '.saveQAns', function(event) {
            event.preventDefault();
            var button = $(this);
            var teamID = $(this).parents('.predictionContainer').attr('id');
            // PREPARE AJAX POST DATA
            var ajaxData = {};
            ajaxData['security'] = object.ajax_nonce;
            ajaxData['eventID'] = $('#eventID').val();
            ajaxData['userID'] = $('#userID').val();
            ajaxData['action'] = 'save_answers';
            // GIVEN ANSWERS ARRAY
            var warnings = '';
            var radioValues = {};
            var radioValueCount = 0;
            warnings = '<h3 class="wTitle">'+ $(this).parents('.teamQuestionContainer').find('.teamName strong').text() +'</h3>';
            var Questions = $(this).parents('.predictionContainer');
            var radioTitle = Questions.find('.title').text();
            var radioName = Questions.prop('id');
            var radioVal = $("input[name='"+ radioName +"']:checked").val();
            radioValues[radioName] = radioVal;
            if (radioVal) { 
                warnings += '<p class="given"><span class="title">'+ radioTitle +' : </span><span class="ans">'+ radioVal +'</span></p>';
                radioValueCount += 1; 
            } else {
                warnings += '<p class="empty"><span class="title">'+ radioTitle +' : </span><span class="ans">unknown </span></p>';
            }
            if (!radioValueCount) alert('You didn\'t select any answer.');
            else cofirmBox(warnings, teamID);
        })
        $(document).on('click', '.confirmed', function(event) {
            event.preventDefault();
            var teamID = $(this).attr('team');
            saveQAns(teamID);
        });
        // ANSWERS
        $('.answersWrapper').each(function(index) {
            var eventID = $(this).attr('event');
            var ditems = $(this).attr('ditems');
            loadAnswers(eventID, ditems);
        })
        $(document).on('click', '.refreshButton', function(event) {
            var eventID = $(this).parents('.answersWrapper').attr('event');
            var ditems = $(this).parents('.answersWrapper').attr('ditems');
            if (!eventID) alert('Not a valid event');
            else loadAnswers(eventID, ditems);
        });
    });
})(jQuery);