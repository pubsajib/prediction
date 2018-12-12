<?php 
class Answers {
	public static function render($attr) {
		$attr = shortcode_atts( ['id' => 1, 'items' => 2], $attr, 'answers' );
		$html  = '';
		$ID = $attr['id'];
		$ditems = $attr['items'];

		if (get_post_type($ID) == 'event') {
			$event = get_post($ID);
			$meta  = get_post_meta($ID, 'event_ops', true);
			$ans   = get_post_meta($ID, 'event_ans', true);
			$answerGiven = @$meta['answers'];
			// GIVEN PREDICTIONS
			$html .= '<div id="answersWrapper_'. $ID .'" class="answersWrapper" event="'. $ID .'" ditems="'. $ditems.'"></div>';
		} else {
			// INVALID EVENT
			$html .= 'May be your given EVENT ID is wrong';
		}
		return $html;
	}
 }
add_shortcode( 'answers', array( 'Answers', 'render' ) );
?>