<?php 
class tossTop {
	public static function render($attr) {
		$defaults = [
			'number' => 10, 
			'avatar' => 0,
			'class' => 'predictorListWrapper excerpt'
		];
		$attr = shortcode_atts($defaults, $attr, 'tossTop');
		$html  = '';
		$predictors = self::getPredictorsList();
		$ranking = self::getRakingFor('toss', false, $predictors);
		// $html .= help($ranking, false);
		if($attr['avatar']) $html .= self::htmlAvatarOnly($predictors, $ranking, $attr);
		else $html .= self::html($predictors, $ranking, $attr);
		return $html;
	}
	public static function getRakingFor($ratingType, $tournamentID=false, $predictors='', $minItemToPredict=100, $itemGrace=10, $minParticipationRate=50) {
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
					$lifeTimeEvents = count(lifeTimePublished($criterias['UID'], $ratingType));
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
		// $lifeTimeParticipationCriteria = 1;
		if ($criterias['participated'] < 10) return 10;
		else if ($criterias['participated'] < 20) return 20;
		else if ($criterias['participated'] < 30) return 30;
		else if ($criterias['participated'] < 40) return 40;
		else if ($criterias['participated'] < 50) return 50;
		else if ($criterias['participated'] < 60) return 60;
		else if ($criterias['participated'] < 70) return 70;
		else if ($criterias['participated'] < 100) return 75;
		else if ($criterias['accuracy'] < 50) return 80;

		// ACTUAL RANKING BEGAIN
		else if ($criterias['grace'] > $criterias['participated']) return 85;
		else if ($lifeTimeParticipationCriteria) {
			// if ($criterias['participated'] < 80) return 95;
			return 90;
		}
		else return 100;
	}
	public static function htmlAvatarOnly($predictors, $ranking, $attr) {
		$html = '';
		if ($ranking['top10']) {
			$owlSelector = 'avatarToss';
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
						$html .= '<div class="profile-info'. $rank['class'] .'">';
							$html .= '<div class="author-photo"> '. get_avatar( $user->user_email , '120 ') .' '. $ratingIcon .'</div>';
	        				$html .= '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3>';
	        				// USER'S ALL PREDICTIONS
        		    		$UP = predictionsOf($user->ID);
                            if (!empty($UP['avg'])) {
                                $html .= '<table class="table top-accuracy">';
                                    $html .= '<tr>';
        								$html .= '<td>' . $UP['avg']['toss']['rate'] . '%</td>';
                                    $html .= '</tr>';
                                $html .= '</table>';
                            }
						$html .= '</div>';
					$html .= '</div>';
				}
			$html .= '</div>';
		}
		return $html;
	}
	public static function html($predictors, $ranking, $attr) {
		$html = '';
		if ($ranking['all']) {
			$html = '';
			$html .= '<div class="predictorListWrapper"><div class="equalAll">';
			foreach ($ranking['all'] as $rankID => $rank) {
				if ($rankID >= $attr['number']) break;
				$user = $predictors[$rank['id']];
				$ratingIcon = '';
				$rank = userRankingStatusFor($user->ID, $ranking);
				if ($rank['num']) $ratingIcon = '<p>'. $rank['num'] .'</p>';
				$profileLink = site_url('predictor/?p='. $user->user_login);
				$html .= '<div id="predictor_'. $user->ID .'" class="predictorContainer author-profile-card'. $rank['class'] .'">';
					// USER'S ALL PREDICTIONS
		    		$UP = predictionsOf($user->ID);
                    if (!empty($UP['avg'])) {
                        $html .= '<table class="table top-accuracy">';
                            $html .= '<tr>';
                                $html .= '<td><small>Toss (' . $UP['avg']['toss']['participated'] . ')</small><br>' . $UP['avg']['toss']['rate'] . '%<br><small class="last"><span class="green">'. $UP['avg']['toss']['correct'] . '</span>/<span class="red">'. $UP['avg']['toss']['incorrect'] . '</span></small></td>';
								$html .= '<td>';
									$t20 = tournamentData($user->ID, 267);
										if (isset($t20['avg'])) {
											$html .= '<small>T20 (' . $t20['avg']['toss']['participated'] . ') </small><br>' . $t20['avg']['toss']['rate'] . '%<br><small class="last"><span class="green">'. $t20['avg']['toss']['correct'] . '</span>/<span class="red">'. $t20['avg']['toss']['incorrect'] . '</span></small>';
										}
								$html .= '<td>';
								$html .= '<td>';
									$odi = tournamentData($user->ID, 266);
										if (isset($odi['avg'])) {
											$html .= '<small>ODI (' . $odi['avg']['toss']['participated'] . ') </small><br>' . $odi['avg']['toss']['rate'] . '%<br><small class="last"><span class="green">'. $odi['avg']['toss']['correct'] . '</span>/<span class="red">'. $odi['avg']['toss']['incorrect'] . '</span></small>';
										}
								$html .= '<td>';
                            $html .= '</tr>';
                        $html .= '</table>';
                    }
		    		// PROFILE INFORMATION
					$html .= '<div class="profile-info">';
						$html .= profileInfo($user, false, $ratingIcon);
					$html .= '</div>';
					// USER'S TOURNAMENT BASIS PREDICTIONS
					$html .= '<div class="sliderFooter">';
						$html .= '<table class="table">';
						$html .= '<tr>';
						$html .= '<th>League</th>';
						$html .= '<th>Accuracy</th>';
						$html .= '</tr>';
						$html .= '<tr>';
						$t20 = tournamentData($user->ID, 267);
						if (isset($t20['avg'])) {
							$html .= "<td>T20</td>";
							$html .= "<td>" . round($t20['avg']['toss']['rate']) . "% (" . $t20['avg']['toss']['participated'] . ")</td>";
							$html .= '</tr>';
						}
						$odi = tournamentData($user->ID, 266);
						if (isset($odi['avg'])) {
							$html .= "<td>ODI</td>";
							$html .= "<td>" . round($odi['avg']['toss']['rate']) . "% (" . $odi['avg']['toss']['participated'] . ")</td>";
							$html .= '</tr>';
						}
						$test = tournamentData($user->ID, 265);
						if (isset($test['avg'])) {
							$html .= "<td>Test</td>";
							$html .= "<td>" . round($test['avg']['toss']['rate']) . "% (" . $test['avg']['toss']['participated'] . ")</td>";
							$html .= '</tr>';
						}
						$html .= '</table>';
						$html .= '</div>';
					$html .= '<div class="profile-link">';
					$html .= '<a href="'. site_url('predictor/?p='. $user->user_login) .'" target="_blank">VIEW PROFILE</a>';
					$html .= '</div>';
				$html .= '</div>';
			}
			$html .= '</div></div>';
		}
		return $html;
	}
 }
add_shortcode('tossTop', ['tossTop', 'render']);