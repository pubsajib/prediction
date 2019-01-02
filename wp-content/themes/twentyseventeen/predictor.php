<?php
/* Template Name: Predictor */
get_header(); 
$permited = ['all', 'match', 'toss'];
// Set the Current Author Variable $user
$user = (isset($_GET['p'])) ? get_user_by('login', $_GET['p']) : [];
?>
     
<div class="author-profile-card">
	<div class="profile-info"> <?php profileInfo($user); ?> </div>
	<div class='tabs tabs_default' id="protab">
         <ul class='horizontal'>
              <li class="proli"><a href="#total">Total</a></li>
              <li class="proli"><a href="#bpl">BBL</a></li>
          </ul>
           <div  id="total">
			   <div class="prediction-summery">
					<?php predictionSummery($user->ID, $permited); ?>
				</div>
			</div>
			<div id="bpl">
				<div class="prediction-summery">
					<?php //tournamentsSelectHtml($user->ID); ?>
					<div class="tournamentWrapper"> <?php tournamentSummery(269, $user->ID, $permited); ?></div>
					<div class="clearfix"></div>
				</div>
			</div>
     </div>
</div>
<?php get_footer();