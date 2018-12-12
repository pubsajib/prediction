<?php 
class Prediction {
	public static function render($attr) {
		$attr = shortcode_atts( ['id' => 1, 'items' => 2], $attr, 'prediction' );
		$html  = '';
		$ID = $attr['id'];
		$ditems = $attr['items'];

		if (get_post_type($ID) == 'event') {
			$event = get_post($ID);
			$meta  = get_post_meta($ID, 'event_ops', true);
			$ans   = get_post_meta($ID, 'event_ans', true);
			$answerGiven = @$meta['answers'];
			// GIVEN PREDICTIONS
			$html .= '<div id="answersWrapper_'. $ID .'" class="answersWrapper" event="'. $ID .'" dItems="'. $ditems.'"></div>';
			
			// USER MUST LOGGED IN TO INTERACT
			if (is_user_logged_in()) {
				if ($userID = getValidUserID(['predictor', 'administrator'])) {
					// PREDICTIN FORM
					$html .= '<div class="predictionWrapper">';
						if (@!$meta['published']) {
							// $html .= '<h3 class="title">'. $event->post_title .'</h3>';
							//if (!$ans[$userID]) {
							if (true) {
								$html .= '<form action="" method="post">';
									$html .= '<input id="eventID" type="hidden" name="event" value="'. $ID .'">';
									$html .= '<input id="userID" type="hidden" name="user" value="'. $userID .'">';
									if ($meta['teams']) {
										$html .= '<div class="teamQuestionWrapper">';
										foreach ($meta['teams'] as $team) {
											$teamID = predictor_id_from_string($team['name']);
											$options = 'team_'. $teamID;
											if (@isValidOption($ans[$userID][$options], $team['end'])) {
												$html .= '<div class="teamQuestionContainer" id="'. $options .'">';
												$html .= '<div class="titleContainer">';
												$html .= '<div class="teamName half left"><strong>'. $team['name'] .'</strong></div>';
												$html .= '<div><div class="endTime helf right text-right" id="'. $teamID .'_end">'. $team['end'] .'</div><p class="text-right">Time remaining to predict </p></div>'; 
												$html .= '</div>';
												if ($meta[$options]) {
													foreach ($meta[$options] as $option) {
														$name = $options .'_'. predictor_id_from_string($option['title']);
														$html .= '<div class="predictionContainer" id="'. $name .'">';
															if ($option['weight']) {
																$html .= '<h4 class="title">'. $option['title'] .'</h4>';
																foreach ($option['weight'] as $weight) {
																	if (!$weight['name']) continue;
																	$html .= '<label><input type="radio" name="'. $name .'" value="'. $weight['name'] .'">'. $weight['name'] .'</label>';
																}
															}
														$html .= '</div>';
													}
												}
												$html .= '<button type="button" class="btn btn-green saveQAns">Submit</button>';
												$html .= '</div>';
											}
										} // teamQuestionContainer
										$html .= '</div>';
									}
								$html .= '</form>';
							} else {
								$html .= 'Answer is given';
							}
						} else {
							// Event is already published
							$html .= 'Event prediction time is over';
						}
					$html .= '</div>'; // predictionWrapper end
				}
			} else {
				// NOT LOGGED IN
				$html .= '<div class="loginModal"><a href="javascript:;" class="custom-login fusion-button button-default button-small">login </a> to predict.</div>';
			}
		} else {
			// INVALID EVENT
			$html .= 'May be your given EVENT ID is wrong';
		}
		
		return $html;
	}
 }
add_shortcode( 'prediction', array( 'Prediction', 'render' ) );
?>