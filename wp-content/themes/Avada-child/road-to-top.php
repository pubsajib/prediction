<?php
/* Template Name: Road To Top */
// if (!is_user_logged_in()) exit('Don\'t have the permission');
get_header();

$user = wp_get_current_user();
// $user = get_user_by('id', 507);
?>
<!-- User Info -->
<div class="author-profile-card">
	<div class="profile-info"> <?php profileInfo($user); ?> </div>
</div>

<?php
RoadToTop($user);
$events = profileEvents($user);
// help($events); exit();
$unpublishedEvents = array_filter($events, function($event) {
	return !$event['published'];
});
$publishedItems = array_filter($events, function($event) {
	return $event['published'];
});

?>
<div class="tabs tabs_default" id="Roadtotop">
	<!-- <ul class="horizontal">
		<li class="proli"><a href="#recent">Recent</a></li>
		<li class="proli"><a href="#completed">Completed</a></li>
	</ul> -->
	<div  id="recent">
		<?php if ($unpublishedEvents) {
			foreach ($unpublishedEvents as $event) {
				echo '<div id="event-'. $event['ID'] .'" class="event-item">';
					echo '<div class="event-title"><span class="event-name"><a href="'. $event['slug'] .'">'. $event['title'] .'</a></span> </div>';
					echo '<small class="info"><a href="javascript:;">'. $event['cats'] .'</a>, <span class="date">'. $event['date'] .'</span></small>';
					if ($event['match']) {
						echo '<div class="row">';
							foreach ($event['match'] as $match) {
								$mTitle = $match['title'];
								$toss = $match['opt']['toss']['answer'] ?? 'N/A';
								$match = $match['opt']['match']['answer'] ?? 'N/A';
								echo '<div class="col-sm-6 items">';
									echo '<a href="#">';
										echo '<div class="event-match">	';
											echo '<p>'. $mTitle .'</p>';
											echo '<div class="event-predict">';
												echo '<span class="toss"><strong>Toss: </strong>'. $toss .'</span>';
												echo '<span class="match"><strong>Match: </strong>'. $match .'</span>';
											echo '</div>';
										echo '</div>';
									echo '</a>';
								echo '</div>';
							}
						echo '</div>';
					}
					echo '<div class="footer"><a href="'. $event['slug'] .'" class="fusion-button button-default button-small">Predict Now</a></div>';
				echo '</div>';
			}
		} ?>
	</div>
	<div id="completed">
		<p>Under Construction</p>
	</div>
</div>
<?php get_footer();