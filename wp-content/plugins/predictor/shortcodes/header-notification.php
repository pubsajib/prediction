<?php 
class headerNotification {
	public static function render($attr) {
		$defaults = ['class'=>'predictorListWrapper'];
		$attr = shortcode_atts($defaults, $attr, 'headerNotification');
		$html  = '';
		$query = ['post_type' => 'event', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids'];
	    $events = new WP_Query($query);
	    $events = $events->posts;
	    if ($events) {
	    	foreach ($events as $event) {
	    		$meta  = get_post_meta($event, 'event_ops', true);
				$ans   = @get_post_meta($event, 'event_ans', true);
				if (@$meta['teams']) {
					foreach ($meta['teams'] as $team) {
						$ID     = predictor_id_from_string($team['name']);
            			$teamID = 'team_'. $ID;
            			if ($meta[$teamID]) {
            				
            			}
					}
				}
	    	}
	    }
		
		$html  = help($events, false);
		
		return $html;
	}
 }
add_shortcode('summery', ['headerNotification', 'render']);