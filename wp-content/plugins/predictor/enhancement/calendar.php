<?php 
function calendarEvents($user=null, $date=false) {
	return CalendarEvents::render($user, $date);
}
class CalendarEvents {
	function __construct() {}
	static function render($user, $date) {
	    $events = self::events($user, $date);
	    $events = self::sortEventsByCats($events);
	    if ($date) {
			return self::getEventHTML($events, $user, $date, true);
	    } else {
			$date = date('Y-m-d');
			return self::getEventHTML($events, $user, $date);
	    }
	}
	static function sortEventsByCats($events){
	    $filteredEvents = [];
	    $catCounter = 0;
	    if  ($events) {
	        foreach($events as $event) {
	           $filteredEvents[$event['catslug']]['name'] = $event['catname'];
	           $filteredEvents[$event['catslug']][] = $event;
	           $catCounter++;
	        }
	    }
	    return $filteredEvents;
	}
	static function events($user=null, $date='') {
    	$items = [];
		if (!$date) $date = date('Y-m-d');
		if (!$user) $user = wp_get_current_user();
		$query = array(
	        'post_type' => 'event',
	        'post_status' => 'publish',
	        'posts_per_page' => -1,
	        'orderby' => 'publish_date',
	        'meta_query' => [['key'=>'pre-date', 'value'=>$date, 'compare'=>'=', 'type'=>'DATE']],
	    	'order' => 'DESC',
	    );
	    $events = new WP_Query($query);
	    $events = $events->posts;
	    if ($events) {
	    	$eventSI = 0;
			foreach ($events as $event) {
				$meta  = get_post_meta($event->ID, 'event_ops', true);
				$ans   = get_post_meta($event->ID, 'event_ans', true);
				$add   = get_post_meta($event->ID, 'pre-add', true);
			    $cat   = self::getEventCategory($event) ?? ['slug'=>'', 'name'=>''];
				$eventInfo = [
					'ID'		=>$event->ID, 
					'title'		=> $event->post_title, 
					'slug'		=>$event->post_name, 
					'catslug' 	=> $cat['slug'],
					'catname' 	=> $cat['name'],
					'teams' 	=> [],
				// 	'date' 		=> date('m/d/Y', strtotime(get_post_meta($event->ID, 'pre-date', true))),
				];

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
	            					'answer'	=> !empty($ans[$user->ID][$answerID]) ? $ans[$user->ID][$answerID] : 'No Answer',
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
	            			if ($add) $teamOpts['add'] = $add; // GET ADD FOR CURRENT EVENT
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
	        	// if(13916 == $event->ID) help($teamInfo);
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
    static function getEventHTML($events, $user, $date, $ajaxCall=false) {
    	$data = $eventData = '';
    	if ($user) $eventData .= self::eventsByDateHtmlForPredictor($events, $user);
		else $eventData .= self::eventsByDateHtmlForAll($events);
    	if ($ajaxCall) $data = $eventData;
    	else {
			$data .= '<div class="jflatTimeline">';
				$data .= self::eventsByDateHtmlHead($date);
				$data .= '<div class="timeline-wrap">';
					$data .= $eventData;
				$data .= '</div>';
	        $data .= '</div>';
    	}
	    return $data;
    }
    static function eventsByDateHtmlHead($date) {
    	$data = '';
    	$data .= '<div class="datepickerWrapper">';
    	    $data .= '<input type="date" id="matchesDatepicker">';
    	    $data .= '<span id="calendar_text">';
    	     	$data .= '<span> '. date('d M Y', strtotime($date)) .'</span> ';
    	     	$data .= '<img src="https://cricdiction.com/wp-content/plugins/predictor/frontend/img/calendar.png">';
    	    $data .= '</span>';
	    $data .= '</div>';
	    return $data;
    }
    static function eventsByDateHtmlForAll($cats) {
		$data = $matchData = '';
		if ($cats) {
	    	foreach ($cats as $cat) {
				if (!empty($cat)) {
			        $matchData .= '<div class="event">';
			        if (isset($cat['name'])) {
						$catName = !empty($cat['name']) ? $cat['name'] : 'Undefined';
						unset($cat['name']);
			        }
					foreach ($cat as $catSlug => $event) {
						if (!empty($event['match'])) {
    						$matchData .= '<div class="eventWrapper">';
							$matchData .= '<div class="title">'. $catName .'</div>';
							foreach ($event['match'] as $item) {
								if (isset($item['opt']['match']) && !empty($item['opt']['match'])) {
								    $discussion = $item['dis'] ?? false;
						            $subTitle   = !empty($item['sub']) ? $item['sub'] .', ' : '';
									$singleItem = isset($item['opt']['match']) && !empty($item['opt']['match']) ? $item['opt']['match'] : [];
									$tossItem = isset($item['opt']['toss']) && !empty($item['opt']['toss']) ? $item['opt']['toss'] : [];
									$default = $tossItem['default'] ? '<div class="result"><strong>'. $tossItem['default'] .'</strong> won the toss</div>' : '';
									$matchData .= '<div class="item">';
										$matchData .= $default ? '<div class="status">Result</div>' : '';
										$matchData .= '<div class="time">'. $subTitle. $item['time'] .'</div>';
										$matchData .= $singleItem['teams'];
										$matchData .= $default;
										$matchData .= '<div class="footer">';
										    $matchData .= '<a href="'. site_url('/event/'. $event['slug']) .'" class="fusion-button button-default button-small predict">View Prediction</a>';
										    if ($discussion) $matchData .= '<a href="'. $discussion .'" class="fusion-button button-default button-small orange" target="_blank">Discussion</a>';
										$matchData .= '</div>';
									$matchData .= '</div>';
								}
								$matchData .= !empty($item['opt']['add']) ? '<div class="item">'. $item['opt']['add'] .'</div>': ''; // LOAD ADD
							}
							$matchData .= self::answeredPredictorsSlider($event['ID']);
							$matchData .= '</div>';
						}
					}
    				$matchData .= '</div>';
				}
			}
		}
		if ($matchData) $data .= $matchData;
		else $data .= '<div class="event notFound"><div class="eventWrapper">Not found</div></div>';
			
		return $data;
    }
    static function eventsByDateHtmlForPredictor($cats, $user) {
		$data = $matchData = '';
		if ($cats) {
			foreach ($cats as $cat) {
				if (!empty($cat)) {
			        $matchData .= '<div class="event">';
			        if (isset($cat['name'])) {
						$catName = !empty($cat['name']) ? $cat['name'] : 'Undefined';
						unset($cat['name']);
			        }
					foreach ($cat as $catSlug => $event) {
						if (!empty($event['match'])) {
    						$matchData .= '<div class="eventWrapper">';
							$matchData .= '<div class="title">'. $catName .'</div>';
							foreach ($event['match'] as $item) {
								if (isset($item['opt']['match']) && !empty($item['opt']['match'])) {
								    $discussion = $item['dis'] ?? false;
						            $subTitle   = !empty($item['sub']) ? $item['sub'] .', ' : '';
									$mTitle 	= !empty($item['title']) ? $item['title'] : '';
									$matchID 	= !empty($item['opt']['match']['ID']) ? $item['opt']['match']['ID'] : '';
									$tossID 	= !empty($item['opt']['toss']['ID']) ? $item['opt']['toss']['ID'] : '';
									$match 		= !empty($item['opt']['match']['answer']) ? $item['opt']['match']['answer'] : 'N/A';
									$toss 		= !empty($item['opt']['toss']['answer']) ? $item['opt']['toss']['answer'] : 'N/A';
									$mStatus 	= !empty($item['opt']['match']['status']) ? $item['opt']['match']['status'] : '';
									$tStatus 	= !empty($item['opt']['toss']['status']) ? $item['opt']['toss']['status'] : '';
									$published  = !empty($item['opt']['match']['published']) ? $item['opt']['match']['published'] : '';
									$matchData .= '<div class="item">';
										$matchData .= $published ? '<div class="status">Result</div>' : '';
										$matchData .= '<p>'. $mTitle .'</p>';
										$matchData .= '<div class="time">'. $subTitle. $item['time'] .'</div>';
								        
										$matchData .= '<div class="event-predict">';
											$matchData .= '<p class="eventToss"><strong>Toss: </strong><span id="'. $tossID .'">'. $toss .'</span> '. $tStatus .' </p>';
											$matchData .= '<p class="eventMatch"><strong>Match: </strong><span id="'. $matchID .'">'. $match .'</span> '. $mStatus .' </p>';
										$matchData .= '</div>';
										$matchData .= '<div class="footer">';
										    $matchData .= '<a href="javascript:;" event="'. $event['ID'] .'" team="'. predictor_id_from_string($mTitle) .'" class="fusion-button button-default button-small predictionFormBtn pcp-btn-one">Predict Now</a>';
										    $matchData .= '<a href="'. site_url('/event/'. $event['slug']) .'" class="fusion-button button-default button-small predict">View Prediction</a>';
										    if ($discussion) $matchData .= '<a href="'. $discussion .'" class="fusion-button button-default button-small" target="_blank">Discussion</a>';
										$matchData .= '</div>';
									$matchData .= '</div>';
								}
							}
							$matchData .= '</div>';
						}
					}
					$matchData .= self::answeredPredictorsSlider($event['ID']);
    				$matchData .= '</div>';
				}
			}
		}
		if ($matchData) $data .= $matchData;
		else $data .= '<div class="event notFound"><div class="eventWrapper">Not found</div></div>';
        	
		return $data;
    }
    static function answeredPredictorsSlider($eventID) {
    	$answers = (array) get_post_meta($eventID, 'event_ans', true);
    	$eventLink = get_permalink($eventID);
        $users = self::getAnsweredUsers($answers);
        return self::getFavoriteEventAllSupportersSlider($users, $eventLink);
    }
    static function getFavoriteEventAllSupportersSlider($supporters, $eventLink=false) {
        $data = '';
        if ($supporters) {
            $data .= '<div style="padding: 0 20px;"><div class="owl-carousel owl-theme eventTopSupperters">';
                foreach ($supporters as $supporter) {
                    if ($eventLink) {
                        $profileLink = $eventLink .'#'. $supporter['id'];
                        $data .= '<div class="item">';
                            $data .= '<a href="'. $profileLink .'" target="_blank">';
                                $data .= '<p>'. $supporter['avatar'] .'</p>';
                                $data .= '<p style="text-align:center;">'. $supporter['name'] .'</p>';
                            $data .= '</a>';
                        $data .= '</div>';
                    }
                }
            $data .= '</div></div>';
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
            }
        }
        return $predictors;
    }
}