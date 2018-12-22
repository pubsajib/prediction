<?php 
function predictionsOf($userID=1, $tournamentID='') {
    $prediction = ['gain'  => 0, 'participated'  => 0, 'correct'  => 0, 'incorrect'  => 0, 'tweight'  => 0, 'win'  => 0, 'lose'  => 0];
    if (!$tournamentID) $events = getEventIDs();
    else $events = eventsByTournament($tournamentID);
    $eventAVG = defaultCriteriaValues();
    foreach ($events as $eventID) {
        $data = predictionFor($eventID, $userID);
        $eventAVG = eventAVG($eventAVG, @$data['avg']);
        $prediction['gain']         += @$data['gain'];
        // SUMMERY BY MATCH COUNT
        $prediction['participated'] += @$data['avg']['all']['participated'];
        $prediction['correct']      += @$data['avg']['all']['correct'];
        $prediction['incorrect']    += @$data['avg']['all']['incorrect'];
        // SUMMERY BY MATCH WEIGHT
        $prediction['win']          += @$data['avg']['all']['win'];
        $prediction['lose']         += @$data['avg']['all']['lose'];
        $prediction['tweight']      += @$prediction['win'] + $prediction['lose'];
        $prediction['test']      = $eventAVG;
        // echo '<br><pre>'. print_r($data, true) .'</pre>';
    }
    if ($prediction['participated']) $prediction['win_rate'] = ($prediction['correct'] / $prediction['participated']) * 100;
    else $prediction['win_rate'] = 0;
    // echo '<br><pre>'. print_r($prediction, true) .'</pre>';
    return $prediction;
}
function predictionFor($eventID, $userID) {
	$meta  = get_post_meta($eventID, 'event_ops', true);
	$ans   = get_post_meta($eventID, 'event_ans', true);
    // help($ans);
    $data = [];
    $eventAvg = defaultCriteriaValues();
    $tgain = $tparticipated = $tcorrect = $tincorrect = $twin = $tlose = 0;
    if (@!$ans[$userID]) return false;
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
                    // echo '<br><br>eventName:'. $criteriaName .' || key:'. $key .' || eventAvg : '. $eventAvg[$criteriaName][$key];
                    // echo '<br>criteria : '. $criteriaAvg[$criteriaName][$key];
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
                // if ($eventAvg[$criteriaName]['tweight']) {
                //     $wrating = ($eventAvg[$criteriaName]['win'] / $eventAvg[$criteriaName]['tweight']) * 100;
                //     $eventAvg[$criteriaName]['wrate'] = number_format((float)$wrating, 2, '.', '');
                // } else {
                //     $eventAvg[$criteriaName]['wrate'] = 0;
                // }
            }
            // echo "<br> {$criteriaName}";
        }
    }
    return $eventAvg;
}