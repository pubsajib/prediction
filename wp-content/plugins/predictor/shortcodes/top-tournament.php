<?php 
class tournamentTop {
	public static function render($attr) {
		$defaults = ['tournament'=>8, 'number' => 10, 'avatar' => 1, 'info' => 1, 'class'=>'predictorListWrapper'];
		$attr = shortcode_atts($defaults, $attr, 'tournamentTop');
		$html  = '';
		$predictors = getPredictorsList();
		$ranking = getRakingFor('all', $attr['tournament'], $predictors);
		// $html  = help($ranking['all'], false);
		if ($ranking['all']) {
			$html .= '<div class="'. $attr['class'] .'">';
			foreach ($ranking['all'] as $rankID => $rank) {
				if ($rankID >= $attr['number']) break;
				$user = $predictors[$rank['id']];
				$rank = userRankingStatusFor($user->ID, $ranking);
				$profileLink = site_url('predictor/?p='. $user->user_login);
				$html .= '<div id="predictor_'. $user->ID .'" class="predictorContainer author-profile-card'. $rank['class'] .'">';
		    		// PROFILE INFORMATION
					$html .= '<div class="profile-info">';
						if ($attr['avatar']) {
							$ratingIcon = $rank['num'] ? '<p>'. $rank['num'] .'</p>' : '';
							$html .= '<div class="author-photo"> '. get_avatar( $user->user_email , '120 ') .' '. $ratingIcon .'</div>';
						}
						if ($attr['info']) {
					        $html .= '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3>';
					        $html .= '<p>';
					            if ($user->user_url) $html .= '<strong>Website:</strong> <a href="'. $user->user_url .'">'. $user->user_url .'</a><br />';
					            if ($user->user_description) $html .= $user->user_description;
					        $html .= '</p>';
						}
					$html .= '</div>';
				$html .= '</div>';
			}
			$html .= '</div>';
		}
		return $html;
	}
 }
add_shortcode('tournamentTop', ['tournamentTop', 'render']);