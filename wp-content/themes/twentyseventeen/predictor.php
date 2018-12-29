<?php
/* Template Name: Predictor */
get_header(); 
$permited = ['all', 'match', 'toss'];
// Set the Current Author Variable $user
$user = (isset($_GET['p'])) ? get_user_by('login', $_GET['p']) : [];
?>
     
<div class="author-profile-card">
	<div class="profile-info"> <?php profileInfo($user); ?> </div>
	<div class="prediction-summery">
		<?php //predictionSummery($user->ID, $permited); ?>
		<?php //tournamentsSelectHtml($user->ID); ?>
		<div class="tournamentWrapper"> <?php //tournamentSummery(4, $user->ID, $permited); ?></div>
		<div class="clearfix"></div>
	</div>
<?php get_footer();
$data = tournamentData($user->ID, 4);
echo "<br>All avg :". $data['avg']['all']['rate'];
echo "<br>All avg :". $data['avg']['match']['rate'];
echo "<br>All avg :". $data['avg']['toss']['rate'];