(function($) {
    'use strict';
    var owlCarousel = function() {
        $('.owlCarousel_headerNotification').owlCarousel({
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
                1000: {items: 4 }
            }
        })
    };
    //  Counter
    var timeCounter = function(ID, end, isToss=false) {
       var time = new Date(end);
       $(ID).timeTo({
           timeTo: new Date(time),
           displayDays: 2,
       }, function(){ 
            // $(ID).parent('.predictionContainer').find('.saveQAns').attr('disabled', true);
            removeCurrentItem(ID);
       });
    };
    var removeCurrentItem = function(ID) {
        var teamContainer = null; 
        var parentItem = null; 
        var container = null;
        if ($(ID).is('.endToss')) container = '.predictionContainer';
        else container = '.teamQuestionContainer';
        teamContainer = $(ID).parents('.teamQuestionContainer');
        parentItem = $(ID).parents(container);
        parentItem.remove();
        removeEmptyParent(teamContainer);
    };
    var removeEmptyParent = function(parentItem) {
        var items = parentItem.find('.predictionContainer').length;
        if (!items) { parentItem.remove(); }
    }
    var removeAllEndedItem = function(isToss) {
        if (isToss) {
            var container = '.teamQuestionContainer';
            var item = '.endTime';
        } else {
            var item = '.endToss';
            var container = '.predictionContainer';
        }
        $(item).each(function(event) {
            var ID  = '#'+ $(this).prop('id');
            var end = $(this).text();
            if (new Date() >= new Date(end)) {
                $(ID).parents(container).remove();
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
    var loadEventAnswer = function(events, ditems) {
        var answersWrapper = $('#answersWrapper_'+ events);
        // PREPARE AJAX POST DATA
        var ajaxData = {};
        ajaxData['security'] = object.ajax_nonce;
        ajaxData['action'] = 'load_events_answers';
        ajaxData['events'] = events;
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
                        favoriteTeamAnimation();
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
        var footer = "<footer><button type=\"button\" class=\"confirmed fusion-button button-default button-small btn-green\" team="+ teamID +">Submit</button> <button data-iziModal-close class=\"fusion-button button-default button-small\">Cancel</button></footer>";
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
    var saveQAns = function(questionID) {
        if (questionID != $('#QID').val()) location.reload();
        else {
            // PREPARE AJAX POST DATA
            var ajaxData = {};
            ajaxData['security']    = object.ajax_nonce;
            ajaxData['eventID']     = $('#eventID').val();
            ajaxData['userID']      = $('#userID').val();
            ajaxData['teamID']      = $('#TID').val();
            ajaxData['qid']         = questionID;
            ajaxData['qans']        = $('#QAns').val();
            ajaxData['action']      = 'save_answer';
            // GIVEN ANSWERS ARRAY
            if (ajaxData['qans']) {
                jQuery.ajax({
                    type: 'POST',
                    url: object.ajaxurl,
                    cache: false,
                    data: ajaxData,
                    success: function(response, status, xhr) {
                        // console.log(response);
                        if (response == 1) {
                            $('.modalWrapper').iziModal('destroy');
                            var questionNODE = $('#'+ questionID);
                            var teamWrapper = questionNODE.parents('.teamQuestionContainer');
                            questionNODE.remove();
                            removeEmptyParent(teamWrapper);
                        }
                        if (response == 3) {
                            var questionNODE = $('#'+ questionID);
                            var teamWrapper = questionNODE.parents('.teamQuestionContainer');
                            questionNODE.remove();
                            removeEmptyParent(teamWrapper);
                            $('.modalWrapper footer .confirmed').attr('disabled', true).removeClass('btn-green').addClass('btn-gray');
                            $('.modalWrapper .iziModal-content .content').html('<p style="text-align:center;font-weight:bold;color:red;">Prediction time is over.</p>');
                            console.log('Times up');
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            }
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
    var favoriteTeamAnimation = function() {
        jQuery('.skillbar').each(function(){
            jQuery(this).find('.skillbar-bar').animate({
                width:jQuery(this).attr('data-percent')
            },5000);
        });
    }
    $(document).ready(function() {
        owlCarousel();
        // Ranking Slider 		
        $('.owl-rank').owlCarousel({
            loop:true,
            margin:10,
            nav:true,
            responsive:{
                0:{items:1 },
                1000:{items:2 }
            }
        })
        // $('#team_test_1_toss_winner___end').parents('.autoRemoveAble').first().css('border', '1px solid red');
        $(document).on('change', '#tournaments', function(event) {
            event.preventDefault();
            var tournamentID = $(this).val();
            var userID = $(this).attr('user');
            loadTournament(tournamentID, userID);
        });
        // TIME COUNTER
        $('.endTime').each(function(event) {
            var ID = '#'+ $(this).prop('id');
            var end = $(this).text();
            timeCounter(ID, end);
        });
        // TIME COUNTER (TOSS)
        $('.endToss').each(function(event) {
            var ID = '#'+ $(this).prop('id');
            var end = $(this).text();
            timeCounter(ID, end, true);
        });
		/* Predictor Page Modal */
		$("#winLosePop").iziModal({
			width: 900
		});
		
		/* Alert */
		$(".closebtn").click(function () {
			$(".notice").fadeOut("slow", function() {
				hide();
			})
		})
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
        
        //Full Page modal
        // $('.ibs-full-modal-container').fullModal({
        //     closeWhenClickBackdrop: true,
        //     duration: 500,
        //     beforeOpen: function (callback) {
        //       callback();
        //     },
        //     afterOpen: function () {
        //       console.log('afterOpen was invoked');
        //     },
        //     beforeClose: function (callback) {
        //       setTimeout(function(){
        //         callback();
        //       },2000);

        //     },
        //     afterClose: function () {
        //       console.log('afterClose was invoke');
        //     }
        // });

      $('#openBtn').on('click', function () {
        $('#modal1').fullModal('open');
      });

      $('#closeBtn').on('click', function () {
        $('#modal1').fullModal('close');
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
        //Tab 		
        $('#protab').tabslet();
        $('#TopPredictor').tabslet();
        $('#Roadtotop').tabslet();
        $('#headerNotification').tabslet();
        //ProgressBar
        $(".progress-bar").loading(); 
        // SAVE PREDICTIONS
        $(document).on('click', '.saveQAns', function(event) {
            event.preventDefault();
            var button = $(this);
            var teamID = button.parents('.teamQuestionContainer').attr('id');
            var Question = button.parents('.predictionContainer');
            var questionID = button.parents('.predictionContainer').attr('id');

            // GIVEN ANSWERS ARRAY
            var warnings = '';
            var radioValueCount = 0;
            warnings = '<h3 class="wTitle">'+ button.parents('.teamQuestionContainer').find('.teamName strong').text() +'</h3>';
            var radioTitle = Question.find('.title').text();

            var radioVal = $("input[name='"+ questionID +"']:checked").val();
            if (radioVal) { 
                $('#TID').val(teamID);
                $('#QID').val(questionID);
                $('#QAns').val(radioVal);
                warnings += '<p class="given"><span class="title">'+ radioTitle +' : </span><span class="ans">'+ radioVal +'</span></p>';
                cofirmBox(warnings, questionID);
            } else {
                $('#TID').val('');
                $('#QID').val('');
                $('#QAns').val('');
                alert('You didn\'t select any answer.');
            }
        })
        $(document).on('click', '.confirmed', function(event) {
            event.preventDefault();
            var questionID = $(this).attr('team');
            saveQAns(questionID);
        });
        // ANSWERS
        $('.answersWrapper').each(function(index) {
            var eventID = $(this).attr('event');
            var ditems = $(this).attr('ditems');
            loadAnswers(eventID, ditems);
        })
        $('.eventsAnswersWrapper').each(function(index) {
            var events = $(this).attr('event');
            var ditems = $(this).attr('ditems');
            loadEventAnswer(events, ditems);
        })
        $(document).on('click', '.refreshButton', function(event) {
            var eventID = $(this).parents('.answersWrapper').attr('event');
            var ditems = $(this).parents('.answersWrapper').attr('ditems');
            if (!eventID) alert('Not a valid event');
            else loadAnswers(eventID, ditems);
        });
        $(document).on('click', '.eventsRefreshButton', function(event) {
            var eventID = $(this).parents('.eventsAnswersWrapper').attr('event');
            var ditems = $(this).parents('.eventsAnswersWrapper').attr('ditems');
            if (!eventID) alert('Not a valid event');
            else loadEventAnswer(eventID, ditems);
        });
    });
})(jQuery);
// new CBPFWTabs( document.getElementById( 'tabs' ) );