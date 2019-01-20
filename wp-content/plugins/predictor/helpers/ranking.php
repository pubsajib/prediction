<?php 
// $ratingType = all, match, toss, ...
function getRakingFor($ratingType='all', $tournamentID=false, $predictors='', $minItemToPredict=50, $itemGrace=30, $minParticipationRate=50, $minRate=30) {
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
				$PRType = $prediction['avg'][$ratingType]['participated'];
				$PMatch = $prediction['avg']['match']['participated'];
				$score = $prediction['avg'][$ratingType]['rate'];
				$criterias = [
					'UID'=>$predictor->ID, 
					'participated' => $PRType, 
					'minItem' => $minItemToPredict, 
					'minParticipation' => $minParticipationRate, 
					'rate' => $score,
					'minRate' => $minRate,
					'grace' => $minParticipationWithGrace,
				];
				if ($PRType) $isRankAble = isValidForRanking($criterias);
				$ranking[$predictor->ID]['id'] = $predictor->ID;
				$ranking[$predictor->ID]['eligible'] = $isRankAble;
				$ranking[$predictor->ID]['score'] = $score;
				$ranking[$predictor->ID]['participated'] = $PRType;
				$ranking[$predictor->ID]['p_match'] = $PMatch;

				$eligible[] = $isRankAble;
				$scoreData[] = $score;
				$PRTypeData[] = $PRType;
				$participatedData[] = $PMatch;
				$matchData[] = $PMatch;
			} else {
				$PRType = 0;
				$PMatch = 0;
				$score = 0;
				$ranking[$predictor->ID]['id'] = $predictor->ID;
				$ranking[$predictor->ID]['eligible'] = 0;
				$ranking[$predictor->ID]['score'] = $score;
				$ranking[$predictor->ID]['participated'] = $PRType;
				$ranking[$predictor->ID]['p_match'] = $PMatch;

				$eligible[] = -100;
				$scoreData[] = $score;
				$PRTypeData[] = $PRType;
				$matchData[] = $PMatch;
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
		// $matchData[] = 16;
		if (isset($scoreData) || isset($PRType) || isset($matchData)) {
			array_multisort($eligible, SORT_DESC, $scoreData, SORT_DESC, $PRTypeData, SORT_DESC, $matchData, SORT_DESC, $ranking);
		}
	}
	// 	PREDICTORS BY RANK
	if ($ranking) {
		$counter = 1;
		$rankingCount = count($ranking);
		if ($top3 > $rankingCount) $top3 = $rankingCount;
		if ($top10 > $rankingCount) $top10 = $rankingCount;
		foreach ($ranking as $userID => $rank) {
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
	$rankedUsers['all'] = $ranking;
		// help($rankedUsers);
	return $rankedUsers;
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

function isValidForRanking($criterias) {
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
function lifeTimePublished($userID) {
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
		                    $published[] = [
		                    	'event' 	=> $eventID, 
		                    	'team' 		=> $team['name'], 
		                    	'item' 		=> $option['title']
		                    ];
            			}
            		}
        		}
        	}
		}
	}
    return $published;
}
function totalPublished() {
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
		                    $published[] = [
		                    	'event' 	=> $eventID, 
		                    	'team' 		=> $team['name'], 
		                    	'item' 		=> $option['title']
		                    ];
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