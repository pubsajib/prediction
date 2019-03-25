<?php 
function getRanksFor($userID, $tournamentID, $tournamentName='') {
    // $userID = 521;
    $html = '';
    if (!$tournamentName) $tournamentName = 'Tournament-'. $tournamentID;
    $predictors = getPredictorsList();
    $match = new classMatchTop;
    $match = getUserRankFor($userID, $match->render($predictors));
    $toss = new classTossTop;
    $toss = getUserRankFor($userID, $toss->render($predictors));
    $all = new classTop;
    $all = getUserRankFor($userID, $all->render($predictors));
    $tournament = '';
    if ($tournamentID) {
        $tournament = new classTournamentTop;
        $tournament = getUserRankFor($userID, $tournament->render($predictors, $tournamentID));
    }
    $html .= '<table class="table ans-ranking">';
        $html .= '<tr><th colspan="4">Rank</th></tr>';
        $html .= '<tr> 
			<td>Match</td> 
			<td>Toss</td> 
			<td>Overall</td> 
			<td>'. $tournamentName .'</td> 
		</tr>';
        $html .= '<tr> 
			<td><span>'. $match .'</span></td> 
			<td><span>'. $toss .'</span></td> 
			<td><span>'. $all .'</span></td> 
			<td><span>'. $tournament .'</span></td> 
		</tr>';
    $html .= '</table>';
    return $html;
}
function getUserRankFor($UID, $ranks) {
    if ($ranks) {
        foreach ($ranks as $rankID => $rank) {
            if ($rank['id'] == $UID) {
                return $rankID + 1;
            }
        }
    }
    return false;
}
class classMatchTop {
    public function render($predictors) {
        $ranking = getRakingFor('match', false, $predictors);
        return $ranking['all'];
    }
    public static function getRakingFor($ratingType='match', $tournamentID=false, $predictors='', $minItemToPredict=100, $itemGrace=10, $minParticipationRate=75) {
        $top3 = 3;
        $top10 = 10;
        $ranking = [];
        $users = [];
        $rankedUsers = ['top3'=>[], 'top10'=>[]];
        $minParticipationWithGrace = $minItemToPredict - $itemGrace;
        // RANKING FOR ALL USERS
        if (!$predictors) $predictors = get_users('role=predictor');
        if ($predictors) {
            foreach ($predictors as $predictor) {
                // LIFE TIME DATA
                if ($tournamentID) $prediction = tournamentData($predictor->ID, $tournamentID);
                else $prediction = predictionsOf($predictor->ID);
                // help($prediction['avg']['all']);
                $isRankAble = false;
                if (!empty($prediction['avg'])) {
                    $participated = $prediction['avg']['all']['participated'];
                    $score = $prediction['avg']['all']['rate'];
                    $rscore = $prediction['avg'][$ratingType]['rate'];
                    $rparticipated = $prediction['avg'][$ratingType]['participated'];
                    
                    $criterias = [
                        'UID'=>$predictor->ID, 
                        'participated' => $rparticipated,
                        'minLifetimeParticipationRate' => $minParticipationRate, 
                        'accuracy' => $rscore,
                        'grace' => $minParticipationWithGrace,
                    ];
                    $lifeTimeEvents = count(lifeTimePublished($criterias['UID'], $ratingType));
                    if ($lifeTimeEvents) {
                        $criterias['lifeTimePublishedEvents'] = $lifeTimeEvents;
                        $criterias['lifeTimePublishedEventRate']  = number_format(($criterias['participated'] / $lifeTimeEvents) * 100, 2);
                    } else {
                        $criterias['lifeTimePublishedEvents'] = 0;
                        $criterias['lifeTimePublishedEventRate'] = 0;
                    }
                    if ($participated) $isRankAble = self::isValidForRanking($criterias);
                    $ranking[$predictor->ID]['id'] = $predictor->ID;
                    $ranking[$predictor->ID]['eligible'] = $isRankAble;
                    $ranking[$predictor->ID]['rscore'] = $rscore;
                    $ranking[$predictor->ID]['rparticipated'] = $rparticipated;
                    $ranking[$predictor->ID]['lifeTimePublishedEvents'] = $criterias['lifeTimePublishedEvents'];
                    $ranking[$predictor->ID]['lifeTimePublishedEventRate'] = $criterias['lifeTimePublishedEventRate'];
                    $ranking[$predictor->ID]['minLifetimeParticipationRate'] = $criterias['minLifetimeParticipationRate'];
                    $ranking[$predictor->ID]['score'] = $score;
                    $ranking[$predictor->ID]['participated'] = $participated;

                    $eligible_sort[] = $isRankAble;
                    $accuracy_sort[] = $rscore;
                    $totalParticipated_sort[] = $rparticipated;
                } else {
                    $rscore = 0;
                    $rparticipated = 0;
                    $ranking[$predictor->ID]['id'] = $predictor->ID;
                    $ranking[$predictor->ID]['eligible'] = 0;
                    $ranking[$predictor->ID]['rscore'] = $rscore;
                    $ranking[$predictor->ID]['rparticipated'] = $rparticipated;
                    $ranking[$predictor->ID]['lifeTimePublishedEvents'] = 0;
                    $ranking[$predictor->ID]['lifeTimePublishedEventRate'] = 0;
                    $ranking[$predictor->ID]['minLifetimeParticipationRate'] = 0;
                    $ranking[$predictor->ID]['score'] = 0;
                    $ranking[$predictor->ID]['participated'] = 0;

                    $eligible_sort[] = -9999;
                    $accuracy_sort[] = $rscore;
                    $totalParticipated_sort[] = $rparticipated;
                }
                $users[$predictor->ID] = $predictor->data;
            }
            if (isset($eligible_sort) || isset($accuracy_sort) || isset($totalParticipated_sort)) {
                array_multisort(
                    $eligible_sort, SORT_DESC, 
                    $accuracy_sort, SORT_DESC,
                    $totalParticipated_sort, SORT_DESC,  
                    $ranking
                );
            }
        }
        //  PREDICTORS BY RANK
        if ($ranking) {
            $counter = 1;
            $rankingCount = count($ranking);
            if ($top3 > $rankingCount) $top3 = $rankingCount;
            if ($top10 > $rankingCount) $top10 = $rankingCount;
            foreach ($ranking as $userID => $rank) {
                if ($rank['eligible'] > 80) {
                    if ($counter > $top10) break;
                    if ($rank['participated'] >= $minParticipationWithGrace ) {
                        if ($counter <= $top3) $rankedUsers['top3'][] = $rank['id'];
                        $rankedUsers['top10'][] = $rank['id'];
                    }
                    // if ($counter <= $top3) $rankedUsers['top3'][$userID] = $users[$userID];
                    // $rankedUsers['top10'][$userID] = $users[$userID];
                    $counter++;
                }
            }
        }
        $rankedUsers['all'] = $ranking;
        // help($rankedUsers);
        return $rankedUsers;
    }
    public static function isValidForRanking($criterias) {
        $lifeTimeParticipationCriteria = $criterias['minLifetimeParticipationRate'] > $criterias['lifeTimePublishedEventRate'];
        // $lifeTimeParticipationCriteria = 1;
        if ($criterias['participated'] < 10) return 10;
        else if ($criterias['participated'] < 20) return 20;
        else if ($criterias['participated'] < 30) return 30;
        else if ($criterias['participated'] < 40) return 40;
        else if ($criterias['participated'] < 50) return 50;
        else if ($criterias['participated'] < 60) return 60;
        else if ($criterias['participated'] < 70) return 70;
        else if ($criterias['participated'] < 100) return 75;
        else if ($criterias['accuracy'] < 50) return 80;

        // ACTUAL RANKING BEGAIN
        else if ($criterias['grace'] > $criterias['participated']) return 85;
        else if ($lifeTimeParticipationCriteria) {
            // if ($criterias['participated'] < 80) return 95;
            return 90;
        }
        else return 100;
    }
}
class classTossTop {
    public static function render($predictors) {
        $ranking = getRakingFor('toss', false, $predictors);
        return $ranking['all'];
    }
    public static function getRakingFor($ratingType, $tournamentID=false, $predictors='', $minItemToPredict=100, $itemGrace=10, $minParticipationRate=50) {
        $top3 = 3;
        $top10 = 10;
        $ranking = [];
        $users = [];
        $rankedUsers = ['top3'=>[], 'top10'=>[]];
        $minParticipationWithGrace = $minItemToPredict - $itemGrace;
        // RANKING FOR ALL USERS
        if (!$predictors) $predictors = get_users('role=predictor');
        if ($predictors) {
            foreach ($predictors as $predictor) {
                // LIFE TIME DATA
                if ($tournamentID) $prediction = tournamentData($predictor->ID, $tournamentID);
                else $prediction = predictionsOf($predictor->ID);
                // help($prediction['avg']['all']);
                $isRankAble = false;
                if (!empty($prediction['avg'])) {
                    $participated = $prediction['avg']['all']['participated'];
                    $score = $prediction['avg']['all']['rate'];
                    $rscore = $prediction['avg'][$ratingType]['rate'];
                    $rparticipated = $prediction['avg'][$ratingType]['participated'];
                    
                    $criterias = [
                        'UID'=>$predictor->ID, 
                        'participated' => $rparticipated,
                        'minLifetimeParticipationRate' => $minParticipationRate, 
                        'accuracy' => $rscore,
                        'grace' => $minParticipationWithGrace,
                    ];
                    $lifeTimeEvents = count(lifeTimePublished($criterias['UID'], $ratingType));
                    if ($lifeTimeEvents) {
                        $criterias['lifeTimePublishedEvents'] = $lifeTimeEvents;
                        $criterias['lifeTimePublishedEventRate']  = number_format(($criterias['participated'] / $lifeTimeEvents) * 100, 2);
                    } else {
                        $criterias['lifeTimePublishedEvents'] = 0;
                        $criterias['lifeTimePublishedEventRate'] = 0;
                    }
                    if ($participated) $isRankAble = self::isValidForRanking($criterias);
                    $ranking[$predictor->ID]['id'] = $predictor->ID;
                    $ranking[$predictor->ID]['eligible'] = $isRankAble;
                    $ranking[$predictor->ID]['rscore'] = $rscore;
                    $ranking[$predictor->ID]['rparticipated'] = $rparticipated;
                    $ranking[$predictor->ID]['lifeTimePublishedEvents'] = $criterias['lifeTimePublishedEvents'];
                    $ranking[$predictor->ID]['lifeTimePublishedEventRate'] = $criterias['lifeTimePublishedEventRate'];
                    $ranking[$predictor->ID]['minLifetimeParticipationRate'] = $criterias['minLifetimeParticipationRate'];
                    $ranking[$predictor->ID]['score'] = $score;
                    $ranking[$predictor->ID]['participated'] = $participated;

                    $eligible_sort[] = $isRankAble;
                    $accuracy_sort[] = $rscore;
                    $totalParticipated_sort[] = $rparticipated;
                } else {
                    $rscore = 0;
                    $rparticipated = 0;
                    $ranking[$predictor->ID]['id'] = $predictor->ID;
                    $ranking[$predictor->ID]['eligible'] = 0;
                    $ranking[$predictor->ID]['rscore'] = $rscore;
                    $ranking[$predictor->ID]['rparticipated'] = $rparticipated;
                    $ranking[$predictor->ID]['lifeTimePublishedEvents'] = 0;
                    $ranking[$predictor->ID]['lifeTimePublishedEventRate'] = 0;
                    $ranking[$predictor->ID]['minLifetimeParticipationRate'] = 0;
                    $ranking[$predictor->ID]['score'] = 0;
                    $ranking[$predictor->ID]['participated'] = 0;

                    $eligible_sort[] = -9999;
                    $accuracy_sort[] = $rscore;
                    $totalParticipated_sort[] = $rparticipated;
                }
                $users[$predictor->ID] = $predictor->data;
            }
            // TEST DATA
            // $ranking[3]['id'] = 3;
            // $ranking[3]['score'] = 8235;
            // $ranking[3]['participated'] = 17;
            // $ranking[3]['match'] = 16;
            // $scoreData[] = 8235;
            // $PRType[] = 17;
            // $matchParticipated[] = 16;
            if (isset($eligible_sort) || isset($accuracy_sort) || isset($totalParticipated_sort)) {
                array_multisort(
                    $eligible_sort, SORT_DESC, 
                    $accuracy_sort, SORT_DESC,
                    $totalParticipated_sort, SORT_DESC,  
                    $ranking
                );
            }
        }
        //  PREDICTORS BY RANK
        if ($ranking) {
            $counter = 1;
            $rankingCount = count($ranking);
            if ($top3 > $rankingCount) $top3 = $rankingCount;
            if ($top10 > $rankingCount) $top10 = $rankingCount;
            foreach ($ranking as $userID => $rank) {
                if ($rank['eligible'] > 80) {
                    if ($counter > $top10) break;
                    if ($rank['participated'] >= $minParticipationWithGrace ) {
                        if ($counter <= $top3) $rankedUsers['top3'][] = $rank['id'];
                        $rankedUsers['top10'][] = $rank['id'];
                    }
                    // if ($counter <= $top3) $rankedUsers['top3'][$userID] = $users[$userID];
                    // $rankedUsers['top10'][$userID] = $users[$userID];
                    $counter++;
                }
            }
        }
        $rankedUsers['all'] = $ranking;
        // help($rankedUsers);
        return $rankedUsers;
    }
    public static function isValidForRanking($criterias) {
        $lifeTimeParticipationCriteria = $criterias['minLifetimeParticipationRate'] > $criterias['lifeTimePublishedEventRate'];
        // $lifeTimeParticipationCriteria = 1;
        if ($criterias['participated'] < 10) return 10;
        else if ($criterias['participated'] < 20) return 20;
        else if ($criterias['participated'] < 30) return 30;
        else if ($criterias['participated'] < 40) return 40;
        else if ($criterias['participated'] < 50) return 50;
        else if ($criterias['participated'] < 60) return 60;
        else if ($criterias['participated'] < 70) return 70;
        else if ($criterias['participated'] < 100) return 75;
        else if ($criterias['accuracy'] < 50) return 80;

        // ACTUAL RANKING BEGAIN
        else if ($criterias['grace'] > $criterias['participated']) return 85;
        else if ($lifeTimeParticipationCriteria) {
            // if ($criterias['participated'] < 80) return 95;
            return 90;
        }
        else return 100;
    }
}
class classTop {
    public static function render($predictors) {
        $ranking = getRakingFor('all', false, $predictors);
        return $ranking['all'];
    }
}
class classTournamentTop {
    public static function render($predictors, $tournament) {
        $ranking = getRakingFor('match', $tournament, $predictors);
        return $ranking['all'];
    }
    public static function getRakingFor($ratingType, $tournamentID=false, $predictors='', $minItems=10, $itemGrace=0, $engagement=30) {
        $top3 = 3;
        $top10 = 10;
        $ranking = [];
        $users = [];
        $rankedUsers = ['top3'=>[], 'top10'=>[]];
        // RANKING FOR ALL USERS
        if (!$predictors) $predictors = get_users('role=predictor');
        if ($predictors) {
            foreach ($predictors as $predictor) {
                // LIFE TIME DATA
                $overall    = predictionsOf($predictor->ID);
                $prediction = tournamentData($predictor->ID, $tournamentID);
                $isRankAble = false;
                $overallScore = $overall['avg'][$ratingType]['rate'] ?? 0;
                if (!empty($prediction['avg'])) {
                    $score = $prediction['avg'][$ratingType]['rate'];
                    $participated = $prediction['avg'][$ratingType]['participated'];
                    $criterias = [
                        'UID'=>$predictor->ID, 
                        'participated' => $participated,
                        'minItems' => $minItems, 
                        'minEngagement' => $engagement, 
                        'accuracy' => $score,
                        'grace' => $itemGrace,
                    ];
                    $lifeTimeEvents = count(self::lifeTimePublishedForTournament($criterias['UID'], $tournamentID, $ratingType));
                    if ($lifeTimeEvents) {
                        $criterias['lifeTimePublishedEvents'] = $lifeTimeEvents;
                        $criterias['engagement']  = number_format(($criterias['participated'] / $lifeTimeEvents) * 100, 2);
                    } else {
                        $criterias['lifeTimePublishedEvents'] = 0;
                        $criterias['engagement'] = 0;
                    }
                    if ($participated) $isRankAble = self::isValidForRanking($criterias);
                    $ranking[$predictor->ID]['id'] = $predictor->ID;
                    $ranking[$predictor->ID]['eligible'] = $isRankAble;
                    $ranking[$predictor->ID]['score'] = $score;
                    $ranking[$predictor->ID]['participated'] = $participated;
                    $ranking[$predictor->ID]['overall'] = $overallScore;
                    $ranking[$predictor->ID]['lifeTimePublishedEvents'] = $criterias['lifeTimePublishedEvents'];
                    $ranking[$predictor->ID]['engagement'] = $criterias['engagement'];
                    $ranking[$predictor->ID]['minEngagement'] = $criterias['minEngagement'];
                    $eligible_sort[] = $isRankAble;
                    $score_sort[] = $score;
                    $participation_sort[] = $participated;
                    $ovarall_sort[] = $overallScore;
                } else {
                    $score = 0;
                    $participated = 0;
                    $ranking[$predictor->ID]['id'] = $predictor->ID;
                    $ranking[$predictor->ID]['eligible'] = 0;
                    $ranking[$predictor->ID]['score'] = $score;
                    $ranking[$predictor->ID]['participated'] = $participated;
                    $ranking[$predictor->ID]['overall'] = $overallScore;
                    $ranking[$predictor->ID]['lifeTimePublishedEvents'] = 0;
                    $ranking[$predictor->ID]['engagement'] = 0;
                    $ranking[$predictor->ID]['minEngagement'] = 0;
                    $eligible_sort[] = -9999;
                    $score_sort[] = $score;
                    $participation_sort[] = $participated;
                    $ovarall_sort[] = $overallScore;
                }
                $users[$predictor->ID] = $predictor->data;
            }
            // add overall match accuracy
            if (isset($eligible_sort) || isset($score_sort) || isset($participation_sort) || isset($ovarall_sort)) {
                array_multisort(
                    $eligible_sort, SORT_DESC, 
                    $score_sort, SORT_DESC,
                    $participation_sort, SORT_DESC,  
                    $ovarall_sort, SORT_DESC,  
                    $ranking
                );
            }
        }
        //  PREDICTORS BY RANK
        if ($ranking) {
            $counter = 1;
            $rankingCount = count($ranking);
            if ($top3 > $rankingCount) $top3 = $rankingCount;
            if ($top10 > $rankingCount) $top10 = $rankingCount;
            foreach ($ranking as $userID => $rank) {
                if ($rank['eligible'] > 80) {
                    if ($counter > $top10) break;
                    if ($counter <= $top3) $rankedUsers['top3'][] = $rank['id'];
                    $rankedUsers['top10'][] = $rank['id'];
                    $counter++;
                }
            }
        }
        $rankedUsers['all'] = $ranking;
        return $rankedUsers;
    }
    public static function isValidForRanking($criterias) {
        $devider = 1;
        if ($criterias['minItems'] <= 10) $devider = 1;
        // else if ($criterias['minItems'] <= 20) $devider = 2;
        else if ($criterias['minItems'] <= 30) $devider = 2;
        else if ($criterias['minItems'] <= 50) $devider = 5;
        else if ($criterias['minItems'] <= 100) $devider = 10;
        else $devider = 15;
        $minItemsWithGrace = $criterias['minItems'] - $criterias['grace'];
        if($criterias['participated'] < $minItemsWithGrace) return $criterias['participated'] / $devider; // MIN ITEMS TO PREDICT WITH GRACE
        else if ($criterias['engagement'] < $criterias['minEngagement']) return 60; // ENGAGEMENT
        else if ($criterias['accuracy'] < 50) return 70; // ACCURACY SHOULD NOT BE LESS THAN 50%
        // ACTUAL RANKING BEGAIN
        else if (($criterias['participated']<=$criterias['minItems']) && ($criterias['participated']>$minItemsWithGrace)) return 85;
        else return 100;
    }
    public static function lifeTimePublishedForTournament($userID, $tournamentID, $type='all') {
        $published = [];
        $udata = get_userdata($userID);
        $registered = $udata->user_registered;
        // $registered = '2019-01-02 20:17:00'; // YYYY-mm-dd
        $query = array(
            'post_type' => 'event',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'date_query' => ['after' => $registered],
            'tax_query' => [['taxonomy' => 'tournament', 'field' => 'term_id', 'terms' => $tournamentID]],
        );
        $events = new WP_Query($query);
        // $events = $events->found_posts;
        $events = $events->posts;
        if ($events) {
            foreach ($events as $eventID) {
                $meta  = get_post_meta($eventID, 'event_ops', true);
                if (!empty($meta['teams'])) {
                    foreach ($meta['teams'] as $team) {
                        $ID     = predictor_id_from_string($team['name']);
                        $teamID = 'team_'. $ID;
                        if (!empty($meta[$teamID])) {
                            foreach ($meta[$teamID] as $option) {
                                $optionID = predictor_id_from_string($option['title']);
                                $defaultID = 'default_'. $ID .'_'. $optionID;
                                if (!@$meta[$defaultID.'_published']) continue;
                                if (!$type) {
                                    $published[] = ['event' => $eventID, 'team' => $team['name'], 'item' => $option['title'], 'type' => $option['id']];
                                } else if($type == $option['id']){
                                    $published[] = ['event' => $eventID, 'team' => $team['name'], 'item' => $option['title'], 'type' => $option['id']];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $published;
    }
}