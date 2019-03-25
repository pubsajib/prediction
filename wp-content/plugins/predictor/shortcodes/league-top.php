<?php 
class leaguetop {
	public static function render($attr) {
		$defaults = [
			'tournament' => 0, 
			'number' => 10, 
			'avatar' => 0,
			'bpl-accuracy' => 0, 
			'bbl-accuracy' => 0, 
			'smash-accuracy' => 0,
			'wbbl-accuracy' => 0,
			'class'=>'predictorListWrapper'
		];
		$attr = shortcode_atts($defaults, $attr, 'leaguetop');
		$html  = '';
		$predictors = self::getPredictorsList();
		$ranking = self::getRakingFor('all', $attr['tournament'], $predictors, 1, 0);
		//$html  .= help($ranking['all'], false);
		$html .= self::html($predictors, $ranking, $attr);
		return $html;
	}
	public static function getRakingFor($ratingType='all', $tournamentID=false, $predictors='', $minItemToPredict=30, $itemGrace=10, $minParticipationRate=70) {
		$top3 = 3;
		$top10 = 10;
		$ranking = [];
		$users = [];
		$rankedUsers = ['top3'=>[], 'top10'=>[]];
		$minParticipationWithGrace = $minItemToPredict - $itemGrace;
		// RANKING FOR ALL USERS
		if (!$predictors) $predictors = get_users('role=predictor');
		if ($predictors) {
			foreach ($predictors as $predictor) {
				// LIFE TIME DATA
				if ($tournamentID) $prediction = tournamentData($predictor->ID, $tournamentID);
				else $prediction = predictionsOf($predictor->ID);
				// help($prediction['avg']['all']);
				$isRankAble = false;
				if (!empty($prediction['avg'])) {
					$participated = $prediction['avg']['all']['participated'];
					$score = $prediction['avg']['all']['rate'];
					$rscore = $prediction['avg'][$ratingType]['rate'];
					$rparticipated = $prediction['avg'][$ratingType]['participated'];
					
					$criterias = [
						'UID'=>$predictor->ID, 
						'participated' => $rparticipated,
						'minLifetimeParticipationRate' => $minParticipationRate, 
						'accuracy' => $rscore,
						'grace' => $minParticipationWithGrace,
					];
					$lifeTimeEvents = count(self::lifeTimePublishedForTournament($criterias['UID'], $tournamentID));
					if ($lifeTimeEvents) {
						$criterias['lifeTimePublishedEvents'] = $lifeTimeEvents;
						$criterias['lifeTimePublishedEventRate']  = number_format(($criterias['participated'] / $lifeTimeEvents) * 100, 2);
					} else {
						$criterias['lifeTimePublishedEvents'] = 0;
						$criterias['lifeTimePublishedEventRate'] = 0;
					}
					if ($participated) $isRankAble = self::isValidForRanking($criterias);
					$ranking[$predictor->ID]['id'] = $predictor->ID;
					$ranking[$predictor->ID]['eligible'] = $isRankAble;
					$ranking[$predictor->ID]['rscore'] = $rscore;
					$ranking[$predictor->ID]['rparticipated'] = $rparticipated;
					$ranking[$predictor->ID]['lifeTimePublishedEvents'] = $criterias['lifeTimePublishedEvents'];
					$ranking[$predictor->ID]['lifeTimePublishedEventRate'] = $criterias['lifeTimePublishedEventRate'];
					$ranking[$predictor->ID]['minLifetimeParticipationRate'] = $criterias['minLifetimeParticipationRate'];
					$ranking[$predictor->ID]['score'] = $score;
					$ranking[$predictor->ID]['participated'] = $participated;

					$eligible_sort[] = $isRankAble;
					$accuracy_sort[] = $rscore;
					$totalParticipated_sort[] = $rparticipated;
				} else {
					$rscore = 0;
					$rparticipated = 0;
					$ranking[$predictor->ID]['id'] = $predictor->ID;
					$ranking[$predictor->ID]['eligible'] = 0;
					$ranking[$predictor->ID]['rscore'] = $rscore;
					$ranking[$predictor->ID]['rparticipated'] = $rparticipated;
					$ranking[$predictor->ID]['lifeTimePublishedEvents'] = 0;
					$ranking[$predictor->ID]['lifeTimePublishedEventRate'] = 0;
					$ranking[$predictor->ID]['minLifetimeParticipationRate'] = 0;
					$ranking[$predictor->ID]['score'] = 0;
					$ranking[$predictor->ID]['participated'] = 0;

					$eligible_sort[] = -9999;
					$accuracy_sort[] = $rscore;
					$totalParticipated_sort[] = $rparticipated;
				}
				$users[$predictor->ID] = $predictor->data;
			}
			// TEST DATA
			// $ranking[3]['id'] = 3;
			// $ranking[3]['score'] = 8235;
			// $ranking[3]['participated'] = 17;
			// $ranking[3]['match'] = 16;
			// $scoreData[] = 8235;
			// $PRType[] = 17;
			// $matchParticipated[] = 16;
			if (isset($eligible_sort) || isset($accuracy_sort) || isset($totalParticipated_sort)) {
				array_multisort(
					$eligible_sort, SORT_DESC, 
					$accuracy_sort, SORT_DESC,
					$totalParticipated_sort, SORT_DESC,  
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
					if ($rank['participated'] >= $minParticipationWithGrace ) {
						if ($counter <= $top3) $rankedUsers['top3'][] = $rank['id'];
						$rankedUsers['top10'][] = $rank['id'];
					}
					// if ($counter <= $top3) $rankedUsers['top3'][$userID] = $users[$userID];
					// $rankedUsers['top10'][$userID] = $users[$userID];
					$counter++;
				}
			}
		}
		$rankedUsers['all'] = $ranking;
		// help($rankedUsers);
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
		$lifeTimeParticipationCriteria = $criterias['minLifetimeParticipationRate'] > $criterias['lifeTimePublishedEventRate'];
// 		$lifeTimeParticipationCriteria = 1;
		if ($criterias['participated'] < 10) return 10;
		else if ($criterias['participated'] < 20) return 20;
		else if ($criterias['participated'] < 30) return 30;
		else if ($criterias['participated'] < 40) return 40;
		else if ($criterias['accuracy'] < 50) return 80;

		// ACTUAL RANKING BEGAIN
		else if ($criterias['grace'] > $criterias['participated']) return 85;
		else if ($lifeTimeParticipationCriteria) {
			// if ($criterias['participated'] < 80) return 95;
			return 90;
		}
		else return 100;
	}
	public static function lifeTimePublishedForTournament($userID, $tournamentID) {
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
			                    $published[] = [
			                    	'event' 	=> $eventID, 
			                    	'team' 		=> $team['name'], 
			                    	'item' 		=> $option['title']
			                    ];
	            			}
	            		}
	        		}
	        	}
			}
		}
	    return $published;
	}
	public static function html($predictors, $ranking, $attr) {
		$html = '';
			if ($ranking['all']) {
			$html .= '<div class="'. $attr['class'] .'">';
			foreach ($ranking['all'] as $rankID => $rank) {
				if ($rankID >= $attr['number']) break;
				$user = $predictors[$rank['id']];
				$rank = userRankingStatusFor($user->ID, $ranking);
				$profileLink = site_url('predictor/?p='. $user->user_login);
				$html .= '<div id="predictor_'. $user->ID .'" class="predictorContainer author-profile-card'. $rank['class'] .'">';
		    		// PROFILE INFORMATION
					$html .= '<div class="profile-info">';
					if ($attr['bpl-accuracy']) {
					$bpl = tournamentData($user->ID, 279);
						$html .= '<table class="table top-accuracy">';
							if (!empty($bpl['avg'])) {   
								$html .= '<tr>';
									$html .= '<td><small>Accuracy</small><br>' . $bpl['avg']['all']['rate'] . '%<br><small class="last">(' . $bpl['avg']['all']['participated'] . ')</small></td>';
									$html .= '<td><small>Match</small><br>' . $bpl['avg']['match']['rate'] . '%<br><small class="last">(' . $bpl['avg']['match']['participated'] . ')</small></td>';
									$html .= '<td><small>Toss</small><br>' . $bpl['avg']['toss']['rate'] . '%<br><small class="last">(' . $bpl['avg']['toss']['participated'] . ')</small></td>';
								$html .= '</tr>';
							}
							$html .= '</table>';
					}/* Top league-accuracy */
					if ($attr['bbl-accuracy']) {
					$bbl = tournamentData($user->ID, 270);
						$html .= '<table class="table top-accuracy">';
							if (!empty($bbl['avg'])) {   
								$html .= '<tr>';
									$html .= '<td><small>Accuracy</small><br>' . $bbl['avg']['all']['rate'] . '%<br><small class="last">(' . $bbl['avg']['all']['participated'] . ')</small></td>';
									$html .= '<td><small>Match</small><br>' . $bbl['avg']['match']['rate'] . '%<br><small class="last">(' . $bbl['avg']['match']['participated'] . ')</small></td>';
									$html .= '<td><small>Toss</small><br>' . $bbl['avg']['toss']['rate'] . '%<br><small class="last">(' . $bbl['avg']['toss']['participated'] . ')</small></td>';
								$html .= '</tr>';
							}
							$html .= '</table>';
					}/* Top league-accuracy */
					if ($attr['smash-accuracy']) {
					$smash = tournamentData($user->ID, 276);
						$html .= '<table class="table top-accuracy">';
							if (!empty($smash['avg'])) {   
								$html .= '<tr>';
									$html .= '<td><small>Accuracy</small><br>' . $smash['avg']['all']['rate'] . '%<br><small class="last">(' . $smash['avg']['all']['participated'] . ')</small></td>';
									$html .= '<td><small>Match</small><br>' . $smash['avg']['match']['rate'] . '%<br><small class="last">(' . $smash['avg']['match']['participated'] . ')</small></td>';
									$html .= '<td><small>Toss</small><br>' . $smash['avg']['toss']['rate'] . '%<br><small class="last">(' . $smash['avg']['toss']['participated'] . ')</small></td>';
								$html .= '</tr>';
							}
							$html .= '</table>';
					}/* Top league-accuracy */
					if ($attr['wbbl-accuracy']) {
					$wbbl = tournamentData($user->ID, 272);
						$html .= '<table class="table top-accuracy">';
							if (!empty($wbbl['avg'])) {   
								$html .= '<tr>';
									$html .= '<td><small>Accuracy</small><br>' . $wbbl['avg']['all']['rate'] . '%<br><small class="last">(' . $wbbl['avg']['all']['participated'] . ')</small></td>';
									$html .= '<td><small>Match</small><br>' . $wbbl['avg']['match']['rate'] . '%<br><small class="last">(' . $wbbl['avg']['match']['participated'] . ')</small></td>';
									$html .= '<td><small>Toss</small><br>' . $wbbl['avg']['toss']['rate'] . '%<br><small class="last">(' . $wbbl['avg']['toss']['participated'] . ')</small></td>';
								$html .= '</tr>';
							}
							$html .= '</table>';
					}/* Top league-accuracy */
					
					
					if ($attr['avatar']) {
						$ratingIcon = $rank['num'] ? '<p>'. $rank['num'] .'</p>' : '';
						$html .= '<div class="author-photo"> '. get_avatar( $user->user_email , '60 ') .' '. $ratingIcon .'</div>';
						$html .= '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3>';
					}
					if ($attr['info']) {
			            if ($user->user_url) $html .= '<strong>Website:</strong> <a href="'. $user->user_url .'">'. $user->user_url .'</a><br />';
			            if ($user->user_description) $html .= $user->user_description;
					}
					if ($attr['win-loss']) { 						
				// 		$html .= winLoseHtml($UP, 'match', 9);
				// 		$html .= winLoseHtml($UP, 'toss', 9);
						$html .= '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user->user_login) .'" target="_blank">VIEW PROFILE</a></div>';
					}
				// 	if ($attr['bplMiddle-accuracy']) {
				// 	$bpl = tournamentData($user->ID, 279);
				// 	if (!empty($bpl['avg'])) {
				// 		$html .= '<div class="sliderFooter">';
				// 			$html .= '<table class="table">';
				// 				$html .= '<tr>';
				// 					$html .= '<th>League</th>';
				// 					$html .= '<th>Accuracy</th>';
				// 					$html .= '<th>Match</th>';
				// 					$html .= '<th>Toss</th>';
				// 				$html .= '</tr>';
				// 				if (!empty($bpl['avg'])) {   
				// 					$html .= '<tr>';
				// 						$html .= "<td>BPL</td>";
				// 						$html .= "<td>" . round($bpl['avg']['all']['rate']) . "%</td>";
				// 						$html .= "<td>" . round($bpl['avg']['match']['rate']) . "% (" . $bpl['avg']['match']['participated'] . ")</td>";
				// 						$html .= "<td>" . round($bpl['avg']['toss']['rate']) . "% (" . $bpl['avg']['toss']['participated'] . ")</td>";
				// 					$html .= '</tr>';
				// 				}
				// 				$html .= '</table>';
				// 			$html .= '</div>';
				// 			}
				// 		}/* league-accuracy */
					$html .= '</div>';
					
				$html .= '</div>';
			}
			$html .= '</div>';
		}
		return $html;
	}
	public static function render_old($attr) {
		$defaults = ['tournament'=>0, 'number' => 10, 'bplMiddle-accuracy' => 0, 'win-loss' => 0, 'avatar' => 0, 'excerpt-avatar' => 0, 'bpl-accuracy' => 0, 'bbl-accuracy' => 0, 'smash-accuracy' => 0, 'wbbl-accuracy' => 0, 'info' => 0, 'class'=>'predictorListWrapper bpl-box'];
		$attr = shortcode_atts($defaults, $attr, 'leaguetop');
		$html  = '';
		$predictors = getPredictorsList();
		$ranking = getRakingForTournament('all', $attr['tournament'], $predictors, 1, 0);
		//$html  .= help($ranking['all'], false);
		if ($ranking['all']) {
			$html .= '<div class="'. $attr['class'] .'">';
			foreach ($ranking['all'] as $rankID => $rank) {
				if ($rankID >= $attr['number']) break;
				$user = $predictors[$rank['id']];
				$rank = userRankingStatusFor($user->ID, $ranking);
				$profileLink = site_url('predictor/?p='. $user->user_login);
				$html .= '<div id="predictor_'. $user->ID .'" class="predictorContainer author-profile-card'. $rank['class'] .'">';
		    		// PROFILE INFORMATION
					$html .= '<div class="profile-info">';
					if ($attr['bpl-accuracy']) {
					$bpl = tournamentData($user->ID, 279);
						$html .= '<table class="table top-accuracy">';
							if (!empty($bpl['avg'])) {   
								$html .= '<tr>';
									$html .= '<td><small>Accuracy</small><br>' . $bpl['avg']['all']['rate'] . '%<br><small class="last">(' . $bpl['avg']['all']['participated'] . ')</small></td>';
									$html .= '<td><small>Match</small><br>' . $bpl['avg']['match']['rate'] . '%<br><small class="last">(' . $bpl['avg']['match']['participated'] . ')</small></td>';
									$html .= '<td><small>Toss</small><br>' . $bpl['avg']['toss']['rate'] . '%<br><small class="last">(' . $bpl['avg']['toss']['participated'] . ')</small></td>';
								$html .= '</tr>';
							}
							$html .= '</table>';
					}/* Top league-accuracy */
					if ($attr['bbl-accuracy']) {
					$bbl = tournamentData($user->ID, 270);
						$html .= '<table class="table top-accuracy">';
							if (!empty($bbl['avg'])) {   
								$html .= '<tr>';
									$html .= '<td><small>Accuracy</small><br>' . $bbl['avg']['all']['rate'] . '%<br><small class="last">(' . $bbl['avg']['all']['participated'] . ')</small></td>';
									$html .= '<td><small>Match</small><br>' . $bbl['avg']['match']['rate'] . '%<br><small class="last">(' . $bbl['avg']['match']['participated'] . ')</small></td>';
									$html .= '<td><small>Toss</small><br>' . $bbl['avg']['toss']['rate'] . '%<br><small class="last">(' . $bbl['avg']['toss']['participated'] . ')</small></td>';
								$html .= '</tr>';
							}
							$html .= '</table>';
					}/* Top league-accuracy */
					if ($attr['smash-accuracy']) {
					$smash = tournamentData($user->ID, 276);
						$html .= '<table class="table top-accuracy">';
							if (!empty($smash['avg'])) {   
								$html .= '<tr>';
									$html .= '<td><small>Accuracy</small><br>' . $smash['avg']['all']['rate'] . '%<br><small class="last">(' . $smash['avg']['all']['participated'] . ')</small></td>';
									$html .= '<td><small>Match</small><br>' . $smash['avg']['match']['rate'] . '%<br><small class="last">(' . $smash['avg']['match']['participated'] . ')</small></td>';
									$html .= '<td><small>Toss</small><br>' . $smash['avg']['toss']['rate'] . '%<br><small class="last">(' . $smash['avg']['toss']['participated'] . ')</small></td>';
								$html .= '</tr>';
							}
							$html .= '</table>';
					}/* Top league-accuracy */
					if ($attr['wbbl-accuracy']) {
					$wbbl = tournamentData($user->ID, 272);
						$html .= '<table class="table top-accuracy">';
							if (!empty($wbbl['avg'])) {   
								$html .= '<tr>';
									$html .= '<td><small>Accuracy</small><br>' . $wbbl['avg']['all']['rate'] . '%<br><small class="last">(' . $wbbl['avg']['all']['participated'] . ')</small></td>';
									$html .= '<td><small>Match</small><br>' . $wbbl['avg']['match']['rate'] . '%<br><small class="last">(' . $wbbl['avg']['match']['participated'] . ')</small></td>';
									$html .= '<td><small>Toss</small><br>' . $wbbl['avg']['toss']['rate'] . '%<br><small class="last">(' . $wbbl['avg']['toss']['participated'] . ')</small></td>';
								$html .= '</tr>';
							}
							$html .= '</table>';
					}/* Top league-accuracy */
					
					
					if ($attr['avatar']) {
						$ratingIcon = $rank['num'] ? '<p>'. $rank['num'] .'</p>' : '';
						$html .= '<div class="author-photo"> '. get_avatar( $user->user_email , '60 ') .' '. $ratingIcon .'</div>';
						$html .= '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3>';
					}
					if ($attr['info']) {
			            if ($user->user_url) $html .= '<strong>Website:</strong> <a href="'. $user->user_url .'">'. $user->user_url .'</a><br />';
			            if ($user->user_description) $html .= $user->user_description;
					}
					if ($attr['win-loss']) { 						
				// 		$html .= winLoseHtml($UP, 'match', 9);
				// 		$html .= winLoseHtml($UP, 'toss', 9);
						$html .= '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user->user_login) .'" target="_blank">VIEW PROFILE</a></div>';
					}
				// 	if ($attr['bplMiddle-accuracy']) {
				// 	$bpl = tournamentData($user->ID, 279);
				// 	if (!empty($bpl['avg'])) {
				// 		$html .= '<div class="sliderFooter">';
				// 			$html .= '<table class="table">';
				// 				$html .= '<tr>';
				// 					$html .= '<th>League</th>';
				// 					$html .= '<th>Accuracy</th>';
				// 					$html .= '<th>Match</th>';
				// 					$html .= '<th>Toss</th>';
				// 				$html .= '</tr>';
				// 				if (!empty($bpl['avg'])) {   
				// 					$html .= '<tr>';
				// 						$html .= "<td>BPL</td>";
				// 						$html .= "<td>" . round($bpl['avg']['all']['rate']) . "%</td>";
				// 						$html .= "<td>" . round($bpl['avg']['match']['rate']) . "% (" . $bpl['avg']['match']['participated'] . ")</td>";
				// 						$html .= "<td>" . round($bpl['avg']['toss']['rate']) . "% (" . $bpl['avg']['toss']['participated'] . ")</td>";
				// 					$html .= '</tr>';
				// 				}
				// 				$html .= '</table>';
				// 			$html .= '</div>';
				// 			}
				// 		}/* league-accuracy */
					$html .= '</div>';
					
				$html .= '</div>';
			}
			$html .= '</div>';
		}
		return $html;
	}
 }
add_shortcode('leaguetop', ['leaguetop', 'render']);