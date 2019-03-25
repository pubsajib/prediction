<?php 
class EventPrediction {
	public static function render($attr) {
		$attr = shortcode_atts( ['id' => '13297,13383,13345'], $attr, 'prediction' );
		$html  = '';
		if (!empty($attr['id'])) {
			$wrapperID = str_replace(',', '_', $attr['id']);
			$html .= '<div id="answersWrapper_'. $wrapperID .'" class="eventsAnswersWrapper" event="'. $wrapperID .'"></div>';
		}
		return $html;
	}
}
add_shortcode( 'multi-event', ['EventPrediction', 'render'] );