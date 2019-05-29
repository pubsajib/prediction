<?php
/* Template Name: Predictor */
get_header(); 
// Set the Current Author Variable $user
$user = (isset($_GET['p'])) ? get_user_by('login', $_GET['p']) : [];
//help($user);
if ($user) {
	$rank = Ranks::getUserRankingFor($user->ID);
	if ($rank) {
		$overall = json_decode($rank->all_desc); $overall->rank = $rank->all_rank; $overall->abandon = $overall->participated - ($overall->win + $overall->lose);
		$match = json_decode($rank->match_desc); $match->rank = $rank->match_rank; $match->abandon = $match->participated - ($match->win + $match->lose);
		$toss = json_decode($rank->toss_desc); $toss->rank = $rank->toss_rank; $toss->abandon = $toss->participated - ($toss->win + $toss->lose);
	}
	$likes = get_the_author_meta('likes',$user->ID);
	if (!$likes) $likes = 0;
	
	$eventID = 'predictor';
	if (empty($_COOKIE['cdpue_'.$eventID.'_'.$user->ID])) $likeBtn = '<button class="btn btn-xs btn-primary predictorLikeBtn userlikebtn" type="button" user='. $user->ID .' event='. $eventID .'><img src="https://www.cricdiction.com/wp-content/plugins/predictor/frontend/img/UserLike.png"></button>';
    else $likeBtn = '<button class="btn btn-xs btn-primary userlikebtn" type="button" user='. $user->ID .' event='. $eventID .'><img src="https://www.cricdiction.com/wp-content/plugins/predictor/frontend/img/liked.png"></button>';
?>
<div class="profile-wrapper">
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="author-profile-card">
				<div class="profile-info"> 
					<?php 
					$data .= '<div class="author-photo"> '. get_avatar( $user->user_email , '120 ') .' '. $ratingIcon .'</div>';
					$data .= '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. $user->data->display_name . '</a></h3>';
						if ($user->user_url) $data .= '<strong>Website:</strong> <a href="'. $user->user_url .'">'. $user->user_url .'</a><br />';
						if ($user->user_description) $data .= $user->user_description;
						echo $data.='<div class="like-sectionprofile">' . $likeBtn . '<span class="countuserlike likeCounter_'.$user->ID.'">'. $likes .'</span></div>';
					 ?>
				</div>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<?php if ($rank): ?>
				<div class="toprankingsection">
					<div class="items">
						<div class="card">
							<h3>Overall Rank</h3>
							<div class="circle">
								<h2><?php echo $overall->rank; ?></h2>
							</div>
						</div>
					</div>
					<div class="items">
						<div class="card">
							<h3>Match Rank</h3>
							<div class="circle">
								<h2><?php echo $match->rank; ?></h2>
							</div>
						</div>
					</div>
					<div class="items last">
						<div class="card">
							<h3>Toss Rank</h3>
							<div class="circle">
								<h2><?php echo $toss->rank; ?></h2>
							</div>
						</div>
					</div>
				</div>
			<?php endif ?>
		</div>
	</div>
	<!-- 	Full Detail	 -->
	<?php if ($rank): ?>
		<div class="card">
			<div class="title">Overall</div>
			<hr>
			<div class="items-full">
				<div class="card">
					<h3>Accuracy</h3>
					<div class="circle">
						<h2><?php echo $overall->accuracy; ?>%</h2>
					</div>
				</div>
			</div>
			<div class="items-full mobile-last">
				<div class="card">
					<h3>Participate</h3>
					<div class="circle">
						<h2><?php echo $overall->participated; ?></h2>
					</div>
				</div>
			</div>
			<div class="items-full">
				<div class="card">
					<h3>Win/Lose</h3>
					<div class="circle">
						<h2><span class="green"><?php echo $overall->win; ?></span>/<span class="red"><?php echo $overall->lose; ?></span></h2>
					</div>
				</div>
			</div>
			<div class="items-full last mobile-last">
				<div class="card">
					<h3>Abandoned</h3>
					<div class="circle">
						<h2><?php echo $overall->abandon; ?></h2>
					</div>
				</div>
			</div>
		</div>
		<div class="card">
			<div class="title">Match</div>
			<hr>
			<div class="items-full">
				<div class="card">
					<h3>Accuracy</h3>
					<div class="circle">
						<h2><?php echo $match->accuracy; ?>%</h2>
					</div>
				</div>
			</div>
			<div class="items-full mobile-last">
				<div class="card">
					<h3>Participate</h3>
					<div class="circle">
						<h2><?php echo $match->participated; ?></h2>
					</div>
				</div>
			</div>
			<div class="items-full">
				<div class="card">
					<h3>Win/Lose</h3>
					<div class="circle">
						<h2><span class="green"><?php echo $match->win; ?></span>/<span class="red"><?php echo $overall->lose; ?></span></h2>
					</div>
				</div>
			</div>
			<div class="items-full last mobile-last">
				<div class="card">
					<h3>Abandoned</h3>
					<div class="circle">
						<h2><?php echo $match->abandon; ?></h2>
					</div>
				</div>
			</div>
		</div>
		<div class="card">
			<div class="title">Toss</div>
			<hr>
			<div class="items-full">
				<div class="card">
					<h3>Accuracy</h3>
					<div class="circle">
						<h2><?php echo $toss->accuracy; ?>%</h2>
					</div>
				</div>
			</div>
			<div class="items-full mobile-last">
				<div class="card">
					<h3>Participate</h3>
					<div class="circle">
						<h2><?php echo $toss->participated; ?></h2>
					</div>
				</div>
			</div>
			<div class="items-full">
				<div class="card">
					<h3>Win/Lose</h3>
					<div class="circle">
						<h2><span class="green"><?php echo $toss->win; ?></span>/<span class="red"><?php echo $toss->lose; ?></span></h2>
					</div>
				</div>
			</div>
			<div class="items-full last mobile-last">
				<div class="card">
					<h3>Abandoned</h3>
					<div class="circle">
						<h2><?php echo $toss->abandon; ?></h2>
					</div>
				</div>
			</div>
		</div>
	<?php endif ?>
</div>
<?php 
}
get_footer();