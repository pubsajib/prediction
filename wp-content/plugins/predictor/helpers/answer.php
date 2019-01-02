<?php 
function answersHTML($meta, $ans, $eventID, $ditems=2) {
    $html = $userNav = '';
    $owlSelector = 'owlCarousel_'. $eventID;
    if (empty($ans)) $html .= 'No answer given yet'; 
    else {
        $html .= '<div class="owl-carousel '.$owlSelector.' owl-theme">';
        foreach ($ans as $uID => $answer) {
            if ($answer) {
                $country = get_the_author_meta( 'country', $uID );
                $highlight = get_the_author_meta( 'highlight', $uID ) ? ' highlighted' : '';
                $user = get_userdata($uID);
                $html .= '<div id="predictor_'. $uID .'" class="answerContainer item'. $highlight .'" data-hash="'.$uID.'">';
                    $html .= '<div class="dashboard-user text-center">';
                    		$html .= '<table class="table top-accuracy">';
                    			$html .= '<tr>';
                    			$data = predictionsOf($uID);
                    				$html .= '<td style="width: 80%">' . round($data['avg']['all']['rate']) . '%<br><small>Accuracy</small></td>';
                    				$html .= '<td style="width: 10%">' . round($data['avg']['match']['rate']) . '%<br><small>Match</small></td>';
                    				$html .= '<td style="width: 10%">' . round($data['avg']['toss']['rate']) . '%<br><small>Toss</small></td>';
                    			$html .= '</tr>';
                    		$html .= '</table>';

							$html .= '<div class="user-avater">'.get_avatar( $user->user_email , '150 ') . '</div>';
							$html .= '<div class="user-information">';
                                $html .= '<h4>';
                                    $html .= '<a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$uID) .'</a>';
                                    if ($country) $html .= '<img class="countryFlag" src="'. PREDICTOR_URL .'frontend/img/'. $country .'.png" alt="country">';
                                $html .= '</h4><br>';
                                    $html .= get_user_meta($user->ID, 'description', true);
								$html .= '</div>';
					$html .= '</div>';
                    if ($meta['teams']) {
                        $html .= '<div class="teamAnsWrapper">';
                            foreach ($meta['teams'] as $team) {
                                $givenAnswers = '';
                                $teamID = predictor_id_from_string($team['name']);
                                $options = 'team_'. $teamID;
                                // GIVEN ANSWERS
                                if ($meta[$options]) {
                                    foreach ($meta[$options] as $option) {
                                        $ansID = $options.'_'.predictor_id_from_string($option['title']);
                                        $default = 'default_'. $teamID .'_'. predictor_id_from_string($option['title']);
                                        if (!$answer[$ansID]) continue;
                                        $isCorrect = '';
                                        if ($meta['published']) {
                                            if ($ans[$uID][$ansID]== @$meta[$default]) $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/checked.png">';
                                            else $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/delete.png">';
                                        }
                                        // $html .= $ans[$uID][$ansID] .'=='. $meta[$default];
                                        $ansWeight = getWeightFromValue($option['weight'], $answer[$ansID]);
                                        $givenAnswers .= '<div class="answer">'; 
                                        	$givenAnswers .= @$option['title'] .' <br>';
                                        	$givenAnswers .= '<strong>'; 
    	                                    	$givenAnswers .= '<span class="ansTxt">'. @$answer[$ansID] .'</span>'; 
    	                                    	if ($ansWeight) {
    	                                    		$givenAnswers .= ' @ <span class="ansWeight">'. $ansWeight .'</span>'; 
    	                                    	}
                                        	$givenAnswers .= '</strong>&nbsp;&nbsp;&nbsp;'; 
                                        	$givenAnswers .= '<span>'. $isCorrect .'</span>'; 
                                        $givenAnswers .= '</div>'; 
                                    }
                                }

                                if ($givenAnswers) {
                                    $html .= '<div class="teamAnsContainer">';
                                    $html .= '<h3 class="teamName">'. $team['name'] .'</h3>';
                                    $html .= $givenAnswers;
                                    $html .= '</div>';
                                }
                            }
							$html .= '<div class="sliderFooter">';
							$html .= '<table class="table">';
							$html .= '<tr>';
							$html .= '<th>League</th>';
							$html .= '<th>Accuracy</th>';
							$html .= '<th>Match</th>';
							$html .= '<th>Toss</th>';
							$html .= '</tr>';
							$html .= '<tr>';
								$data = tournamentData($uID, 269);
								$html .= "<td rowspan='2'>BBL</td>";
								$html .= "<td>" . round($data['avg']['all']['rate']) . "%</td>";
								$html .= "<td>" . round($data['avg']['match']['rate']) . "% (" . $data['avg']['match']['participated'] . ")</td>";
								$html .= "<td>" . round($data['avg']['toss']['rate']) . "% (" . $data['avg']['toss']['participated'] . ")</td>";
							$html .= '</tr>';	
							$html .= '</table>';
							   $html .= '</div>';
                        $html .= '</div>';
                    }
                $html .= '</div>';
                $userNav .= '<a href="#'.$uID.'">'.get_avatar( $user->user_email , '40 ') . '</a>';
            }
        }
        $html .= '</div>';
        $html .= '<ul class="menuSlider">';
        $html .= $userNav;
        $html .= '</ul>';
        $html .= '<script> jQuery(".'. $owlSelector .'").owlCarousel({loop:true, margin: 10, nav: true, autoplay:true, autoplayTimeout:15000, URLhashListener:true, autoplayHoverPause:true, startPosition: "URLHash", responsive: {0: {items: 1 }, 600: {items: 1 }, 1000: {items: '. $ditems .' } } }) </script>';
    }
    // $html .= '<br><pre>'. print_r($ans, true) .'</pre>';
    // $html .= '<br><pre>'. print_r($meta, true) .'</pre>';
    return $html;
}
function adminAnswersHTML($meta, $ans) {
    $html = '';
    if (!empty($ans)) {
        $html .= '<div class="answersWrapper">';
        foreach ($ans as $uID => $answer) {
            if ($answer) {
                $user = get_userdata($uID);
                $html .= '<div id="predictor_'. $uID .'" class="answerContainer">';
                $html .= '<button class="adminButton removeAns" event="'. $_GET['post'] .'" user="'. $uID .'">Delete</button>';
                $html .= '<div class="text-center header">';
                    $html .= get_avatar( $user->user_email , '70 ');
                    $html .= '<h4><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$uID) .'</a></h4>';
                $html .= '</div>';
                if ($meta['teams']) {
                    $html .= '<div class="teamAnsWrapper">';
                    foreach ($meta['teams'] as $team) {
                        $teamID = predictor_id_from_string($team['name']);
                        $options = 'team_'. $teamID;
                        if (@$ans[$uID][$options]) {
                            $html .= '<div class="teamAnsContainer">';
                            $html .= '<h3 class="teamName">'. $team['name'] .'</h3>';
                            if ($meta[$options]) {
                                foreach ($meta[$options] as $option) {
                                    $ansID = $options.'_'.predictor_id_from_string($option['title']);
                                    $default = 'default_'. $teamID .'_'. predictor_id_from_string($option['title']);
                                    if (!$answer[$ansID]) continue;
                                    $isCorrect = '';
                                    if ($meta['published']) {
                                        $isCorrect = @$ans[$uID][$ansID]== @$meta[$default] ? '<img src="http://cricdiction.com/wp-content/uploads/2018/11/checked.png">' : '<img src="http://cricdiction.com/wp-content/uploads/2018/11/delete.png">';
                                    }
                                    // $html .= $ans[$uID][$ansID] .'=='. $meta[$default];
                                    $html .= '<div class="answer">'. @$option['title'] .' <br><strong><span>'. @$answer[$ansID] .'</span></strong>&nbsp;&nbsp;&nbsp;<span>'. $isCorrect .'</span></div>'; 
                                }
                            }
                            $html .= '</div>';
                        }
                    }
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
        }
        $html .= '</div>';
    } else {
        $html .= 'No answer given yet';
    }
    // $html .= '<br><pre>'. print_r($ans, true) .'</pre>';
    // $html .= '<br><pre>'. print_r($meta, true) .'</pre>';
    return $html;
}
function getWeightFromValue($weights, $ans) {
	$html = '';
	if ($weights) {
		foreach ($weights as $weight) {
			if ($weight['name'] == $ans) {
				$html .= $weight['value'];
			}
		}
	}
	// $html .= '<br><pre>'. print_r($weights, true) .'</pre>';
	return $html;
}