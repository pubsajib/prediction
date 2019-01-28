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
                            $teams[$options]['items'][$option['id']][predictor_id_from_string($itemTeam)] = count($answerCountByOption);
                        }
                    }
                    $teams[$options]['items'][$option['id']]['name'] = $option['title'];
                }
            }
        }
    }
    // $data .= help($teams, false);
    

    if ($teams) {
        $data .= '<table style="text-align:center;">';
        foreach ($teams as $team) {
            $data .= '<tr>';
                $data .= '<th colspan="3">'. $team['name'] .'</th>';
            $data .= '</tr>';
            if (isset($team['teams'])) {
                $firstTeamName = $team['teams'][0];
                $secondTeamName = $team['teams'][1];
                $firstTeamID = predictor_id_from_string($team['teams'][0]);
                $secondTeamID = predictor_id_from_string($team['teams'][1]);
                $data .= '<tr>';
                    $data .= '<td>'. $firstTeamName .'</td>';
                    $data .= '<td>Type</td>';
                    $data .= '<td>'. $secondTeamName .'</td>';
                $data .= '</tr>';
                if (isset($team['items']['match'])) {
                    if ($team['items']['match'][$firstTeamID] > $team['items']['match'][$secondTeamID]) {
                        $firstMatchItemClass = 'style="background:green;"';
                        $secondMatchItemClass = 'style="background:red;"';
                    } else if ($team['items']['match'][$firstTeamID] < $team['items']['match'][$secondTeamID]) {
                        $firstMatchItemClass = 'style="background:red;"';
                        $secondMatchItemClass = 'style="background:green;"';
                    } else {
                        $firstMatchItemClass = '';
                        $secondMatchItemClass = '';
                    }
                    $data .= '<tr>';
                        $data .= '<td '. $firstMatchItemClass .'>'. $team['items']['match'][$firstTeamID] .'('. $team['items']['match']['sum'] .')</td>';
                        $data .= '<td>'. $team['items']['match']['name'] .$isFirstMatchGreater.'</td>';
                        $data .= '<td '. $secondMatchItemClass .'>'. $team['items']['match'][$secondTeamID] .'('. $team['items']['match']['sum'] .')</td>';
                    $data .= '</tr>';
                }
                if (isset($team['items']['toss'])) {
                    if ($team['items']['toss'][$firstTeamID] > $team['items']['toss'][$secondTeamID]) {
                        $firstTossItemClass = 'style="background:green;"';
                        $secondTossItemClass = 'style="background:red;"';
                    } else if ($team['items']['toss'][$firstTeamID] < $team['items']['toss'][$secondTeamID]) {
                        $firstTossItemClass = 'style="background:red;"';
                        $secondTossItemClass = 'style="background:green;"';
                    } else {
                        $firstTossItemClass = '';
                        $secondTossItemClass = '';
                    }
                    $isFirstTossGreater = $team['items']['toss'][$firstTeamID] <=> $team['items']['toss'][$secondTeamID];
                    $data .= '<tr>';
                        $data .= '<td '. $firstTossItemClass .'>'. $team['items']['toss'][$firstTeamID] .'('. $team['items']['toss']['sum'] .')</td>';
                        $data .= '<td>'. $team['items']['toss']['name'] .$isFirstTossGreater.'</td>';
                        $data .= '<td '. $secondTossItemClass .'>'. $team['items']['toss'][$secondTeamID] .'('. $team['items']['toss']['sum'] .')</td>';
                    $data .= '</tr>';
                }
            }
        }
        $data .= '</table>';
    }
    // $data .= help($answers, false);
    return $data;
}