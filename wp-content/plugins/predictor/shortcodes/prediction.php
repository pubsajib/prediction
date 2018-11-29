<?php 
class Prediction {
	public static function render($attr) {
		$attr = shortcode_atts( array(
			'id' => 1,
		), $attr, 'prediction' );
		$html  = '';
		$ID = $attr['id'];
		$html .= '<style>.site-header{ display:none;}</style>';

		if (get_post_type($ID) == 'event') {
			$event = get_post($ID);
			$meta  = get_post_meta($ID, 'event_ops', true);
			$ans   = get_post_meta($ID, 'event_ans', true);
			$answerGiven = @$meta['answers'];
			if (!$meta['restricted']) $html .= answersHTML($meta, $ans);

			// USER MUST LOGGED IN TO INTERACT
			if (is_user_logged_in()) {
				if (getValidUserID(['viewer', 'predictor', 'administrator']) && $meta['restricted']) {
					// GIVEN PREDICTIONS
					$html .= answersHTML($meta, $ans);
				}
				if ($userID = getValidUserID(['predictor', 'administrator'])) {
					// PREDICTIN FORM
					$html .= '<div class="predictionWrapper">';
						if (@!$meta['published']) {
							// $html .= '<h3 class="title">'. $event->post_title .'</h3>';
							// if (!$ans[$userID]) {
							if (true) {
								$html .= '<form action="" method="post">';
									$html .= '<input id="eventID" type="hidden" name="event" value="'. $ID .'">';
									$html .= '<input id="userID" type="hidden" name="user" value="'. $userID .'">';
									if ($meta['teams']) {
										$html .= '<div class="teamQuestionWrapper">';
										foreach ($meta['teams'] as $team) {
											$options = 'team_'. predictor_id_from_string($team['name']);
											if (!$ans[$userID][$options]) {
												$html .= '<div class="box teamQuestionContainer" id="'. $options .'">';
												$html .= '<h3 class="teamName">'. $team['name'] .'</h3>';
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
												$html .= '<button type="button" class="btn btn-md btn-primary saveQAns">Save</button>';
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
				$html .= '<div class="box loginModal"><a href="javascript:;" class="custom-login">Please login </a> to predict.</DIV>';
			}
		} else {
			// INVALID EVENT
			$html .= 'May be your given EVENT ID is wrong';
		}
		
		return $html;
	}
 }
add_shortcode( 'prediction', array( 'Prediction', 'render' ) ); 

if (isset($_POST['prediction'])) {
	$ID = $_POST['event']; unset($_POST['event']);
	$user = $_POST['user']; unset($_POST['user']);
	unset($_POST['prediction']);

	$answers = $_POST;
	updateAnswers($ID, $user, $answers);
}
?>