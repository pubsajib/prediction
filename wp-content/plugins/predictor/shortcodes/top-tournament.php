<?php 
class tournamentTop {
	public static function render($attr) {
		$defaults = ['tournament'=>0, 'number' => 10, 'avatar' => 0, 'excerpt-avatar' => 0, 'bpl-accuracy' => 0, 'bbl-accuracy' => 0, 'smash-accuracy' => 0, 'info' => 0, 'class'=>'predictorListWrapper'];
		$attr = shortcode_atts($defaults, $attr, 'tournamentTop');
		$html  = '';
		$predictors = getPredictorsList();
		$ranking = getRakingForTournament('all', $attr['tournament'], $predictors, 1, 0);
		//$html  .= help($ranking['all'], false);
		if ($ranking['all']) {
			$html .= '<div class="'. $attr['class'] .'">';
			foreach ($ranking['all'] as $rankID => $rank) {
				if ($rankID >= $attr['number']) break;
				$user = $predictors[$rank['id']];
				$rank = userRankingStatusFor($user->ID, $ranking);
				$profileLink = site_url('predictor/?p='. $user->user_login);
				//Excerpt 				
				$html .= '<div class="excerpt-top">';
					if ($attr['excerpt-avatar']) {
						$ratingIcon = $rank['num'] ? '<p>'. $rank['num'] .'</p>' : '';
						$html .= '<div class="author-photo"> <a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_avatar( $user->user_email , '60 ') .'</a></div>';
						$html .= '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3>';
					}
					// BPL Accuracy					
					if ($attr['bpl-accuracy']) {
						$bpl = tournamentData($user->ID, 279);
						if (!empty($bpl['avg'])) {
							if (!empty($bpl['avg'])) {   
									$html .= "<h4>" . $bpl['avg']['all']['rate'] . "%</h4>";
							}
						}
					}
					//BBL Accuracy 
					if ($attr['bbl-accuracy']) {
						$bbl = tournamentData($user->ID, 270);
						if (!empty($bbl['avg'])) {
							if (!empty($bbl['avg'])) {   
									$html .= "<h4>" . $bbl['avg']['all']['rate'] . "%</h4>";
							}
						}
					}	
					//Super Smash 
					if ($attr['smash-accuracy']) {
						$smash = tournamentData($user->ID, 276);
						if (!empty($smash['avg'])) {
							if (!empty($smash['avg'])) {   
									$html .= "<h4>" . $smash['avg']['all']['rate'] . "%</h4>";
							}
						}
					}	
					
				$html .= '</div>';
// 				$html .= '<div id="predictor_'. $user->ID .'" class="predictorContainer author-profile-card'. $rank['class'] .'">';
		    		// PROFILE INFORMATION
					$html .= '<div class="profile-info">';
						if ($attr['avatar']) {
							$ratingIcon = $rank['num'] ? '<p>'. $rank['num'] .'</p>' : '';
							$html .= '<div class="author-photo"> '. get_avatar( $user->user_email , '60 ') .'</div>';
							$html .= '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3>';
						}
						if ($attr['info']) {
					        $html .= '<p>';
					            if ($user->user_url) $html .= '<strong>Website:</strong> <a href="'. $user->user_url .'">'. $user->user_url .'</a><br />';
					            if ($user->user_description) $html .= $user->user_description;
					        $html .= '</p>';
						}
					$html .= '</div>';
// 				$html .= '</div>';
			}
			$html .= '</div>';
		}
		return $html;
	}
 }
add_shortcode('tournamentTop', ['tournamentTop', 'render']);