<?php 
/**
 * Road To Top
 */
function RoadToTop($uID=507) {
		$current_user = wp_get_current_user();
		$uID = $current_user->ID;
	if (!$uID) {
	}
	// echo "User ID : $uID";
	if ( !in_array( 'predictor', (array) $current_user->roles ) ) echo "Not a predictor";
	else{
		$ranking = getRakingFor();
		$rankInfo = getUserRankedDetails($ranking['all'], $uID);
		$UP = predictionsOf($uID);
		$lifeTimeEvents = count(lifeTimePublished($uID));
		$toalPublished = count(totalPublished());
		if (isset($rankInfo['participated'])) {
			if ($lifeTimeEvents) $engagement = ($rankInfo['participated'] / $lifeTimeEvents) * 100;
			$engagement = 0;
			$engagement = number_format($engagement, 2);
		}

		// RANK
		if ($rankInfo) {
			echo "<h4 style='margin-bottom: 0;'> Rank Info </h4>";
			echo "<ul style='margin-top:0;'>";
				echo "<li>Rank : {$rankInfo['rank']} </li>";
				echo "<li>Among : ". count($ranking['all']) ." </li>";
			echo "</ul>";
		}

		// ACCURICY
		if ($UP['avg']) {
			echo "<h4 style='margin-bottom: 0;'> Accuracy </h4>";
			echo "<ul style='margin-top:0;'>";
				echo "<li>Accurity : {$UP['avg']['all']['rate']} </li>";
				echo "<li>Match : {$UP['avg']['match']['participated']} </li>";
				echo "<li>Toss : {$UP['avg']['toss']['participated']} </li>";
			echo "</ul>";
		}
		
		// ENGAGEMENT
		if ($lifeTimeEvents) {
			echo "<h4 style='margin-bottom: 0;'> Engagement </h4>";
			echo "<ul style='margin-top:0;'>";
				echo "<li>engagement : $engagement </li>";
				echo "<li>toalPublished : $toalPublished </li>";
			echo "</ul>";
		}

		// PARTICIPATED
		if ($rankInfo) {
			echo "<h4 style='margin-bottom: 0;'> Participated </h4>";
			echo "<ul style='margin-top:0;'>";
				echo "<li>Participated : {$rankInfo['participated']} </li>";
				echo "<li>Match : {$rankInfo['match']} </li>";
				echo "<li>Toss : {$rankInfo['toss']} </li>";
			echo "</ul>";
		}
		echo "<h4 style='margin-bottom: 0;'> Image </h4>";
		echo "<p style='margin-top: 0;'> On hold (prending) </p>";
		
	}
}
function getUserRankedDetails($ranks, $uID) : array {
	$rankInfo = [];
	if ($ranks) {
		foreach ($ranks as $key => $rank) {
			if ($rank['id'] == $uID) {
				$rankInfo = $rank;
				$rankInfo['rank'] = $key + 1;
				break;
			}
		}
	}
	return $rankInfo;
}
function profileEvents($uID=507) {
	$items = [];
	$udata = get_userdata($uID);
	$registered = $udata->user_registered;
	// $registered = '2019-01-02 20:17:00'; // YYYY-mm-dd
	$query = array(
        'post_type' => 'event',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'date_query' => ['after' => $registered],
    );
    $events = new WP_Query($query);
    // $events = $events->found_posts;
    $events = $events->posts;
    if ($events) {
		foreach ($events as $event) {
			$eventInfo = ['ID'=>$event->ID, 'title'=> $event->post_title, 'slug'=>$event->post_name, 'cats' => getEventCategories($event)];
			$meta  = get_post_meta($event->ID, 'event_ops', true);
			$ans  = get_post_meta($event->ID, 'event_ans', true);
			if (!empty($meta['teams'])) {
        		foreach ($meta['teams'] as $team) {
        			$ID     	= predictor_id_from_string($team['name']);
            		$teamID 	= 'team_'. $ID;
            		// $teamInfo 	= ['ID'=>$teamID, 'title'=>$team['name'], 'end'=>$team['end']];
            		$teamInfo 	= [
            			'ID'		=> $teamID, 
            			'title'		=> $team['name'], 
            			'time'		=> $team['end'] ? date('d-m-Y H:i:s A', strtotime($team['end'])) : '',
            		];

            		if (!empty($meta[$teamID])) {
            			foreach ($meta[$teamID] as $option) {
            				$optionID 	= predictor_id_from_string($option['title']);
		                    $defaultID 	= 'default_'. $ID .'_'. $optionID;
		                    $answerID 	= $teamID .'_'. $optionID;
		                    $published 	= $meta[$defaultID.'_published'] ?? 0;
            				$itemInfo 	= [
            					'ID'		=> $answerID, 
            					'title'		=> $option['title'], 
            					'type'		=> $option['id'], 
            					'published'	=> $published,
            					'options'	=> getOptions($option['weight']),
            					'default'	=> $meta[$defaultID] ?? '', 
            					'answer'	=> $ans[$uID][$answerID] ?? 'N/A',
            					'answerable' => 0,
            				];
            				if ($published) {
            					if ($itemInfo['default'] === 'abandon') $itemInfo['isCorrect'] = $itemInfo['default'];
            					else $itemInfo['isCorrect'] = $itemInfo['default'] == $itemInfo['answer'];
            					$itemInfo['status'] = getWLStatus($itemInfo['answer'], $itemInfo['isCorrect'], 1);
            				} else {
            					$itemInfo['status'] = getWLStatus($itemInfo['answer'], 0, 0);
            					if (time() < strtotime($teamInfo['time'])) $itemInfo['answerable'] = 1;
            				}
		                    $items[] = [
		                    	'event' => $eventInfo,
		                    	'team' 	=> $teamInfo, 
		                    	'item' 	=> $itemInfo,
		                    ];
            			}
            		}
        		}
        	}
		}
	}
	return $items;
}
function unpublishedEvents($uID=507) {
	$items = profileEvents($uID);
	$events = array_filter($items, function($item) {
		return !$item['item']['published'];
	});
	return $events;
}
function publishedEvents($uID=507) {
	$items = profileEvents($uID);
	$events = array_filter($items, function($item) {
		return $item['item']['published'];
	});
	return $events;
}
function getEventCategories($event) {
	$tournaments = '';
	$cats = get_the_terms($event, 'tournament');
	if ($cats) {
		$tournaments .= '<ul class="tournaments">';
		foreach ($cats as $cat) {
			$tournaments .= '<li>'. $cat->name .'</li>';
		}
		$tournaments .= '</ul>';
	}
	return $tournaments;
}
function getOptions($weights) {
	$options = '';
	if ($weights) {
		$options .= '<ul class="options">';
		foreach ($weights as $weight) {
			if ($weight['name']) {
				$options .= "<li>";
				$options .= $weight['name'];
				$options .= "</li>";
			}
		}
		$options .= '</ul>';
	}
	return $options;
}
function getWLStatus($answer, $isCorrect, $isPublished) {
	$WLStatus = '';
	if (!$isPublished) $WLStatus = $answer;
	else {
		if ($isCorrect === 'abandon') $WLStatus = 'Abandon';
		else if ($isCorrect) $WLStatus = 'Win';
		else $WLStatus = 'Lose';
	}
	return $WLStatus;
}
function getUserRank($uID, $ranks) : int {
	$rank = 0;
	if ($ranks && $uID) {
		foreach ($ranks as $key => $rank) {
			if ($rank['id'] == $uID) return $key;
		}
	}
	return $rank;
}