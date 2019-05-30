<?php
/* Template Name: Ranks All*/
get_header();
$ranks = Ranks::all();
if ($ranks) {
    $all = $matches = $tosses = $tournaments = $t_20 = $odi = $ipl = $t20_toss = $odi_toss = $ipl_toss = [];
    foreach($ranks as $user) {
        $user = (array) $user;
        // Ovarall
        $all[$user['overall_rank']] = $user; ksort($all);
        $matches[$user['overall_match_rank']] = $user; ksort($matches);
        $tosses[$user['overall_toss_rank']] = $user; ksort($tosses);
        // Matches
        $t_20[$user['t20_match_rank']] = $user; ksort($t_20);
        $odi[$user['odi_match_rank']] = $user; ksort($odi);
        $test[$user['test_match_rank']] = $user; ksort($test);
        $ipl[$user['ipl_match_rank']] = $user; ksort($ipl);
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
                    
                    if ($userRank < 4 && $predictor['overall_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                    else if ($userRank < 11 && $predictor['overall_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                    else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                    
                    echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                        echo '<table class="table top-accuracy">';
                            echo '<tr>';
                                echo '<td><small>All (' . $predictor['overall_participated'] . ') </small><br>' . $predictor['overall_accuracy'] . '%<br><small class="last"><span class="green">'. $predictor['overall_win'] . '</span>/<span class="red">'. $predictor['overall_lose'] . '</span></small></td>';
                                echo '<td><small>Match (' . $predictor['overall_match_participated'] . ') </small><br>' . $predictor['overall_match_accuracy'] . '%<br><small class="last"><span class="green">'. $predictor['overall_match_win'] . '</span>/<span class="red">'. $predictor['overall_match_lose'] . '</span></small></td>';
                                echo '<td><small>Toss (' . $predictor['overall_toss_participated'] . ') </small><br>' . $predictor['overall_toss_accuracy'] . '%<br><small class="last"><span class="green">'. $predictor['overall_toss_win'] . '</span>/<span class="red">'. $predictor['overall_toss_lose'] . '</span></small></td>';
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
                            
                            if ($userRank < 4 && $predictor['overall_match_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $predictor['overall_match_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $predictor['overall_match_accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $predictor['overall_match_participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $predictor['overall_match_win'] . '</span>/<span class="red">'. $predictor['overall_match_lose'] . '</span></small></td>';
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
                            
                            if ($userRank < 4 && $predictor['t20_match_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $predictor['t20_match_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $predictor['t20_match_accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $predictor['t20_match_participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $predictor['t20_match_win'] . '</span>/<span class="red">'. $predictor['t20_match_lose'] . '</span></small></td>';
                                    echo '</tr>';
                                echo '</table>';
                                //echo $t20['eligibility'];
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
                            
                            if ($userRank < 4 && $predictor['odi_match_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $predictor['odi_match_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $predictor['odi_match_accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $predictor['odi_match_participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $predictor['odi_match_win'] . '</span>/<span class="red">'. $predictor['odi_match_lose'] . '</span></small></td>';
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
                            
                            if ($userRank < 4 && $predictor['test_match_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $predictor['test_match_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $predictor['test_match_accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $predictor['test_match_participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $predictor['test_match_win'] . '</span>/<span class="red">'. $predictor['test_match_lose'] . '</span></small></td>';
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
                            
                            if ($userRank < 4 && $predictor['ipl_match_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $predictor['ipl_match_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $predictor['ipl_match_accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $predictor['ipl_match_participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $predictor['ipl_match_win'] . '</span>/<span class="red">'. $predictor['ipl_match_lose'] . '</span></small></td>';
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
                            
                            if ($userRank < 4 && $predictor['overall_toss_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $predictor['overall_toss_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $predictor['overall_toss_accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $predictor['overall_toss_participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $predictor['overall_toss_win'] . '</span>/<span class="red">'. $predictor['overall_toss_lose'] . '</span></small></td>';
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
                            
                            if ($userRank < 4 && $predictor['t20_toss_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $predictor['t20_toss_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $predictor['t20_toss_accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $predictor['t20_toss_participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $predictor['t20_toss_win'] . '</span>/<span class="red">'. $predictor['t20_toss_lose'] . '</span></small></td>';
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
                            
                            if ($userRank < 4 && $predictor['odi_toss_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $predictor['odi_toss_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $predictor['odi_toss_accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $predictor['odi_toss_participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $predictor['odi_toss_win'] . '</span>/<span class="red">'. $predictor['odi_toss_lose'] . '</span></small></td>';
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
                            
                            if ($userRank < 4 && $predictor['test_toss_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $predictor['test_toss_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $predictor['test_toss_accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $predictor['test_toss_participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $predictor['test_toss_win'] . '</span>/<span class="red">'. $predictor['test_toss_lose'] . '</span></small></td>';
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
                            
                            if ($userRank < 4 && $predictor['ipl_toss_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                            else if ($userRank < 11 && $predictor['ipl_toss_eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                            else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                            
                            echo '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                                echo '<table class="table top-accuracy">';
                                    echo '<tr>';
                                        echo '<td><small>Accuracy</small><br>' . $predictor['ipl_toss_accuracy'] . '%</td>';
                                        echo '<td><small>Participated</small><br>' . $predictor['ipl_toss_participated'] . '</td>';
                                        echo '<td><small>Win/Loss</small><br><small class="last"><span class="green">'. $predictor['ipl_toss_win'] . '</span>/<span class="red">'. $predictor['ipl_toss_lose'] . '</span></small></td>';
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
    echo '</div>';
}

get_footer();