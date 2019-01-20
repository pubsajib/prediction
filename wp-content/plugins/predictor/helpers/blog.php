<?php 
function getCurrentState($postID) {
	$meta = get_post_meta($postID, 'date_ops', true);
	$startTime = !empty($meta['start']) ? strtotime($meta['start']) : 0;
	$endTime = !empty($meta['end']) ? strtotime($meta['end']) : 0;
	$currentTime = time();
	if ($startTime && $endTime) {
		// echo "<br>TimeZone : ". date_default_timezone_get();
		// echo "<br>Time : ". date('m-d-Y h:i:s A', $currentTime);
		if ($startTime > $endTime) return ''; // Time input error
		if ($currentTime >= $startTime && $currentTime <= $endTime) return 'running';
		if ($currentTime < $startTime ) return 'upcoming';
		if ($currentTime > $startTime) return ''; // end
	}
	return false;
}