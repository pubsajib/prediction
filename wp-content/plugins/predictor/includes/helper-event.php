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
            $gain = $participated = $correct = $incorrect = 0;
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

                    $data[$teamID][$answerID]['question']   = $option['title'];
                    $data[$teamID][$answerID]['weight']     = $default;
                    $data[$teamID][$answerID]['default']    = $defaultAns;
                    $data[$teamID][$answerID]['given']      = $givenAns;
                    if (!$givenAns) {
                        $data[$teamID][$answerID]['is_correct'] = true;
                        $gain += 0;
                    } else if ($defaultAns == $givenAns) {
                        $data[$teamID][$answerID]['is_correct'] = true;
                        $gain += $default;
                        $participated++;
                        $correct++;
                    } else{
                        $data[$teamID][$answerID]['is_correct'] = false;
                        $gain -= $default;
                        $participated++;
                        $incorrect++;
                    }
                }
            }
            $tgain += $gain;
            $tparticipated += $participated;
            $tcorrect += $correct;
            $tincorrect += $incorrect;
            // AVG RESULTS BY QUESTIONS
            $data[$teamID]['name'] = $team['name'];
            $data[$teamID]['gain'] = $gain;
            $data[$teamID]['participated'] = $participated;
            $data[$teamID]['correct'] = $correct;
            $data[$teamID]['incorrect'] = $incorrect;
        }
        $data['gain'] = $tgain;
        $data['participated'] = $tparticipated;
        $data['correct'] = $tcorrect;
        $data['incorrect'] = $tincorrect;
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
        $prediction['gain'] += $data['gain'];
        $prediction['participated'] += $data['participated'];
        $prediction['correct'] += $data['correct'];
        $prediction['incorrect'] += $data['incorrect'];
        // echo '<br><pre>'. print_r($data, true) .'</pre>';
    }
    $prediction['accuracy'] += ($prediction['correct'] / $prediction['participated']) * 100;
    return $prediction;
}
// echo '<br><pre>'. print_r(getEventIDs(), true) .'</pre>';