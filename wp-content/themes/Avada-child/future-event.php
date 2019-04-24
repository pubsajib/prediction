<?php
/* Template Name: Future Event */
get_header();
?>
	<link rel = "stylesheet" href = "https://cricdiction.com/wp-content/plugins/predictor/frontend/css/res-timeline.css"/>
	<!--<script type = "text/javascript" src ="https://cricdiction.com/wp-content/plugins/predictor/frontend/js/calendar-jquery.min.js"></script>	-->
	<script type = "text/javascript" src="https://cricdiction.com/wp-content/plugins/predictor/frontend/js/res-timeline.js"></script>
<body>
<?php

$events = calendarEvents();
if ($events) {
	$data = '';
	$data .= '<div class ="jflatTimeline">';
	    $data .= '<div class="datepickerWrapper"><input type="date" id="matchesDatepicker"><span id="calendar_text"><span> '. date('d M Y') .'</span> <img src="https://cricdiction.com/wp-content/plugins/predictor/frontend/img/calendar.png"></span></div>';
		$data .= '<div class ="timeline-wrap">';
			foreach ($events as $eventDate => $cats) {
				if ($cats) {
					$selected = date('Ymd') == date('Ymd', strtotime($eventDate)) ? 'selected' : '';
					$data .= '<div class="event '. $selected .'" data-date="'. date('Y-m-d', strtotime($eventDate)) .'">';
					foreach ($cats as $catSlug => $cat) {
						$catName = $cat['name'];
						unset($cat['name']);
						$data .= '<div class="eventWrapper">';
							$data .= '<div class="title">'. $catName .'</div>';
							if ($cat) {
								foreach ($cat as $eventSI => $event) {
									if ($event['match']) {
										foreach ($event['match'] as $item) {
											if (isset($item['opt']['match']) && !empty($item['opt']['match'])) {
											    $discussion = $item['dis'] ?? false;
									            $subTitle   = !empty($item['sub']) ? $item['sub'] .', ' : '';
												$singleItem = isset($item['opt']['match']) && !empty($item['opt']['match']) ? $item['opt']['match'] : [];
												$tossItem = isset($item['opt']['toss']) && !empty($item['opt']['toss']) ? $item['opt']['toss'] : [];
												$default = !empty($tossItem['default']) ? '<div class="result"><strong>'. $tossItem['default'] .'</strong> won the toss</div>' : '';
												$data .= '<div class="item">';
													$data .= $default ? '<div class="status">Result</div>' : '';
													$data .= '<div class="time">'. $subTitle. $item['time'] .'</div>';
													$data .= $singleItem['teams'];
													$data .= $default;
													$data .= '<div class="footer">';
													    $data .= '<a href="'. site_url('/event/'. $event['slug']) .'" class="fusion-button button-default button-small predict">View Prediction</a>';
													    if ($discussion) $data .= '<a href="'. $discussion .'" class="fusion-button button-default button-small" target="_blank">Discussion</a>';
													$data .= '</div>';
												$data .= '</div>';
											}
											$data .= !empty($item['opt']['add']) ? '<div class="item">'. $item['opt']['add'] .'</div>': ''; // LOAD ADD
										}
									}
								}
							}
						$data .= '</div>';
					}
				}
				$data .= '</div>';
			}
			$data .= '<div class="event notFound"><div class="eventWrapper">Not found</div></div>';
		$data .= '</div>';
	$data .= '</div>';
	echo $data;
}

?>
</body>
<?php
get_footer();