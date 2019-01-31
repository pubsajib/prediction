<?php 
function RoadToTop($user=null) {
	if (!$user) $user = wp_get_current_user();
	// echo "User ID : $user->ID";
	if ( !in_array( 'predictor', (array) $user->roles ) ) echo "Not a predictor";
	else{
		$ranking = getRakingFor();
		$rankInfo = getUserRankedDetails($ranking['all'], $user->ID);
		$UP = predictionsOf($user->ID);
		$lifeTimeEvents = count(lifeTimePublished($user->ID));
		$toalPublished = count(totalPublished());
		$engagement = 0;
		if (isset($rankInfo['participated'])) {
			if ($lifeTimeEvents) $engagement = ($rankInfo['participated'] / $lifeTimeEvents) * 100;
			$engagement = number_format($engagement, 2);
		}
		echo '<div class="login-profile">';
		// RANK
		if ($rankInfo) {
			echo '<div class="item">
					<h3>My Rank</h3>
					<div class="circle">
						<p>'. $rankInfo['rank'] .'</p>
					</div>
					<div class="additional">
						<span><strong>Among:</strong> '. count($ranking['all']) .' </span>
					</div>
			</div>';
		}
		
		// ACCURICY
		if ($UP['avg']) {
			echo '<div class="item">
					<h3>Accuracy</h3>
					<div class="circle green">
						<p>'. $UP['avg']['all']['rate'] .'</p>
					</div>
					<div class="additional">
						<span><strong>Match:</strong> '. $UP['avg']['match']['participated'] .' </span>
						<span><strong>Toss:</strong> '. $UP['avg']['toss']['participated'] .' </span>
					</div>
			</div>';
		}
		
		// ENGAGEMENT
		if ($lifeTimeEvents) {
			echo '<div class="item">
					<h3>Engagement</h3>
					<div class="circle red">
						<p>'. $engagement .'</p>
					</div>
					<div class="additional">
						<span><strong>My Published:</strong> '. $lifeTimeEvents .' </span>
						<span><strong>Total Published:</strong> '. $toalPublished .' </span>
					</div>
			</div>';
		}

		// PARTICIPATED
		if ($rankInfo) {
			echo '<div class="item">
					<h3>Participated</h3>
					<div class="circle red">
						<p>'. $rankInfo['participated'] .'</p>
					</div>
					<div class="additional">
						<span><strong>Match:</strong> '. $rankInfo['match'] .' </span>
						<span><strong>Toss:</strong> '. $rankInfo['toss'] .' </span>
					</div>
			</div>';
		}
		echo '</div>';
		// echo "<h4 style='margin-bottom: 0;'> Image </h4>";
		// echo "<p style='margin-top: 0;'> On hold (prending) </p>";
		
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
function profileEvents($user=null) {
	$items = [];
	if (!$user) $user = wp_get_current_user();
	$registered = $user->user_registered;
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
function profileEvents2($uID=507) {
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