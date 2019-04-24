(function($) {
    'use strict';
    var owlCarousel = function() {
        $('.owlCarousel_headerNotification').owlCarousel({
            loop:false,
            margin: 10,
            nav: true,
            autoplay:false,
			dots: true,
            autoplayTimeout:10000,
            URLhashListener:true,
            autoplayHoverPause:true,
            startPosition: 'URLHash',
            responsive: {
                0: {items: 2 }, 
                600: {items: 2 }, 
                1000: {items: 6 }
            }
        })
		$('.avatarToss, .avatarMatch').owlCarousel({
            loop:false,
            margin: 10,
            nav: true,
            autoplay:false,
			dots: true,
            autoplayTimeout:15000,
            URLhashListener:true,
            autoplayHoverPause:true,
            startPosition: 'URLHash',
            responsive: {
                0: {items: 3 }, 
                600: {items: 3 }, 
                1000: {items: 7 }
            }
        })
		$('.favouriteTeam').owlCarousel({
            loop:false,
            margin: 10,
            nav: true,
            autoplay:false,
			dots: true,
            autoplayTimeout:15000,
            URLhashListener:true,
            autoplayHoverPause:true,
            startPosition: 'URLHash',
            responsive: {
                0: {items: 3 }, 
                600: {items: 3 }, 
                1000: {items: 5 }
            }
        })
		$('.eventSupperters').owlCarousel({
            loop:false,
            margin: 10,
            nav: true,
            autoplay:false,
			dots: true,
            autoplayTimeout:15000,
            URLhashListener:true,
            autoplayHoverPause:true,
            startPosition: 'URLHash',
            responsive: {
                0: {items: 5 }, 
                600: {items: 5 }, 
                1000: {items: 10 }
            }
        })
    }
    var timeCounter = function(ID, end, isToss=false) {
       var time = new Date(end);
       $(ID).timeTo({
           timeTo: new Date(time),
           displayDays: 2,
       }, function(){ 
            // $(ID).parent('.predictionContainer').find('.saveQAns').attr('disabled', true);
            removeCurrentItem(ID);
       });
    }
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
    }
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
    }
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
    var loadEventAnswer = function(events) {
        var answersWrapper = $('#answersWrapper_'+ events);
        // PREPARE AJAX POST DATA
        var ajaxData = {};
        ajaxData['security'] = object.ajax_nonce;
        ajaxData['action'] = 'load_events_answers';
        ajaxData['events'] = events;
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
                        favoriteTeamAnimation();
                        nestedTab(events, true);
                        owlCarousel();
                    }, 500);
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }
    var loadAnswers = function(ID, ditems, html, avatarslider) {
        var answersWrapper = $('#answersWrapper_'+ ID);
        // PREPARE AJAX POST DATA
        var ajaxData            = {};
        ajaxData['security']    = object.ajax_nonce;
        ajaxData['action']      = 'load_answers';
        ajaxData['ID']          = ID;
        ajaxData['ditems']      = ditems;
        ajaxData['html']        = html;
        ajaxData['avatarslider']= avatarslider;
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
                        favoriteTeamAnimation();
                        nestedTab(ID);
                        owlCarousel();
                    }, 500);
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }
    var nestedTab = function(event, multiple=false){
        let selectorIDS = '';
        if (!multiple) {
            selectorIDS += '#favouriteTeamName-'+ event +'1,#favouriteTeam'+ event +'11,#favouriteTeam'+ event +'12,';
            selectorIDS += '#favouriteTeamName-'+ event +'2,#favouriteTeam'+ event +'21,#favouriteTeam'+ event +'22,';
            selectorIDS += '#favouriteTeamName-'+ event +'3,#favouriteTeam'+ event +'31,#favouriteTeam'+ event +'32,';
            selectorIDS += '#favouriteTeamName-'+ event +'4,#favouriteTeam'+ event +'41,#favouriteTeam'+ event +'42,';
        } else {
            let events = event.split('_');
            let eLength = events.length;
            if (eLength > 1) {
                for (var i = eLength - 1; i >= 0; i--) {
                    selectorIDS += '#favouriteTeamName-'+ events[i] +'1,#favouriteTeam'+ events[i] +'11,#favouriteTeam'+ events[i] +'12,';
                    selectorIDS += '#favouriteTeamName-'+ events[i] +'2,#favouriteTeam'+ events[i] +'21,#favouriteTeam'+ events[i] +'22,';
                    selectorIDS += '#favouriteTeamName-'+ events[i] +'3,#favouriteTeam'+ events[i] +'31,#favouriteTeam'+ events[i] +'32,';
                    selectorIDS += '#favouriteTeamName-'+ events[i] +'4,#favouriteTeam'+ events[i] +'41,#favouriteTeam'+ events[i] +'42,';
                }
            }
        }
        $(selectorIDS.replace(/,+$/,'')).tabslet();
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
    var cofirmBoxModal = function(warnings, eventID, questionID, teamID, radioVal) {
        $('.confirm-modal').iziModal('destroy');
        var footer = "";
        var modal = $("<div>").attr("class", "modalWrapper3 confirm-modal");
        footer += "<footer>";
        footer += '<button type="button" class="modalConfirmed fusion-button button-default button-small btn-green" event='+ eventID +' team='+ teamID +' qid='+ questionID +' ans="'+ radioVal +'">Submit</button> ';
        footer += "<button data-iziModal-close class=\"fusion-button button-default button-small\">Cancel</button>";
        footer += "</footer>";
        modal.html("<div class=\"iziModal-header\" style=\"background: rgb(136, 160, 185); padding-right: 78px;\"><i class=\"iziModal-header-icon icon-home\"></i><h2 class=\"iziModal-header-title\">Welcome to the iziModal</h2><p class=\"iziModal-header-subtitle\">Elegant, responsive, flexible and lightweight modal plugin with jQuery.</p><div class=\"iziModal-header-buttons\"><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-close\" data-izimodal-close=\"\"></a><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-fullscreen\" data-izimodal-fullscreen=\"\"></a></div></div>");
        modal.iziModal({
            title: "Prediction confirmation",
            // subtitle: 'SUB',
            autoOpen: 1,
            onOpening: function(modal) {
                modal.startLoading();
                $(".modalWrapper3 .iziModal-content").html('<div class="content">'+ warnings +'</div>'+ footer);
                modal.stopLoading();
            }
        });
    }
    var saveModalQAns = function(eventID, teamID, questionID, answer) {
        if (!eventID || !teamID || !questionID || !answer) location.reload();
        else {
            // PREPARE AJAX POST DATA
            var ajaxData = {};
            ajaxData['security']    = object.ajax_nonce;
            ajaxData['eventID']     = eventID;
            // ajaxData['userID']      = '';
            ajaxData['teamID']      = teamID;
            ajaxData['qid']         = questionID;
            ajaxData['qans']        = answer;
            ajaxData['action']      = 'save_answer';
            // GIVEN ANSWERS ARRAY
            if (ajaxData['qans']) {
                jQuery.ajax({
                    type: 'POST',
                    url: object.ajaxurl,
                    cache: false,
                    data: ajaxData,
                    success: function(response, status, xhr) {
                        if (response == 1) {
                            $('.event-predict #'+ questionID).text(answer);
                            messageModal('<p style="text-align:center;font-weight:bold;color:green;">Saved successfully.</p>');
                        }
                        if (response == 3) {
                            messageModal('<p style="text-align:center;font-weight:bold;color:red;">Prediction time is over.</p>');
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
    var messageModal = function(message) {
        $('.confirm-modal').iziModal('destroy');
        var footer = "";
        var modal = $("<div>").attr("class", "modalWrapper4 confirm-modal");
        footer += "<footer>";
        footer += "<button data-iziModal-close class=\"fusion-button button-default button-small\">Close</button>";
        footer += "</footer>";
        modal.html("<div class=\"iziModal-header\" style=\"background: rgb(136, 160, 185); padding-right: 78px;\"><i class=\"iziModal-header-icon icon-home\"></i><h2 class=\"iziModal-header-title\">Welcome to the iziModal</h2><p class=\"iziModal-header-subtitle\">Elegant, responsive, flexible and lightweight modal plugin with jQuery.</p><div class=\"iziModal-header-buttons\"><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-close\" data-izimodal-close=\"\"></a><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-fullscreen\" data-izimodal-fullscreen=\"\"></a></div></div>");
        modal.iziModal({
            title: "Tips",
            // subtitle: 'SUB',
            autoOpen: 1,
            onOpening: function(modal) {
                modal.startLoading();
                $(".modalWrapper4 .iziModal-content").html('<div class="content">'+ message +'</div>'+ footer);
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
                        var questionNODE = $('#'+ questionID);
                        var teamWrapper = questionNODE.parents('.teamQuestionContainer');
                        if (response == 1) {
                            $('.confirm-modal').iziModal('destroy');
                            questionNODE.remove();
                            removeEmptyParent(teamWrapper);
                        }
                        if (response == 3) {
                            $('.modalWrapper footer .confirmed').attr('disabled', true).removeClass('btn-green').addClass('btn-gray');
                            $('.modalWrapper .iziModal-content .content').html('<p style="text-align:center;font-weight:bold;color:red;">Prediction time is over.</p>');
                            questionNODE.remove();
                            removeEmptyParent(teamWrapper);
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
    var getEventQuestionsHTML = function(eventID, team) {
        jQuery.ajax({
            type: 'POST',
            url: object.ajaxurl + '?action=getpredictionform',
            cache: false,
            data: {
                event: eventID,
                team: team,
                security: object.ajax_nonce
            },
            success: function(response, status, xhr) {
                if (response) showEventQuestionsModal(response);
                else messageModal('<p style="text-align:center;font-weight:bold;color:red;">Nothing is remaining to predict.</p>');
            },
            error: function(error) {
                console.log(error);
            }
        });
    }
    var showEventQuestionsModal = function(content) {
        $('.confirm-modal').iziModal('destroy');
        var modal = $("<div>").attr("class", "modalWrapper2 confirm-modal");
        modal.html("<div class=\"iziModal-header\" style=\"background: rgb(136, 160, 185); padding-right: 78px;\"><i class=\"iziModal-header-icon icon-home\"></i><h2 class=\"iziModal-header-title\">Welcome to the iziModal</h2><p class=\"iziModal-header-subtitle\">Elegant, responsive, flexible and lightweight modal plugin with jQuery.</p><div class=\"iziModal-header-buttons\"><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-close\" data-izimodal-close=\"\"></a><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-fullscreen\" data-izimodal-fullscreen=\"\"></a></div></div>");
        modal.iziModal({
            title: "Prediction form",
            // subtitle: 'SUB',
            autoOpen: 1,
            onOpening: function(modal) {
                modal.startLoading();
                $(".modalWrapper2 .iziModal-content").html('<div class="content">'+ content +'</div>');
                modal.stopLoading();
            }
        });
    }
    var showPopUp = function(className, title='Message', message='Test content') {
        $('.confirm-modal').iziModal('destroy');
        var footer = "";
        var modal = $("<div>").attr("class", className +" confirm-modal");
        footer += "<footer>";
        footer += "<button data-iziModal-close class=\"fusion-button button-default button-small\">Close</button>";
        footer += "</footer>";
        modal.html("<div class=\"iziModal-header\" style=\"background: rgb(136, 160, 185); padding-right: 78px;\"><i class=\"iziModal-header-icon icon-home\"></i><h2 class=\"iziModal-header-title\">Welcome to the iziModal</h2><p class=\"iziModal-header-subtitle\">Elegant, responsive, flexible and lightweight modal plugin with jQuery.</p><div class=\"iziModal-header-buttons\"><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-close\" data-izimodal-close=\"\"></a><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-fullscreen\" data-izimodal-fullscreen=\"\"></a></div></div>");
        modal.iziModal({
            title: title,
            // subtitle: 'SUB',
            autoOpen: 1,
            onOpening: function(modal) {
                modal.startLoading();
                $("."+ className +" .iziModal-content").html('<div class="content">'+ message +'</div>'+ footer);
                modal.stopLoading();
            }
        });
    }
    var formatDate = function(date) {
        var monthNames = [
        "Jan", "Feb", "Mar",
        "Apr", "May", "Jun", "Jul",
        "Aug", "Sep", "Oct",
        "Nov", "Dec"
        ];

        var day = date.getDate();
        var monthIndex = date.getMonth();
        var year = date.getFullYear();

        return day + ' ' + monthNames[monthIndex] + ' ' + year;
    }
    var supportedMatchTossPopup = function(button) {
        let overall     = button.attr('overall');
        let ipl         = button.attr('ipl');
        let link        = button.attr('event');
        let nickname    = button.attr('nickname');
        if (overall || ipl) {
            overall = JSON.parse(overall);
            ipl = JSON.parse(ipl);
            let footer = '';
            let content = '';
            content += '<section>';
                content += '<div class="supporterItem overAll">'
                content += '<h4 class="text-center title">Over All Accuracy</h4>'
                    content += '<div class="inline all"><span>Total</span>'+ overall.all +'%</div>';    
                    content += '<div class="inline match"><span>Match</span>'+ overall.match +'%</div>';
                    content += '<div class="inline toss"><span>Toss</span>'+ overall.toss +'%</div>';
                content += '</div>'
                content += '<div class="divider"></div>';
                content += '<div class="supporterItem ipl">'
                content += '<h4 class="text-center title">IPL Accuracy</h4>'
                    content += '<div class="inline all"><span>Total</span>'+ ipl.all +'%</div>';
                    content += '<div class="inline match"><span>Match</span>'+ ipl.match +'%</div>';
                    content += '<div class="inline toss"><span>Toss</span>'+ ipl.toss +'%</div>';
                content += '</div>'
                content += '<footer>';
                    // content += '<a class="btn btn-green button-small" target="_blank" href="'+ link +'">VIEW PREDICTION OF THIS EVENT</a>';
                    // content += '<button data-iziModal-close>Cancel</button>';
                content += '</footer>';
            content += '</section>';

            let modal = $("<div>").attr("class", "iziLoginModal confirm-modal supportedMatchTossPopup");
            modal.html("<div class=\"iziModal-header\" style=\"background: rgb(136, 160, 185); padding-right: 78px;\"><i class=\"iziModal-header-icon icon-home\"></i><h2 class=\"iziModal-header-title\">Welcome to the iziModal</h2><p class=\"iziModal-header-subtitle\">Elegant, responsive, flexible and lightweight modal plugin with jQuery.</p><div class=\"iziModal-header-buttons\"><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-close\" data-izimodal-close=\"\"></a><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-fullscreen\" data-izimodal-fullscreen=\"\"></a></div></div>");
            modal.iziModal({
                title: nickname,
                autoOpen: 1,
                onOpening: function(modal) {
                    modal.startLoading();
                    $(".iziLoginModal .iziModal-content").html(content);
                    modal.stopLoading();
                }
            });
        }
    }
    var supportersPopUp = function(button) {
        var message     = ''
        var match       = button.attr('match')
        var toss        = button.attr('toss')
        var tname       = button.attr('tname')

        if (match || toss) {
            message += '<div id="favouriteTeamTab" class="tabs tabs_default">';
                message += '<ul class="horizontal">';
                    message += '<li class="proli active"><a href="javascript:;" tab=".match">Match</a></li>';
                    message += '<li class="proli"><a href="javascript:;" tab=".toss">Toss</a></li>';
                message += '</ul>';
                // match
                message += '<div class="tabContent match">';
                    if (!match) message += 'nothing found'
                    else {
                        match = match.split(',')
                        message += '<ul class="list-unstyled votting-list">'
                            match.forEach(function(predictor) {
                                predictor = predictor.split('###')
                                if (predictor) {
                                    message += '<li>'
                                        message += '<a href="'+ object.home_url +'/predictor/?p='+ predictor[1] +'" target="_blank">'
                                            message += predictor[2]
                                        message += '</a>'
                                    message += '</li>'
                                }
                            })
                        message += '</ul>'
                    }
                message += '</div>';
                message += '<div class="tabContent toss" style="display:none;">';
                    if (!toss) message += 'nothing found'
                    else {
                        toss = toss.split(',')
                        message += '<ul class="list-unstyled votting-list">'
                            toss.forEach(function(predictor) {
                                predictor = predictor.split('###')
                                if (predictor) {
                                    message += '<li>'
                                        message += '<a href="'+ object.home_url +'/predictor/?p='+ predictor[1] +'" target="_blank">'
                                            message += predictor[2]
                                        message += '</a>'
                                    message += '</li>'
                                }
                            })
                        message += '</ul>'
                    }
                message += '</div>';
            message += '</div>';
        } else {
            message += '<p style="text-align:center;color:red;"> No one supported this team. </p>';
        }
        showPopUp('supportersPopUpModal', tname, message)
        $('#favouriteTeamTab').tabslet();
    }
    var iziLoginModalSubmit = function() {
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
                    console.log(response);
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
    }
    var customLogin = function() {
        var footer = '';
        var content = '';
        content += '<section>';
            content += '<div class="pLoginMessage"></div>';
            content += '<input id="p_user" type="text" placeholder="Username">';
            content += '<input id="P_pass" type="password" placeholder="Password">';
            content += '<footer>';
                content += '<button data-iziModal-close>Cancel</button>';
                content += '<button type="submit" class="iziLoginModalSubmit">Log in</button>';
            content += '</footer>';
        content += '</section>';

        var modal = $("<div>").attr("class", "iziLoginModal confirm-modal");
        modal.html("<div class=\"iziModal-header\" style=\"background: rgb(136, 160, 185); padding-right: 78px;\"><i class=\"iziModal-header-icon icon-home\"></i><h2 class=\"iziModal-header-title\">Welcome to the iziModal</h2><p class=\"iziModal-header-subtitle\">Elegant, responsive, flexible and lightweight modal plugin with jQuery.</p><div class=\"iziModal-header-buttons\"><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-close\" data-izimodal-close=\"\"></a><a href=\"javascript:void(0)\" class=\"iziModal-button iziModal-button-fullscreen\" data-izimodal-fullscreen=\"\"></a></div></div>");
        modal.iziModal({
            title: "Login form",
            // subtitle: 'SUB',
            autoOpen: 1,
            onOpening: function(modal) {
                modal.startLoading();
                $(".iziLoginModal .iziModal-content").html(content);
                modal.stopLoading();
            }
        });
    }
    var addLikeForEvent = function(button) {
        var event = button.attr('event');
        var user = button.attr('user');
        var btnTxt = button.text();
        // alert(btnTxt); return false; 
        if (event && user) {
            var ajaxData = {};
            ajaxData['security'] = object.ajax_nonce;
            ajaxData['action'] = 'like_event_user';
            ajaxData['event'] = event;
            ajaxData['user'] = user;
            $.ajax({
                type: 'POST',
                url: object.ajaxurl,
                cache: false,
                data: ajaxData,
                beforeSend: function() { button.html('Saving ..').attr('disabled', true); },
                success: function(response, status, xhr) {
                    console.log(response);
                    if (response == 200) button.removeClass('likeBtn').html('LIKED').attr('disabled', true);
                    else button.html(btnTxt).attr('disabled', false);
                },
                error: function(error) {
                    button.html(btnTxt).attr('disabled', true);
                    console.log(error);
                }
            });
        }
    }
    var test = function(button) {}
    // *************************** //
    // ***** READY FUNCTION ****** //
    // *************************** //
    $(document).ready(function() {
        owlCarousel();
        //Tab 		
        $('#protab').tabslet();
        $('#TopPredictor').tabslet();
        $('#Roadtotop').tabslet();
        $('#headerNotification').tabslet();
		$('#TopMatchPredictor').tabslet();
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
		$(document).on('click', 'closebtn', function(event) {
			$(".notice").fadeOut("slow", function() {
				hide();
			})
		})
        $(document).on('click', '.custom-login', function(event) {
            event.preventDefault();
            customLogin();
        })
		
       // Menu Modal
		$('.md-trigger').on('click', function() {
			$('.md-modal').addClass('md-show');
		});
		$('.md-close').on('click', function() {
			$('.md-modal').removeClass('md-show');
		});
		
		// Calender
		$(".dates-bar .month").text(function(index, currentText) {
			return currentText.substr(0, 3);
		});

        $('#openBtn').on('click', function () {
            $('#modal1').fullModal('open');
        });
        $('#closeBtn').on('click', function () {
            $('#modal1').fullModal('close');
        });
        // USER LOGIN
        $(document).on('click', '.iziLoginModalSubmit', function(event) {
            event.preventDefault();
            iziLoginModalSubmit();
        });
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
        // $('.answersWrapper').each(function(index) {
        //     var eventID     = $(this).attr('event');
        //     var ditems      = $(this).attr('ditems');
        //     var html        = $(this).attr('html');
        //     var avatarslider = $(this).attr('avatarslider');
        //     loadAnswers(eventID, ditems, html, avatarslider);
        // })
        $(document).on('click', '.refreshButton', function(event) {
            var eventID     = $(this).parents('.answersWrapper').attr('event');
            var ditems      = $(this).parents('.answersWrapper').attr('ditems');
            var html        = $(this).parents('.answersWrapper').attr('html');
            var avatarslider = $(this).parents('.answersWrapper').attr('avatarslider');
            if (!eventID) alert('Not a valid event');
            else loadAnswers(eventID, ditems, html, avatarslider);
        });
        $('.eventsAnswersWrapper').each(function(index) {
            var events = $(this).attr('event');
            var ditems = $(this).attr('ditems');
            loadEventAnswer(events, ditems);
        })
        $(document).on('click', '.eventsRefreshButton', function(event) {
            var eventID = $(this).parents('.eventsAnswersWrapper').attr('event');
            var ditems = $(this).parents('.eventsAnswersWrapper').attr('ditems');
            if (!eventID) alert('Not a valid event');
            else loadEventAnswer(eventID, ditems);
        });
        $(document).on('click', '.predictionFormBtn', function(event) {
            event.preventDefault();
            var eventID = $(this).attr('event');
            var team = $(this).attr('team');
            getEventQuestionsHTML(eventID, team);
        });
        $(document).on('click', '.saveModalQAns', function(event) {
            event.preventDefault();
            var button = $(this);
            var teamID = button.parents('.teamQuestionContainer').attr('id');
            var Question = button.parents('.predictionContainer');
            var questionID = button.parents('.predictionContainer').attr('id');
            var eventID = button.parents('.predictionWrapper').attr('event');

            // GIVEN ANSWERS ARRAY
            var warnings = '';
            var radioValueCount = 0;
            warnings = '<h3 class="wTitle">'+ button.parents('.teamQuestionContainer').find('.teamName strong').text() +'</h3>';
            var radioTitle = Question.find('.title').text();

            var radioVal = $("input[name='"+ questionID +"']:checked").val();
            if (radioVal) { 
                warnings += '<p class="given"><span class="title">'+ radioTitle +' : </span><span class="ans">'+ radioVal +'</span></p>';
                cofirmBoxModal(warnings, eventID, questionID, teamID, radioVal);
            } else {
                alert('You didn\'t select any answer.');
            }
        })
        $(document).on('click', '.modalConfirmed', function(event) {
            event.preventDefault();
            var button      = $(this);
            var eventID     = button.attr('event');
            var teamID      = button.attr('team');
            var questionID  = button.attr('qid');
            var answer      = button.attr('ans');
            saveModalQAns(eventID, teamID, questionID, answer);
        })
        $(document).on('click', '.supportersPopUp', function(event) {
            event.preventDefault();
            var button      = $(this);
            supportersPopUp(button);
        })
        $(document).on('click', '#favouriteTeamTab .proli a', function(event) {
            event.preventDefault();
            // RESET THE TAB
            $('#favouriteTeamTab .proli').removeClass('active');
            $('#favouriteTeamTab .tabContent').css('display', 'none');
            // MAKE CHANGES
            var button = $(this);
            var activeItem = button.attr('tab');
            button.parents('.proli').addClass('active');
            button.parents('.tabs_default').find(activeItem).css('display', 'block');
        })
        // matchesDatepicker
        $(document).on('change', '#matchesDatepicker', function(event) {
            event.preventDefault();
            let notFound = 1;
            let selectedDate = $(this).val();
            $('#calendar_text span').text(formatDate(new Date(selectedDate)));
            $('.timeline-wrap .event').removeClass('selected');
            $('.timeline-wrap .event').each(function() {
                let event = $(this).attr('data-date');
                if (event == selectedDate) {
                    $(this).addClass('selected');
                    notFound = 0;
                }
            });
            if(notFound) $('.timeline-wrap .notFound').addClass('selected');
        })
        $(document).on('click', '.supportedMatchTossPopup', function(event) {
            event.preventDefault();
            var button = $(this);
            supportedMatchTossPopup(button);
        })
        $(document).on('click', '.likeBtn', function(event) {
            event.preventDefault();
            var button = $(this);
            addLikeForEvent(button);
        })
    });
})(jQuery);
// new CBPFWTabs( document.getElementById( 'tabs' ) );