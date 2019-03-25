<?php 
function roadToTop($user=null, $min=null) {
	$min = ['avg' => 50, 'match' => 70, 'engagement' => 40];
	$user = wp_get_current_user();
	if ( !in_array( 'predictor', (array) $user->roles ) ) echo "Not a predictor";
	else {
		$type = 'all';
		$ranking  = getRakingFor();
		$rankInfo = getUserRankedDetails($ranking['all'], $user->ID);
		$UP = predictionsOf($user->ID);
		$lifeTimeEvents = count(lifeTimePublished($user->ID, $type));
		$toalPublished = count(totalPublished($type));
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
				<div class="circle"><p><strong>'. $rankInfo['rank'] .'</strong></p></div>
				<div class="additional"><span><strong>Among:</strong> '. count($ranking['all']) .' </span></div>
			</div>';
		}
		// ACCURICY
		if ($UP['avg']) {
		    $class = $UP['avg'] >= $min['avg'] ? 'green' : 'red';
			echo '<div class="item">
					<h3>Accuracy</h3>
					<div class="circle '. $class .'">
						<p><strong>'. $UP['avg']['all']['rate'] .'%</strong></p>
					</div>
					<div class="additional">
						<span style="display: inline-block"><strong>Match:</strong> '. $rankInfo['matchAccuracy'] .'%</span>
						<span style="display: inline-block"><strong>Toss:</strong> '. $rankInfo['tossAccuricy'] .'%</span>
					</div>
			</div>';
		}
		// ENGAGEMENT (red/green)
		if ($lifeTimeEvents) {
		    $class = $engagement >= $min['engagement'] ? 'green' : 'red';
			echo '<div class="item">
					<h3>Engagement</h3>
					<div class="circle '. $class .'">
						<p><strong>'. $engagement .'%</strong></p>
					</div>
					<div class="additional">
						<span>Your minimal engagement should be <strong>40%</strong></span>
					</div>
			</div>';
		}
		// PARTICIPATED
		if ($rankInfo) {
		    $class = $rankInfo['participated'] >= $min['match'] ? 'green' : 'red';
			echo '<div class="item">
					<h3>Participated</h3>
					<div class="circle '. $class .'">
						<p><strong>'. $rankInfo['participated'] .'<strong></p>
					</div>
					<div class="additional">
						<span style="display: inline-block"><strong>Match:</strong> '. $UP['avg']['match']['participated'] .' </span>
						<span style="display: inline-block"><strong>Toss:</strong> '. $UP['avg']['toss']['participated'] .' </span>
					</div>
			</div>';
		}
		// $html .= '<div class="notice"><span class="small"><strong>0</strong> Events published since you join as an expert and <strong>1</strong> Events published since the system was born on 1st Jan 2019.</span></div>';
		echo '</div>';
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
	$query = array(
        'post_type' => 'event',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        // 'date_query' => ['after' => $registered],
        'orderby' => 'publish_date',
    	'order' => 'DESC',
    );
    $events = new WP_Query($query);
    $events = $events->posts;
    if ($events) {
    	$eventSI = 0;
		foreach ($events as $event) {
			$eventInfo = [
				'ID'=>$event->ID, 
				'title'=> $event->post_title, 
				'slug'=>$event->post_name, 
				'cats' => getEventCategories($event), 
				'date' => date('d-m-Y H:i:s A', strtotime($event->post_date)),
				'published' => 0
			];
			$meta  = get_post_meta($event->ID, 'event_ops', true);
			$ans  = get_post_meta($event->ID, 'event_ans', true);

			if (!empty($meta['teams'])) {
				$teamInfo = [];
				$itemSI = 0;
        		foreach ($meta['teams'] as $team) {
        			$ID     	= predictor_id_from_string($team['name']);
            		$teamID 	= 'team_'. $ID;
            		$teamInfo[$itemSI] 	= [
            			'ID'		=> $teamID, 
            			'title'		=> $team['name'], 
            			'time'		=> $team['end'] ? date('d-m-Y H:i:s A', strtotime($team['end'])) : '',
            		];

            		$teamOpts = [];
            		if (!empty($meta[$teamID])) {
            			$SI = 0;
            			foreach ($meta[$teamID] as $option) {
            				$optionID 	= predictor_id_from_string($option['title']);
		                    $defaultID 	= 'default_'. $ID .'_'. $optionID;
		                    $answerID 	= $teamID .'_'. $optionID;
		                    $published 	= $meta[$defaultID.'_published'] ?? 0;
		                    $type = $option['id'];
            				$teamOpts[$type] = [
            					'ID'		=> $answerID, 
            					'title'		=> $option['title'], 
            					// 'type'		=> $type, 
            					// 'published'	=> $published,
            					// 'options'	=> getOptions($option['weight']),
            					// 'default'	=> $meta[$defaultID] ?? '', 
            					// 'answerable' => 0,
            					'answer'	=> $ans[$user->ID][$answerID] ?? 'N/A',
            				];
            				if ($published && isset($teamOpts[$type]['default'])) {
            					if ($teamOpts[$type]['default'] === 'abandon') $teamOpts[$type]['isCorrect'] = $teamOpts[$type]['default'];
            					else $teamOpts[$type]['isCorrect'] = $teamOpts[$type]['default'] == $teamOpts[$type]['answer'];
            					$teamOpts[$type]['status'] = getWLStatus($teamOpts[$type]['answer'], $teamOpts[$type]['isCorrect'], 1);
            				} else {
            					$teamOpts[$type]['status'] = getWLStatus($teamOpts[$type]['answer'], 0, 0);
            					if (time() < strtotime($team['end'])) $teamOpts[$type]['answerable'] = 1;
            				}
            				$SI++;
            			}
            		}
            		$teamInfo[$itemSI]['opt'] = $teamOpts;
            		$itemSI++;
        		}
        		if ($teamInfo && count($items) <= 9) {
        			$items[$eventSI] = $eventInfo;
        			$items[$eventSI]['match'] = $teamInfo;
        		}
            	$eventSI++;
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
function getEventCategories($event, $html=0) {
	$tournaments = '';
	$cats = get_the_terms($event, 'tournament');
	if ($html) {
		if ($cats) {
			$tournaments .= '<ul class="tournaments">';
			foreach ($cats as $cat) {
				$tournaments .= '<li>'. $cat->name .'</li>';
			}
			$tournaments .= '</ul>';
		}
	} else {
		$catArray = [];
		if ($cats) {
			foreach ($cats as $cat) {
				$catArray[] = $cat->name;
			}
		}
		$tournaments = implode(', ', $catArray);
	}
	return $tournaments;
}
function getOptions($weights, $array=1) {
	if ($array) {
		$options = [];
		if ($weights) {
			foreach ($weights as $SI => $weight) {
				if ($weight['name']) {
					$options[] = $weight['name'];
				}
			}
		}
		return implode(', ',$options);
	} else {
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
function recentMatches($tournament=null) {
	$items = [];
	$itemSI = 0;
	$query = ['post_type' => 'event', 'post_status' => 'publish', 'posts_per_page' => 5,];
	if ($tournament) $query['tax_query'] = [['taxonomy' => 'tournament', 'field' => 'term_id', 'terms' => $tournament]];
    $events = new WP_Query($query);
    // $events = $events->found_posts;
    $events = $events->posts;
    if ($events) {
		foreach ($events as $event) {
			$meta  = get_post_meta($event->ID, 'event_ops', true);
			if (!empty($meta['teams'])) {
        		foreach ($meta['teams'] as $team) {
        			$ID     	= predictor_id_from_string($team['name']);
            		$teamID 	= 'team_'. $ID;
            		$teamInfo 	= [
            			'eventID'	=>	$event->ID, 
            			'title'		=> 	$team['name'], 
            			'link'		=>	site_url('event/'). $event->post_name,
            			'time'		=> 	$team['end'] ? date('M d, Y h:i A', strtotime($team['end'])) : '',
            			'cats' 		=> 	getEventCategories($event),
            			'status'	=> 	strtotime($team['end']) >= time() ? 'Active' : 'Completed',
            			'subtitle'	=> '',
            			'discussion'	=> '',
            		];
    				if (isset($team['subtitle'])) $teamInfo['subtitle'] = $team['subtitle'] ?? '';
    				if (isset($team['discussion'])) $teamInfo['discussion'] = $team['discussion'] ?? '';
            		$itemInfo = [];
            		if (!empty($meta[$teamID])) {
            			foreach ($meta[$teamID] as $option) {
            				$optionID 	= predictor_id_from_string($option['title']);
		                    $defaultID 	= 'default_'. $ID .'_'. $optionID;
		                    $answerID 	= $teamID .'_'. $optionID;
		                    $published 	= $meta[$defaultID.'_published'] ?? 0;
            				$itemInfo[$itemSI] 	= [
            					// 'ID'		=> $answerID, 
            					'title'		=> $option['title'],
            					'options'	=> getOptions($option['weight']),
            					'default'	=> 'N/A',
            				];
            				if ($published) $itemInfo[$itemSI]['default'] = $meta[$defaultID] ?? '';
            				$itemSI++;
            			}
            		}
            		if ($itemInfo) {
	                    $items[$itemSI] = $teamInfo;
	                    $items[$itemSI]['item'] = $itemInfo;
            		}
            		$itemSI++;
        		}
        	}
		}
	}
	return $items;
}