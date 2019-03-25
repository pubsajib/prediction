<?php 
function tournamentSummery($tournamentID, $userID, $permited=['all']) {
	$data = '';
	if ($tournamentID && $userID) {
		$prediction = tournamentData($userID, $tournamentID);
		$data = summeryHtml($prediction, $permited);
	}
	echo $data;
}
function tournamentData($userID=1, $tournamentID=4) {
	$data = predictionsOf($userID, $tournamentID);
	return $data;
}
function tournamentsSelectHtml($userID='') {
	$html = '';
	$tournaments = get_terms(['taxonomy' => 'tournament', 'hide_empty' => true,]);
	if ($tournaments) {
		$html .= '<select name="tournaments" id="tournaments" user='. $userID .'>';
			$html .= '<option value="">Select Turnament</option>';
			foreach ($tournaments as $tournament) {
				$html .= '<option value="'. $tournament->term_id .'">'. $tournament->name .'</option>';
			}
		$html .= '</select>';
	}
	echo $html;
}
function eventsByTournament($tournamentID=4) {
	$args = array(
	'post_type' => 'event',
	'fields' => 'ids',
	'posts_per_page' => -1,
	'tax_query' => array(
	    array(
		    'taxonomy' => 'tournament',
		    'field' => 'term_id',
		    'terms' => $tournamentID
	    )
	  )
	);
	$query = new WP_Query( $args );
	return $query->posts;
}