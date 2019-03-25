<?php
/* Template Name: Future Event */
get_header();
?>
	<link rel = "stylesheet" href = "https://cricdiction.com/wp-content/plugins/predictor/frontend/css/res-timeline.css"/>
	<script type = "text/javascript" src ="https://cricdiction.com/wp-content/plugins/predictor/frontend/js/calendar-jquery.min.js"></script>	
	<script type = "text/javascript" src="https://cricdiction.com/wp-content/plugins/predictor/frontend/js/res-timeline.js"></script>
<body>
<?php

$events = calendarEvents();
help($events);
if ($events) {
	$html = '';
	$html .= '<div class = "jflatTimeline">';
		$html .= '<div class = "timeline-wrap">';
			foreach ($events as $eventDate => $cats) {
				if ($cats) {
					$selected = date('Ymd') == date('Ymd', strtotime($eventDate)) ? 'selected' : '';
					$html .= '<div class="event '. $selected .'" data-date="'. $eventDate .'">';
					foreach ($cats as $catSlug => $cat) {
						$catName = $cat['name'];
						unset($cat['name']);
						$html .= '<div class="eventWrapper">';
							$html .= '<div class="title">'. $catName .'</div>';
							if ($cat) {
								foreach ($cat as $eventSI => $event) {
									if ($event['match']) {
										foreach ($event['match'] as $item) {
											if (isset($item['opt']['match']) && !empty($item['opt']['match'])) {
											    $discussion = $item['dis'] ?? false;
									            $subTitle   = !empty($item['sub']) ? $item['sub'] .', ' : '';
												$singleItem = isset($item['opt']['match']) && !empty($item['opt']['match']) ? $item['opt']['match'] : [];
												$tossItem = isset($item['opt']['toss']) && !empty($item['opt']['toss']) ? $item['opt']['toss'] : [];
												$default = $tossItem['default'] ? '<div class="result"><strong>'. $tossItem['default'] .'</strong> won the toss</div>' : '';
												$html .= '<div class="item">';
													$html .= $default ? '<div class="status">Result</div>' : '';
													$html .= '<div class="time">'. $subTitle. $item['time'] .'</div>';
													$html .= $singleItem['teams'];
													$html .= $default;
													$html .= '<div class="footer">';
													    $html .= '<a href="'. $event['slug'] .'" class="fusion-button button-default button-small predict">View Prediction</a>';
													    if ($discussion) $html .= '<a href="'. $discussion .'" class="fusion-button button-default button-small" target="_blank">Discussion</a>';
													$html .= '</div>';
												$html .= '</div>';
											}
										}
									}
								}
							}
						$html .= '</div>';
					}
				}
				$html .= '</div>';
			}
		$html .= '</div>';
	$html .= '</div>';
	echo $html;
}

?>
</body>
<?php
get_footer();