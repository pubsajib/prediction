<?php
/* Template Name: class */
get_header();
$ranks = Ranks::all();
if ($ranks) {
    foreach($ranks as $user) {
        $user = (array) $user;
        // Ovarall
        $classes[$user['class']][] = $user;
    }
    // help($classes);
    if ($classes) {
	    echo '<div class="tabs tabs_default parent-ranking" id="RankAll">';
	        echo '<ul class="horizontal rank">';
	        	foreach ($classes as $className => $class) {
	        	    if(empty($className)) continue;
	            	echo '<li class="proli rank"><a href="#'. $className .'">'. $className .'</a></li>';
	        	}
	        echo '</ul>';
	        foreach ($classes as $className => $class) {
	            if(empty($className)) continue;
		        echo '<div id="'. $className .'">';
		            echo '<div class="predictorListWrapper"><div class="equalAll">';
		                foreach ($class as $predictor) {
		                    $user = (array) $predictor;
		                    $ratingIcon = $ratingClass = '';
		                    $profileLink = site_url('predictor/?p='. $user['login']);
		                    
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
        	}
	    echo '</div>';
    }
}

get_footer();