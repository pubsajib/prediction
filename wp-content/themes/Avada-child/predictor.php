<?php
/* Template Name: Predictor */
get_header(); 
$permited = ['all', 'match', 'toss'];
// Set the Current Author Variable $user
$user = (isset($_GET['p'])) ? get_user_by('login', $_GET['p']) : [];
if(!empty($user)) {
	// RANKS
	$ranking    = getRakingFor()['all'];
	$rank_all       = getUserRankedDetails($ranking, $user->ID)['rank'];
	$ranking    = getRakingFor('match')['all'];
	$rank_match       = getUserRankedDetails($ranking, $user->ID)['rank'];
	$ranking    = getRakingFor('toss')['all'];
	$rank_toss       = getUserRankedDetails($ranking, $user->ID)['rank'];
	// TOURNAMENT
	$predictors = getPredictorsList();
	$ranking    = getRakingForTournament('all', 12, $predictors, 1, 0);
	$rank_tournament       = userRankingStatusFor($user->ID, $ranking)['num'];
	

	// help($rank);
	echo "<br> Rank for All : ". $rank_all;
	echo "<br> Rank for Match : ". $rank_match;
	echo "<br> Rank for Toss : ". $rank_toss;
	echo "<br> Rank for Tournament ID 12 : ". $rank_tournament;
?>
<div class="author-profile-card">
	<div class="profile-info"> 
		<?php profileInfo($user); 
		$likes = get_user_meta($user->ID, 'likes', true);
		if ($likes) echo 'Likes : '. $likes; 
		?>
	</div>
	<div class='tabs tabs_default' id="protab">
         <ul class='horizontal'>
              <li class="proli"><a href="#total">Total</a></li>
              <li class="proli"><a href="#bbl">BBL</a></li>
              <li class="proli"><a href="#bpl">BPL</a></li>
              <li class="proli"><a href="#smash">Smash</a></li>
          </ul>
           <div  id="total">
			   <div class="prediction-summery">
					<?php predictionSummery($user->ID, $permited); ?>
				</div>
			</div>
			<div id="bbl">
				<div class="prediction-summery">
					<?php //tournamentsSelectHtml($user->ID); ?>
					<div class="tournamentWrapper"> <?php tournamentSummery(270, $user->ID, $permited); ?></div>
					<div class="clearfix"></div>
				</div>
			</div>
			<div id="bpl">
				<div class="prediction-summery">
					<?php //tournamentsSelectHtml($user->ID); ?>
					<div class="tournamentWrapper"> <?php tournamentSummery(279, $user->ID, $permited); ?></div>
					<div class="clearfix"></div>
				</div>
			</div>
			<div id="smash">
				<div class="prediction-summery">
					<?php //tournamentsSelectHtml($user->ID); ?>
					<div class="tournamentWrapper"> <?php tournamentSummery(276, $user->ID, $permited); ?></div>
					<div class="clearfix"></div>
				</div>
			</div>
     </div>
	
</div>
<?php 
}
get_footer();