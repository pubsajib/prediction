<?php 
// $ratingType = all, match, toss, ...
function getRakingFor($ratingType='all', $tournamentID=false, $predictors='', $minItemToPredict=80, $itemGrace=10, $minParticipationRate=40) {
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
				$participated = $prediction['avg'][$ratingType]['participated'];
				$PMatch = $prediction['avg']['match']['rate'];
				$PToss = $prediction['avg']['toss']['rate'];
				$score = $prediction['avg'][$ratingType]['rate'];
				$criterias = [
					'UID'=>$predictor->ID, 
					'participated' => $participated,
					'minLifetimeParticipationRate' => $minParticipationRate, 
					'accuracy' => $score,
					'grace' => $minParticipationWithGrace,
				];
				$lifeTimeEvents = count(lifeTimePublished($criterias['UID']));
				if ($lifeTimeEvents) {
					$criterias['lifeTimePublishedEvents'] = $lifeTimeEvents;
					$criterias['lifeTimePublishedEventRate']  = number_format(($criterias['participated'] / $lifeTimeEvents) * 100, 2);
				} else {
					$criterias['lifeTimePublishedEvents'] = 0;
					$criterias['lifeTimePublishedEventRate'] = 0;
				}
				if ($participated) $isRankAble = isValidForRanking($criterias);
				$ranking[$predictor->ID]['id'] = $predictor->ID;
				$ranking[$predictor->ID]['eligible'] = $isRankAble;
				$ranking[$predictor->ID]['score'] = $score;
				$ranking[$predictor->ID]['matchAccuracy'] = $PMatch;
				$ranking[$predictor->ID]['tossAccuricy'] = $PToss;
				$ranking[$predictor->ID]['participated'] = $participated;
				$ranking[$predictor->ID]['lifeTimePublishedEvents'] = $criterias['lifeTimePublishedEvents'];
				$ranking[$predictor->ID]['lifeTimePublishedEventRate'] = $criterias['lifeTimePublishedEventRate'];
				$ranking[$predictor->ID]['minLifetimeParticipationRate'] = $criterias['minLifetimeParticipationRate'];

				$eligible_sort[] = $isRankAble;
				$accuracy_sort[] = $score;
				$matchParticipated_sort[] = $PMatch;
				$tossParticipated_sort[] = $PToss;
				$totalParticipated_sort[] = $participated;
			} else {
				$score = 0;
				$PMatch = 0;
				$PToss = 0;
				$participated = 0;
				$ranking[$predictor->ID]['id'] = $predictor->ID;
				$ranking[$predictor->ID]['eligible'] = 0;
				$ranking[$predictor->ID]['score'] = $score;
				$ranking[$predictor->ID]['matchAccuracy'] = $PMatch;
				$ranking[$predictor->ID]['tossAccuricy'] = $PToss;
				$ranking[$predictor->ID]['participated'] = $participated;
				$ranking[$predictor->ID]['lifeTimePublishedEvents'] = 0;
				$ranking[$predictor->ID]['lifeTimePublishedEventRate'] = 0;
				$ranking[$predictor->ID]['minLifetimeParticipationRate'] = 0;

				$eligible_sort[] = -9999;
				$accuracy_sort[] = $score;
				$matchParticipated_sort[] = $PMatch;
				$tossParticipated_sort[] = $PToss;
				$totalParticipated_sort[] = $participated;
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
		if (isset($eligible_sort) || isset($accuracy_sort) || isset($matchParticipated_sort) || isset($tossParticipated_sort) || isset($totalParticipated_sort)) {
			array_multisort(
				$eligible_sort, SORT_DESC, 
				$accuracy_sort, SORT_DESC, 
				$matchParticipated_sort, SORT_DESC, 
				$tossParticipated_sort, SORT_DESC, 
				$totalParticipated_sort, SORT_DESC, 
				$ranking
			);
		}
	}
	// 	PREDICTORS BY RANK
	if ($ranking) {
		$counter = 1;
		$rankingCount = count($ranking);
		if ($top3 > $rankingCount) $top3 = $rankingCount;
		if ($top10 > $rankingCount) $top10 = $rankingCount;
		foreach ($ranking as $userID => $rank) {
			if ($rank['eligible'] >= 95) {
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
function isValidForRanking($criterias) {
	$lifeTimeParticipationCriteria = $criterias['minLifetimeParticipationRate'] > $criterias['lifeTimePublishedEventRate'];
	if ($criterias['participated'] < 10) return 10;
	else if ($criterias['participated'] < 20) return 20;
	else if ($criterias['participated'] < 30) return 30;
	else if ($criterias['participated'] < 40) return 40;
	else if ($criterias['participated'] < 50) return 50;
	else if ($criterias['participated'] < 60) return 60;
	else if ($criterias['participated'] < 70) return 70;
	else if ($criterias['accuracy'] < 50) return 80;

	// ACTUAL RANKING BEGAIN
	else if ($criterias['grace'] > $criterias['participated']) return 85;
	else if ($lifeTimeParticipationCriteria) {
		// if ($criterias['participated'] < 80) return 95;
		return 90;
	}
	else return 100;
}
function userRankingStatusFor($userID, $ranks) {
	$rank = ['class'=>'', 'num'=>0];
	if ($userID && !empty($ranks)) {
		$top10 = array_search($userID, $ranks['top10']);
		$top3 = array_search($userID, $ranks['top3']);
		if (is_int($top3)) { $rank['class'] = ' ranked top3 rank_'. ($top3 + 1); $rank['num'] = $top3 + 1; }
		else if (is_int($top10)) {$rank['class'] = ' ranked top10 rank_'. ($top10 + 1); $rank['num'] = $top10 + 1; }
	}
	return $rank;
}

function isValidForRankingOld($criterias) {
	// echo '<br>'. $criterias['UID'] .' '.$criterias['rate'] .' < '. $criterias['minRate'];
	if (10 > $criterias['participated']) return -8;
	else if (20 > $criterias['participated']) return -7;
	else if (30 > $criterias['participated']) return -6;
	else if (40 > $criterias['participated']) return -5;
	else if (50 > $criterias['participated']) return -4;
	else if (60 > $criterias['participated']) return -3;
	else if (70 > $criterias['participated']) return -2;
	else if (($criterias['grace'] > $criterias['participated']) || ($criterias['rate'] < 50) ) return -1;
	else if ($criterias['minParticipation'] > $criterias['participated']) return 0;
	else if ($criterias['rate'] < $criterias['minRate']) return 0;
	else {
		$lifeTimeEvents = count(lifeTimePublished($criterias['UID']));
		$rating  = ($criterias['participated']/$lifeTimeEvents) * 100;
		if ($criterias['minParticipation'] > $rating) return 0;
		// return $rating;
		return 1;
	}
}
function lifeTimePublished($userID, $type=false) {
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
function totalPublished($type=false) {
	$published = [];
	$query = array(
        'post_type' => 'event',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
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
function getPredictorsList() {
	$users = [];
	$predictors = get_users( 'role=predictor' );
	if ($predictors) {
		foreach ($predictors as $predictor) {
			$users[$predictor->ID] = $predictor;
		}
	}
	return $users;
}