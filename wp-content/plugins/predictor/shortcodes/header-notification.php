<?php 
class headerNotification {
	public static function render($attr) {
		$defaults = ['class'=>'predictorListWrapper'];
		$attr = shortcode_atts($defaults, $attr, 'headerNotification');
		$matches  = recentMatches();
		$html  = help($matches, false);
		return $html;
	}
 }
add_shortcode('header-notification', ['headerNotification', 'render']);