<?php 
class Prediction {
	public static function render($attr) {
		$attr = shortcode_atts( ['id' => 1, 'items' => 2], $attr, 'prediction' );
		$html  = '';
		$ID = $attr['id'];
		$ditems = $attr['items'];

		if (get_post_type($ID) != 'event') $html .= 'May be your given EVENT ID is wrong'; // INVALID EVENT
		else {
			$event = get_post($ID);
			$meta  = get_post_meta($ID, 'event_ops', true);
			$ans   = get_post_meta($ID, 'event_ans', true);
			// GIVEN PREDICTIONS
			$html .= '<div id="answersWrapper_'. $ID .'" class="answersWrapper" event="'. $ID .'" dItems="'. $ditems.'"></div>';
			
			// USER MUST LOGGED IN TO INTERACT
			if (!is_user_logged_in()) {
				// NOT LOGGED IN
				$html .= '<div class="loginModal"><a href="javascript:;" class="custom-login fusion-button button-default button-small">login </a> to predict.</div>';
			} else {
				if ($userID = getValidUserID(['predictor', 'administrator'])) {
					// PREDICTIN FORM
					$html .= '<div class="predictionWrapper">';
						if (@$meta['published']) $html .= 'Event prediction time is over'; // Event is already published
						else {
							$html .= '<form action="" method="post">';
								$html .= '<input id="userID" type="hidden" name="user" value="'. $userID .'">';
								$html .= '<input id="eventID" type="hidden" name="event" value="'. $ID .'">';
								$html .= '<input id="TID" type="hidden" name="team">';
								$html .= '<input id="QID" type="hidden" name="qid">';
								$html .= '<input id="QAns" type="hidden" name="qans">';
								if ($meta['teams']) {
									$html .= '<div class="teamQuestionWrapper">';
									foreach ($meta['teams'] as $team) {
										$teamID = predictor_id_from_string($team['name']);
										$options = 'team_'. $teamID;
										if (@isValidOption($ans[$userID][$options], $team['end'])) {
											$questions = '';
											if ($meta[$options]) {
												foreach ($meta[$options] as $option) {
													$question = $tossTime = '';
													$name = $options .'_'. predictor_id_from_string($option['title']);
													if (@ !$ans[$userID][$name]) {
														if ($option['id'] == 'toss') {
															$tossTime =  $option['time'] ? $option['time'] : 30;
															$tossTime =  date('Y-m-d H:i:s',strtotime("-". $tossTime ." minutes",strtotime($team['end'])));
															if (!isValidOption('', $tossTime)) continue;
														}
														$question .= '<div class="predictionContainer" id="'. $name .'">';
															if ($option['weight']) {
																$question .= '<h4 class="title">'. $option['title'] .'</h4>';
																if ($tossTime ) $question .= '<div class="endToss" id="'. $name .'_end">'. $tossTime .'</div>';
																foreach ($option['weight'] as $weight) {
																	if (!$weight['name']) continue;
																	$question .= '<label><input type="radio" name="'. $name .'" value="'. $weight['name'] .'">'. $weight['name'] .'</label>';
																}
															}
															$question .= '<button type="button" class="btn btn-green saveQAns">Submit</button>';
														$question .= '</div>';
													}
													$questions .= $question;
												}
											}
											if ($question) {
												$html .= '<div class="teamQuestionContainer" id="'. $options .'">';
												$html .= '<div class="titleContainer">';
												$html .= '<div class="teamName half left"><strong>'. $team['name'] .'</strong></div>';
												$html .= '<div><div class="endTime helf right text-right" id="'. $teamID .'_end">'. $team['end'] .'</div><p class="text-right">Time remaining to predict </p></div>'; 
												$html .= '</div>';
												$html .= $questions;
												$html .= '</div>';
											}
										}
									} // teamQuestionContainer
									$html .= '<div class="notice">';
										$html .= '<div class="alert">';
  											$html .= '<span class="closebtn">&times;</span>';
   											$html .= '<h3 style="color: #fff;">Road to top 10</h3>';
   											$html .= '<ul style="margin-left: 15px;">';
  												$html .= '<li>Participate <strong>70</strong> events ( it just take less then 3 weeks, if you participate both tosses/matches).</li>';
  												$html .= '<li>Make sure to participate 75% events from your starting date .. else even if you have good accuracy, you won’t able to qualify for top10 or may get dropped from the position.</li>';
  												$html .= '<li>And finally, you have to ensure at-least <strong>40%</strong> accuracy to be eligible for top10.</li>';
 										$html .= '</div>';
									$html .= '</div>';
									$html .= '</div>';
								}
							$html .= '</form>';
						}
					$html .= '</div>'; // predictionWrapper end
				}
			}
		}
		return $html;
	}
 }
add_shortcode( 'prediction', array( 'Prediction', 'render' ) );