<?php
/* Template Name: Predictor */
get_header(); 
// echo "<br><br><br><br>string<br><br><br><br>";
// Set the Current Author Variable $user
$user = (isset($_GET['p'])) ? get_user_by('login', $_GET['p']) : [];
$currentUser = currentUser();
if ($user->ID && $currentUser->ID) {
	$roles = (array) $user->roles;
	if (in_array('subscriber', $roles)) {
		$data = '';
		$data = '<style>.m-0{margin: 0;}</style>';
		$feeds = Follower::feed($user->ID);
		$followees = Follower::getFollowees($user->ID);
		$followers = Follower::getFollowers($user->ID);
		$data .= '<div class="col-sm-8 col-sm-offset-2 followWrapper"><div class="tabs tabs_default" id="followTab">';
            $data .= '<ul class="horizontal">';
                $data .= '<li class="proli"><a href="#feed">Feed</a></li>';
                $data .= '<li class="proli"><a href="#followers">Followers</a></li>';
                $data .= '<li class="proli"><a href="#following">Following</a></li>';
                $data .= '<li class="proli"><a href="#notification">Notifications</a></li>';
            $data .= '</ul>';
            $data .= '<div id="feed" class="row feedsWrapper">';
				// $data .= '<div class="title">Feeds</div> <hr>';
				if (!$feeds) $data .= '<h3 class="text-center text-danger m-0"> No feed is available. </h3>';
				else {
					foreach ($feeds as $feed) {
						$data .= '<div class="col-sm-4 feedContainer">';
						$data .= '<div style="border:1px solid red; margin: 0 0 25px 0; padding: 10px 15px; height:255px;">';
							$data .= '<h2 class="text-warning">'. $feed['name'] .'</h2>';
							if (!empty($feed['matches'])) {
								$data .= '<div class="matches">';
									foreach ($feed['matches'] as $match) {
										$data .= '<h4 class="text-info m-0">'. $match['name'] .'</h4>';
										if (!empty($match['ans']['match'])) $data .= '<p class="match m-0">Match : '. $match['ans']['match'] .'</p>';
										if (!empty($match['ans']['toss'])) $data .= '<p class="toss m-0">Toss : '. $match['ans']['toss'] .'</p>';
									}
								$data .= '</div>';
							}
						$data .= '</div>';
						$data .= '</div>';
					}
				}
            $data .= '</div>';
            $data .= '<div id="followers">';
            	if (!$followers) $data .= '<h3 class="text-center text-danger m-0"> Not available. </h3>';
				else {
					foreach ($followers as $follower) {
						$data .= '<div class="col-sm-4 text-center feedContaine">';
						$data .= '<div style="border:1px solid red; margin: 0 0 25px 0; padding: 10px 15px;">';
							$data .= '<p class="image">'. $follower['avatar'] .'</p>';
							$data .= '<p class="text-warning">'. $follower['name'] .'</p>';
						$data .= '</div>';
						$data .= '</div>';
					}
				}
            $data .= '</div>';
            $data .= '<div id="following">';
            	if (!$followees) $data .= '<h3 class="text-center text-danger m-0"> Not available. </h3>';
				else {
					foreach ($followees as $followee) {
						$data .= '<div class="col-sm-4 text-center feedContaine">';
						$data .= '<div style="border:1px solid red; margin: 0 0 25px 0; padding: 10px 15px;">';
							$data .= '<p class="image">'. $followee['avatar'] .'</p>';
							$data .= '<p class="text-warning">'. $followee['name'] .'</p>';
						$data .= '</div>';
						$data .= '</div>';
					}
				}
            $data .= '</div>';
            $data .= '<div id="notification">Notifications</div>';
        $data .= '</div></div>';
		echo $data;
		// help($followees);
	} else if ( in_array('predictor', $roles) ) {
		$rank = Ranks::getUserRankingFor($user->ID);
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
								if ($currentUser) echo '<br><div class="follow">'. Follower::followBtn($user->ID, $currentUser->ID, $currentUser->follows) .'</div>';
								echo 'Followers : '. count(Follower::getFollowees($user->ID));
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
										<h2><?php echo $rank->overall_rank; ?></h2>
									</div>
								</div>
							</div>
							<div class="items">
								<div class="card">
									<h3>Match Rank</h3>
									<div class="circle">
										<h2><?php echo $rank->overall_match_rank; ?></h2>
									</div>
								</div>
							</div>
							<div class="items last">
								<div class="card">
									<h3>Toss Rank</h3>
									<div class="circle">
										<h2><?php echo $rank->overall_toss_rank; ?></h2>
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
								<h2><?php echo $rank->overall_accuracy; ?>%</h2>
							</div>
						</div>
					</div>
					<div class="items-full mobile-last">
						<div class="card">
							<h3>Participate</h3>
							<div class="circle">
								<h2><?php echo $rank->overall_participated; ?></h2>
							</div>
						</div>
					</div>
					<div class="items-full">
						<div class="card">
							<h3>Win/Lose</h3>
							<div class="circle">
								<h2><span class="green"><?php echo $rank->overall_win; ?></span>/<span class="red"><?php echo $rank->overall_lose; ?></span></h2>
							</div>
						</div>
					</div>
					<div class="items-full last mobile-last">
						<div class="card">
							<h3>Abandoned</h3>
							<div class="circle">
								<h2><?php echo $rank->overall_abandon; ?></h2>
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
								<h2><?php echo $rank->overall_match_accuracy; ?>%</h2>
							</div>
						</div>
					</div>
					<div class="items-full mobile-last">
						<div class="card">
							<h3>Participate</h3>
							<div class="circle">
								<h2><?php echo $rank->overall_match_participated; ?></h2>
							</div>
						</div>
					</div>
					<div class="items-full">
						<div class="card">
							<h3>Win/Lose</h3>
							<div class="circle">
								<h2><span class="green"><?php echo $rank->overall_match_win; ?></span>/<span class="red"><?php echo $rank->overall_match_lose; ?></span></h2>
							</div>
						</div>
					</div>
					<div class="items-full last mobile-last">
						<div class="card">
							<h3>Abandoned</h3>
							<div class="circle">
								<h2><?php echo $rank->overall_match_abandon; ?></h2>
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
								<h2><?php echo $rank->overall_toss_accuracy; ?>%</h2>
							</div>
						</div>
					</div>
					<div class="items-full mobile-last">
						<div class="card">
							<h3>Participate</h3>
							<div class="circle">
								<h2><?php echo $rank->overall_toss_participated; ?></h2>
							</div>
						</div>
					</div>
					<div class="items-full">
						<div class="card">
							<h3>Win/Lose</h3>
							<div class="circle">
								<h2><span class="green"><?php echo $rank->overall_toss_win; ?></span>/<span class="red"><?php echo $rank->overall_toss_lose; ?></span></h2>
							</div>
						</div>
					</div>
					<div class="items-full last mobile-last">
						<div class="card">
							<h3>Abandoned</h3>
							<div class="circle">
								<h2><?php echo $rank->overall_toss_abandon; ?></h2>
							</div>
						</div>
					</div>
				</div>
			<?php endif ?>
		</div>
		<?php 
	} else echo "Not permitted";
} else echo "Not permitted";
get_footer();