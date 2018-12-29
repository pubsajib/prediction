<?php 
function predictionsOf($userID=1, $tournamentID='') {
    $prediction = ['gain'  => 0];
    if (!$tournamentID) $events = getEventIDs();
    else $events = eventsByTournament($tournamentID);
    $eventAVG = defaultCriteriaValues();
    foreach ($events as $eventID) {
        $data = predictionFor($eventID, $userID);
        if (!$data) continue;
        $eventAVG = eventAVG($eventAVG, @$data['avg']);
        $prediction['gain']         += @$data['gain'];
        $prediction['avg']      = $eventAVG;
        // echo '<br><pre>'. print_r($data, true) .'</pre>';
    }
    // echo '<br><pre>'. print_r($prediction, true) .'</pre>';
    return $prediction;
}
function predictionFor($eventID, $userID) {
	$meta  = get_post_meta($eventID, 'event_ops', true);
	$ans   = get_post_meta($eventID, 'event_ans', true);
    $data = [];
    if (@!$meta['published']) return $data;
    $eventAvg = defaultCriteriaValues();
    $tgain = $tparticipated = $tcorrect = $tincorrect = $twin = $tlose = 0;
    if (@!$ans[$userID]) return [];
    if (@$meta['teams']) {
        foreach ($meta['teams'] as $team) {
            $gain   = $participated = $correct = $incorrect = $win = $lose = 0;
            $criteriaAvg = defaultCriteriaValues();
            $ID     = predictor_id_from_string($team['name']);
            $teamID = 'team_'. $ID;

            // OPTIONS
            if ($meta[$teamID]) {
                foreach ($meta[$teamID] as $option) {
                    $isCorrect = null;
                    $optionID = predictor_id_from_string($option['title']);
                    $defaultID = 'default_'. $ID .'_'. $optionID;
                    $defaultAns = @$meta[$defaultID];

                    $answerID = $teamID .'_'. $optionID;
                    $givenAns = @$ans[$userID][$answerID];
                    $dWeight = getDefaultWeight($option['weight'], $defaultAns);

                    if (!$givenAns) $data[$teamID][$answerID]['warning'] = 'Answer is not given.'; 
                    else {
                        if ($defaultAns == $givenAns) {
                            $criteriaAvg = updateCriteriaAVGFor($criteriaAvg, $option['id'], $dWeight, 1);
                            $isCorrect = true;
                            $gain += $dWeight;
                        } else{
                            $isCorrect = false;
                            $criteriaAvg = updateCriteriaAVGFor($criteriaAvg, $option['id'], $dWeight, 0);
                            $gain -= $dWeight;
                        }
                        // FOR DEBUGING / SHOW
                        $data[$teamID][$answerID]['question']   = $option['title'];
                        $data[$teamID][$answerID]['weight']     = $dWeight;
                        $data[$teamID][$answerID]['default']    = $defaultAns;
                        $data[$teamID][$answerID]['given']      = $givenAns;
                        $data[$teamID][$answerID]['is_correct'] = $isCorrect;
                    }
                }
            }
            $eventAvg = eventAVG($eventAvg, $criteriaAvg);
            $tgain          += @$gain;
            // AVG RESULTS BY QUESTIONS
            $data[$teamID]['name']          = $team['name'];
            $data[$teamID]['gain']          = $gain;
            // AVG FOR CRITERIAS DATA
            $data[$teamID]['avg']           = $criteriaAvg;
        }
        $data['event']  = $eventID;
        $data['gain']   = $tgain;
        $data['avg']    = $eventAvg;
    }
    return $data;
}
function getDefaultWeight($weights, $defaultAns) {
    if ($weights) {
        foreach ($weights as $weight) {
            if (!$weight['name']) continue;
            if ($weight['name'] == $defaultAns) return $weight['value'];
        }
    }
    return false;
}
function getEventIDs() {
    $query = array(
        'post_type' => 'event',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
    );
    $events = new WP_Query($query);
    // $events = $events->found_posts;
    $events = $events->posts;
    return $events;
}
function updateCriteriaAVGFor($criteriaAvg, $criteria='', $weight=0, $isCorrect=false) {
    if ($criteria) {
        // CRITERIA
        $criteriaID = predictor_id_from_string($criteria);
        $criteriaAvg[$criteriaID]['participated']++;
        if ($isCorrect) $criteriaAvg[$criteriaID]['correct']++;
        else $criteriaAvg[$criteriaID]['incorrect']++;
        // ALL
        $criteriaAvg['all']['participated']++;
        if ($isCorrect) {
            $criteriaAvg['all']['correct']++;
            $criteriaAvg['all']['win'] = $weight;
        } else {
            $criteriaAvg['all']['incorrect']++;
            $criteriaAvg['all']['lose'] = $weight;
        }
    }
    return $criteriaAvg;
}
function defaultCriteriaValues() {
    $data = [];
    // ALL
    $data['all']['participated'] = 0;
    $data['all']['correct'] = 0;
    $data['all']['incorrect'] = 0;
    $data['all']['win'] = 0;
    $data['all']['lose'] = 0;
    $data['all']['tweight'] = 0;
    $data['all']['rate'] = 0;
    // CRITERIAS
    $criterias = cs_get_option('criteria_event');
    if ($criterias) {
        foreach ($criterias as $criteria) {
            $criteriaID = predictor_id_from_string($criteria['name']);
            $data[$criteriaID]['participated'] = 0;
            $data[$criteriaID]['correct'] = 0;
            $data[$criteriaID]['incorrect'] = 0;
            $data[$criteriaID]['rate'] = 0;
        }
    }
    return $data;
}
function eventAVG($eventAvg, $criteriaAvg) {
    if ($eventAvg) {
        foreach ($eventAvg as $criteriaName => $criteriaValues) {
            if ($criteriaValues) {
                foreach ($criteriaValues as $key => $value) {
                    $eventAvg[$criteriaName][$key] += $criteriaAvg[$criteriaName][$key];
                }
            }
            // RATE BY WIN
            if ($eventAvg[$criteriaName]['participated']) {
                $rating = ($eventAvg[$criteriaName]['correct'] / $eventAvg[$criteriaName]['participated']) * 100;
                $eventAvg[$criteriaName]['rate'] = number_format((float)$rating, 2, '.', '');
            }
            if ($criteriaName == 'all') {
                // RATE BY WEIGHT
                $eventAvg[$criteriaName]['tweight'] = $eventAvg[$criteriaName]['win'] + $eventAvg[$criteriaName]['lose'];
            }
        }
    }
    return $eventAvg;
}
function profileInfo($user) {
    // help($user);
    $data = '';
    if ($user) {
        $data .= '<div class="author-photo"> '. get_avatar( $user->user_email , '120 ') .' </div>';
        $data .= '<h3>'. get_the_author_meta('nickname',$user->ID) .'</h3>';
        $data .= '<p>';
            if ($user->user_url) $data .= '<strong>Website:</strong> <a href="'. $user->user_url .'">'. $user->user_url .'</a><br />';
            if ($user->user_description) $data .= $user->user_description;
        $data .= '</p>';
    }
    echo $data;
}
function summeryHtml($prediction, $permited=['all']) {
    $data = '';
    if (@$prediction['avg']) {
        foreach ($prediction['avg'] as $type => $prediction) {
            if ($prediction['participated'] && in_array($type, $permited)) {
                $percentage = number_format((float)$prediction['rate'], 2, '.', '');
                $data .= '<div class="win-accuracy '. $type .'" id="'. $type .'">';
                    $data .= '<h3 class="title">'. $type .' rate</h3>';
                    $data .= '<ul class="prediction-full-result">';
                        $data .= '<li>';
                            $data .= '<strong>Total Rate</strong><br>';
                            $data .= '<div class="progress-bar" value="'. $prediction['rate'] .'" data-percent="'. $percentage .'" max="100"></div>';
                        $data .= '</li>';
                        $data .= '<li>';
                            $data .= '<strong>Participated</strong><br>';
                            $data .= '<div class="common">'. $prediction['participated'] .'</div>';
                        $data .= '</li>';
                        $data .= '<li>';
                            $data .= '<strong>Match Win</strong><br>';
                            $data .= '<div class="common">'. $prediction['correct'] .'</div>';
                        $data .= '</li>';
                        $data .= '<li>';
                            $data .= '<strong>Match lose</strong><br>';
                            $data .= '<div class="common red">'. $prediction['incorrect'] .'</div>';
                        $data .= '</li>';
                    $data .= '</ul>';
                    $data .= '<div class="clearfix"></div>';
                $data .= '</div>';
            }
        }
    }
    return $data;
}
function predictionSummery($userID, $permited=[]) {
    $data = '';
    if ($userID) {
        $prediction = predictionsOf($userID);
        $data = summeryHtml($prediction, $permited);
    }
    echo $data;
}