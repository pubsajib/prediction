<?php
/* Template Name: Predictor List*/
get_header(); 
$permited = ['all', 'match', 'toss'];
$predictors = getPredictorsList();
$ranking = getRakingFor('all', false, $predictors);
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
	    				$html .= '<td style="width: 12%">' . round($UP['avg']['all']['rate']) . '%<br><small>Accuracy</small></td>';
						$html .= '<td style="width: 68%;visibility: hidden;">%<br><small>Accuracy</small></td>';
						$html .= '<td style="width: 10%">' . round($UP['avg']['match']['rate']) . '%<br><small>Match</small></td>';
						$html .= '<td style="width: 10%">' . round($UP['avg']['toss']['rate']) . '%<br><small>Toss</small></td>';
	    			$html .= '</tr>';
	    		$html .= '</table>';
    		}
    		// PROFILE INFORMATION
			$html .= '<div class="profile-info">';
				$html .= profileInfo($user, false, $ratingIcon);
			$html .= '</div>';
		$html .= '</div>';
	}
	$html .= '</div></div>';
}
echo $html;

get_footer();