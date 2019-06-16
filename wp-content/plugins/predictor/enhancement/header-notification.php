<?php 
// [header-notification tournaments="12:bpl,15:ipl"]
class HeaderNotification {
	public static function render($attr) {
		$html = $tabNavigation =  $tabNavigationItems = $tabContent = '';
		$allTournaments = '';
		$defaults = ['tournaments'=>''];
		$attr = shortcode_atts($defaults, $attr, 'HeaderNotification');
		$tournaments = self::tournaments($attr['tournaments']);
		$owlSelector = 'owlCarousel_headerNotification';
		if ($tournaments) {
			foreach ($tournaments as $tournamentID => $tournament) {
				$tournamentMatches = self::html(self::recentMatches($tournamentID));
				$allTournaments .= $tournamentMatches; 
				$tabNavigationItems .= '<li class="proli"><a href="#tournamentID-'. $tournamentID .'">'. $tournament .'</a></li>';
				$tabContent .= '<div id="tournamentID-'. $tournamentID .'">';
					$tabContent .=  '<div class="owl-carousel '.$owlSelector.' owl-theme">';
						$tabContent .=  $tournamentMatches;
					$tabContent .=  '</div>';
				$tabContent .=  '</div>';
			}
			// NAVIGATION
			$tabNavigation .= '<ul class="horizontal">';
			$tabNavigation .= '<li class="proli"><a href="#tournament-all">All Matches</a></li>';
			$tabNavigation .= $tabNavigationItems;
			$tabNavigation .= '</ul>';
			// TAB CONTENT
			$html .= '<div class="tabs tabs_default" id="headerNotification">';
				$html .= $tabNavigation;
				$html .= '<div id="tournament-all">';
					$html .=  '<div class="owl-carousel '.$owlSelector.' owl-theme">';
						$html .=  self::html(self::recentMatches());
					$html .=  '</div>';
				$html .=  '</div>';
				$html .= $tabContent;
			$html .= '</div>';
		}
		//$html .= help(self::recentMatches(), false);
		return $html;
	}
	public static function tournaments($tournaments) {
		$cats = [];
		if ($tournaments) {
			$tournaments = explode(',', $tournaments);
			if ($tournaments) {
				foreach ($tournaments as $tournament) {
					$tmp = explode(':', $tournament);
					$cats[$tmp[0]] = $tmp[1];
				}
			}
		}
		return $cats;
	}
	public static function html($matches) {
		$html = '';
		if ($matches) {
			// $html .= '<div class="row">';
				foreach ($matches as $match) {
					// $html .= '<div class="col-sm-4">';
					$html .= '<div class="item">';
						$html .= '<div class="matchContainer">';
						    
						    // $html .= '<div class="status">'. $match['featured'] .'</div>';
							// $html .= '<div class="status">'. $match['status'] .'</div>';
							$html .= '<h3>'. $match['title'] .'</h3>';
							$html .= '<div class="date">'. $match['time'] .'</div>';
							if ($match['subtitle']) $html .= '<span>'. $match['subtitle'] .'</span>';
							// $html .= '<small class="info">'. $match['cats'] .'</small>';
							// if ($items = $match['item']) {
							// 	foreach ($items as $item) {
							// 		$html .= '<div class="toss"><strong>'. $item['title'] .': </strong>'. $item['default'] .'</div>';
							// 	}
							// }
							$html .='<div class="notification-footer">';
								$html .='<a href="'. $match['link'] .'">PREDICTION</a>&nbsp;&nbsp;&nbsp;';
								if ($match['discussion']) $html .='<a class="dis-red" href="'. $match['discussion'] .'">DISCUSSION</a>';
								if ($match['live']) $html .='<div style="margin-top: 10px;"><a class="dis-red live" href="'. $match['live'] .'" target="_blank">LIVE SCORE</a></div>';
							$html .='</div>';
						$html .= '</div>';
					$html .= '</div>';
				}
			// $html .= '</div>';
		}
		return $html;
	}
	public static function recentMatches($tournament=null) {
		$items = [];
		$itemSI = 0;
		// $query = ['post_type' => 'event', 'post_status' => 'publish', 'posts_per_page' => 12,];
		$query = [
			'post_type' => 'event', 
			'post_status' => 'publish', 
			'posts_per_page' => 12, 
			'meta_query' => [['key'=>'pre-featured', 'value' => 'on', 'type' => 'CHAR']],
		];
		if ($tournament) $query['tax_query'] = [['taxonomy' => 'tournament', 'field' => 'term_id', 'terms' => $tournament]];
	    $events = new WP_Query($query);
	    // $events = $events->found_posts;
	    $events = $events->posts;
	    // help($events);
	    if ($events) {
			foreach ($events as $event) {
				$meta  = get_post_meta($event->ID, 'event_ops', true);
				$featured = get_post_meta($event->ID, 'pre-featured', true);
				if (!empty($meta['teams'])) {
	        		foreach ($meta['teams'] as $team) {
	        			$ID     	= predictor_id_from_string($team['name']);
	            		$teamID 	= 'team_'. $ID;
	            		$teamInfo 	= [
	            			'eventID'	=>	$event->ID, 
	            			'title'		=> 	$team['name'], 
	            			'link'		=>	site_url('event/'). $event->post_name,
	            			'time'		=> 	$team['end'] ? date('M d, Y h:i A', strtotime($team['end'])) : '',
	            			'cats' 		=> 	self::getEventCategories($event),
	            			'status'	=> 	strtotime($team['end']) >= time() ? 'Active' : 'Completed',
	            			'subtitle'	=> '',
	            			'discussion'	=> '',
	            			'live'	=> '',
	            			'featured'	=> $featured,
	            		];
	    				if (isset($team['subtitle'])) $teamInfo['subtitle'] = $team['subtitle'] ?? '';
	    				if (isset($team['discussion'])) $teamInfo['discussion'] = $team['discussion'] ?? '';
	    				if (isset($team['live'])) $teamInfo['live'] = $team['live'] ?? '';
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
	            					'options'	=> self::getOptions($option['weight']),
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
}
add_shortcode('header-notification', ['HeaderNotification', 'render']);