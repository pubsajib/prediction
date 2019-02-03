<?php
/* Template Name: Predictor */
get_header();
// $ranking = tournamentData($userID=1, $tournamentID=4);
$ranking = getRakingFor();
// $userRank = userRankingStatusFor(516, $ranking);
// help($userRank);
// help($ranking);
$permited = ['all', 'match', 'toss'];
// Set the Current Author Variable $user
$user = (isset($_GET['p'])) ? get_user_by('login', $_GET['p']) : [];
$prediction = predictionsOf($user->ID);

// help(winLoseDataBy('match', $prediction['wl']));
?>
<div class="author-profile-card">
	<div class="profile-info"> <?php profileInfo($user); ?> </div>
	<div class='tabs tabs_default' id="protab">
         <ul class='horizontal'>
              <li class="proli"><a href="#total">Total</a></li>
              <li class="proli"><a href="#bpl">BPL</a></li>
          </ul>
           <div  id="total">
			   <div class="prediction-summery">
					<?php echo summeryHtml($prediction, $permited); ?>
				</div>
				<?php echo winLoseHtml($prediction, 'match'); ?>
			</div>
			<div id="bpl">
				<div class="prediction-summery">
					<?php //tournamentsSelectHtml($user->ID); ?>
					<div class="tournamentWrapper"> <?php tournamentSummery(12, $user->ID, $permited); ?></div>
					<div class="clearfix"></div>
				</div>
			</div>
     </div>
</div>
<?php get_footer();