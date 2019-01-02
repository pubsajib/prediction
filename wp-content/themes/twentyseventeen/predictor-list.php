<?php
/* Template Name: Predictor List*/
get_header(); 
$permited = ['all', 'match', 'toss'];
$predictors = get_users( 'role=predictor' );
// Array of WP_User objects.
if ($predictors) {
	$html = '';
	$html .= '<div class="predictorListWrapper">';
	foreach ( $predictors as $user ) {
		$profileLink = site_url('predictor/?p='. $user->user_login);
		$html .= '<div class="predictorContainer author-profile-card">';
			// USER'S ALL PREDICTIONS
    		$data = predictionsOf($user->ID);
    		if (@$data['avg']) {
				$html .= '<table class="table top-accuracy">';
	    			$html .= '<tr>';
	    				$html .= '<td style="width: 80%">' . round($data['avg']['all']['rate']) . '%<br><small>Accuracy</small></td>';
	    				$html .= '<td style="width: 10%">' . round($data['avg']['match']['rate']) . '%<br><small>Match</small></td>';
	    				$html .= '<td style="width: 10%">' . round($data['avg']['toss']['rate']) . '%<br><small>Toss</small></td>';
	    			$html .= '</tr>';
	    		$html .= '</table>';
    		}
    		// PROFILE INFORMATION
			$html .= '<div class="profile-info">';
				$html .= profileInfo($user, false);
			$html .= '</div>';
			// USER'S TOURNAMENT BASIS PREDICTIONS
			$data = tournamentData($user->ID, 4);
			if (@$data['avg']) {
				$html .= '<table class="table">';
					$html .= '<tr>';
						$html .= '<th>League</th>';
						$html .= '<th>Accuracy</th>';
						$html .= '<th>Match</th>';
						$html .= '<th>Toss</th>';
					$html .= '</tr>';
					$html .= '<tr>';
						$html .= "<td rowspan='2'>BBL</td>";
						$html .= "<td>" . round($data['avg']['all']['rate']) . "%</td>";
						$html .= "<td>" . round($data['avg']['match']['rate']) . "% (" . $data['avg']['match']['participated'] . ")</td>";
						$html .= "<td>" . round($data['avg']['toss']['rate']) . "% (" . $data['avg']['toss']['participated'] . ")</td>";
					$html .= '</tr>';	
				$html .= '</table>';
			}

		$html .= '</div>';
	}
	$html .= '</div>';
}
echo $html;
?>
<style>
	.predictorListWrapper{width: 80%;margin-left: auto;margin-right: auto;text-align: center;}
	.predictorContainer{min-height: 100px;border: 1px solid red; margin-bottom: 20px;}
</style>
<div class="predictorListWrapper" style="display: none;">
	<div class="predictorContainer author-profile-card"><div class="profile-info"> Lorem ipsum dolor sit amet.</div></div>
	<div class="predictorContainer author-profile-card"><div class="profile-info"> Lorem ipsum dolor sit amet.</div></div>
	<div class="predictorContainer author-profile-card"><div class="profile-info"> Lorem ipsum dolor sit amet.</div></div>
	<div class="predictorContainer author-profile-card"><div class="profile-info"> Lorem ipsum dolor sit amet.</div></div>
</div>
<?php get_footer();