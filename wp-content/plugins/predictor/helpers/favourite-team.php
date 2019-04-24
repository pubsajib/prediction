<?php 
function getFavoriteTeamForThisEvent($meta, $answers, $eventID, $showTab=false, $logoSlider=false) : string {
    $data       = '';
    $data2      = '';
    $teams      = [];
    $ansUsers   = [];
    
    $event = get_post($eventID);
    $eventLink = esc_url( site_url('/event/'. $event->post_name));

    $ansCount = [];
    if ($answers) {
        foreach ($answers as $uID => $ans) {
            $user = get_user_by( 'id', $uID );
            if ($user) { // check for deleted users
                $UP = predictionsOf($uID);
                if (!empty($UP['avg'])) {
                    $mAVG = !empty($UP['avg']['match']['rate']) ? $UP['avg']['match']['rate'] : 0;
                    $tAVG = !empty($UP['avg']['toss']['rate']) ? $UP['avg']['toss']['rate'] : 0;
                }
                $predictorData = [
                    'uID'       => $uID, 
                    'username'  => $user->data->user_login ?? 'N/A', 
                    'nickname'  => get_the_author_meta('nickname',$uID) ?: 'N/A', 
                    'img'       => get_avatar_url( $user->user_email) ?: 'N/A',
                    'mAVG'      => $mAVG,
                    'tAVG'      => $tAVG,
                ];
                $predictors[$uID] = $predictorData + ['match' => '', 'toss' => ''];
                if ($ans) {
                    foreach ($ans as $key => $value) {
                        $ansCount[$key][] = $predictorData + ['team' => $value];
                    }
                }
            }
        }
    }
    // $data .= help($ans, false);

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
                            if ($ansCount[$ansID]) {
                                foreach ($ansCount[$ansID] as $ansByOption) {
                                    $tmpUserData = $ansByOption['uID'] .'###'. $ansByOption['username'] .'###'. $ansByOption['nickname'] .'###'. $ansByOption['img'] .'###'. $ansByOption['mAVG'] .'###'. $ansByOption['tAVG'];
                                    if ($ansByOption['team'] == $itemTeam) $answerCountByOption[] =  $tmpUserData;
                                    $teams[$options]['predictors'][$ansByOption['uID']][$option['id']] = $answers[$ansByOption['uID']][$ansID];

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
    // $data .= help($teams['team_perth_scorchers_vs_melbourne_stars']['items']['toss']['perth_scorchers-supporters'], false);
    // $data .= help($teams['team_perth_scorchers_vs_melbourne_stars']['predictors'][502], false);

    if ($teams) {
        $teamSI = 1;
        $firstTeamColor = '#9afff8';
        $secondTeamColor = '#d2ffc2';
        // $data .= '<div class="progressWrapper">';
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
                        $data .= '<div class="teamTitle"> <h4><a href="javascript:;">'. $team['name']  .'</a></h4> </div>';
                        // TAB
                        if ($showTab) {
                            $data .= '<div id="favouriteTeamName-'. $uniqueID .'" class="tabs tabs_default favouriteTeamName">';
                                $data .= '<ul class="horizontal style-two">';
                                    $data .= '<li class="proli one"><a href="#team1">'. $firstTeamName .'</a></li>';
                                    $data .= '<li class="proli two"><a href="#team2">'. $secondTeamName .'</a></li>';
                                $data .= '</ul>';
                                $data .= '<div id="team1">';
                                    $data .= '<div class="favouriteTeambg">';
                                        $data .= '<div id="favouriteTeam'. $uniqueID .'1" class="tabs tabs_default">';
                                            $data .= '<ul class="horizontal">';
                                                $data .= '<li class="proli"><a href="#match">Match</a></li>';
                                                $data .= '<li class="proli"><a href="#toss">Toss</a></li>';
                                            $data .= '</ul>';
                                            $data .= '<div id="match">'. getFavoriteTeamForThisEventSliderFor($firstTeamMatchSupporters) .'</div>';
                                            $data .= '<div id="toss">'. getFavoriteTeamForThisEventSliderFor($firstTeamTossSupporters) .'</div>';
                                        $data .= '</div>';
                                    $data .= '</div>';
                                $data .= '</div>';
                                $data .= '<div id="team2">';
                                    $data .= '<div class="favouriteTeambg">';
                                        $data .= '<div id="favouriteTeam'. $uniqueID .'2" class="tabs tabs_default">';
                                            $data .= '<ul class="horizontal">';
                                                $data .= '<li class="proli"><a href="#match">Match</a></li>';
                                                $data .= '<li class="proli"><a href="#toss">Toss</a></li>';
                                            $data .= '</ul>';
                                            $data .= '<div id="match">'. getFavoriteTeamForThisEventSliderFor($secondTeamMatchSupporters) .'</div>';
                                            $data .= '<div id="toss">'. getFavoriteTeamForThisEventSliderFor($secondTeamTossSupporters) .'</div>';
                                        $data .= '</div>';
                                    $data .= '</div>';
                                $data .= '</div>';
                            $data .= '</div>';
                            $data .= '<div class="block-btn"><a href="'. $eventLink .'" target="_blank" class="fusion-button button-default button-small predict">view expert predictions</a></div>';
                        }
                        // ANIMATED
                        $data .= '<div class="teamItems">'; 
                            $data .= '<div class="progressContainer">';
                                // $data .= '<h4 style="text-align:center;">'. $team['name'] .'</h4>';
                                $data .= $teamNamesData;
                                $data .= $matchData;
                                $data .= $tossData;
                            $data .= '</div>';
                        $data .= '</div>';
                        $data .= '<div class="block-btn viewEventBtn"><a href="'. $eventLink .'" target="_blank" class="fusion-button button-default button-small predict">view expert predictions</a></div>';
                    }
                    // $data .= help($team["predictors"], false);
                    $ansUsersData = '';
                    if ($logoSlider && !empty($team['predictors']) && $data) $data .= getFavoriteEventAllSupportersSlider($team["predictors"], $eventLink);
                    $teamSI++;
                }
            }
            // PERTICIPATED PREDICTORS SLIDER
            if ($data) $data2 .= '<div class="progressWrapper">'. $data . $ansUsersData .'</div>';
        // $data .= '</div>';
    }
    // $data .= help($answers, false);
    return $data2;
}
function getFavoriteTeamForThisEventSliderFor($supporters) {
    $data = '';
    if (!$supporters) $data .= '<div class="item">No supporter</div>';
    else {
        $data .= '<div class="owl-carousel favouriteTeam owl-theme">';
            foreach ($supporters as $supporter) {
                $data .= '<div class="item"><div class="profile-info">';
                    $tmp = explode('###', $supporter);
                    $data .= '<p><img src="'. $tmp[3] .'"></p>';
                    $data .= '<div class="info">'. $tmp[2] .'<br><strong>('. $tmp[4] .'%)</strong></div>';
                $data .= '</div></div>';
            }
        $data .= '</div>';
    }
    return $data;
}
function getFavoriteEventAllSupportersSlider($supporters, $eventLink=0, $class='') {
    $data = '';
    if (!$supporters) $data .= '<div class="item">No supporter</div>';
    else {
        $data .= '<div class="owl-carousel owl-theme eventSupperters">';
            foreach ($supporters as $supporter) {
                $overAll = ['all'=>0, 'match'=>0, 'toss'=>0]; 
                $ipl = ['all'=>0, 'match'=>0, 'toss'=>0];
                $UP = predictionsOf($supporter['uID']);
                if (!empty($UP['avg'])) {
					$overAll['all'] = $UP['avg']['all']['rate'];
                    $overAll['match'] = $UP['avg']['match']['rate'];
                    $overAll['toss'] = $UP['avg']['toss']['rate'];
                }
                $tournament = tournamentData($supporter['uID'], 313);
                if (!empty($tournament['avg'])) {
					$ipl['all'] = $tournament['avg']['all']['rate'];
                    $ipl['match'] = $tournament['avg']['match']['rate'];
                    $ipl['toss'] = $tournament['avg']['toss']['rate'];
                }
                if ($eventLink) {
                    $matchToss = ['match' => $match, 'toss' => $toss];
                    $profileLink = $eventLink .'/#'. $supporter['uID'];
                    $nickname = !empty($supporter['nickname']) ? $supporter['nickname'] : '';
                    $data .= '<div class="item"><div class="profile-info supportedMatchTossPopup" overall=\''. json_encode($overAll) .'\' ipl=\''. json_encode($ipl) .'\' event=\''. $profileLink .'\' nickname=\''. $nickname .'\'>';
                        $data .= '<p><img src="'. $supporter['img'] .'"></p>';
                    $data .= '</div></div>';
                }
            }
        $data .= '</div>';
    }
    return $data;
}