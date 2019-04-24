<?php 
/**
 * enhancement
 */
class Enhancement {
	static function loadEventSingle($eventID, $ditems=2) {
        $data = '';
        $answers = '';
        $meta    = get_post_meta($eventID, 'event_ops', true);
        $ans     = (array) get_post_meta($eventID, 'event_ans', true); 
        if (isset($ans[0])) unset($ans[0]);

        $answers = self::answersHTML($meta, $ans, $eventID);
        if ($answers) {
            $data .= '<div id="answersWrapper_'.$eventID.'" class="answersWrapper" event="'.$eventID.'" ditems="2" html="box" avatarslider="0">';
            $data .= '<span class="refreshButton fusion-button button-default button-small" event="'. $eventID .'">Reload</span>';
            $data .= $answers;
            $data .= '</div>';
        }
        else $data .= 'No one predicted this event yet. If you are an expert you may <a href="'. site_url('log-in') .'">Login</a> here.';
        echo $data;
	}
    static function answersHTML($meta, $ans, $eventID) {
        $html = $userNav = '';
        if (empty($ans)) $html .= 'No one predicted this event yet. If you are an expert you may <a href="http://cricdiction.com/log-in/">Login</a> here.'; 
        else {
            // $ranking = getRakingFor();
            $likes = likesByEvent($eventID);
            $html .= '<div class="owl-carousel owlCarousel_'.$eventID.' owl-theme">';
            foreach ($ans as $uID => $answer) {
                if ($answer) {
                    $ratingIcon = '';
                    // $rank = userRankingStatusFor($uID, $ranking);
                    $rank = ['num'=>0,'class'=>'',];
                    if (!empty($rank['num'])) $ratingIcon = '<p>'. $rank['num'] .'</p>';
                    $country = get_the_author_meta( 'country', $uID );
                    $highlight = get_the_author_meta( 'highlight', $uID ) ? ' highlighted' : '';
                    $user = get_userdata($uID);
                    if ($user) {
                        $html .= '<div id="predictor_'. $uID .'" class="answerContainer item'. $highlight . $rank['class'] .'" data-hash="'.$uID.'">';
                            $html .= '<div class="dashboard-user text-center">';
                                $html .= '<div class="user-avater">'.get_avatar( $user->user_email , '90') .'</div>';
                                $html .= '<div class="user-information">';
                                    $html .= '<h4>';
                                        $html .= '<a href="'. site_url('predictor/?p='. $user->user_login) .'"  target="_blank">'. get_the_author_meta('nickname',$uID) .'</a>';
                                        if ($country) $html .= '<img class="countryFlag" src="'. PREDICTOR_URL .'frontend/img/'. $country .'.png" alt="country">';
                                    $html .= !empty($likes[$uID]) ? ' Likes : '. $likes[$uID] : '';
                                    $html .= '</h4><br>';
                                        $html .= get_user_meta($user->ID, 'description', true);
                                $html .= '</div>';
                            $html .= '</div>';
                            if (!empty($meta['teams'])) {
                                $html .= '<div class="teamAnsWrapper">';
                                    foreach ($meta['teams'] as $team) {
                                        $givenAnswers = '';
                                        $teamID = predictor_id_from_string($team['name']);
                                        $options = 'team_'. $teamID;
                                        // GIVEN ANSWERS
                                        if ($meta[$options]) {
                                            foreach ($meta[$options] as $option) {
                                                $ansID = $options.'_'.predictor_id_from_string($option['title']);
                                                if (empty($answer[$ansID])) continue;
                                                $defaultID = 'default_'. $teamID .'_'. predictor_id_from_string($option['title']);
                                                $defaultAns = $meta[$defaultID];
                                                $published = !empty($meta[$defaultID.'_published']) ? $meta[$defaultID.'_published'] : [];
                                                $isCorrect = '';
                                                if ($published) {
                                                    if ($defaultAns == 'abandon') {
                                                        $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/warning.png">';
                                                    } else if ($ans[$uID][$ansID]== $defaultAns) $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/happy.png">';
                                                    else $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/sad.png">';
                                                }
                                                // $html .= '<br>published: '.$published.' == givenAns: '.$ans[$uID][$ansID] .' == DefaultAns: '. $defaultAns;
                                                $userAnswer = !empty($answer[$ansID]) ? $answer[$ansID] : false;
                                                $ansWeight = getWeightFromValue($option['weight'], $userAnswer);
                                                $givenAnswers .= '<div class="answer">'; 
                                                    $givenAnswers .= !empty($option['title']) ? $option['title'] : '';
                                                    if ($defaultAns == 'abandon') $givenAnswers .= ' <span class="text-danger noResult"></span>';
                                                    $givenAnswers .= ' <strong>'; 
                                                        $givenAnswers .= '<span class="ansTxt">'. $userAnswer .'</span>'; 
                                                        if ($ansWeight) {
                                                            $givenAnswers .= ' @ <span class="ansWeight">'. $ansWeight .'</span>'; 
                                                        }
                                                    $givenAnswers .= '</strong>&nbsp;'; 
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
                                $html .= '</div>';
                                // WINE LOSE DATA
                                // $html .= winLoseHtml($UP, 'match', 9);
                                // $html .= winLoseHtml($UP, 'toss', 9);
                                $html .= '<div class="profile-link">';
                                    $html .= '<a href="'. site_url('predictor/?p='. $user->user_login) .'" target="_blank">VIEW PROFILE</a>';
                                    $html .= likeDislikeBtnFor($uID, $eventID);
                                $html .= '</div>';
                            }
                        $html .= '</div>';
                        $userNav .= '<a class="userNavItem'. $rank['class'] .'" href="#'.$uID.'">'.get_avatar( $user->user_email) . '</a>';
                    }
                }
            }
            $html .= '</div>';
            $html .= '<ul class="menuSlider">'. $userNav .'</ul>';
            // $html .= getFavoriteTeamForThisEvent($meta, $ans, $eventID, false);
        }
        return $html;
    }
	static function loadAnswers($eventID, $html='box', $ditems=2, $avatarslider=0) {
		$ID             = $eventID;
        if (get_post_type($ID) == 'event') {
            $answers        = '';
            $meta           = (array) get_post_meta($ID, 'event_ops', true);
            $ans            = (array) get_post_meta($ID, 'event_ans', true);
            $answerGiven    = @$meta['answers'];
            if (isset($ans[0])) unset($ans[0]);
            
            // GIVEN PREDICTIONS FOR SINGLE PAGE
            $answers = answersHTML($meta, $ans, $ID, $ditems);
            $data .= '<span class="refreshButton fusion-button button-default button-small" event="'. $ID .'">Reload</span>';
            if ($answers) $data .= $answers;
            else $data .= 'No one predicted this event yet. If you are an expert you may <a href="'. site_url('log-in') .'">Login</a> here.';
            
        }
        echo $data;
	}
}