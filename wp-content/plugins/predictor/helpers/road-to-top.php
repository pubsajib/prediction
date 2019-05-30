<?php 
function roadToTop($user=null, $min=null) {
	$min = ['avg' => 50, 'match' => 70, 'engagement' => 40];
	$user = wp_get_current_user();
	if ( !in_array( 'predictor', (array) $user->roles ) ) echo "Not a predictor";
	else {
		$type = 'all';
		$ranking  = getRakingFor();
		$rankInfo = 2;
		$UP = predictionsOf($user->ID);
		$lifeTimeEvents = count(lifeTimePublished($user->ID, $type));
		$toalPublished = count(totalPublished($type));
		$engagement = 0;
		if (isset($rankInfo['participated'])) {
			if ($lifeTimeEvents) $engagement = ($rankInfo['participated'] / $lifeTimeEvents) * 100;
			$engagement = number_format($engagement, 2);
		}
		echo '<div class="login-profile">';
		// RANK
		if ($rankInfo) {
			echo '<div class="item">
				<h3>My Rank</h3>
				<div class="circle"><p><strong>'. $rankInfo['rank'] .'</strong></p></div>
				<div class="additional"><span><strong>Among:</strong> '. count($ranking['all']) .' </span></div>
			</div>';
		}
		// ACCURICY
		if ($UP['avg']) {
		    $class = $UP['avg'] >= $min['avg'] ? 'green' : 'red';
			echo '<div class="item">
					<h3>Accuracy</h3>
					<div class="circle '. $class .'">
						<p><strong>'. $UP['avg']['all']['rate'] .'%</strong></p>
					</div>
					<div class="additional">
						<span style="display: inline-block"><strong>Match:</strong> '. $rankInfo['matchAccuracy'] .'%</span>
						<span style="display: inline-block"><strong>Toss:</strong> '. $rankInfo['tossAccuricy'] .'%</span>
					</div>
			</div>';
		}
		// ENGAGEMENT (red/green)
		if ($lifeTimeEvents) {
		    $class = $engagement >= $min['engagement'] ? 'green' : 'red';
			echo '<div class="item">
					<h3>Engagement</h3>
					<div class="circle '. $class .'">
						<p><strong>'. $engagement .'%</strong></p>
					</div>
					<div class="additional">
						<span>Your minimal engagement should be <strong>40%</strong></span>
					</div>
			</div>';
		}
		// PARTICIPATED
		if ($rankInfo) {
		    $class = $rankInfo['participated'] >= $min['match'] ? 'green' : 'red';
			echo '<div class="item">
					<h3>Participated</h3>
					<div class="circle '. $class .'">
						<p><strong>'. $rankInfo['participated'] .'<strong></p>
					</div>
					<div class="additional">
						<span style="display: inline-block"><strong>Match:</strong> '. $UP['avg']['match']['participated'] .' </span>
						<span style="display: inline-block"><strong>Toss:</strong> '. $UP['avg']['toss']['participated'] .' </span>
					</div>
			</div>';
		}
		echo '</div>';
	}
}