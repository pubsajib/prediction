<?php 
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
function predictionFor($eventID, $userID) {
	$meta  = get_post_meta($eventID, 'event_ops', true);
	$ans   = get_post_meta($eventID, 'event_ans', true);
    $data = [];
    $tgain = $tparticipated = $tcorrect = $tincorrect = 0;
    if (!$ans[$userID]) return false;
    if ($meta['teams']) {
        foreach ($meta['teams'] as $team) {
            $gain = $participated = $correct = $incorrect = $win = $lose = 0;
            $ID = predictor_id_from_string($team['name']);
            $teamID = 'team_'. $ID;

            // OPTIONS
            if ($meta[$teamID]) {
                foreach ($meta[$teamID] as $option) {
                    $optionID = predictor_id_from_string($option['title']);
                    $defaultID = 'default_'. $ID .'_'. $optionID;
                    $defaultAns = $meta[$defaultID];

                    $answerID = $teamID .'_'. $optionID;
                    $givenAns = $ans[$userID][$answerID];
                    $default = getDefaultWeight($option['weight'], $defaultAns);

                    if (!$givenAns) {
                        $data[$teamID][$answerID]['is_correct'] = true;
                        $gain += 0;
                    } else if ($defaultAns == $givenAns) {
                        $data[$teamID][$answerID]['is_correct'] = true;
                        $win += $default;
                        $gain += $default;
                        $participated++;
                        $correct++;
                    } else{
                        $data[$teamID][$answerID]['is_correct'] = false;
                        $lose += $default;
                        $gain -= $default;
                        $participated++;
                        $incorrect++;
                    }

                    $data[$teamID][$answerID]['question']   = $option['title'];
                    $data[$teamID][$answerID]['weight']     = $default;
                    $data[$teamID][$answerID]['default']    = $defaultAns;
                    $data[$teamID][$answerID]['given']      = $givenAns;
                    // $data[$teamID][$answerID]['win']        = $win;
                    // $data[$teamID][$answerID]['lose']       = $lose;
                }
            }
            
            $twin           += $win;
            $tlose          += $lose;
            $tgain          += $gain;
            $tparticipated  += $participated;
            $tcorrect       += $correct;
            $tincorrect     += $incorrect;
            // AVG RESULTS BY QUESTIONS
            $data[$teamID]['name']          = $team['name'];
            $data[$teamID]['gain']          = $gain;
            $data[$teamID]['participated']  = $participated;
            $data[$teamID]['correct']       = $correct;
            $data[$teamID]['incorrect']     = $incorrect;
            $data[$teamID]['win']           = $win;
            $data[$teamID]['lose']          = $lose;
        }
        $data['gain']           = $tgain;
        $data['participated']   = $tparticipated;
        $data['correct']        = $tcorrect;
        $data['incorrect']      = $tincorrect;
        $data['win']            = $twin;
        $data['lose']           = $tlose;
        $data['tweight']        = $data['win'] + $data['lose'];
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
function predictionsOf($userID=1) {
    $events = getEventIDs();
    foreach ($events as $eventID) {
        $data = predictionFor($eventID, $userID);
        $prediction['gain']         += $data['gain'];
        // SUMMERY BY MATCH COUNT
        $prediction['participated'] += $data['participated'];
        $prediction['correct']      += $data['correct'];
        $prediction['incorrect']    += $data['incorrect'];
        // SUMMERY BY MATCH WEIGHT
        $prediction['tweight']      += $data['tweight'];
        $prediction['win']          += $data['win'];
        $prediction['lose']         += $data['lose'];
        // echo '<br><pre>'. print_r($data, true) .'</pre>';
    }
    if ($prediction['participated']) $prediction['win_rate'] = ($prediction['correct'] / $prediction['participated']) * 100;
    else $prediction['win_rate'] = 0;
    if ($prediction['tweight']) $prediction['weight_rate']   = ($prediction['win'] / $prediction['tweight']) * 100;
    else $prediction['tweight'] = 0;
    // echo '<br><pre>'. print_r($prediction, true) .'</pre>';
    return $prediction;
}