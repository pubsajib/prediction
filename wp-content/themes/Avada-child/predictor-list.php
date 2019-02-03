<?php
/* Template Name: Predictor List*/
get_header(); 
$permited = ['all', 'match', 'toss'];
$predictors = getPredictorsList();
$ranking = getRakingFor('toss', false, $predictors);
// help($ranking);
if ($ranking['all']) {
	$html = '';
	$html .= '<div class="predictorListWrapper"><div class="equalAll">';
	foreach ($ranking['all'] as $rank) {
		$user = $predictors[$rank['id']];
		$ratingIcon = '';
		$rank = userRankingStatusFor($user->ID, $ranking);
		if ($rank['num']) $ratingIcon = '<p>'. $rank['num'] .'</p>';
		$profileLink = site_url('predictor/?p='. $user->user_login);
		$html .= '<div id="predictor_'. $user->ID .'" class="predictorContainer author-profile-card'. $rank['class'] .'">';
			// USER'S ALL PREDICTIONS
    		$UP = predictionsOf($user->ID);
                        if (!empty($UP['avg'])) {
                            $html .= '<table class="table top-accuracy">';
                                $html .= '<tr>';
									$html .= '<td><small>Accuracy (' . $UP['avg']['all']['participated'] . ') </small><br>' . $UP['avg']['all']['rate'] . '%<br><small class="last"><span class="green">'. $UP['avg']['all']['correct'] . '</span>/<span class="red">'. $UP['avg']['all']['incorrect'] . '</span></small></td>';
                                    
                                    $html .= '<td><small>Match (' . $UP['avg']['match']['participated'] . ') </small><br>' . round($UP['avg']['match']['rate']) . '%<br><small class="last"><span class="green">'. $UP['avg']['match']['correct'] . '</span>/<span class="red">'. $UP['avg']['match']['incorrect'] . '</span></small></td>';
                                    $html .= '<td><small>Toss (' . $UP['avg']['toss']['participated'] . ')</small><br>' . round($UP['avg']['toss']['rate']) . '%<br><small class="last"><span class="green">'. $UP['avg']['toss']['correct'] . '</span>/<span class="red">'. $UP['avg']['toss']['incorrect'] . '</span></small></td>';
                                $html .= '</tr>';
                            $html .= '</table>';
                        }
    		// PROFILE INFORMATION
			$html .= '<div class="profile-info">';
				$html .= profileInfo($user, false, $ratingIcon);
			$html .= '</div>';
			// USER'S TOURNAMENT BASIS PREDICTIONS
			$html .= '<div class="sliderFooter">';
				$html .= '<table class="table">';
				$html .= '<tr>';
				$html .= '<th>League</th>';
				$html .= '<th>Accuracy</th>';
				$html .= '<th>Match</th>';
				$html .= '<th>Toss</th>';
				$html .= '</tr>';
				$html .= '<tr>';
				$data = tournamentData($user->ID, 270);
				if (isset($data['avg'])) {
					$html .= "<td>BBL</td>";
					$html .= "<td>" . round($data['avg']['all']['rate']) . "%</td>";
					$html .= "<td>" . round($data['avg']['match']['rate']) . "% (" . $data['avg']['match']['participated'] . ")</td>";
					$html .= "<td>" . round($data['avg']['toss']['rate']) . "% (" . $data['avg']['toss']['participated'] . ")</td>";
					$html .= '</tr>';
				}
				$data = tournamentData($user->ID, 276);
				if (isset($data['avg'])) {
					$html .= "<td>Smash</td>";
					$html .= "<td>" . round($data['avg']['all']['rate']) . "%</td>";
					$html .= "<td>" . round($data['avg']['match']['rate']) . "% (" . $data['avg']['match']['participated'] . ")</td>";
					$html .= "<td>" . round($data['avg']['toss']['rate']) . "% (" . $data['avg']['toss']['participated'] . ")</td>";
					$html .= '</tr>';
				}
				$data = tournamentData($user->ID, 279);
				if (isset($data['avg'])) {
					$html .= "<td>BPL</td>";
					$html .= "<td>" . round($data['avg']['all']['rate']) . "%</td>";
					$html .= "<td>" . round($data['avg']['match']['rate']) . "% (" . $data['avg']['match']['participated'] . ")</td>";
					$html .= "<td>" . round($data['avg']['toss']['rate']) . "% (" . $data['avg']['toss']['participated'] . ")</td>";
					$html .= '</tr>';
				}
				$data = tournamentData($user->ID, 272);
				if (isset($data['avg'])) {
					$html .= "<td>WBBL</td>";
					$html .= "<td>" . round($data['avg']['all']['rate']) . "%</td>";
					$html .= "<td>" . round($data['avg']['match']['rate']) . "% (" . $data['avg']['match']['participated'] . ")</td>";
					$html .= "<td>" . round($data['avg']['toss']['rate']) . "% (" . $data['avg']['toss']['participated'] . ")</td>";
					$html .= '</tr>';
				}
				$html .= '</table>';
				$html .= '</div>';
				$html .= '<div class="profile-link">';
				$html .= '<a href="'. site_url('predictor/?p='. $user->user_login) .'" target="_blank">VIEW PROFILE</a>';
				$html .= '</div>';

		$html .= '</div>';
	}
	$html .= '</div></div>';
}
echo $html;

get_footer();