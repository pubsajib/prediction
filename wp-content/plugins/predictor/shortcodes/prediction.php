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
			// $html .= help($meta, false);
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
								$html .= '<input id="eventID" type="hidden" name="event" value="'. $ID .'">';
								$html .= '<input id="userID" type="hidden" name="user" value="'. $userID .'">';
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
													if ($option['id'] == 'toss') {
														$tossTime =  $option['time'] ? $option['time'] : 30;
														$tossTime =  date('Y-m-d H:i:s',strtotime("-". $tossTime ." minutes",strtotime($team['end'])));
														// $question .= '<br> Main : '. $team['end'] .' === Time : '. $tossTime;
													}
													if ($tossTime && !isValidOption('', $tossTime)) { // CHECK TOSS TIME
														$question .= '<div class="predictionContainer">';
															$question .= '<h4 class="title">'. $option['title'] .'</h4>';
															$question .= '<p class="text-danger">Toss time is over.</p>';
														$question .= '</div>';
													} else {
														if (@$ans[$userID][$name]) {
															$question .= '<div class="predictionContainer">';
																$question .= '<h4 class="title">'. $option['title'] .'</h4>';
																$question .= '<p class="text-success">Answer is given.</p>';
															$question .= '</div>';
														} else {
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
?>