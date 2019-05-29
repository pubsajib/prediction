<?php
/* Template Name: Ranks All*/
get_header();
$rankType = 'all';
$ranks = Ranks::rankByType($rankType);
if ($ranks) {
    $all = $matches = $tosses = $tournaments = $t_20 = $odi = $ipl = $t20_toss = $odi_toss = $ipl_toss = [];
    foreach($ranks as $user) {
        $user = (array) $user;
        // Ovarall
        $all[$user['all_rank']] = $user; ksort($all);
        $matches[$user['match_rank']] = $user; ksort($matches);
        $tosses[$user['toss_rank']] = $user; ksort($tosses);
        // Matches
        $t_20[$user['t_20_rank']] = $user; ksort($t_20);
        $odi[$user['odi_rank']] = $user; ksort($odi);
        $test[$user['test_rank']] = $user; ksort($test);
        $ipl[$user['ipl_rank']] = $user; ksort($ipl);
        // Tosses
        $t20_toss[$user['t20_toss_rank']] = $user; ksort($t20_toss);
        $odi_toss[$user['odi_toss_rank']] = $user; ksort($odi_toss);
        $test_toss[$user['test_toss_rank']] = $user; ksort($test_toss);
        $ipl_toss[$user['ipl_toss_rank']] = $user; ksort($ipl_toss);
    }
    // help($all);
    // ranksContentHTML($all, 'all');
    echo '<div class="tabs tabs_default parent-ranking" id="RankAll">';
        echo '<ul class="horizontal rank">
            <li class="proli rank"><a href="#all">Overall</a></li>
            <li class="proli rank"><a href="#match">Match</a></li>
            <li class="proli rank"><a href="#toss">Toss</a></li>
        </ul>';
        
        // echo '<div id="all">'.Ranks::ranksContentHTML($all, 'all').'</div>';
        echo '<div id="all">';
            echo '<div class="predictorListWrapper"><div class="equalAll">';
                foreach ($all as $userRank => $predictor) {
                    $user = (array) $predictor;
                    $ratingIcon = $ratingClass = '';
                    $profileLink = site_url('predictor/?p='. $user['login']);
                    $all = json_decode($user['all_desc'], true);
                    $match = json_decode($user['match_desc'], true);
                    $toss = json_decode($user['toss_desc'], true);
                    
                    
                    if ($userRank < 4 && $all['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                    else if ($userRank < 11 && $all['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                    else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                    
                    echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                        echo '<table class="table top-accuracy">';
                            echo '<tr>';
                                echo '<td><small>All (' . $all['participated'] . ') </small><br>' . $all['accuracy'] . '%<br><small class="last"><span class="green">'. $all['win'] . '</span>/<span class="red">'. $all['lose'] . '</span></small></td>';
                                echo '<td><small>Match (' . $match['participated'] . ') </small><br>' . $match['accuracy'] . '%<br><small class="last"><span class="green">'. $match['win'] . '</span>/<span class="red">'. $match['lose'] . '</span></small></td>';
                                echo '<td><small>Toss (' . $toss['participated'] . ') </small><br>' . $toss['accuracy'] . '%<br><small class="last"><span class="green">'. $toss['win'] . '</span>/<span class="red">'. $toss['lose'] . '</span></small></td>';
                            echo '</tr>';
                        echo '</table>';
                        // PROFILE INFORMATION
                        echo '<div class="profile-info">'. Ranks::profileInfoFromArr($user, false, $ratingIcon).'</div>';
                        echo '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
                    echo '</div>';
                }
            echo '</div></div>';
        echo '</div>';
        // ================================== MATCH ===================================== //
        echo '<div id="match">';
            echo '<div class="tabs tabs_default" id="RankingAllMatch">';
                echo '<ul class="horizontal">';
                    echo '<li class="proli"><a href="#all">Overall</a></li>';
                    echo '<li class="proli"><a href="#t20">T20</a></li>';
                    echo '<li class="proli"><a href="#odi">ODI</a></li>';
                    echo '<li class="proli"><a href="#test">TEST</a></li>';
                    echo '<li class="proli"><a href="#ipl">IPL</a></li>';
                echo '</ul>';
                
                // echo '<div id="all">'.Ranks::ranksContentHTML($matches, 'match').'</div>';
                echo '<div id="all">';
                    echo '<div class="predictorListWrapper"><div class="equalAll">';
                        foreach ($matches as $userRank => $predictor) {
                            $user = (array) $predictor;
                            $ratingIcon = $ratingClass = '';
                            $profileLink = site_url('predictor/?p='. $user['login']);
                            $all = json_decode($user['all_desc'], true);
                            $match = json_decode($user['match_desc'], true);
                            $toss = json_decode($user['toss_desc'], true);
                            
                            if ($userRank < 4 && $all['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $all['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $match['accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $match['participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $match['win'] . '</span>/<span class="red">'. $match['lose'] . '</span></small></td>';
                                    echo '</tr>';
                                echo '</table>';
                                // PROFILE INFORMATION
                                echo '<div class="profile-info">'. Ranks::profileInfoFromArr($user, false, $ratingIcon).'</div>';
                                echo '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
                            echo '</div>';
                        }
                    echo '</div></div>';
                echo '</div>';
                // T20
                echo '<div id="t20">';
                    echo '<div class="predictorListWrapper"><div class="equalAll">';
                        foreach ($t_20 as $userRank => $predictor) {
                            if($userRank>10) break;
                            $user = (array) $predictor;
                            $ratingIcon = $ratingClass = '';
                            $profileLink = site_url('predictor/?p='. $user['login']);
                            $t20 = json_decode($user['t_20_desc'], true);
                            
                            if ($userRank < 4 && $t20['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $t20['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $t20['accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $t20['participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $t20['win'] . '</span>/<span class="red">'. $t20['lose'] . '</span></small></td>';
                                    echo '</tr>';
                                echo '</table>';
//                          echo $t20['eligibility'];
                                // PROFILE INFORMATION
                                echo '<div class="profile-info">'. Ranks::profileInfoFromArr($user, false, $ratingIcon).'</div>';
                                echo '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
                            echo '</div>';
                        }
                    echo '</div></div>';
                echo '</div>';
                // ODI
                echo '<div id="odi">';
                    echo '<div class="predictorListWrapper"><div class="equalAll">';
                        foreach ($odi as $userRank => $predictor) {
                            if($userRank>10) break;
                            $user = (array) $predictor;
                            $ratingIcon = $ratingClass = '';
                            $profileLink = site_url('predictor/?p='. $user['login']);
                            $odi = json_decode($user['odi_desc'], true);
                            
                            if ($userRank < 4 && $odi['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $odi['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $odi['accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $odi['participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $odi['win'] . '</span>/<span class="red">'. $odi['lose'] . '</span></small></td>';
                                    echo '</tr>';
                                echo '</table>';
                                // PROFILE INFORMATION
                                echo '<div class="profile-info">'. Ranks::profileInfoFromArr($user, false, $ratingIcon).'</div>';
                                echo '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
                            echo '</div>';
                        }
                    echo '</div></div>';
                echo '</div>';
                // TEST
                echo '<div id="test">';
                    echo '<div class="predictorListWrapper"><div class="equalAll">';
                        foreach ($test as $userRank => $predictor) {
                            if($userRank>10) break;
                            $user = (array) $predictor;
                            $ratingIcon = $ratingClass = '';
                            $profileLink = site_url('predictor/?p='. $user['login']);
                            $test = json_decode($user['test_desc'], true);
                            
                            if ($userRank < 4 && $test['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $test['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $test['accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $test['participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $test['win'] . '</span>/<span class="red">'. $test['lose'] . '</span></small></td>';
                                    echo '</tr>';
                                echo '</table>';
                                // PROFILE INFORMATION
                                echo '<div class="profile-info">'. Ranks::profileInfoFromArr($user, false, $ratingIcon).'</div>';
                                echo '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
                            echo '</div>';
                        }
                    echo '</div></div>';
                echo '</div>';
                // IPL
                echo '<div id="ipl">';
                    echo '<div class="predictorListWrapper"><div class="equalAll">';
                        foreach ($ipl as $userRank => $predictor) {
                            if($userRank>10) break;
                            $user = (array) $predictor;
                            $ratingIcon = $ratingClass = '';
                            $profileLink = site_url('predictor/?p='. $user['login']);
                            $ipl = json_decode($user['ipl_desc'], true);
                            
                            if ($userRank < 4 && $ipl['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $ipl['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $ipl['accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $ipl['participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $ipl['win'] . '</span>/<span class="red">'. $ipl['lose'] . '</span></small></td>';
                                    echo '</tr>';
                                echo '</table>';
                                // PROFILE INFORMATION
                                echo '<div class="profile-info">'. Ranks::profileInfoFromArr($user, false, $ratingIcon).'</div>';
                                echo '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
                            echo '</div>';
                        }
                    echo '</div></div>';
                echo '</div>';
                
            echo '</div>';
        echo '</div>';
        // ================================== TOSS ====================================== //
        echo '<div id="toss">';
            echo '<div class="tabs tabs_default" id="RankingAllToss">';
                echo '<ul class="horizontal">';
                    echo '<li class="proli"><a href="#all_toss">Overall</a></li>';
                    echo '<li class="proli"><a href="#t20_toss">T20</a></li>';
                    echo '<li class="proli"><a href="#odi_toss">ODI</a></li>';
                    echo '<li class="proli"><a href="#test_toss">TEST</a></li>';
                    echo '<li class="proli"><a href="#ipl_toss">IPL</a></li>';
                echo '</ul>';
                
                // echo '<div id="all">'.Ranks::ranksContentHTML($matches, 'match').'</div>';
                echo '<div id="all_toss">';
                    echo '<div class="predictorListWrapper"><div class="equalAll">';
                        foreach ($tosses as $userRank => $predictor) {
                            $user = (array) $predictor;
                            $ratingIcon = $ratingClass = '';
                            $profileLink = site_url('predictor/?p='. $user['login']);
                            $all = json_decode($user['all_desc'], true);
                            $match = json_decode($user['match_desc'], true);
                            $toss = json_decode($user['toss_desc'], true);
                            
                            
                            if ($userRank < 4 && $toss['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $toss['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $toss['accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $toss['participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $toss['win'] . '</span>/<span class="red">'. $toss['lose'] . '</span></small></td>';
                                    echo '</tr>';
                                echo '</table>';
                                // PROFILE INFORMATION
                                echo '<div class="profile-info">'. Ranks::profileInfoFromArr($user, false, $ratingIcon).'</div>';
                                echo '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
                            echo '</div>';
                        }
                    echo '</div></div>';
                echo '</div>';
                // T20
                echo '<div id="t20_toss">';
                    echo '<div class="predictorListWrapper"><div class="equalAll">';
                        foreach ($t20_toss as $userRank => $predictor) {
                            if($userRank>10) break;
                            $user = (array) $predictor;
                            $ratingIcon = $ratingClass = '';
                            $profileLink = site_url('predictor/?p='. $user['login']);
                            $t20 = json_decode($user['t20_toss_desc'], true);
                            
                            if ($userRank < 4 && $t20['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $t20['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $t20['accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $t20['participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $t20['win'] . '</span>/<span class="red">'. $t20['lose'] . '</span></small></td>';
                                    echo '</tr>';
                                echo '</table>';
                                // PROFILE INFORMATION
                                echo '<div class="profile-info">'. Ranks::profileInfoFromArr($user, false, $ratingIcon).'</div>';
                                echo '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
                            echo '</div>';
                        }
                    echo '</div></div>';
                echo '</div>';
                // ODI
                echo '<div id="odi_toss">';
                    echo '<div class="predictorListWrapper"><div class="equalAll">';
                        foreach ($odi_toss as $userRank => $predictor) {
                            if($userRank>10) break;
                            $user = (array) $predictor;
                            $ratingIcon = $ratingClass = '';
                            $profileLink = site_url('predictor/?p='. $user['login']);
                            $odi = json_decode($user['odi_toss_desc'], true);
                            
                            if ($userRank < 4 && $odi['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $odi['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $odi['accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $odi['participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $odi['win'] . '</span>/<span class="red">'. $odi['lose'] . '</span></small></td>';
                                    echo '</tr>';
                                echo '</table>';
                                // PROFILE INFORMATION
                                echo '<div class="profile-info">'. Ranks::profileInfoFromArr($user, false, $ratingIcon).'</div>';
                                echo '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
                            echo '</div>';
                        }
                    echo '</div></div>';
                echo '</div>';
                // TEST
                echo '<div id="test_toss">';
                    echo '<div class="predictorListWrapper"><div class="equalAll">';
                        foreach ($test_toss as $userRank => $predictor) {
                            if($userRank>10) break;
                            $user = (array) $predictor;
                            $ratingIcon = $ratingClass = '';
                            $profileLink = site_url('predictor/?p='. $user['login']);
                            $test = json_decode($user['test_desc'], true);
                            
                            if ($userRank < 4 && $test['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $test['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $test['accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $test['participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $odi['win'] . '</span>/<span class="red">'. $odi['lose'] . '</span></small></td>';
                                    echo '</tr>';
                                echo '</table>';
                                // PROFILE INFORMATION
                                echo '<div class="profile-info">'. Ranks::profileInfoFromArr($user, false, $ratingIcon).'</div>';
                                echo '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
                            echo '</div>';
                        }
                    echo '</div></div>';
                echo '</div>';
                // IPL
                echo '<div id="ipl_toss">';
                    echo '<div class="predictorListWrapper"><div class="equalAll">';
                        foreach ($ipl_toss as $userRank => $predictor) {
                            if($userRank>10) break;
                            $user = (array) $predictor;
                            $ratingIcon = $ratingClass = '';
                            $profileLink = site_url('predictor/?p='. $user['login']);
                            $ipl = json_decode($user['ipl_toss_desc'], true);
                            
                            if ($userRank < 4 && $ipl['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $ipl['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $ipl['accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $ipl['participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $ipl['win'] . '</span>/<span class="red">'. $ipl['lose'] . '</span></small></td>';
                                    echo '</tr>';
                                echo '</table>';
                                // PROFILE INFORMATION
                                echo '<div class="profile-info">'. Ranks::profileInfoFromArr($user, false, $ratingIcon).'</div>';
                                echo '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
                            echo '</div>';
                        }
                    echo '</div></div>';
                echo '</div>';
                
            echo '</div>';
        echo '</div>';
        // TOSS
//      echo '<div id="toss">';
//          echo '<div class="predictorListWrapper"><div class="equalAll">';
//              foreach ($tosses as $userRank => $predictor) {
//                  $user = (array) $predictor;
//                  $ratingIcon = $ratingClass = '';
//                  $profileLink = site_url('predictor/?p='. $user['login']);
//                     $all = json_decode($user['all_desc'], true);
//                     $match = json_decode($user['match_desc'], true);
//                     $toss = json_decode($user['toss_desc'], true);
                    
                    
//                  if ($userRank < 4 && $toss['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
//                  else if ($userRank < 11 && $toss['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
//                  else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                    
//                  echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
//                      echo '<table class="table top-accuracy">';
//                          echo '<tr>';
//                              echo '<td><small>Toss (' . $toss['participated'] . ') </small><br>' . $toss['accuracy'] . '%<br><small class="last"><span class="green">'. $toss['win'] . '</span>/<span class="red">'. $toss['lose'] . '</span></small></td>';
//                              echo '<td><small>All (' . $all['participated'] . ') </small><br>' . $all['accuracy'] . '%<br><small class="last"><span class="green">'. $all['win'] . '</span>/<span class="red">'. $all['lose'] . '</span></small></td>';
//                              echo '<td><small>Match (' . $match['participated'] . ') </small><br>' . $match['accuracy'] . '%<br><small class="last"><span class="green">'. $match['win'] . '</span>/<span class="red">'. $match['lose'] . '</span></small></td>';
//                          echo '</tr>';
//                      echo '</table>';
//                      // PROFILE INFORMATION
//                      echo '<div class="profile-info">'. Ranks::profileInfoFromArr($user, false, $ratingIcon).'</div>';
//                      echo '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
//                  echo '</div>';
//              }
//          echo '</div></div>';
//      echo '</div>';
    echo '</div>';
}

get_footer();