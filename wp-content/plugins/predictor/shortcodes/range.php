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
        if (isset($ans[0])) unset($ans[0]);
        // GIVEN PREDICTIONS
        $html .= self::getFavoriteTeamForThisEvent($ans, $eventID, true);
        // $html .= help(getAvatarURL('<img alt="" src="//www.cricdiction.com/wp-content/uploads/wpforo/avatars/sahilrobin_570.jpg" class="avatar avatar-96 photo" height="96" width="96">'), false);
        //$html .= '<p><img style="border-radius:50%" src="//www.cricdiction.com/wp-content/uploads/wpforo/avatars/sahilrobin_570.jpg"></p>';
        return $html;
    }
    static function getFavoriteTeamForThisEvent($answers, $eventID, $showTab=false) : string {
        $data       = '';
        $eventLink = get_permalink($eventID);
        $users = self::getAnsweredUsers($answers);
        // $data .= help($users, false);
        $data .= self::teams($eventID);
        $data .= self::getFavoriteEventAllSupportersSlider($users, $eventLink);
        $data .= '<div class="text-center viewEventBtn"><a href="'. $eventLink .'" target="_blank" class="fusion-button button-flat fusion-button-pill button-small button-default predict">view expert predictions</a></div>';
        $data .= self::getWinningSummeryForThisEvent($answers, $eventID, $users);
        return $data;
    }
    static function getFavoriteEventAllSupportersSlider($supporters, $eventLink=false) {
        $data = '';
        if ($supporters) {
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
    static function teams($eventID) {
        $data  = '';
        $meta  = (array) get_post_meta($eventID, 'event_ops', true);
        if (!empty($meta['teams'])) {
            foreach ($meta['teams'] as $team) $data .= !empty($team['name']) ? $team['name'] .', ' : '';
        }
        if ($data) return '<h2 class="eventTitles text-left">'. rtrim($data, ', ') .'</h2>';
        else return false;
    }
    static function getWinningSummeryForThisEvent($answers, $eventID, $users) : string {
        $data       = '';
        $teams      = [];
        $meta    = get_post_meta($eventID, 'event_ops', true);
        
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
                            $matchData .= '<div class="title">cricdiction\'s pick</div>';
                            $matchData .= '<div class="result"><span class="name">'.$winningTeam.'</span> to win the match</div>';
                            $matchData .= '<div class="percentage">'. number_format($winningScore, 2) .'% winning chances</div>';
                        $matchData .= '</div>';
                    }
                    if ($matchData) $data .= $matchData;
                } else {
                    $data .= '<div class="range-result">';
                        $data .= '<div class="title">cricdiction\'s pick</div>';
                        $data .= '<div class="summeryTime" id="'. $teamID .'_end">'. date('Y-m-d H:i:s', strtotime($team['end'])) .'</div>';
                    $data .= '</div>';
                }
            }
        }
        return $data;
    }
}