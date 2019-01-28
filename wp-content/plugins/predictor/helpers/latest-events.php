<?php 
function latestEvents($tournament='') : array {
	$items = [];
	$args = ['post_type' => 'event', 'post_status' => 'publish', 'posts_per_page' => -1];
	if ($tournament) {
    	$args['tax_query'] = [['taxonomy' => 'tournament', 'field' => 'term_id', 'terms' => $tournament]];
    }
    $events = new WP_Query($args);
    $events = $events->posts;
    $items = latestItems($events);
    return $items;
}
function latestItems($events, $limit=5) : array {
	$items = [];
	$counter = 1;
    if ($events) {
		foreach ($events as $event) {
			$eventInfo = [
				'event_slug'	=>$event->post_name, 
				'event_cats' 	=> getLatestEventCategories($event)
			];
			$meta  = get_post_meta($event->ID, 'event_ops', true);
			$ans  = get_post_meta($event->ID, 'event_ans', true);
			if (!empty($meta['teams'])) {
        		foreach ($meta['teams'] as $team) {
        			if ($counter > $limit) return $items;
        			$ID     	= predictor_id_from_string($team['name']);
            		$teamID 	= 'team_'. $ID;
            		$teamInfo 	= [ 
            			'team_title'		=> $team['name'], 
            			'team_time'		=> $team['end'] ? date('d-m-Y H:i:s A', strtotime($team['end'])) : '',
            		];
            		$itemInfo 	= [];
            		if (!empty($meta[$teamID])) {
            			$matchInfo = $tossInfo = [];
            			foreach ($meta[$teamID] as $option) {
            				$optionID 	= predictor_id_from_string($option['title']);
		                    $defaultID 	= 'default_'. $ID .'_'. $optionID;
		                    $answerID 	= $teamID .'_'. $optionID;
		                    $published 	= $meta[$defaultID.'_published'] ?? 0;
            				if ($option['id'] =='match') {
	            				$matchInfo 	= [
	            					'match_title'		=> $option['title'],
	            					'match_published'	=> $published,
	            					'match_options'		=> getLatestOptions($option['weight']),
	            					'match_default'		=> $meta[$defaultID] ?? '',
	            				];
            				} else if($option['id'] =='toss' && $published) {
            					$tossInfo 	= [
	            					'toss_title'		=> $option['title'],
	            					'toss_default'		=> $meta[$defaultID] ?? '',
	            				];
            				}
            			}
            			$itemInfo = array_merge($matchInfo, $tossInfo);
			            $counter++;
            		}
			        $items[] = array_merge($teamInfo, $eventInfo, $itemInfo);

            		// if (!empty($meta[$teamID])) {
            		// 	foreach ($meta[$teamID] as $option) {
            		// 		$optionID 	= predictor_id_from_string($option['title']);
		            //         $defaultID 	= 'default_'. $ID .'_'. $optionID;
		            //         $answerID 	= $teamID .'_'. $optionID;
		            //         $published 	= $meta[$defaultID.'_published'] ?? 0;
            		// 		$matchInfo 	= [
            		// 			'match_ID'		=> $answerID, 
            		// 			'match_title'		=> $option['title'], 
            		// 			'match_type'		=> $option['id'], 
            		// 			'match_published'	=> $published,
            		// 			'match_options'	=> getLatestOptions($option['weight']),
            		// 			'match_default'	=> $meta[$defaultID] ?? '',
            		// 		];
		            //         $items[] = $eventInfo + $teamInfo + $matchInfo;
		            //         if ($counter >= $limit) return $items;
		            //         $counter++;
            		// 	}
            		// }
        		}
        	}
		}
	}
	return $items;
}
function getLatestEventCategories($event) : array {
	$tournaments = [];
	$cats = get_the_terms($event, 'tournament');
	if ($cats) {
		foreach ($cats as $cat) $tournaments[] = $cat->name;
	}
	return $tournaments;
}
function getLatestOptions($weights) : array {
	$items = [];
	if ($weights) {
		foreach ($weights as $weight) {
			if ($weight['name']) $items[] = $weight['name'];
		}
	}
	return $items;
}