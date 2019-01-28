<?php 
class EventPrediction {
	public static function render($attr) {
		$attr = shortcode_atts( ['id' => '13297,13383,13345', 'items' => 2], $attr, 'prediction' );
		$html  = '';
		$ID = explode(',', $attr['id']);
		$wrapperID = implode('_', $ID);
		// $wrapperID = 13345;
		// if ($ID) $ID = $ID[2];
		$ditems = $attr['items'];
		$html .= '<div id="answersWrapper_'. $wrapperID .'" class="eventsAnswersWrapper" event="'. $wrapperID .'" ditems='. $attr['items'] .'></div>';
		return $html;
	}
}
add_shortcode( 'multi-event', ['EventPrediction', 'render'] );