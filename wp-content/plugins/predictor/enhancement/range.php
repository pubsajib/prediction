<?php 
// [range id=123]
add_shortcode( 'range', array( 'Range', 'render' ) );
class Range {
    public static function render($attr) {
        $attr = shortcode_atts( ['id' => 1], $attr, 'range' );
        $html  = '';
        $eventID = $attr['id'];
        if (get_post_type($eventID) != 'event') $html .= 'May be your given EVENT ID is wrong';
        else {
            $html .= '<div class="progressContainer" style="position:relative">';
                $html .= '<span class="rangeRefreshBtn fusion-button button-default button-small" event="'.$eventID.'">Reload</span>';
                $html .= '<div id="progressWrapper_'.$eventID.'" class="progressWrapper">';
                    $html .= self::contentHTML($eventID);
                $html .= '</div>';
            $html .= '</div>';
        }
        return $html;
    }
    static function contentHTML($eventID) {
        $html  = '';
        $ans   = (array) get_post_meta($eventID, 'event_ans', true);
        $meta    = get_post_meta($eventID, 'event_ops', true);
        if (isset($ans[0])) unset($ans[0]);
        // GIVEN PREDICTIONS
        $html .= self::getFavoriteTeamForThisEvent($answers, $meta, $eventID, true);
        // $html .= help(getAvatarURL('<img alt="" src="//www.cricdiction.com/wp-content/uploads/wpforo/avatars/sahilrobin_570.jpg" class="avatar avatar-96 photo" height="96" width="96">'), false);
        //$html .= '<p><img style="border-radius:50%" src="//www.cricdiction.com/wp-content/uploads/wpforo/avatars/sahilrobin_570.jpg"></p>';
        return $html;
    }
    static function getFavoriteTeamForThisEvent($answers, $meta, $eventID, $showTab=false) : string {
        $data       = '';
        $eventLink = get_permalink($eventID);
        $users = self::getAnsweredUsers($answers);
        // $data .= help($users, false);
        $data .= self::teams($eventID, $meta);
        $data .= self::getWinningSummeryForThisEvent($answers, $meta, $eventID, $users, $eventLink);
        $data .= self::getFavoriteEventAllSupportersSlider($users, $eventLink);
        $data .= '<div class="text-center viewEventBtn">';
        $data .= '<a href="'. $eventLink .'" target="_blank" class="fusion-button button-flat fusion-button-pill button-small button-default predict">view expert predictions</a>';
        $data .= '</div>';
        
        return $data;
    }
    static function getFavoriteEventAllSupportersSlider($supporters, $eventLink=false) {
        $data = '';
        if (!$supporters) $data = '<p class="noItem">No one predicted this event yet. If you are an expert you may <a href="'. site_url('log-in') .'">Login</a> here.</p>'; 
        else {
            $data .= '<div class="owl-carousel owl-theme eventSupperters">';
                foreach ($supporters as $supporter) {
                    if ($eventLink) {
                        $profileLink = $eventLink .'#'. $supporter['id'];
                        $data .= '<div class="item">';
                            $data .= '<a href="'. $profileLink .'" target="_blank">';
                                $data .= '<p>'. $supporter['avatar'] .'</p>';
                                // $data .= '<p><img style="border-radius:50%" src="'. $supporter['avatar'] .'"></p>';
                                $data .= '<p style="text-align:center;">'. $supporter['name'] .'</p>';
                            $data .= '</a>';
                        $data .= '</div>';
                    }
                }
            $data .= '</div>';
        }
        return $data;
    }
    static function getAnsweredUsers($ans) {
        global $wpdb;
        $predictors = [];
        $userIDs = implode(',', array_keys($ans));
        $users = $wpdb->get_results( "SELECT id, user_login, user_email, display_name AS name FROM $wpdb->users WHERE ID IN ($userIDs)", ARRAY_A);

        if ($users) {
            foreach ($users as $user) {
                $predictors[$user['id']] = array_merge((array)$user, ['avatar'=>get_avatar($user['user_email'])]);
                // $predictors[$user['id']] = array_merge((array)$user, ['avatar'=>get_avatar($user['user_email'])]);
            }
        }
        return $predictors;
    }
    static function teams($eventID, $meta) {
        $data  = '';
        if (!empty($meta['teams'])) {
            foreach ($meta['teams'] as $team) $data .= !empty($team['name']) ? $team['name'] .', ' : '';
        }
        // if ($data) return '<h2 class="eventTitles text-left">'. rtrim($data, ', ') .'</h2>';
        if ($data) return '<h2 class="eventTitles">cricdiction\'s pick of '.$team['name'].'</h2>';
        else return false;
    }
    static function getWinningSummeryForThisEvent($answers, $meta, $eventID, $users, $eventLink) : string {
        $data       = '';
        $teams      = [];
        
        $ansCount = [];
        if ($answers) {
            foreach ($answers as $uID => $ans) {
                if (!empty($users[$uID])) { // check for deleted users
                    $predictorData = $users[$uID];
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
                    $teams[$options]['end'] = $team['end'];
                    if (!isset($teams[$options]['teams'])) {
                        if ($weights = $meta[$options][1]['weight']) {
                            foreach ($weights as $weight) { if ($weight['name']) $teams[$options]['teams'][] = $weight['name']; }
                        }
                    }
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
                                        if ($ansByOption['team'] == $itemTeam) $answerCountByOption[] =  $ansByOption;
                                    }
                                }
                                if (count($ansCount[$ansID])) $itemPercentage = (count($answerCountByOption) / count($ansCount[$ansID])) * 100;
                                else $itemPercentage = 0;
                                
                                $teams[$options]['items'][$option['id']][predictor_id_from_string($itemTeam)] = $itemPercentage;
                            }
                        }
                        $teams[$options]['items'][$option['id']]['name'] = $option['title'];
                    }
                }
            }
        }

        if ($teams) {
            foreach ($teams as $teamID => $team) {
                if (isset($team['teams']) && strtotime($team['end']) < time()) {
                    // $data .= help($team);
                    $matchData = '';
                    $firstTeamName = $team['teams'][0];
                    $secondTeamName = $team['teams'][1];
                    $firstTeamID = predictor_id_from_string($team['teams'][0]);
                    $secondTeamID = predictor_id_from_string($team['teams'][1]);
                    $firstTeamMatchSupporters = !empty($team['items']['match'][$firstTeamID .'-supporters']) ? $team['items']['match'][$firstTeamID .'-supporters'] : [];
                    $secondTeamMatchSupporters = !empty($team['items']['match'][$secondTeamID .'-supporters']) ? $team['items']['match'][$secondTeamID .'-supporters'] : [];
                    if (isset($team['items']['match']) && (!empty($team['items']['match'][$firstTeamID]) || !empty($team['items']['match'][$secondTeamID]))) {
                        $firstValue = $team['items']['match'][$firstTeamID];
                        $secondValue = $team['items']['match'][$secondTeamID];
                        if ($firstValue > $secondValue) {
                            $winningTeam = $firstTeamName;
                            $winningScore = $firstValue;
                        } else if($firstValue < $secondValue) {
                            $winningTeam = $secondTeamName;
                            $winningScore = $secondValue;
                        } else {
                            $winningTeam = $firstTeamName.' and '.$secondTeamName;
                            $winningScore = 'same or 50';
                        }

                        $matchData .= '<div class="range-result">';
                            // $matchData .= '<div class="title">cricdiction\'s pick of '.$team['name'].'</div>';
                            $matchData .= '<div class="item one">';
                                    $matchData .= '<div class="result"><span class="name">'.$winningTeam.'</span><br>Win The Match</div>';
                                $matchData .= '</div>';
                            
                            $matchData .= '<div class="item two">';
                                    $matchData .= '<div class="percentage"><span class="chances">'. number_format($winningScore, 2) .'%</span><br>Winning Chances</div>';
                                $matchData .= '</div>';
                            $tradings = self::tradings($eventID, $teamID);
                            $matchData .= '
                                <div class="item three">
                                    <div class="result trading">Trading Chances<br><span class="name"><div class="halfs"><span class="'. $tradings['yes'] .'">'. $tradings['yesNumber'] .'%</span><span class="'. $tradings['yesTxt'] .'">YES!!<span></div><div class="halfs"><span class="'. $tradings['no'] .'">'. $tradings['noNumber'] .'%</span><span class="'. $tradings['noTxt'] .'">NO!!<span></div></div>
                                </div>
                            ';
                        $matchData .= '</div>';
                        $matchData .= '<h2 class="eventTitles">'.count($users).' EXPERTS</strong> PREDICTED '.$team['name'].'</h2>';
                    }
                    if ($matchData) $data .= $matchData;
                } else {
                    $data .= '<div class="range-results">';
                        $data .= '<div class="title">cricdiction\'s pick</div>';
                        $data .= '<div class="summeryTime" id="'. $teamID .'_end">'. date('Y-m-d H:i:s', strtotime($team['end'])) .'</div>';
                        $data .= '<div style="margin-top: 15px;color:#fff;font-size: 15px;"><strong>'.count($users).' EXPERTS</strong> PREDICTED<strong><br>'.$team['name'].'</strong></div>';
                            $data .= '<div class="text-center viewEventBtn">';
                            $data .= '<a href="'. $eventLink .'" target="_blank" class="fusion-button button-flat fusion-button-pill button-small button-default predict">CLICK HERE TO VIEW THEIR PREDICTION</a>';
                            if (!empty($team['live'])) $data .= '<a href="'. $team['live'] .'" target="_blank" class="fusion-button button-flat fusion-button-pill button-small button-default predict">CLICK HERE TO VIEW THEIR PREDICTION</a>';
                            $data .= '</div>';
                    $data .= '</div>';
                }
            }
        }
        return $data;
    }
    static function tradings($eventID, $teamID) {
        $data = ['yes'=>'equal', 'no'=>'equal', 'yesTxt'=>'equalTxt', 'noTxt'=>'equalTxt', 'yesNumber'=>0, 'noNumber'=>0];
        $tradings   = get_post_meta($eventID, 'trading', true);
        if ($tradings) {
            $yesCounter = $noCounter = 0;
            if ($tradings) {
                foreach ($tradings as $trading) {
                    if ($trading) {
                        foreach ($trading as $teamID => $team) {
                            if ($team == 'yes') $yesCounter++;
                            else if ($team == 'no') $noCounter++;
                        }
                    }
                }
            }
            if ($yesCounter == $noCounter) {
                $data['yes'] = 'equal';
                $data['no'] = 'equal';
                $data['yesTxt'] = 'equalTxt';
                $data['noTxt'] = 'equalTxt';
            } else if ($yesCounter > $noCounter) {
                $data['yes'] = 'success';
                $data['no'] = 'danger';
                $data['yesTxt'] = 'successTxt';
                $data['noTxt'] = 'dangerTxt';
            } else {
                $data['yes'] = 'danger';
                $data['no'] = 'success';
                $data['yesTxt'] = 'dangerTxt';
                $data['noTxt'] = 'successTxt';
            }
            $total = $yesCounter + $noCounter;
            $data['yesNumber']  = ($yesCounter / $total) * 100;
            $data['noNumber']  = ($noCounter / $total) * 100;
        }
        return $data;
    }
}