<?php
/* Template Name: Road To Top */
get_header();
RoadToTop();
$items = profileEvents();
$unpublishedItems = array_filter($items, function($item) {
	return !$item['item']['published'];
});
$publishedItems = array_filter($items, function($item) {
	return $item['item']['published'];
});
// $cat = get_the_terms(13355, 'tournament');
// echo '<br><pre>'. print_r(count($items), true) .'</pre>';
// echo '<br><pre>'. print_r(count($publishedItems), true) .'</pre>';
// echo '<br>unpublishedItems : '.count($unpublishedItems).'<pre>'. print_r($unpublishedItems, true) .'</pre>';
$events = $unpublishedItems;
if ($events) {
	$counter = 1;
	$data = '';
	$data = '<h3>Items ('. count($events) .')</h3>';
	$data .= '<table class="ubpublished">';
		$data .= '<thead>';
			$data .= '<tr>';
				$data .= '<th> # </th>';
				$data .= '<th> Event </th>';
				$data .= '<th> Category </th>';
				$data .= '<th> Team </th>';
				$data .= '<th> Options </th>';
				$data .= '<th> Status </th>';
				$data .= '<th> Date </th>';
			$data .= '</tr>';
		$data .= '</thead>';
		$data .= '<tbody>';
			foreach ($events as $item) {
				$class = $answerBtn = '';
				if ($item['item']['answerable']) {
					$class 	= ' answerable';
					$status = '<a href="'. $item['slug'] .'" class="btn">Participate</a>';
				} else {
					$status = $item['item']['status'];
				}
				$data .= '<tr class="'. $class .'">';
					$data .= '<td> '. $counter .' </td>';
					$data .= '<td> '. $item['event']['title'] .' </td>';
					$data .= '<td> '. $item['event']['cats'] .' </td>';
					$data .= '<td> '. $item['team']['title'] .' </td>';
					$data .= '<td> '. $item['item']['title'] .' </td>';
					$data .= '<td> '. $status .' </td>';
					$data .= '<td> '. $item['team']['time'] .' </td>';
				$data .= '</tr>';
				$counter++;
			}
		$data .= '</tbody>';
	$data .= '</table>';
	
	echo $data;
}
get_footer();