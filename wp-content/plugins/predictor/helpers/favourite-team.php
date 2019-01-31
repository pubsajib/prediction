<?php 
function getFavoriteTeamForThisEvent($meta, $answers) : string {
    $data = '';
    $teams=[];

    $ansCount = [];
    if ($answers) {
        foreach ($answers as $uID => $ans) {
            if ($ans) {
                foreach ($ans as $key => $value) {
                    $ansCount[$key][] = $value;
                }
            }
        }
    }
    // $data .= help($ansCount, false);

    if (!empty($meta['teams'])) {
        foreach ($meta['teams'] as $key => $team) {
            $teamID = predictor_id_from_string($team['name']);
            $options = 'team_'. $teamID;
            if ($meta[$options]) {
                $teams[$options]['name'] = $team['name'];
                if (!isset($teams[$options]['teams'])) {
                    if ($weights = $meta[$options][1]['weight']) {
                        foreach ($weights as $weight) {
                            if ($weight['name']) $teams[$options]['teams'][] = $weight['name'];
                        }
                    }
                }
                foreach ($meta[$options] as $option) {
                    $ansID = $options.'_'.predictor_id_from_string($option['title']);
                    $teams[$options]['items'][$option['id']]['id'] = $ansID;
                    $teams[$options]['items'][$option['id']]['name'] = $option['title'];
                    $teams[$options]['items'][$option['id']]['sum'] = count($ansCount[$ansID]);
                    if ($itemTeams = $teams[$options]['teams']) {
                        foreach ($itemTeams as $itemTeam) {
                            $answerCountByOption = [];
                            if ($ansCount[$ansID]) {
                                foreach ($ansCount[$ansID] as $ansByOption) {
                                    if ($ansByOption == $itemTeam) $answerCountByOption[] = $ansByOption;
                                }
                            }
                            if (count($ansCount[$ansID])) {
                                $itemPercentage = (count($answerCountByOption) / count($ansCount[$ansID])) * 100;
                            } else {
                                $itemPercentage = 0;
                            }
                            $teams[$options]['items'][$option['id']][predictor_id_from_string($itemTeam)] = $itemPercentage;
                        }
                    }
                    $teams[$options]['items'][$option['id']]['name'] = $option['title'];
                }
            }
        }
    }
    // $data .= help($teams, false);

    if ($teams) {
        $firstTeamColor = '#6adcfa';
        $secondTeamColor = '#3498db';
        $data .= '<div class="progressWrapper">';
            foreach ($teams as $team) {
                if (isset($team['teams'])) {
                    $matchData = '';
                    $tossData = '';
                    $teamNamesData = '';
                    $firstTeamName = $team['teams'][0];
                    $secondTeamName = $team['teams'][1];
                    $firstTeamID = predictor_id_from_string($team['teams'][0]);
                    $secondTeamID = predictor_id_from_string($team['teams'][1]);
                    
                    $teamNamesData .= '<div class="teamNameContainer">';
                        $teamNamesData .= '<div class="w50p first"> <h6 style="color:'. $firstTeamColor .'">'. $firstTeamName .'</h6> </div>';
                        $teamNamesData .= '<div class="w50p last"> <h6 style="color:'. $secondTeamColor .'">'. $secondTeamName .'</h6> </div>';
                    $teamNamesData .= '</div>';

                    if (isset($team['items']['match']) && (!empty($team['items']['match'][$firstTeamID]) || !empty($team['items']['match'][$secondTeamID]))) {
                        $firstValue = $team['items']['match'][$firstTeamID];
                        $secondValue = $team['items']['match'][$secondTeamID];
                        $matchData .= '<div class="progressContainer">';
                            if ($firstValue) {
                                $matchData .= '<div class="skillbar w50p first" style="width:'. $firstValue .'%;" data-percent="100%">';
                                    $matchData .= '<div class="skillbar-bar" style="background: '. $$firstTeamColor .';"></div>';
                                    $matchData .= '<div class="skill-bar-percent">'. number_format($firstValue , 2).'%</div>';
                                $matchData .= '</div>';
                            }
                            if ($secondValue) {
                                $matchData .= '<div class="skillbar w50p last" style="width:'. $secondValue .'%;" data-percent="100%">';
                                    $matchData .= '<div class="skillbar-bar" style="background: '. $secondTeamColor .';"></div>';
                                    $matchData .= '<div class="skill-bar-percent">'. number_format($secondValue, 2) .'%</div>';
                                $matchData .= '</div>';
                            }
                            $matchData .= '<div class="typeContainer"><small>'. $team['items']['match']['name'] .' ('. $team['items']['match']['sum'] .')</small></div>';
                        $matchData .= '</div>';
                    }
                    if (isset($team['items']['toss']) && (!empty($team['items']['toss'][$firstTeamID]) || !empty($team['items']['toss'][$secondTeamID]))) {
                        $firstValue = $team['items']['toss'][$firstTeamID];
                        $secondValue = $team['items']['toss'][$secondTeamID];
                        $tossData .= '<div class="progressContainer">';
                            if ($firstValue) {
                                $tossData .= '<div class="skillbar w50p first" style="width:'. $firstValue .'%;" data-percent="100%">';
                                    $tossData .= '<div class="skillbar-bar" style="background: '. $$firstTeamColor .';"></div>';
                                    $tossData .= '<div class="skill-bar-percent">'. number_format($firstValue , 2).'%</div>';
                                $tossData .= '</div>';
                            }
                            if ($secondValue) {
                                $tossData .= '<div class="skillbar w50p last" style="width:'. $secondValue .'%;" data-percent="100%">';
                                    $tossData .= '<div class="skillbar-bar" style="background: '. $secondTeamColor .';"></div>';
                                    $tossData .= '<div class="skill-bar-percent">'. number_format($secondValue, 2) .'%</div>';
                                $tossData .= '</div>';
                            }
                            $tossData .= '<div class="typeContainer"><small>'. $team['items']['toss']['name'] .' ('. $team['items']['toss']['sum'] .')</small></div>';
                        $tossData .= '</div>';
                    }
                    if ($matchData || $tossData) {
                        $data .= '<div class="progressContainer">';
                            $data .= '<h4 style="text-align:center;">'. $team['name'] .'</h4>';
                            $data .= $teamNamesData;
                            $data .= $matchData;
                            $data .= $tossData;
                        $data .= '</div>';
                    }
                }
            }
        $data .= '</div>';
    }
    // $data .= help($answers, false);
    return $data;
}