<?php 
function calendarEvents($user=null) {
	return CalendarEvents::render($user=null);
}
class CalendarEvents {
	function __construct() {}
	static function render($user=null) {
	    $events = self::events($user=null);
	    // help($events);
	    $events = self::eventsByDate($events);
	    //$eventsByCat = self::eventsByCat($events);
		return $events;
	}
	static function eventsByDate($events){
	    $filteredEvents = [];
	    $catCounter = 0;
	    if  ($events) {
	        foreach($events as $event) {
	           $filteredEvents[$event['date']][$event['catslug']]['name'] = $event['catname'];
	           $filteredEvents[$event['date']][$event['catslug']][] = $event;
	           $catCounter++;
	        }
	    }
	    return $filteredEvents;
	}
	static function events($user=null) {
		if (!$user) $user = $user = wp_get_current_user();
    	$items = [];
		$query = array(
	        'post_type' => 'event',
	        'post_status' => 'publish',
	        'posts_per_page' => -1,
	        'orderby' => 'publish_date',
	    	'order' => 'ASC',
	    );
	    $events = new WP_Query($query);
	    $events = $events->posts;
	    if ($events) {
	    	$eventSI = 0;
			foreach ($events as $event) {
			    $cat = self::getEventCategory($event) ?? ['slug'=>'', 'name'=>''];
				$eventInfo = [
					'ID'=>$event->ID, 
					'title'=> $event->post_title, 
					'slug'=>$event->post_name, 
					'catslug' => $cat['slug'],
					'catname' => $cat['name'],
					'teams' => [],
					'date' => date('m/d/Y', strtotime(get_post_meta($event->ID, 'pre-date', true))),
				];
				$meta  = get_post_meta($event->ID, 'event_ops', true);
				$ans  = get_post_meta($event->ID, 'event_ans', true);
				$add  = get_post_meta($event->ID, 'pre-add', true);

				if (!empty($meta['teams'])) {
					$teamInfo = [];
					$itemSI = 0;
	        		foreach ($meta['teams'] as $team) {
	        			$ID     	= predictor_id_from_string($team['name']);
	            		$teamID 	= 'team_'. $ID;
	            		$teamInfo[$itemSI] 	= [
	            			'ID'		=> $teamID, 
	            			'title'		=> $team['name'], 
	            			'time'		=> $team['end'] ? date('M d Y H:i A', strtotime($team['end'])) : '',
	            			'dis' 		=> $team['discussion'] ?? '',
	            			'sub' 		=> $team['subtitle'] ?? '',
	            			'isValid'   => time() <= strtotime($team['end']),
	            		];

	            		$teamOpts = [];
	            		$isTeamAnswerable = false;
	            		if (!empty($meta[$teamID])) {
	            			$SI = 0;
	            			foreach ($meta[$teamID] as $option) {
	            				$optionID 	= predictor_id_from_string($option['title']);
			                    $defaultID 	= 'default_'. $ID .'_'. $optionID;
			                    $answerID 	= $teamID .'_'. $optionID;
			                    $published 	= $meta[$defaultID.'_published'] ?? 0;
			                    $type = $option['id'];
			                    $default = $meta[$defaultID] ?? '';
	            				$teamOpts[$type] = [
	            					'ID'		=> $answerID, 
	            					'title'		=> $option['title'], 
	            					// 'type'		=> $type, 
	            					'published'	=> $published,
	            					// 'options'	=> getOptions($option['weight']),
	            					'default'	=> $default, 
	            					'answerable' => 0,
	            					'answer'	=> @$ans[$user->ID][$answerID] ?? 'No Answer',
	            					'teams' => self::getTeams($option['weight'], $default)
	            				];
	            				if ($published && isset($teamOpts[$type]['default'])) {
	            					if ($teamOpts[$type]['default'] === 'abandon') $teamOpts[$type]['isCorrect'] = $teamOpts[$type]['default'];
	            					else $teamOpts[$type]['isCorrect'] = $teamOpts[$type]['default'] == $teamOpts[$type]['answer'];
	            					$teamOpts[$type]['status'] = self::getWLStatus($teamOpts[$type]['answer'], $teamOpts[$type]['isCorrect'], 1);
	            				} else {
	            					$teamOpts[$type]['status'] = self::getWLStatus($teamOpts[$type]['answer'], 0, 0);
	            					if (time() < strtotime($team['end'])) $teamOpts[$type]['answerable'] = 1;
	            					if (!$teamOpts[$type]['status']) $isTeamAnswerable = true;
	            				}
	            				$SI++;
	            			}
	            			// GET ADD FOR CURRENT EVENT
	            			if ($add) $teamOpts['add'] = $add;
	            		}
	            		$teamInfo[$itemSI]['opt'] = $teamOpts;
	            		$teamInfo[$itemSI]['isValid'] = (time() <= strtotime($team['end'])) && $isTeamAnswerable;
	            		$itemSI++;
	        		}
	        		if ($teamInfo && count($items) <= 9999) {
	        			$items[$eventSI] = $eventInfo;
	        			$items[$eventSI]['match'] = $teamInfo;
	        		}
	            	$eventSI++;
	        	}
	        	// if(14338 == $event->ID) help($teamInfo);
			}
		}
		return $items;
	}
	static function getEventCategory($event) {
		$cats = get_the_terms($event, 'tournament');
		if ($cats[0]) {
		    return ['slug'=>$cats[0]->slug, 'name'=>$cats[0]->name];
		}
		return 0;
	}
	static function getTeams($weights, $default) {
		$options = '';
		$teamOneWin = $weights[0]['name'] == $default ? ' <img src="'. PREDICTOR_URL .'frontend/img/checked.png">' : '';
		$teamTwoWin = $weights[1]['name'] == $default ? ' <img src="'. PREDICTOR_URL .'frontend/img/checked.png">' : '';
		if ($weights) {
			$options .= '<div class="team-name team-one">'. $weights[0]['name'] . $teamOneWin .'</div>';
			$options .= '<div class="team-name team-two">'. $weights[1]['name'] . $teamTwoWin .'</div>';
		}
		return $options;
    }
    static function getWLStatus($answer, $isCorrect, $isPublished) {
    	$WLStatus = '';
    	if ($answer == 'No Answer' || !$isPublished) $WLStatus = '';
    	else {
    		if ($isCorrect === 'abandon') $WLStatus = ' <img class="statusIcon" src="'. PREDICTOR_URL .'frontend/img/unhappy.png">';
    		else if ($isCorrect) $WLStatus = ' <img class="statusIcon" src="'. PREDICTOR_URL .'frontend/img/happy.png">';
    		else $WLStatus = ' <img class="statusIcon" src="'. PREDICTOR_URL .'frontend/img/sad.png">';
    	}
    	return $WLStatus;
    }
}