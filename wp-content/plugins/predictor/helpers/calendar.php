<?php 
function calendarEvents($user=null, $html=false) {
	return CalendarEvents::render($user, $html);
}
class CalendarEvents {
	function __construct() {}
	static function render($user, $html) {
	    $events = self::events($user);
	    $events = self::eventsByDate($events);
	    //$eventsByCat = self::eventsByCat($events);
	    if ($html && $user) return self::eventsByDateHtmlForPredictor($events, $user);
	    else if($html && !$user) return self::eventsByDateHtmlForAll($events);
	    else return $events;
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
		if (!$user) $user = wp_get_current_user();
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
					'date' 		=> date('m/d/Y', strtotime(get_post_meta($event->ID, 'pre-date', true))),
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
    static function eventsByDateHtmlForAll($events) {
		$data = '';
    	if ($events) {
			$data .= '<div class="jflatTimeline">';
			    $data .= '<div class="datepickerWrapper">';
	        	    $data .= '<input type="date" id="matchesDatepicker">';
	        	    $data .= '<span id="calendar_text">';
	        	     	$data .= '<span> '. date('d M Y') .'</span> ';
	        	     	$data .= '<img src="https://cricdiction.com/wp-content/plugins/predictor/frontend/img/calendar.png">';
	        	    $data .= '</span>';
        	    $data .= '</div>';
        	    $data .= '<div class="timeline-wrap">';
					foreach ($events as $eventDate => $cats) {
						if ($cats) {
							$selected = date('Ymd') == date('Ymd', strtotime($eventDate)) ? 'selected' : '';
							$data .= '<div class="event '. $selected .'" data-date="'. date('Y-m-d', strtotime($eventDate)) .'">';
							foreach ($cats as $catSlug => $cat) {
								$catName = $cat['name'];
								unset($cat['name']);
								$data .= '<div class="eventWrapper">';
									$data .= '<div class="title">'. $catName .'</div>';
									if ($cat) {
										foreach ($cat as $eventSI => $event) {
											if ($event['match']) {
												foreach ($event['match'] as $item) {
													if (isset($item['opt']['match']) && !empty($item['opt']['match'])) {
													    $discussion = $item['dis'] ?? false;
											            $subTitle   = !empty($item['sub']) ? $item['sub'] .', ' : '';
														$singleItem = isset($item['opt']['match']) && !empty($item['opt']['match']) ? $item['opt']['match'] : [];
														$tossItem = isset($item['opt']['toss']) && !empty($item['opt']['toss']) ? $item['opt']['toss'] : [];
														$default = $tossItem['default'] ? '<div class="result"><strong>'. $tossItem['default'] .'</strong> won the toss</div>' : '';
														$data .= '<div class="item">';
															$data .= $default ? '<div class="status">Result</div>' : '';
															$data .= '<div class="time">'. $subTitle. $item['time'] .'</div>';
															$data .= $singleItem['teams'];
															$data .= $default;
															$data .= '<div class="footer">';
															    $data .= '<a href="'. site_url('/event/'. $event['slug']) .'" class="fusion-button button-default button-small predict">View Prediction</a>';
															    if ($discussion) $data .= '<a href="'. $discussion .'" class="fusion-button button-default button-small" target="_blank">Discussion</a>';
															$data .= '</div>';
														$data .= '</div>';
													}
													$data .= !empty($item['opt']['add']) ? '<div class="item">'. $item['opt']['add'] .'</div>': ''; // LOAD ADD
												}
											}
										}
									}
								$data .= '</div>';
							}
						}
						$data .= '</div>';
					}
					$data .= '<div class="event notFound"><div class="eventWrapper">Not found</div></div>';
				$data .= '</div>';
			$data .= '</div>';
		}
		return $data;
    }
    static function eventsByDateHtmlForPredictor($events, $user) {
		$data = '';
    	if ($events) {
        	$data = '';
        	$data .= '<div class="jflatTimeline">';
        		$data .= '<div class="datepickerWrapper">';
	        	    $data .= '<input type="date" id="matchesDatepicker">';
	        	    $data .= '<span id="calendar_text">';
	        	     	$data .= '<span> '. date('d M Y') .'</span> ';
	        	     	$data .= '<img src="https://cricdiction.com/wp-content/plugins/predictor/frontend/img/calendar.png">';
	        	    $data .= '</span>';
        	    $data .= '</div>';
        	    $data .= '<div class="timeline-wrap">';
        			foreach ($events as $eventDate => $cats) {
        				if ($cats) {
        					$selected = date('Ymd') == date('Ymd', strtotime($eventDate)) ? 'selected' : '';
					        $data .= '<div class="event '. $selected .'" data-date="'. date('Y-m-d', strtotime($eventDate)) .'">';
        					foreach ($cats as $catSlug => $cat) {
        						$catName = $cat['name'];
        						unset($cat['name']);
        						$data .= '<div class="eventWrapper">';
        							$data .= '<div class="title">'. $catName .'</div>';
        							if ($cat) {
        								foreach ($cat as $eventSI => $event) {
        									if ($event['match']) {
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
        												$data .= '<div class="item">';
        													$data .= $published ? '<div class="status">Result</div>' : '';
															$data .= '<p>'. $mTitle .'</p>';
        													$data .= '<div class="time">'. $subTitle. $item['time'] .'</div>';
													        
															$data .= '<div class="event-predict">';
																$data .= '<p class="eventToss"><strong>Toss: </strong><span id="'. $tossID .'">'. $toss .'</span> '. $tStatus .' </p>';
																$data .= '<p class="eventMatch"><strong>Match: </strong><span id="'. $matchID .'">'. $match .'</span> '. $mStatus .' </p>';
															$data .= '</div>';
        													$data .= '<div class="footer">';
        													    $data .= '<a href="javascript:;" event="'. $event['ID'] .'" team="'. predictor_id_from_string($mTitle) .'" class="fusion-button button-default button-small predictionFormBtn pcp-btn-one">Predict Now</a>';
        													    $data .= '<a href="'. site_url('/event/'. $event['slug']) .'" class="fusion-button button-default button-small predict">View Prediction</a>';
        													    if ($discussion) $data .= '<a href="'. $discussion .'" class="fusion-button button-default button-small" target="_blank">Discussion</a>';
        													$data .= '</div>';
        												$data .= '</div>';
        											}
        										}
        									}
        								}
        							}
        						$data .= '</div>';
        					}
        				}
        				$data .= '</div>';
        			}
        			$data .= '<div class="event notFound"><div class="eventWrapper">Not found</div></div>';
        		$data .= '</div>';
        	$data .= '</div>';
        }
		return $data;
    }
}