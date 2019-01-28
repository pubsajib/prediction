<?php
/* Template Name: Latest events */
get_header(); 
$matches = latestEvents();
echo '<br><pre>'. print_r($matches, true) .'</pre>';
if ($matches) {
	$counter = 1;
	$data = '';
	$data = '<h3>Items ('. count($matches) .')</h3>';
	$data .= '<table class="ubpublished">';
		$data .= '<thead>';
			$data .= '<tr>';
				$data .= '<th> # </th>';
				$data .= '<th> Team </th>';
				$data .= '<th> Category </th>';
				$data .= '<th> Options </th>';
				$data .= '<th> Status </th>';
				$data .= '<th> Date </th>';
			$data .= '</tr>';
		$data .= '</thead>';
		$data .= '<tbody>';
			foreach ($matches as $match) {
				$class = '';
				$data .= '<tr class="'. $class .'">';
					$data .= '<td> '. $counter .' </td>';
					$data .= '<td> '. $match['team_title'] .' </td>';
					$data .= '<td> '. implode(', ', $match['event_cats']) .' </td>';
					$data .= '<td> '. implode(', ', $match['match_options']) .' </td>';
					$data .= '<td> '. $match['match_published'] .' </td>';
					$data .= '<td> '. $match['team_time'] .' </td>';
				$data .= '</tr>';
				$counter++;
			}
		$data .= '</tbody>';
	$data .= '</table>';
	
	echo $data;
}
get_footer();