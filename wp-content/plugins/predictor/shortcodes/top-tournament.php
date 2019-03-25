<?php 
class tournamenttop {
	public static function render($attr) {
		$defaults = [
			'tournament' => 0, 
			'number' => 10, 
			'html' => 'box', // box, slider, avatar
			'ips' => 3, // items per slide
			'grace' => 0,
			'minitems' => 10,
			'engagement' => 30,
			'type' => 'match',
			'class'=>'predictorListWrapper'
		];
		$attr = shortcode_atts($defaults, $attr, 'tournamenttop');
		$html  = '';
		if ($attr['tournament']) {
			$predictors = self::getPredictorsList();
			$ranking = self::getRakingFor($attr['type'], $attr['tournament'], $predictors, $attr['minitems'], $attr['grace'], $attr['engagement']);
			//$html  .= help($ranking['all'], false);
			if($attr['html'] == 'avatar') $html .= self::avartarHTML($predictors, $ranking, $attr);
			else if($attr['html'] == 'slider') $html .= self::sliderHTML($predictors, $ranking, $attr);
			else $html .= self::html($predictors, $ranking, $attr);
		}
		return $html;
	}
	public static function getRakingFor($ratingType, $tournamentID=false, $predictors='', $minItems=100, $itemGrace=0, $engagement=80) {
		$top3 = 3;
		$top10 = 10;
		$ranking = [];
		$users = [];
		$rankedUsers = ['top3'=>[], 'top10'=>[]];
		// RANKING FOR ALL USERS
		if (!$predictors) $predictors = get_users('role=predictor');
		if ($predictors) {
			foreach ($predictors as $predictor) {
				// LIFE TIME DATA
				$overall    = predictionsOf($predictor->ID);
				$prediction = tournamentData($predictor->ID, $tournamentID);
				$isRankAble = false;
				$overallScore = $overall['avg'][$ratingType]['rate'] ?? 0;
				if (!empty($prediction['avg'])) {
					$score = $prediction['avg'][$ratingType]['rate'];
					$participated = $prediction['avg'][$ratingType]['participated'];
					$criterias = [
						'UID'=>$predictor->ID, 
						'participated' => $participated,
						'minItems' => $minItems, 
						'minEngagement' => $engagement, 
						'accuracy' => $score,
						'grace' => $itemGrace,
					];
					$lifeTimeEvents = count(self::lifeTimePublishedForTournament($criterias['UID'], $tournamentID, $ratingType));
					if ($lifeTimeEvents) {
						$criterias['lifeTimePublishedEvents'] = $lifeTimeEvents;
						$criterias['engagement']  = number_format(($criterias['participated'] / $lifeTimeEvents) * 100, 2);
					} else {
						$criterias['lifeTimePublishedEvents'] = 0;
						$criterias['engagement'] = 0;
					}
					if ($participated) $isRankAble = self::isValidForRanking($criterias);
					$ranking[$predictor->ID]['id'] = $predictor->ID;
					$ranking[$predictor->ID]['eligible'] = $isRankAble;
					$ranking[$predictor->ID]['score'] = $score;
					$ranking[$predictor->ID]['participated'] = $participated;
					$ranking[$predictor->ID]['overall'] = $overallScore;
					$ranking[$predictor->ID]['lifeTimePublishedEvents'] = $criterias['lifeTimePublishedEvents'];
					$ranking[$predictor->ID]['engagement'] = $criterias['engagement'];
					$ranking[$predictor->ID]['minEngagement'] = $criterias['minEngagement'];
					$eligible_sort[] = $isRankAble;
					$score_sort[] = $score;
					$participation_sort[] = $participated;
					$ovarall_sort[] = $overallScore;
				} else {
					$score = 0;
					$participated = 0;
					$ranking[$predictor->ID]['id'] = $predictor->ID;
					$ranking[$predictor->ID]['eligible'] = 0;
					$ranking[$predictor->ID]['score'] = $score;
					$ranking[$predictor->ID]['participated'] = $participated;
					$ranking[$predictor->ID]['overall'] = $overallScore;
					$ranking[$predictor->ID]['lifeTimePublishedEvents'] = 0;
					$ranking[$predictor->ID]['engagement'] = 0;
					$ranking[$predictor->ID]['minEngagement'] = 0;
					$eligible_sort[] = -9999;
					$score_sort[] = $score;
					$participation_sort[] = $participated;
					$ovarall_sort[] = $overallScore;
				}
				$users[$predictor->ID] = $predictor->data;
			}
			// add overall match accuracy
			if (isset($eligible_sort) || isset($score_sort) || isset($participation_sort) || isset($ovarall_sort)) {
				array_multisort(
					$eligible_sort, SORT_DESC, 
					$score_sort, SORT_DESC,
					$participation_sort, SORT_DESC,  
					$ovarall_sort, SORT_DESC,  
					$ranking
				);
			}
		}
		// 	PREDICTORS BY RANK
		if ($ranking) {
			$counter = 1;
			$rankingCount = count($ranking);
			if ($top3 > $rankingCount) $top3 = $rankingCount;
			if ($top10 > $rankingCount) $top10 = $rankingCount;
			foreach ($ranking as $userID => $rank) {
				if ($rank['eligible'] > 80) {
					if ($counter > $top10) break;
					if ($counter <= $top3) $rankedUsers['top3'][] = $rank['id'];
					$rankedUsers['top10'][] = $rank['id'];
					$counter++;
				}
			}
		}
		$rankedUsers['all'] = $ranking;
		return $rankedUsers;
	}
	public static function getPredictorsList() {
		$users = [];
		$predictors = get_users( 'role=predictor' );
		if ($predictors) {
			foreach ($predictors as $predictor) {
				$users[$predictor->ID] = $predictor;
			}
		}
		return $users;
	}
	public static function isValidForRanking($criterias) {
		$devider = 1;
		if ($criterias['minItems'] <= 10) $devider = 1;
		// else if ($criterias['minItems'] <= 20) $devider = 2;
		else if ($criterias['minItems'] <= 30) $devider = 2;
		else if ($criterias['minItems'] <= 50) $devider = 5;
		else if ($criterias['minItems'] <= 100) $devider = 10;
		else $devider = 15;
		$minItemsWithGrace = $criterias['minItems'] - $criterias['grace'];
		if($criterias['participated'] < $minItemsWithGrace) return $criterias['participated'] / $devider; // MIN ITEMS TO PREDICT WITH GRACE
		else if ($criterias['engagement'] < $criterias['minEngagement']) return 60; // ENGAGEMENT
		else if ($criterias['accuracy'] < 50) return 70; // ACCURACY SHOULD NOT BE LESS THAN 50%
		// ACTUAL RANKING BEGAIN
		else if (($criterias['participated']<=$criterias['minItems']) && ($criterias['participated']>$minItemsWithGrace)) return 85;
		else return 100;
	}
	public static function lifeTimePublishedForTournament($userID, $tournamentID, $type='all') {
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
	        'tax_query' => [['taxonomy' => 'tournament', 'field' => 'term_id', 'terms' => $tournamentID]],
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
			                    if (!$type) {
			                    	$published[] = ['event' => $eventID, 'team' => $team['name'], 'item' => $option['title'], 'type' => $option['id']];
			                    } else if($type == $option['id']){
			                    	$published[] = ['event' => $eventID, 'team' => $team['name'], 'item' => $option['title'], 'type' => $option['id']];
			                    }
	            			}
	            		}
	        		}
	        	}
			}
		}
	    return $published;
	}
	public static function avartarHTML($predictors, $ranking, $attr) {
		$html = '';
		if ($ranking['top10']) {
			$owlSelector = 'avatarMatch';
			$html = '';
			$html .=  '<div class="avatarWrapper">';
				foreach ($ranking['top10'] as $rankID => $rank) {
					if ($rankID >= $attr['number']) break;
					$user = $predictors[$rank];
					$ratingIcon = '';
					$rank = userRankingStatusFor($user->ID, $ranking);
					if ($rank['num']) $ratingIcon = '<p>'. $rank['num'] .'</p>';
					$profileLink = site_url('predictor/?p='. $user->user_login);
		    		// PROFILE INFORMATION
					$html .= '<div class="profile-info">';
						$html .= '<div class="author-photo"> '. get_avatar( $user->user_email , '120 ') .'</div>';
        				$html .= '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3>';
        				$accuracy = tournamentData($user->ID, $attr['tournament']);
        					if (!empty($accuracy['avg'])) { 
        						$html .= '<table class="table top-accuracy">';
        							$html .= '<tr>';
        								$html .= '<td>' . $accuracy['avg']['match']['rate'] . '%</td>';
        							$html .= '</tr>';
        						$html .= '</table>';
        					}
					$html .= '</div>';
				}
			$html .= '</div>';
		}
		return $html;
	}
	public static function sliderHTML($predictors, $ranking, $attr) {
		$html = '';
		if ($ranking['top10']) {
			$owlSelector = 'avatarMatch';
			$html = '';
			$html .=  '<div class="owl-carousel '.$owlSelector.' owl-theme">';
				foreach ($ranking['top10'] as $rankID => $rank) {
					if ($rankID >= $attr['number']) break;
					$user = $predictors[$rank];
					$ratingIcon = '';
					$rank = userRankingStatusFor($user->ID, $ranking);
					if ($rank['num']) $ratingIcon = '<p>'. $rank['num'] .'</p>';
					$profileLink = site_url('predictor/?p='. $user->user_login);
					$html .= '<div class="item">';
			    		// PROFILE INFORMATION
						$html .= '<div class="profile-info">';
							$html .= '<div class="author-photo"> '. get_avatar( $user->user_email , '120 ') .' '. $ratingIcon .'</div>';
	        				$html .= '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3>';
						$html .= '</div>';
					$html .= '</div>';
				}
			$html .= '</div>';
		}
		return $html;
	}
	public static function html($predictors, $ranking, $attr) {
		$html = '';
		if ($ranking['top10']) {
			$html .= '<div class="'. $attr['class'] .'">';
			foreach ($ranking['top10'] as $rankID => $rank) {
				if ($rankID >= $attr['number']) break;
				$user = $predictors[$rank];
				$rank = userRankingStatusFor($user->ID, $ranking);
				$profileLink = site_url('predictor/?p='. $user->user_login);
				$html .= '<div id="predictor_'. $user->ID .'" class="predictorContainer author-profile-card sub-tab'. $rank['class'] .'">';
		    		// PROFILE INFORMATION
					$html .= '<div class="profile-info">';
					$bpl = tournamentData($user->ID, $attr['tournament']);
					if (!empty($bpl['avg'])) { 
						$html .= '<table class="table top-accuracy">';
							$html .= '<tr>';
								$html .= '<td><small>Accuracy</small><br>' . $bpl['avg']['match']['rate'] . '</small></td>';
								$html .= '<td><small>Participate</small><br>' . $bpl['avg']['match']['participated'] . '</small></td>';
								$html .= '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $bpl['avg']['match']['correct'] . '</span>/<span class="red">'. $bpl['avg']['match']['incorrect'] . '</span></small></td>';
								// $html .= '<td><small>Over All</small><br>' . $ranking['all'][$rankID]['overall'] . '</small></td>';
								// $html .= '<td><small>Accuracy</small><br>' . $bpl['avg']['all']['rate'] . '%<br><small class="last">(' . $bpl['avg']['all']['participated'] . ')</small></td>';
								// $html .= '<td><small>Toss</small><br>' . $bpl['avg']['toss']['rate'] . '%<br><small class="last">(' . $bpl['avg']['toss']['participated'] . ')</small></td>';
							$html .= '</tr>';
						$html .= '</table>';
					}
						$ratingIcon = $rank['num'] ? '<p>'. $rank['num'] .'</p>' : '';
						$html .= '<div class="author-photo"> '. get_avatar( $user->user_email , '60 ') .' '. $ratingIcon .'</div>';
						$html .= '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3>';
					if (isset($attr['info']) && !empty($attr['info'])) {
			            if ($user->user_url) $html .= '<strong>Website:</strong> <a href="'. $user->user_url .'">'. $user->user_url .'</a><br />';
			            if ($user->user_description) $html .= $user->user_description;
					}
					if (isset($attr['win-loss']) && !empty($attr['win-loss'])) { 						
						// $html .= winLoseHtml($UP, 'match', 9);
						// $html .= winLoseHtml($UP, 'toss', 9);
						$html .= '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user->user_login) .'" target="_blank">VIEW PROFILE</a></div>';
					}
					$html .= '</div>';
				$html .= '</div>';
			}
			$html .= '</div>';
		}
		return $html;
	}
 }
add_shortcode('tournamenttop', ['tournamenttop', 'render']);