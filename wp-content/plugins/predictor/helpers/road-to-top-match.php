<?php 
function roadToTopMatch() {
    $min = ['avg' => 50, 'match' => 100, 'engagement' => 65];
    RoadToTopMatch::render('match', $min);
}
class RoadToTopMatch {
    static function render($type='all', $min=[], $user=null) {
        if (empty($user)) $user = wp_get_current_user();
        if ( !in_array( 'predictor', (array) $user->roles ) ) echo "Not a predictor";
        else{
            $html = '';
            if (!$min) $min = ['avg' => 50, 'match' => 70, 'engagement' => 40];
    		$ranking = getRakingFor('match');
    		$rankInfo = self::getUserRankedDetails($ranking['all'], $user->ID);
    		$UP = predictionsOf($user->ID);
    		$lifeTimeEvents = count(lifeTimePublished($user->ID, $type));
    		$toalPublished = count(totalPublished($type));
    		$engagement = 0;
    		if (isset($rankInfo['participated'])) {
    			if ($lifeTimeEvents) $engagement = ($rankInfo['participated'] / $lifeTimeEvents) * 100;
    			$engagement = number_format($engagement, 2);
    		}
    		$html .= '<div class="login-profile">';
        		// RANK
        		if ($rankInfo) {
        			$html .= '<div class="item">
        					<h3>My Rank</h3>
        					<div class="circle">
        						<p><strong>'. $rankInfo['rank'] .'</strong></p>
        					</div>
        					<div class="additional">
        						<span><strong>Among:</strong> '. count($ranking['all']) .' </span>
        					</div>
        			</div>';
        		}
        		// ACCURICY
        		if ($UP['avg']) {
        		    $class = $UP['avg'] >= $min['avg'] ? 'green' : 'red';
        			$html .= '<div class="item">
        					<h3>Accuracy</h3>
        					<div class="circle '. $class .'">
        						<p><strong>'. $UP['avg'][$type]['rate'] .'%</strong></p>
        					</div>
        					<div class="additional">
        						<span style="display: inline-block"><strong>Win:</strong> '. $UP['avg'][$type]['correct'] .', </span>
        						<span style="display: inline-block"><strong>Lose:</strong> '. $UP['avg'][$type]['incorrect'] .' </span>
        					</div>
        			</div>';
        		}
        		// ENGAGEMENT (red/green)
        		if ($lifeTimeEvents) {
        		    $class = $engagement >= $min['engagement'] ? 'green' : 'red';
        			$html .= '<div class="item">
        					<h3>Engagement</h3>
        					<div class="circle '. $class .'">
        						<p><strong>'. $engagement .'%</strong></p>
        					</div>
        					<div class="additional">
								<span>Your minimal engagement should be <strong>65%</strong></span>
        					</div>
        			</div>';
        		}
        
        		if ($rankInfo) {
                    if ($rankInfo['participated'] >= $min['match']) {
                        $message = 'You\'ve completed the milestone';
                        $class = 'green';
                    } else {
                        $need = $min['match']-$rankInfo['participated'];
                        $item = $need > 1 ? 'matches' : 'match';
                        $message = 'You need to predict <strong>'. $need .'</strong> more '. $item;
                        $class = 'red';
                    }
                    $html .= '<div class="item">
                        <h3>Participated</h3>
                        <div class="circle '. $class .'">
                            <p><strong>'. $rankInfo['participated'] .'</strong></p>
                        </div>
                        <div class="additional '. $class .'">
                            <span>'. $message .'</span>
                        </div>
                    </div>';
                }
    		$html .= '</div>';
    		$html .= '<div class="notice"><span class="small"><strong>'. $lifeTimeEvents .'</strong> Matches published since you join as an expert and <strong>'. $toalPublished .'</strong> matches published since the system was born on 1st Jan 2019.</span></div>';
            echo $html;
    		// echo "<h4 style='margin-bottom: 0;'> Image </h4>";
    		// echo "<p style='margin-top: 0;'> On hold (prending) </p>";
    		
    	}
    }
    static function getUserRankedDetails($ranks, $uID) : array {
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
    static function profileEvents($user=null) {
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
    				'cats' => self::getEventCategories($event), 
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
                					// 'options'	=> self::getOptions($option['weight']),
                					// 'default'	=> $meta[$defaultID] ?? '', 
                					// 'answerable' => 0,
                					'answer'	=> $ans[$user->ID][$answerID] ?? 'N/A',
                				];
                				if ($published && isset($teamOpts[$type]['default'])) {
                					if ($teamOpts[$type]['default'] === 'abandon') $teamOpts[$type]['isCorrect'] = $teamOpts[$type]['default'];
                					else $teamOpts[$type]['isCorrect'] = $teamOpts[$type]['default'] == $teamOpts[$type]['answer'];
                					$teamOpts[$type]['status'] = self::getWLStatus($teamOpts[$type]['answer'], $teamOpts[$type]['isCorrect'], 1);
                				} else {
                					$teamOpts[$type]['status'] = self::getWLStatus($teamOpts[$type]['answer'], 0, 0);
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
    static function unpublishedEvents($uID=507) {
    	$items = self::profileEvents($uID);
    	$events = array_filter($items, function($item) {
    		return !$item['item']['published'];
    	});
    	return $events;
    }
    static function publishedEvents($uID=507) {
    	$items = self::profileEvents($uID);
    	$events = array_filter($items, function($item) {
    		return $item['item']['published'];
    	});
    	return $events;
    }
    static function getEventCategories($event, $html=0) {
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
    static function getOptions($weights, $array=1) {
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
    static function getWLStatus($answer, $isCorrect, $isPublished) {
    	$WLStatus = '';
    	if (!$isPublished) $WLStatus = $answer;
    	else {
    		if ($isCorrect === 'abandon') $WLStatus = 'Abandon';
    		else if ($isCorrect) $WLStatus = 'Win';
    		else $WLStatus = 'Lose';
    	}
    	return $WLStatus;
    }
    static function getUserRank($uID, $ranks) : int {
    	$rank = 0;
    	if ($ranks && $uID) {
    		foreach ($ranks as $key => $rank) {
    			if ($rank['id'] == $uID) return $key;
    		}
    	}
    	return $rank;
    }
}