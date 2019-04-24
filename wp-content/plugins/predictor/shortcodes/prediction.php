<?php 
class Prediction {
    public static function render($attr) {
        $attr = shortcode_atts( ['id' => 1, 'items' => 2, 'html' =>'animate', 'answer'=>0, 'avatarslider'=>1], $attr, 'prediction' ); // box, tab, animate
        $data          = '';
        $ID            = $attr['id'];
        $ditems        = $attr['items'];
        $html          = $attr['html'];
        $answer        = $attr['answer'];
        $avatarslider  = $attr['avatarslider'];
        if (get_post_type($ID) != 'event') $data .= 'May be your given EVENT ID is wrong'; // INVALID EVENT
        else {
            // $event = get_post($ID);
            $meta  = get_post_meta($ID, 'event_ops', true);
            $ans   = get_post_meta($ID, 'event_ans', true);
            // GIVEN PREDICTIONS
            $data .= '<div id="answersWrapper_'. $ID .'" class="answersWrapper" event='. $ID .' dItems="'. $ditems.'" html="'. $html .'" avatarslider='. $avatarslider .'></div>';
            if($answer) {
                // USER MUST LOGGED IN TO INTERACT
                if (!is_user_logged_in()) {
                    // NOT LOGGED IN
                    $data .= '<div class="text-center"><a href="'. esc_url(site_url('pcp')) .'" class="fusion-button button-default button-small">login </a> to predict.</div>';
                } else {
                    if ($userID = getValidUserID(['predictor', 'administrator'])) {
                        // PREDICTIN FORM
                        $data .= '<div class="predictionWrapper">';
                            if (@$meta['published']) $data .= 'Event prediction time is over'; // Event is already published
                            else {
                                $data .= '<form action="" method="post">';
                                    $data .= '<input id="userID" type="hidden" name="user" value="'. $userID .'">';
                                    $data .= '<input id="eventID" type="hidden" name="event" value="'. $ID .'">';
                                    $data .= '<input id="TID" type="hidden" name="team">';
                                    $data .= '<input id="QID" type="hidden" name="qid">';
                                    $data .= '<input id="QAns" type="hidden" name="qans">';
                                    if ($meta['teams']) {
                                        $data .= '<div class="teamQuestionWrapper">';
                                        foreach ($meta['teams'] as $team) {
                                            $teamID = predictor_id_from_string($team['name']);
                                            $options = 'team_'. $teamID;
                                            if (@isValidOption($ans[$userID][$options], $team['end'])) {
                                                $questions = '';
                                                if ($meta[$options]) {
                                                    foreach ($meta[$options] as $option) {
                                                        $question = $tossTime = '';
                                                        $name = $options .'_'. predictor_id_from_string($option['title']);
                                                        if (@ !$ans[$userID][$name]) {
                                                            if ($option['id'] == 'toss') {
                                                                $tossTime =  $option['time'] ? $option['time'] : 30;
                                                                $tossTime =  date('Y-m-d H:i:s',strtotime("-". $tossTime ." minutes",strtotime($team['end'])));
                                                                if (!isValidOption('', $tossTime)) continue;
                                                            }
                                                            $question .= '<div class="predictionContainer" id="'. $name .'">';
                                                                if ($option['weight']) {
                                                                    $question .= '<h4 class="title">'. $option['title'] .'</h4>';
                                                                    if ($tossTime ) $question .= '<div class="endToss" id="'. $name .'_end">'. $tossTime .'</div>';
                                                                    foreach ($option['weight'] as $weight) {
                                                                        if (!$weight['name']) continue;
                                                                        $question .= '<label><input type="radio" name="'. $name .'" value="'. $weight['name'] .'">'. $weight['name'] .'</label>';
                                                                    }
                                                                }
                                                                $question .= '<button type="button" class="btn btn-green saveQAns">Submit</button>';
                                                            $question .= '</div>';
                                                        }
                                                        $questions .= $question;
                                                    }
                                                }
                                                if ($question) {
                                                    $data .= '<div class="teamQuestionContainer" id="'. $options .'">';
                                                    $data .= '<div class="titleContainer">';
                                                    $data .= '<div class="teamName half left"><strong>'. $team['name'] .'</strong></div>';
                                                    $data .= '<div><div class="endTime helf right text-right" id="'. $teamID .'_end">'. $team['end'] .'</div><p class="text-right">Time remaining to predict </p></div>'; 
                                                    $data .= '</div>';
                                                    $data .= $questions;
                                                    $data .= '</div>';
                                                }
                                            }
                                        } // teamQuestionContainer
                                        $data .= '<div class="notice">';
                                            $data .= '<div class="alert">';
                                                $data .= '<span class="closebtn">&times;</span>';
                                        //      $data .= '<h3 style="color: #fff;">Road to top 10</h3>';
                                                $data .= '<ul style="margin-left: 15px;">';
                                                    $data .= '<li><a href="https://cricdiction.com/eligibility-process">Click here</a> to see the eligibility process</li>';
                                            $data .= '</div>';
                                        $data .= '</div>';
                                        $data .= '</div>';
                                    }
                                $data .= '</form>';
                            }
                        $data .= '</div>'; // predictionWrapper end
                    }
                }
            }
        }
        return $data;
    }
 }
add_shortcode( 'prediction', array( 'Prediction', 'render' ) );