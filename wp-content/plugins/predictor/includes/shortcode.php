<?php 
class Prediction {
	public static function render($attr) {
		$attr = shortcode_atts( array(
			'id' => 1,
		), $attr, 'prediction' );
		$html  = '';
		$ID = $attr['id'];
		$userID = get_current_user_id();
		if (get_post_type($ID) == 'event') {
			$event = get_post($ID);
			$meta  = get_post_meta($ID, 'event_ops', true);
			$ans   = get_post_meta($ID, 'event_ans', true);
			$answerGiven = @$meta['answers'];

			// GIVEN PREDICTIONS
			$html .= '<style>.site-header{ display:none;}</style>';
			$html .= answersHTML($meta, $ans);
			
			// PREDICTIN FORM
			$html .= '<div class="box predictionWrapper">';
				if (@!$meta['published']) {
				$html .= '<h3 class="title">'. $event->post_title .'</h3>';
					if (!$answerGiven[$userID]) {
						$html .= '<form action="" method="post">';
							$html .= '<input type="hidden" name="event" value="'. $ID .'">';
							$html .= '<input type="hidden" name="user" value="'. $userID .'">';
							if ($meta['options']) {
								foreach ($meta['options'] as $option) {
									$name = predictor_id_from_string($option['title']);
									$html .= '<div class="predictionContainer">';
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
							$html .= '<button type="submit" name="prediction" class="btn btn-md btn-primary">Save</button>';
						$html .= '</form>';
					} else {
						$html .= 'Answer is given';
					}
				} else {
					// Event is already published
					$html .= 'Event prediction time is over';
				}
			$html .= '</div>';
		} else {
			$html .= 'May be your given EVENT ID is wrong';
		}

		// $html .= '<br><pre>'. print_r($ans, true) .'</pre>';
		help(get_post_meta($ID));
		return $html;
	}
 }
add_shortcode( 'prediction', array( 'Prediction', 'render' ) ); 

if (isset($_POST['prediction'])) {
	$ID = $_POST['event']; unset($_POST['event']);
	$user = $_POST['user']; unset($_POST['user']);
	unset($_POST['prediction']);

    help($_POST);
    serialize($_POST);
	$answers = get_post_meta($ID, 'event_ans');
    help($answers);
		delete_post_meta($ID, 'event_ans');
    $answers[$user] = $_POST;
    $_POST = '';
    // $answers = [1,2];
		add_post_meta( $ID, 'event_ans', $answers );
 //    if (!$answers) { 
	//     update_post_meta( $ID, 'event_ans', $answers );
	// } else {
	// }
}

?>