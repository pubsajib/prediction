<?php 
function answersHTML($meta, $ans, $eventID, $ditems=2) {
    $html = $userNav = '';
    $owlSelector = 'owlCarousel_'. $eventID;
    if (empty($ans)) $html .= 'No one predicted this event yet. If you are an expert you may <a href="http://cricdiction.com/log-in/">Login</a> here.'; 
    else {
        $ranking = getRakingFor();
        $likes = likesByEvent($eventID);
        $html .= '<div class="owl-carousel '.$owlSelector.' owl-theme">';
        foreach ($ans as $uID => $answer) {
            if ($answer) {
                $ratingIcon = '';
                $rank = userRankingStatusFor($uID, $ranking);
                if (!empty($rank['num'])) $ratingIcon = '<p>'. $rank['num'] .'</p>';
                $country = get_the_author_meta( 'country', $uID );
                $highlight = get_the_author_meta( 'highlight', $uID ) ? ' highlighted' : '';
                $user = get_userdata($uID);
                $html .= '<div id="predictor_'. $uID .'" class="answerContainer item'. $highlight . $rank['class'] .'" data-hash="'.$uID.'">';
                    $html .= '<div class="dashboard-user text-center">';
                        $html .= '<div class="user-avater">'.get_avatar( $user->user_email , '90') .'</div>';
                        $html .= '<div class="user-information">';
                            $html .= '<h4>';
                                $html .= '<a href="'. site_url('predictor/?p='. $user->user_login) .'"  target="_blank">'. get_the_author_meta('nickname',$uID) .'</a>';
                                if ($country) $html .= '<img class="countryFlag" src="'. PREDICTOR_URL .'frontend/img/'. $country .'.png" alt="country">';
                            $html .= !empty($likes[$uID]) ? ' Likes : '. $likes[$uID] : '';
                            $html .= '</h4><br>';
                                $html .= get_user_meta($user->ID, 'description', true);
                        $html .= '</div>';
                    $html .= '</div>';
                    if (!empty($meta['teams'])) {
                        $html .= '<div class="teamAnsWrapper">';
                            foreach ($meta['teams'] as $team) {
                                $givenAnswers = '';
                                $teamID = predictor_id_from_string($team['name']);
                                $options = 'team_'. $teamID;
                                // GIVEN ANSWERS
                                if ($meta[$options]) {
                                    foreach ($meta[$options] as $option) {
                                        $ansID = $options.'_'.predictor_id_from_string($option['title']);
                                        if (empty($answer[$ansID])) continue;
                                        $defaultID = 'default_'. $teamID .'_'. predictor_id_from_string($option['title']);
                                        $defaultAns = $meta[$defaultID];
                                        $published = !empty($meta[$defaultID.'_published']) ? $meta[$defaultID.'_published'] : [];
                                        $isCorrect = '';
                                        if ($published) {
                                            if ($defaultAns == 'abandon') {
                                                $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/warning.png">';
                                            } else if ($ans[$uID][$ansID]== $defaultAns) $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/happy.png">';
                                            else $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/sad.png">';
                                        }
                                        // $html .= '<br>published: '.$published.' == givenAns: '.$ans[$uID][$ansID] .' == DefaultAns: '. $defaultAns;
                                        $userAnswer = !empty($answer[$ansID]) ? $answer[$ansID] : false;
                                        $ansWeight = getWeightFromValue($option['weight'], $userAnswer);
                                        $givenAnswers .= '<div class="answer">'; 
                                            $givenAnswers .= !empty($option['title']) ? $option['title'] : '';
                                            if ($defaultAns == 'abandon') $givenAnswers .= ' <span class="text-danger noResult"></span>';
                                            $givenAnswers .= ' <strong>'; 
                                                $givenAnswers .= '<span class="ansTxt">'. $userAnswer .'</span>'; 
                                                if ($ansWeight) {
                                                    $givenAnswers .= ' @ <span class="ansWeight">'. $ansWeight .'</span>'; 
                                                }
                                            $givenAnswers .= '</strong>&nbsp;'; 
                                            $givenAnswers .= '<span>'. $isCorrect .'</span>'; 
                                        $givenAnswers .= '</div>'; 
                                    }
                                }

                                if ($givenAnswers) {
                                    $html .= '<div class="teamAnsContainer">';
                                    $html .= '<h3 class="teamName">'. $team['name'] .'</h3>';
                                    $html .= $givenAnswers;
                                    $html .= '</div>';
                                }
                            }
                        $html .= '</div>';
                        // WINE LOSE DATA
                        // $html .= winLoseHtml($UP, 'match', 9);
                        // $html .= winLoseHtml($UP, 'toss', 9);
                        $html .= '<div class="profile-link">';
                            $html .= '<a href="'. site_url('predictor/?p='. $user->user_login) .'" target="_blank">VIEW PROFILE</a>';
                            $html .= likeDislikeBtnFor($uID, $eventID);
                        $html .= '</div>';
                    }
                $html .= '</div>';
                $userNav .= '<a class="userNavItem'. $rank['class'] .'" href="#'.$uID.'">'.get_avatar( $user->user_email) . '</a>';
            }
        }
        $html .= '</div>';
        $html .= '<ul class="menuSlider">'. $userNav .'</ul>';
        $html .= '<script> (function($) { jQuery(".'. $owlSelector .'").owlCarousel({loop:true, margin: 10, nav: true, autoplay:true, autoplayTimeout:15000, URLhashListener:true, autoplayHoverPause:true, startPosition: "URLHash", responsive: {0: {items: 1 }, 600: {items: 1 }, 1000: {items: '. $ditems .' } } }) })(jQuery); </script>';
        $html .= getFavoriteTeamForThisEvent($meta, $ans, $eventID, false);
    }
    // $html .= '<br><pre>'. print_r($ans, true) .'</pre>';
    // $html .= '<br>'. $eventID .'<pre>'. print_r($meta, true) .'</pre>';
    return $html;
}
function adminAnswersHTML($meta, $ans) {
    $html = '';
    if (!empty($ans)) {
        $html .= '<div class="answersWrapper">';
        foreach ($ans as $uID => $answer) {
            if ($answer) {
                $user = get_userdata($uID);
                $html .= '<div id="predictor_'. $uID .'" class="answerContainer">';
                // $html .= '<button class="adminButton removeAns" event="'. $_GET['post'] .'" user="'. $uID .'">Delete</button>';
                $html .= '<div class="text-center header">';
                    $html .= get_avatar( @$user->user_email , '70 ');
                    $html .= '<h4><a href="'. site_url('predictor/?p='. @$user->user_login) .'">'. get_the_author_meta('nickname',$uID) .'</a></h4>';
                $html .= '</div>';
                if (!empty($meta['teams'])) {
                    $html .= '<div class="teamAnsWrapper">';
                    foreach ($meta['teams'] as $team) {
                        $teamID = predictor_id_from_string($team['name']);
                        $options = 'team_'. $teamID;
                            $html .= '<div class="teamAnsContainer">';
                            $html .= '<h3 class="teamName">'. $team['name'] .'</h3>';
                            if (!empty($meta[$options])) {
                                foreach ($meta[$options] as $option) {
                                    $ansID = $options.'_'.predictor_id_from_string($option['title']);
                                    if (!isset($answer[$ansID]) || empty($answer[$ansID])) continue;
                                    $defaultID = 'default_'. $teamID .'_'. predictor_id_from_string($option['title']);
                                    $defaultAns = $meta[$defaultID] ?? '';
                                    $published = isset($meta[$defaultID.'_published']) && $meta[$defaultID.'_published'] ? $meta[$defaultID.'_published'] : false ;
                                    $isCorrect = '';
                                    if ($published) {
                                        if ($defaultAns == 'abandon') {
                                            $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/unhappy.png">';
                                        } else if ($ans[$uID][$ansID] == $defaultAns) $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/happy.png">';
                                        else $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/sad.png">';
                                    }
                                    // $html .= '<pre>'. print_r($option['title'], true) .'</pre>';
                                    $html .= '<div class="answer">';
                                    $html .= $option['title'];
                                    $html .= ' <strong><span>'. @$answer[$ansID] .'</span></strong>&nbsp;&nbsp;&nbsp;<span>'. $isCorrect .'</span>'; 
                                    $html .= '<span class="adminButton removeAns" answerid="'. $ansID .'" event="'. $_GET['post'] .'" user="'. $uID .'"> Delete <span>'; 
                                    $html .= '<div style="clear:both;"></div>'; 
                                    $html .= '</div>'; 
                                }
                            }
                            $html .= '</div>';
                        if (@$ans[$uID][$options]) {
                        }
                    }
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
        }
        $html .= '</div>';
    } else {
        $html .= 'No answer given yet';
    }
    // $html .= '<br><pre>'. print_r($ans, true) .'</pre>';
    // $html .= '<br><pre>'. print_r($meta, true) .'</pre>';
    return $html;
}
function getWeightFromValue($weights, $ans) {
    $html = '';
    if ($weights) {
        foreach ($weights as $weight) {
            if ($weight['name'] == $ans) {
                $html .= $weight['value'];
            }
        }
    }
    // $html .= '<br><pre>'. print_r($weights, true) .'</pre>';
    return $html;
}