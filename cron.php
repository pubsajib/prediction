<?php 
// define( 'SHORTINIT', true );
require_once( __DIR__.'/wp-load.php' );
require_once( __DIR__.'\wp-content\plugins\predictor\helpers\ranking.php' );
global $wpdb;
$rankingType = 'all';
$tableName   = $wpdb->prefix.'predictor_rating_'.$rankingType;
$predictors  = getRakingFor()['all']; 
if ($predictors) {
	$insert = '';
	$insert .= 'INSERT INTO `'. $tableName .'` (`id`, `user_id`, `accuracy`, `win`, `lose`, `life_time_events`, `participated_events`, `participation_rate`, `min_participation_rate`, `min_participation_event`, `eligibility`) VALUES ';
	foreach ($predictors as $predictorRank => $predictor) {
		// $insert .= "<br>";
		$insert .= "(NULL, {$predictor['id']}, {$predictor['correct']}, {$predictor['incorrect']}, {$predictor['score']}, {$predictor['eligible']}, {$predictor['lifeTimePublishedEvents']}, {$predictor['participated']}, {$predictor['lifeTimePublishedEventRate']}, {$predictor['minLifetimeParticipationRate']}, {$predictor['minLifetimeParticipation']}),";
	}
	$insert = rtrim($insert, ',').';';
	$truncate = "TRUNCATE ".$tableName;
	if ($wpdb->query($truncate) && $wpdb->query($insert)) { 
		echo "Success";
	} else {
		echo "Fail";
	}
}