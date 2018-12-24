<?php
/* Template Name: Predictor */
$permited = ['all', 'match', 'toss'];
get_header(); 
// Set the Current Author Variable $user
$user = (isset($_GET['p'])) ? get_user_by('login', $_GET['p']) : [];
$prediction = predictionsOf($user->ID);
// $prediction = predictionFor(47,1);
// echo '<br><pre>'. print_r($prediction['avg'], true) .'</pre>';
?>
     
<div class="author-profile-card">
	<div class="profile-info">
	    <div class="author-photo"> <?php echo get_avatar( $user->user_email , '120 '); ?> </div>
	    <h3><?php echo $user->display_name; ?></h3>
		<?php if ($user->user_url): ?>
	    	
	    <p><strong>Website:</strong> <a href="<?php echo $user->user_url; ?>"><?php echo $user->user_url; ?></a><br />
	    <?php endif ?>
	    <?php if ($user->user_description): ?>
	    	
	     <?php echo $user->user_description; ?></p>
	    <?php endif ?>
	</div>
	<div class="prediction-summery">
		<?php if ($prediction['avg']):
			foreach ($prediction['avg'] as $type => $prediction):
				if ($prediction['participated'] && in_array($type, $permited)): ?>
					<div class="win-accuracy <?php echo $type; ?>">
						<h3 class="text-center" style="text-transform: capitalize;"><?php echo $type ?> rate</h3>
						<ul class="prediction-full-result">
							<li>
								<strong>Total Rate</strong><br>
								<div class="progress-bar" value="<?php echo $prediction['rate']; ?>" data-percent="<?php echo number_format((float)$prediction['rate'], 2, '.', ''); ?>" max="100">								</div>
							</li>
							<li>
								<strong>Participated</strong><br>
								<div class="common"><?php echo $prediction['participated'] ?></div>
							</li>
							<li>
								<strong>Match Win</strong><br>
								<div class="common"><?php echo $prediction['correct'] ?></div>
							</li>
							<li>
								<strong>Match lose</strong><br>
								<div class="common red"><?php echo $prediction['incorrect'] ?></div>
							</li>
						</ul>
						<div class="clearfix"></div>
					</div>
				<?php endif;
			endforeach;
		endif ?>
		<?php //tournamentsSelectHtml($user->ID); ?>
		<div class="tournamentWrapper"></div>
		<div class="clearfix"></div>
	</div>
<?php get_footer();