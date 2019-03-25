<?php 
function predictionsOf($userID=1, $tournamentID='') {
    $prediction = ['gain'  => 0, 'wl' =>[]];
    if (!$tournamentID) $events = getEventIDs();
    else $events = eventsByTournament($tournamentID);
    $eventAVG = defaultCriteriaValues();
    $eveID = '';
    foreach ($events as $eventID) {
        $data = predictionFor($eventID, $userID);
        if (!$data) continue;
        $eventAVG = eventAVG($eventAVG, @$data['avg']);
        $prediction['gain'] += @$data['gain'];
        $prediction['avg']  = $eventAVG;
        $prediction['wl']   = array_merge($prediction['wl'], $data['wl']);
        // echo '<br>'. $eventID .'<pre>'. print_r($data, true) .'</pre>';
    }
    // echo '<br><pre>'. print_r($prediction, true) .'</pre>';
    return $prediction;
}
function predictionFor($eventID, $userID) {
	$meta  = get_post_meta($eventID, 'event_ops', true);
	$ans   = get_post_meta($eventID, 'event_ans', true);
    $data = [];
    $winLose = [];
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
                    $optionID = predictor_id_from_string($option['title']);
                    $defaultID = 'default_'. $ID .'_'. $optionID;
                    if (!@$meta[$defaultID.'_published']) continue;
                    $isCorrect = null;
                    $defaultAns = @$meta[$defaultID];

                    $answerID = $teamID .'_'. $optionID;
                    $givenAns = @$ans[$userID][$answerID];
                    $dWeight = getDefaultWeight($option['weight'], $defaultAns);
                    $dWeight = $dWeight ? $dWeight : 0;
                    
                    if (!$givenAns) $data[$teamID][$answerID]['warning'] = 'Answer is not given.';
                    else {
                        if ($defaultAns == 'abandon') {
                            $criteriaAvg = updateCriteriaAVGFor($criteriaAvg, $option['id'], $dWeight, 'abandon');
                            $isCorrect = 'abandon';
                            $gain += 0;
                        } else if ($defaultAns == $givenAns) {
                            $criteriaAvg = updateCriteriaAVGFor($criteriaAvg, $option['id'], $dWeight, 1);
                            $isCorrect = 1;
                            @$gain += $dWeight;
                        } else{
                            $isCorrect = 0;
                            $criteriaAvg = updateCriteriaAVGFor($criteriaAvg, $option['id'], $dWeight, 0);
                            @$gain -= $dWeight;
                        }
                        // FOR DEBUGING / SHOW
                        $data[$teamID][$answerID]['question']   = $option['title'];
                        $data[$teamID][$answerID]['weight']     = $dWeight;
                        $data[$teamID][$answerID]['default']    = $defaultAns;
                        $data[$teamID][$answerID]['given']      = $givenAns;
                        $data[$teamID][$answerID]['is_correct'] = $isCorrect;
                        $winLose[] = ['event'=>$eventID, 'team' => $team['name'],'item'=> $option['title'], 'type'=> $option['id'], 'status'=>$isCorrect];
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
        $data['wl']     = $winLose;
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
    return 0;
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
        if ($isCorrect === 'abandon') {$criteriaAvg[$criteriaID]['abandon']++;}
        elseif ($isCorrect == 1) $criteriaAvg[$criteriaID]['correct']++;
        else $criteriaAvg[$criteriaID]['incorrect']++;
        // ALL
        $criteriaAvg['all']['participated']++;
        if ($isCorrect === 'abandon') {
            $criteriaAvg['all']['abandon']++; 
        } elseif ($isCorrect == 1) {
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
    $data['all']['abandon'] = 0;
    // CRITERIAS
    $criterias = cs_get_option('criteria_event');
    if ($criterias) {
        foreach ($criterias as $criteria) {
            $criteriaID = predictor_id_from_string($criteria['name']);
            $data[$criteriaID]['participated'] = 0;
            $data[$criteriaID]['correct'] = 0;
            $data[$criteriaID]['incorrect'] = 0;
            $data[$criteriaID]['rate'] = 0;
            $data[$criteriaID]['abandon'] = 0;
        }
    }
    return $data;
}
function eventAVG($eventAvg, $criteriaAvg) {
    if ($eventAvg) {
        foreach ($eventAvg as $criteriaName => $criteriaValues) {
            if ($criteriaValues) {
                foreach ($criteriaValues as $key => $value) {
                    $eventAvg[$criteriaName][$key] += $criteriaAvg[$criteriaName][$key] ? $criteriaAvg[$criteriaName][$key] : 0;
                }
            }
            // RATE BY WIN
            if ($eventAvg[$criteriaName]['participated']) {
                $totalEven = $eventAvg[$criteriaName]['participated'] - $eventAvg[$criteriaName]['abandon'];
                if ($totalEven > 0) $rating = ($eventAvg[$criteriaName]['correct'] / $totalEven) * 100;
                else $rating = 0;
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
// ALL HTML GENERATOR FUNCTIONS
function profileInfo($user, $echo=true, $ratingIcon='') {
    $data = '';
    if ($user) {
        $data .= '<div class="author-photo"> '. get_avatar( $user->user_email , '120 ') .' '. $ratingIcon .'</div>';
        $data .= '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3>';
            if ($user->user_url) $data .= '<strong>Website:</strong> <a href="'. $user->user_url .'">'. $user->user_url .'</a><br />';
            if ($user->user_description) $data .= $user->user_description;
    }
    if ($echo) echo $data;
    return $data;
}
function summeryHtml($prediction, $permited=['all']) {
    $data = '';
    $data = '<style>.prediction-full-result li{width:20%;}</style>';
    if (@$prediction['avg']) {
        foreach ($prediction['avg'] as $type => $prediction) {
            if ($prediction['participated'] && in_array($type, $permited)) {
                $percentage = number_format((float)$prediction['rate'], 2, '.', '');
                $data .= '<div class="win-accuracy '. $type .'" id="'. $type .'">';
                    $data .= '<h3 class="title">'. $type .'</h3>';
                    $data .= '<ul class="prediction-full-result">';
                        $data .= '<li class="block">';
                            $data .= '<strong>Accuracy</strong><br>';
                            $data .= '<div class="progress-bar" value="'. $prediction['rate'] .'" data-percent="'. $percentage .'" max="100"></div>';
                        $data .= '</li>';
                        $data .= '<li>';
                            $data .= '<strong>Participated</strong><br>';
                            $data .= '<div class="common">'. $prediction['participated'] .'</div>';
                        $data .= '</li>';
                        $data .= '<li>';
                            $data .= '<strong>Win</strong><br>';
                            $data .= '<div class="common">'. $prediction['correct'] .'</div>';
                        $data .= '</li>';
                        $data .= '<li>';
                            $data .= '<strong>Lose</strong><br>';
                            $data .= '<div class="common red">'. $prediction['incorrect'] .'</div>';
                        $data .= '</li>';
                        $data .= '<li>';
                            $data .= '<strong>Abandon</strong><br>';
                            $data .= '<div class="common red">'. $prediction['abandon'] .'</div>';
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
        $data .= summeryHtml($prediction, $permited);
        $data .= '<hr><hr>';
        $data .= winLoseHtml($prediction);
		$data .= '<p class="winLoselink"><a href="#" data-izimodal-open="#winLosePop" data-izimodal-transitionin="fadeInDown">View All</a></p>';
		$data .= '<div id="winLosePop" data-iziModal-group="grupo1">';
		$data .= '<button data-izimodal-close="" class="icon-close">x</button>';
		$data .= '<section>';
			$data .= winLoseHtml($prediction);
		$data .= '</section>';
	$data .= '</div>';
    }
    echo $data;
}