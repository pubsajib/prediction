<?php 
/**
 * enhancement
 */
class Enhancement {
    static function loadEventSingle($eventID, $ditems=2) {
        $data = '';
        $answers = '';
        $meta    = get_post_meta($eventID, 'event_ops', true);
        $ans     = (array) get_post_meta($eventID, 'event_ans', true); 
        if (isset($ans[0])) unset($ans[0]);
        $answers = self::answersHTML($meta, $ans, $eventID);
        if ($answers) {
            $data .= '<div id="answersWrapper_'.$eventID.'" class="answersWrapper" event="'.$eventID.'" ditems="2" html="box" avatarslider="0">';
            $data .= '<span class="refreshButton fusion-button button-default button-small" event="'. $eventID .'">Reload</span>';
            $data .= $answers;
            $data .= '</div>';
        }
        else $data = 'No one predicted this event yet. If you are an expert you may <a href="'. site_url('log-in') .'">Login</a> here.';
        $data .= self::answerForm($meta, $ans, $eventID);
        $data .= self::discussionLink($meta);
        echo $data;
    }
    static function answersHTML($meta, $ans, $eventID) {
        $html = $userNav = '';
        if (empty($ans)) $html .= 'No one predicted this event yet. If you are an expert you may <a href="http://cricdiction.com/log-in/">Login</a> here.'; 
        else {
            $users = self::getAnsweredUsers($ans);
            $html .= '<div class="owl-carousel owlCarousel_'.$eventID.' owl-theme">';
            foreach ($ans as $uID => $answer) {
                if ($answer) {
                    $ratingIcon = '';
                    // $rank = userRankingStatusFor($uID, $ranking);
                    $rank = ['num'=>0,'class'=>'',];
                    $user = !empty($users[$uID]) ? $users[$uID] : false;
                    if (!empty($rank['num'])) $ratingIcon = '<p>'. $rank['num'] .'</p>';
                    if ($user) {
                        $user['likes'] = !empty($user['likes']) ? $user['likes'] : 0;
                        if (empty($_COOKIE['cdpue_'.$eventID.'_'.$uID])) $likeBtn = '<button class="btn btn-xs btn-primary predictorLikeBtn userlikebtn" type="button" user='. $uID .' event='. $eventID .'><img src="https://www.cricdiction.com/wp-content/plugins/predictor/frontend/img/UserLike.png"></button>';
                        else $likeBtn = '<button class="btn btn-xs btn-primary userlikebtn" type="button" user='. $uID .' event='. $eventID .'><img src="https://www.cricdiction.com/wp-content/plugins/predictor/frontend/img/liked.png"></button>';
                        $html .= '<div id="predictor_'. $uID .'" class="answerContainer item" data-hash="'.$uID.'">';
                            $html .= '<div class="dashboard-user text-center">';
                                // $html .= help($user, false);
                                $html .= '<div class="user-avater">'.$user['avatar'].'</div>';
                                $html .= '<div class="user-information">';
                                    $html .= '<h4>';
                                        $html .= '<a href="'. site_url('predictor/?p='. $user['user_login']) .'"  target="_blank">'. $user['name'] .'</a>';
                                        if ($user['country']) $html .= '<img class="countryFlag" src="'. PREDICTOR_URL .'frontend/img/'. $user['country'] .'.png" alt="country">';
                                    $html .= '</h4><br>';
                                        $html .= $user['description'];
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
                                                $defaultAns = !empty($meta[$defaultID]) ? $meta[$defaultID] : false;
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
                                                $givenAnswers .= '<div class="answer">'; 
                                                    $givenAnswers .= !empty($option['title']) ? $option['title'] : '';
                                                    if ($defaultAns == 'abandon') $givenAnswers .= ' <span class="text-danger noResult"></span>';
                                                    $givenAnswers .= ' <strong>'; 
                                                        $givenAnswers .= '<span class="ansTxt">'. $userAnswer .'</span>'; 
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
                                $html .='<div class="like-section">';
                                    $html .= '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['user_login']) .'"  target="_blank">VIEW PROFILE</a></div>';
                                    $html .='<div class="like-right">';
                                        $html .= $likeBtn . '<span class="countuserlike likeCounter_'.$uID.'">'. $user['likes'] .'</span>';
                                    $html .='</div>';
                                $html .='</div>';
                            }
                        $html .= '</div>';
                        $userNav .= '<a class="userNavItem'. $rank['class'] .'" href="#'.$uID.'">'. $user['avatar'] . '</a>';
                    }
                }
            }
            $html .= '</div>';
            $html .= '<ul class="menuSlider">'. $userNav .'</ul>';
            $html .= self::getFavoriteTeamForThisEvent($meta, $ans, $eventID, $users);
        }
        return $html;
    }
    static function getAnsweredUsers($ans) {
        global $wpdb;
        $predictors = [];
        $userIDs = implode(',', array_keys($ans));
        $users = $wpdb->get_results( "SELECT id, user_login, user_email, display_name AS name FROM $wpdb->users WHERE ID IN ($userIDs)" );

        if ($users) {
            foreach ($users as $user) {
                $meta = ['country'=>'','description'=>'','avatar'=>'','likes'=>''];
                $sql = "SELECT umeta_id, user_id, meta_key,`meta_value` FROM $wpdb->usermeta WHERE `user_id`= {$user->id} AND `meta_key` IN ('country', 'description', 'likes')";
                $umetas = $wpdb->get_results( $sql );
                $meta['avatar'] = get_avatar( $user->user_email);
                if ($umetas) {
                    foreach ($umetas as $umeta) {
                        $meta[$umeta->meta_key] = $umeta->meta_value;
                    }
                }
                $predictors[$user->id] = array_merge((array)$user, $meta);
            }
        }
        return $predictors;
    }
    static function answerForm2($meta, $ans, $eventID) {
        $data = '';
        $user = wp_get_current_user();
        // $data = help($user, false);
        if (!$user) {
            // NOT LOGGED IN
            $data .= '<div class="text-center"><a href="'. esc_url(site_url('pcp')) .'" class="fusion-button button-default button-small">login </a> to predict.</div>';
        } else {
            if ($userID = self::getValidUserID(['predictor', 'administrator'], $user)) {
                // PREDICTIN FORM
                if (!empty($meta['published'])) $data .= 'Event prediction time is over'; // Event is already published
                    else {
                    $data .= '<div class="predictionWrapper">';
                        $data .= '<form action="" method="post">';
                            $data .= '<input id="userID" type="hidden" name="user" value="'. $userID .'">';
                            $data .= '<input id="eventID" type="hidden" name="event" value="'. $eventID .'">';
                            $data .= '<input id="TID" type="hidden" name="team">';
                            $data .= '<input id="QID" type="hidden" name="qid">';
                            $data .= '<input id="QAns" type="hidden" name="qans">';
                            if ($meta['teams']) {
                                $data .= '<div class="teamQuestionWrapper">';
                                foreach ($meta['teams'] as $team) {
                                    $teamID = predictor_id_from_string($team['name']);
                                    $options = 'team_'. $teamID;
                                    $optionVals = !empty($ans[$userID][$options]) ? $ans[$userID][$options] : false;
                                    if (isValidOption($optionVals, $team['end'])) {
                                        $questions = '';
                                        if ($meta[$options]) {
                                            foreach ($meta[$options] as $option) {
                                                $question = $tossTime = '';
                                                $name = !empty($option['title']) ? $options .'_'. predictor_id_from_string($option['title']) : '';
                                                if (empty($ans[$userID][$name])) {
                                                    if ($option['id'] == 'toss') {
                                                        $tossTime =  $option['time'] ? $option['time'] : 30;
                                                        $tossTime =  date('Y-m-d H:i:s',strtotime("-". $tossTime ." minutes",strtotime($team['end'])));
                                                        if (!isValidOption('', $tossTime)) continue;
                                                    }
                                                    $question .= '<div class="predictionContainer" id="'. $name .'">';
                                                        if ($option['weight']) {
                                                            $question .= '<h4 class="title">'. $option['title'] .'</h4>';
                                                            if ($tossTime ) $question .= '<div class="endToss" id="'. $name .'_end">'. $tossTime .'</div>';
                                                            foreach ($option['weight'] as $weight) {
                                                                if (!$weight['name']) continue;
                                                                $question .= '<label><input type="radio" name="'. $name .'" value="'. $weight['name'] .'">'. $weight['name'] .'</label>';
                                                            }
                                                        }
                                                        $question .= '<button type="button" class="btn btn-green saveQAns">Submit</button>';
                                                    $question .= '</div>';
                                                }
                                                $questions .= $question;
                                            }
                                        }
                                        if ($question) {
                                            $data .= '<div class="teamQuestionContainer" id="'. $options .'">';
                                            $data .= '<div class="titleContainer">';
                                            $data .= '<div class="teamName half left"><strong>'. $team['name'] .'</strong></div>';
                                            $data .= '<div><div class="endTime helf right text-right" id="'. $teamID .'_end">'. $team['end'] .'</div><p class="text-right">Time remaining to predict </p></div>'; 
                                            $data .= '</div>';
                                            $data .= $questions;
                                            $data .= '</div>';
                                        }
                                    }
                                } // teamQuestionContainer
                                $data .= '<div class="notice">';
                                    $data .= '<div class="alert">';
                                        $data .= '<span class="closebtn">&times;</span>';
                                        $data .= '<ul style="margin-left: 15px;">';
                                            $data .= '<li><a href="https://cricdiction.com/eligibility-process">Click here</a> to see the eligibility process</li>';
                                    $data .= '</div>';
                                $data .= '</div>';
                                $data .= '</div>';
                            }
                        $data .= '</form>';
                    
                $data .= '</div>'; // predictionWrapper end
                }
            }
        }
        return $data;
    }
    static function answerForm($meta, $ans, $eventID) {
        $data = '';
        $user = wp_get_current_user();
        // $data = help($user, false);
        if (!$user) {
            // NOT LOGGED IN
            $data .= '<div class="text-center"><a href="'. esc_url(site_url('pcp')) .'" class="fusion-button button-default button-small">login </a> to predict.</div>';
        } else {
            if ($userID = self::getValidUserID(['predictor', 'administrator'], $user)) {
                // PREDICTIN FORM
                if (!empty($meta['published'])) $data .= 'Event prediction time is over'; // Event is already published
                else {
                    if ($meta['teams']) {
                        $teamQuestions = '';
                        foreach ($meta['teams'] as $team) {
                            $teamID = predictor_id_from_string($team['name']);
                            $options = 'team_'. $teamID;
                            $optionVals = !empty($ans[$userID][$options]) ? $ans[$userID][$options] : false;
                            if (isValidOption($optionVals, $team['end'])) {
                                $questions = '';
                                if ($meta[$options]) {
                                    foreach ($meta[$options] as $option) {
                                        $question = $tossTime = '';
                                        $name = !empty($option['title']) ? $options .'_'. predictor_id_from_string($option['title']) : '';
                                        if (empty($ans[$userID][$name])) {
                                            if ($option['id'] == 'toss') {
                                                $tossTime =  $option['time'] ? $option['time'] : 30;
                                                $tossTime =  date('Y-m-d H:i:s',strtotime("-". $tossTime ." minutes",strtotime($team['end'])));
                                                if (!isValidOption('', $tossTime)) continue;
                                            }
                                            $question .= '<div class="predictionContainer" id="'. $name .'">';
                                                if ($option['weight']) {
                                                    $question .= '<h4 class="title">'. $option['title'] .'</h4>';
                                                    if ($tossTime ) $question .= '<div class="endToss" id="'. $name .'_end">'. $tossTime .'</div>';
                                                    foreach ($option['weight'] as $weight) {
                                                        if (!$weight['name']) continue;
                                                        $question .= '<label><input type="radio" name="'. $name .'" value="'. $weight['name'] .'">'. $weight['name'] .'</label>';
                                                    }
                                                }
                                                $question .= '<button type="button" class="btn btn-green saveQAns">Submit</button>';
                                            $question .= '</div>';
                                        }
                                        $questions .= $question;
                                    }
                                }
                                if ($question) {
                                    $teamQuestions .= '<div class="teamQuestionContainer" id="'. $options .'">';
                                    $teamQuestions .= '<div class="titleContainer">';
                                    $teamQuestions .= '<div class="teamName half left"><strong>'. $team['name'] .'</strong></div>';
                                    $teamQuestions .= '<div><div class="endTime helf right text-right" id="'. $teamID .'_end">'. $team['end'] .'</div><p class="text-right">Time remaining to predict </p></div>'; 
                                    $teamQuestions .= '</div>';
                                    $teamQuestions .= $questions;
                                    $teamQuestions .= '</div>';
                                }
                            }
                        } // teamQuestionContainer
                        // $data .= self::notice();
                    }
                }
                $data .= '<div class="predictionWrapper">';
                    if ($teamQuestions) {
                        $data .= '<form action="" method="post">';
                            $data .= '<input id="userID" type="hidden" name="user" value="'. $userID .'">';
                            $data .= '<input id="eventID" type="hidden" name="event" value="'. $eventID .'">';
                            $data .= '<input id="TID" type="hidden" name="team">';
                            $data .= '<input id="QID" type="hidden" name="qid">';
                            $data .= '<input id="QAns" type="hidden" name="qans">';
                            $data .= '<div class="teamQuestionWrapper">';
                                $data .= $teamQuestions;
                            $data .= '</div>';
                        $data .= '</form>';
                    } else {
                        $data .= '<h2 style="line-height: 100px; text-align: center;">Nothing remains to predict</h2>';
                    }
                $data .= '</div>'; // predictionWrapper end
            }
        }
        return $data;
    }
    static function notice() {
        $data = '';
        $data .= '<div class="notice">';
            $data .= '<div class="alert">';
                $data .= '<span class="closebtn">&times;</span>';
                $data .= '<ul style="margin-left: 15px;">';
                    $data .= '<li><a href="https://cricdiction.com/eligibility-process">Click here</a> to see the eligibility process</li>';
            $data .= '</div>';
        $data .= '</div>';
        return $data;
    }
    static function getValidUserID($type='viewer', $user='') {
        $user_roles=$user->roles;
        if (is_array($type)) {
            if (!in_array_any($type, $user_roles)) return false;
            else return $user->ID;
        } else {
            if (!in_array($type, $user_roles)) return false;
            else return $user->ID;
        }
    }
    static function discussionLink($meta) {
        $data = '';
        $discussion = !empty($meta['teams'][1]['discussion']) ? $meta['teams'][1]['discussion'] : false;
        if ($discussion) $data .= '<a href="'. $discussion .'" class="fusion-button button-default button-medium orange block" target="_blank">View Discussion Page</a>';
        if (!is_user_logged_in()) $data .= '<p style="background :#fff;margin-top: 15px;padding: 20px;border-radius: 10px;">If you are an expert Please <strong><a href="https://www.cricdiction.com/log-in/">Login</a></strong> to predict. Or if you need access <strong><a href="https://www.cricdiction.com/sign-up/" target="_blank">Register here</a></strong>.</p>';
        return $data;
    }
    static function getFavoriteTeamForThisEvent($meta, $answers, $eventID, $users) : string {
        $data       = '';
        $teams      = [];
        $ansUsers   = [];
        
        $ansCount = [];
        if ($answers) {
            foreach ($answers as $uID => $ans) {
                if (!empty($users[$uID])) { // check for deleted users
                    $predictorData = $users[$uID];
                    $predictors[$uID] = $predictorData + ['match' => '', 'toss' => ''];
                    if ($ans) {
                        foreach ($ans as $key => $value) {
                            $ansCount[$key][] = $predictorData + ['team' => $value];
                        }
                    }
                }
            }
        }
        // $data .= help($ansCount, false);

        if (!empty($meta['teams'])) {
            foreach ($meta['teams'] as $key => $team) {
                $teamID = predictor_id_from_string($team['name']);
                $options = 'team_'. $teamID;
                if ($meta[$options]) {
                    $teams[$options]['name'] = $team['name'];
                    if (!isset($teams[$options]['teams'])) {
                        if ($weights = $meta[$options][1]['weight']) {
                            foreach ($weights as $weight) { if ($weight['name']) $teams[$options]['teams'][] = $weight['name']; }
                        }
                    }
                    $teams[$options]['predictors'] = $predictors;
                    foreach ($meta[$options] as $option) {
                        $ansID = $options.'_'.predictor_id_from_string($option['title']);
                        $teams[$options]['items'][$option['id']]['id'] = $ansID;
                        $teams[$options]['items'][$option['id']]['name'] = $option['title'];
                        $teams[$options]['items'][$option['id']]['sum'] = count($ansCount[$ansID]);
                        if ($itemTeams = $teams[$options]['teams']) {
                            foreach ($itemTeams as $itemTeam) {
                                $answerCountByOption = [];
                                if (!empty($ansCount[$ansID])) {
                                    foreach ($ansCount[$ansID] as $ansByOption) {
                                        $tmpUserData = $ansByOption['id'] .'###'. $ansByOption['user_login'] .'###'. $ansByOption['name'] .'###avatar';
                                        if ($ansByOption['team'] == $itemTeam) $answerCountByOption[] =  $tmpUserData;
                                        $teams[$options]['predictors'][$ansByOption['id']][$option['id']] = $answers[$ansByOption['id']][$ansID];

                                    }
                                }
                                if (count($ansCount[$ansID])) $itemPercentage = (count($answerCountByOption) / count($ansCount[$ansID])) * 100;
                                else $itemPercentage = 0;
                                
                                $teams[$options]['items'][$option['id']][predictor_id_from_string($itemTeam)] = $itemPercentage;
                                $teams[$options]['items'][$option['id']][predictor_id_from_string($itemTeam) .'-supporters'] = $answerCountByOption;
                            }
                        }
                        $teams[$options]['items'][$option['id']]['name'] = $option['title'];
                    }
                }
            }
        }

        if ($teams) {
            $teamSI = 1;
            $firstTeamColor = '#9afff8';
            $secondTeamColor = '#d2ffc2';
            foreach ($teams as $team) {
                if (isset($team['teams'])) {
                    $matchData = '';
                    $tossData = '';
                    $teamNamesData = '';
                    $uniqueID   = $eventID . $teamSI;
                    $firstTeamName = $team['teams'][0];
                    $secondTeamName = $team['teams'][1];
                    $firstTeamID = predictor_id_from_string($team['teams'][0]);
                    $secondTeamID = predictor_id_from_string($team['teams'][1]);
                    $firstTeamMatchSupporters = !empty($team['items']['match'][$firstTeamID .'-supporters']) ? $team['items']['match'][$firstTeamID .'-supporters'] : [];
                    $secondTeamMatchSupporters = !empty($team['items']['match'][$secondTeamID .'-supporters']) ? $team['items']['match'][$secondTeamID .'-supporters'] : [];
                    $firstTeamTossSupporters = !empty($team['items']['toss'][$firstTeamID .'-supporters']) ? $team['items']['toss'][$firstTeamID .'-supporters'] : [];
                    $secondTeamTossSupporters = !empty($team['items']['toss'][$secondTeamID .'-supporters']) ? $team['items']['toss'][$secondTeamID .'-supporters'] : [];
                    $firstTeamMatchSupportersList = ' <img class="supportersPopUp" tname=\''. $firstTeamName .'\' match=\''. implode(',', $firstTeamMatchSupporters) .'\' toss=\''. implode(',', $firstTeamTossSupporters) .'\' src="'. PREDICTOR_URL .'frontend/img/team2.png">';
                    $secondTeamMatchSupportersList = ' <img class="supportersPopUp" tname=\''. $secondTeamName .'\' match=\''. implode(',', $secondTeamMatchSupporters) .'\' toss=\''. implode(',', $secondTeamTossSupporters) .'\' src="'. PREDICTOR_URL .'frontend/img/team1.png">';
                    
                    $teamNamesData .= '<div class="teamNameContainer">';
                        $teamNamesData .= '<div class="w50p first"> <h6 style="color:'. $firstTeamColor .'">'. $firstTeamName . $firstTeamMatchSupportersList .'</h6> </div>';
                        $teamNamesData .= '<div class="w50p last"> <h6 style="color:'. $secondTeamColor .'">'. $secondTeamName . $secondTeamMatchSupportersList .'</h6> </div>';
                        $teamNamesData .= '<div class="clearfix"></div>';
                    $teamNamesData .= '</div>';

                    if (isset($team['items']['match']) && (!empty($team['items']['match'][$firstTeamID]) || !empty($team['items']['match'][$secondTeamID]))) {
                        $firstValue = $team['items']['match'][$firstTeamID];
                        $secondValue = $team['items']['match'][$secondTeamID];
                        $matchData .= '<div class="progressContainer">';
                            if ($firstValue) {
                                $matchData .= '<div class="skillbar w50p first" style="width:'. $firstValue .'%;" data-percent="100%">';
                                    $matchData .= '<div class="skillbar-bar" style="background: '. $firstTeamColor .';"></div>';
                                    $matchData .= '<div class="skill-bar-percent">'. number_format($firstValue , 2).'%</div>';
                                $matchData .= '</div>';
                            }
                            if ($secondValue) {
                                $matchData .= '<div class="skillbar w50p last" style="width:'. $secondValue .'%;" data-percent="100%">';
                                    $matchData .= '<div class="skillbar-bar" style="background: '. $secondTeamColor .';"></div>';
                                    $matchData .= '<div class="skill-bar-percent">'. number_format($secondValue, 2) .'%</div>';
                                $matchData .= '</div>';
                            }
                            $matchData .= '<div class="typeContainer"><small>'. $team['items']['match']['name'] .' ('. $team['items']['match']['sum'] .')</small></div>';
                        $matchData .= '</div>';
                    }
                    if (isset($team['items']['toss']) && (!empty($team['items']['toss'][$firstTeamID]) || !empty($team['items']['toss'][$secondTeamID]))) {
                        $firstValue = $team['items']['toss'][$firstTeamID];
                        $secondValue = $team['items']['toss'][$secondTeamID];
                        $tossData .= '<div class="progressContainer">';
                            if ($firstValue) {
                                $tossData .= '<div class="skillbar w50p first" style="width:'. $firstValue .'%;" data-percent="100%">';
                                    $tossData .= '<div class="skillbar-bar" style="background: '. $firstTeamColor .';"></div>';
                                    $tossData .= '<div class="skill-bar-percent">'. number_format($firstValue , 2).'%</div>';
                                $tossData .= '</div>';
                            }
                            if ($secondValue) {
                                $tossData .= '<div class="skillbar w50p last" style="width:'. $secondValue .'%;" data-percent="100%">';
                                    $tossData .= '<div class="skillbar-bar" style="background: '. $secondTeamColor .';"></div>';
                                    $tossData .= '<div class="skill-bar-percent">'. number_format($secondValue, 2) .'%</div>';
                                $tossData .= '</div>';
                            }
                            $tossData .= '<div class="typeContainer"><small>'. $team['items']['toss']['name'] .' ('. $team['items']['toss']['sum'] .')</small></div>';
                        $tossData .= '</div>';
                    }
                    if ($matchData || $tossData) {
                        $data .= '<div class="progressWrapper"><div class="teamTitle"> <h4><a href="javascript:;">'. $team['name']  .'</a></h4> </div>';
                        // ANIMATED
                        $data .= '<div class="teamItems">'; 
                            $data .= '<div class="progressContainer">';
                                $data .= $teamNamesData;
                                $data .= $matchData;
                                $data .= $tossData;
                            $data .= '</div>';
                        $data .= '</div></div>';
                    }
                    $teamSI++;
                }
            }
            $script = "<script> jQuery(document).ready(function() {
                        jQuery('.skillbar').each(function(){
                        jQuery(this).find('.skillbar-bar').animate({
                            width:jQuery(this).attr('data-percent')
                        },5000);
                    });
                    })</script>";
            // PERTICIPATED PREDICTORS SLIDER
            if ($data) $data .= $script;
        }
        return $data;
    }
}