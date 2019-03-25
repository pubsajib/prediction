<?php 
class top {
	public static function render($attr) {
		$attr = shortcode_atts(['number' => 3, 'avatar' => 1, 'info' => 0, 'session-accuracy' => 0, 'accuracy' => 0, 'league-accuracy' => 0, 'win-loss' => 0, 'class' => 'predictorListWrapper excerpt'], $attr, 'top');
		$html  = '';
		$predictors = getPredictorsList();
		$ranking = getRakingFor('all', false, $predictors);
		if ($ranking['all']) {
 			$html .= '<div class="'. $attr['class'] . '">';
			foreach ($ranking['all'] as $rankID => $rank) {
				if ($rankID >= $attr['number']) break;
				$user = $predictors[$rank['id']];
				$rank = userRankingStatusFor($user->ID, $ranking);
				$profileLink = site_url('predictor/?p='. $user->user_login);
				$html .= '<div id="predictor_'. $user->ID .'" class="predictorContainer item author-profile-card'. $rank['class'] .'">';
				// PROFILE INFORMATION
				$html .= '<div class="profile-info">';
				if ($attr['accuracy']) {
					$html .= '<div class="accuracy-right">';
						$UP = predictionsOf($user->ID);
                        if (!empty($UP['avg'])) {
                            $html .= '<table class="table top-accuracy">';
                                $html .= '<tr>';
									$html .= '<td><small>Accuracy</small><br>' . $UP['avg']['all']['rate'] . '%<br><small class="last">(' . $UP['avg']['all']['participated'] . ')</small></td>';
                                    
                                    $html .= '<td><small>Match</small><br>' . $UP['avg']['match']['rate'] . '%<br><small class="last">(' . $UP['avg']['match']['participated'] . ')</small></td>';
                                    $html .= '<td><small>Toss</small><br>' . $UP['avg']['toss']['rate'] . '%<br><small class="last">(' . $UP['avg']['toss']['participated'] . ')</small></td>';
                                $html .= '</tr>';
                            $html .= '</table>';
                        }
					$html .= '</div>';
				}/*Accuracy*/
				if ($attr['avatar']) {
					$ratingIcon = $rank['num'] ? '<p>'. $rank['num'] .'</p>' : '';
					$html .= '<div class="author-photo"> '. get_avatar( $user->user_email , '70 ') .' '. $ratingIcon .'</div>';
				}/*Profile Picture*/
				if ($attr['info']) {
					$html .= '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3>';
// 					$html .= '<p>';
					if ($user->user_url) $html .= '<strong>Website:</strong> <a href="'. $user->user_url .'">'. $user->user_url .'</a><br />';
					if ($user->user_description) $html .= $user->user_description;
// 					$html .= '</p>';
				}/*Info*/
				$html .= '</div>';
				if ($attr['session-accuracy']) {
					$html .= '<div class="accuracy-right">';
						$UP = predictionsOf($user->ID);
                        if (!empty($UP['avg'])) {
                            $html .= '<table class="table top-accuracy">';
								$html .= '<tr>';
									$html .= '<td colspan="3" style="padding-top: 0;padding-bottom: 0;text-align: left;"><h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3></td>';
								$html .= '</tr>';
                                $html .= '<tr>';
									$html .= '<td><small>Accuracy</small><br>' . $UP['avg']['all']['rate'] . '%<br><small class="last">(' . $UP['avg']['all']['participated'] . ')</small></td>';
                                    
                                    $html .= '<td><small>Match</small><br>' . $UP['avg']['match']['rate'] . '%<br><small class="last">(' . $UP['avg']['match']['participated'] . ')</small></td>';
                                    $html .= '<td><small>Toss</small><br>' . $UP['avg']['toss']['rate'] . '%<br><small class="last">(' . $UP['avg']['toss']['participated'] . ')</small></td>';
                                $html .= '</tr>';
                            $html .= '</table>';
                        }
					$html .= '</div>';
				}/*Session Accuracy*/	
				
				if ($attr['league-accuracy']) {
					$bbil = tournamentData($user->ID, 270);
					$smash = tournamentData($user->ID, 276);
					$bpl = tournamentData($user->ID, 279);
					$wbbl = tournamentData($user->ID, 272);
					if (!empty($bbil['avg']) || !empty($smash['avg']) || !empty($bpl['avg']) || !empty($wbbl['avg'])) {
						$html .= '<div class="sliderFooter">';
							$html .= '<table class="table">';
								$html .= '<tr>';
									$html .= '<th>League</th>';
									$html .= '<th>Accuracy</th>';
									$html .= '<th>Match</th>';
									$html .= '<th>Toss</th>';
								$html .= '</tr>';
								if (!empty($bbil['avg'])) {
								$html .= '<tr>';
									$html .= "<td>BBL</td>";
									$html .= "<td>" . round($bbil['avg']['all']['rate']) . "%</td>";
									$html .= "<td>" . round($bbil['avg']['match']['rate']) . "% (" . $bbil['avg']['match']['participated'] . ")</td>";
									$html .= "<td>" . round($bbil['avg']['toss']['rate']) . "% (" . $bbil['avg']['toss']['participated'] . ")</td>";
								$html .= '</tr>';
								}
								if (!empty($smash['avg'])) {   
									$html .= '<tr>';
										$html .= "<td>Smash</td>";
										$html .= "<td>" . round($smash['avg']['all']['rate']) . "%</td>";
										$html .= "<td>" . round($smash['avg']['match']['rate']) . "% (" . $smash['avg']['match']['participated'] . ")</td>";
										$html .= "<td>" . round($smash['avg']['toss']['rate']) . "% (" . $smash['avg']['toss']['participated'] . ")</td>";
									$html .= '</tr>';
								}
								if (!empty($bpl['avg'])) {   
									$html .= '<tr>';
										$html .= "<td>BPL</td>";
										$html .= "<td>" . round($bpl['avg']['all']['rate']) . "%</td>";
										$html .= "<td>" . round($bpl['avg']['match']['rate']) . "% (" . $bpl['avg']['match']['participated'] . ")</td>";
										$html .= "<td>" . round($bpl['avg']['toss']['rate']) . "% (" . $bpl['avg']['toss']['participated'] . ")</td>";
									$html .= '</tr>';
								}
								if (!empty($wbbl['avg'])) {   
											$html .= '<tr>';
												$html .= "<td>WBBL</td>";
												$html .= "<td>" . round($wbbl['avg']['all']['rate']) . "%</td>";
												$html .= "<td>" . round($wbbl['avg']['match']['rate']) . "% (" . $wbbl['avg']['match']['participated'] . ")</td>";
												$html .= "<td>" . round($wbbl['avg']['toss']['rate']) . "% (" . $wbbl['avg']['toss']['participated'] . ")</td>";
											$html .= '</tr>';
										}
								$html .= '</table>';
							$html .= '</div>';
							}
						}/* league-accuracy */
						//Win Loss
						if ($attr['win-loss']) { 						
							$html .= winLoseHtml($UP, 'match', 9);
							$html .= winLoseHtml($UP, 'toss', 9);
							$html .= '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user->user_login) .'" target="_blank">VIEW PROFILE</a></div>';
						}
				
				$html .= '</div>';
			}
			$html .= '</div>';
		}
		return $html;
	}
 }
add_shortcode('top', ['top', 'render']);