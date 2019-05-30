<?php 
/**
 * RANK
 */
class Ranks {
    static function all() {
        global $wpdb;
        $tableName   = $wpdb->prefix.'predictor_rating_summery';
        $sql  = "SELECT * FROM `". $tableName ."`;";
        $ranks = $wpdb->get_results($sql);
        return $ranks;
    }
    static function getUserRankingFor($userID) {
        global $wpdb;
        $tableName   = $wpdb->prefix.'predictor_rating_summery';
        $sql  = "SELECT * FROM `". $tableName ."` WHERE `user_id` = $userID LIMIT 1;";
        $ranks = $wpdb->get_results($sql);
        return !empty($ranks[0]) ? $ranks[0] : false;
    }
    static function rankByType($rankingType='') {
        global $wpdb;
        $tableName   = $wpdb->prefix.'predictor_rating_summery';
        $sql  = "SELECT * FROM `". $tableName ."`";
        if ($rankingType) $sql .= " ORDER BY ". $rankingType."_rank ASC";
        $sql .= ";";
        $ranks = $wpdb->get_results($sql);
        return $ranks;
    }
	static function rankByClassName($className) {
		global $wpdb;
		$tableName   = $wpdb->prefix.'predictor_rating_summery';
        $sql  = "SELECT * FROM `". $tableName ."`";
        $sql .= " WHERE `class` = '". $className ."'";
        $sql .= " ORDER BY overall_rank ASC";
        $sql .= ";";
		$ranks = $wpdb->get_results($sql);
		return $ranks;
	}
	static function ranksContentHTML($users, $type, $torunament=false) {
    	$data = '';
        $data .= '<div class="predictorListWrapper"><div class="equalAll">';
            foreach ($users as $userRank => $predictor) {
                $user = (array) $predictor;
                $ratingIcon = $ratingClass = '';
                $profileLink = site_url('predictor/?p='. $user['login']);
                if ($torunament) {
                    $all = json_decode($user[$type.'_desc'], true);
                    $match = json_decode($user['all_desc'], true);
                    $toss = json_decode($user['match_desc'], true);
                    $allTitle = 'IPL';
                    $matchTitle = 'All';
                    $tossTitle = 'Match';
                } else {
                    if ($type == 'match') {
                        $all = json_decode($user[$type.'_desc'], true);
                        $match = json_decode($user['all_desc'], true);
                        $toss = json_decode($user['toss_desc'], true);
                        $allTitle = 'Match';
                        $matchTitle = 'All';
                        $tossTitle = 'Toss';
                    } else if ($type == 'toss') {
                        $all = json_decode($user[$type.'_desc'], true);
                        $match = json_decode($user['all_desc'], true);
                        $toss = json_decode($user['match_desc'], true);
                        $allTitle = 'Toss';
                        $matchTitle = 'All';
                        $tossTitle = 'Match';
                    } else {
                        $all = json_decode($user[$type.'_desc'], true);
                        $match = json_decode($user['match_desc'], true);
                        $toss = json_decode($user['match_desc'], true);
                        $allTitle = 'All';
                        $matchTitle = 'Match';
                        $tossTitle = 'Toss';
                    }
                }
                
                if ($userRank < 4 && $all['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top3 rank_'. $userRank; }
                else if ($userRank < 11 && $all['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>'; $ratingClass = 'ranked top10 rank_'. $userRank; }
                else { $ratingIcon = ''; $ratingClass = 'rank_'. $userRank; }
                
                $data .= '<div id="predictor_'. $user['id'] .'" class="predictorContainer author-profile-card '. $ratingClass .'">';
                    $data .= '<table class="table top-accuracy">';
                        $data .= '<tr>';
                            $data .= '<td><small>'. $allTitle .' (' . $all['participated'] . ') </small><br>' . $all['accuracy'] . '%<br><small class="last"><span class="green">'. $all['win'] . '</span>/<span class="red">'. $all['lose'] . '</span></small></td>';
                            $data .= '<td><small>'. $matchTitle .' (' . $match['participated'] . ') </small><br>' . $match['accuracy'] . '%<br><small class="last"><span class="green">'. $match['win'] . '</span>/<span class="red">'. $match['lose'] . '</span></small></td>';
                            $data .= '<td><small>'. $tossTitle .' (' . $toss['participated'] . ') </small><br>' . $toss['accuracy'] . '%<br><small class="last"><span class="green">'. $toss['win'] . '</span>/<span class="red">'. $toss['lose'] . '</span></small></td>';
                        $data .= '</tr>';
                    $data .= '</table>';
                    // $data .= 'Eligibility : '. $all['eligibility'];
                    // PROFILE INFORMATION
                    $data .= '<div class="profile-info">'. self::profileInfoFromArr($user, false, $ratingIcon).'</div>';
                    $data .= '<div class="profile-link"><a href="'. site_url('predictor/?p='. $user['login']) .'" target="_blank">VIEW PROFILE</a></div>';
                $data .= '</div>';
            }
        $data .= '</div></div>';
        return $data;
    }
	static function profileInfoFromArr($user, $echo=true, $ratingIcon='') {
	    $data = '';
	    if ($user) {
	        $data .= '<div class="author-photo"> <img src="'. $user['avatar'] .'" alt="Avatar"> '. $ratingIcon .'</div>';
	        $data .= '<h3><a href="'. site_url('predictor/?p='. $user['login']) .'">'. $user['name'] .'('. $user['likes'] .')</a></h3>';
	            if (!empty($user['url'])) $data .= '<strong>Website:</strong> <a href="'. $user['url'] .'">'. $user['url'] .'</a><br />';
	            // if (!empty($user['description'])) $data .= $user['description'];
	    }
	    if ($echo) echo $data;
	    return $data;
	}
	static function profileInfoFromObj($user, $echo=true, $ratingIcon='') {
	    $data = '';
	    if ($user) {
	        $data .= '<div class="author-photo"> <img src="'. $user->avatar .'" alt="Avatar"> '. $ratingIcon .'</div>';
	        $data .= '<h3><a href="'. site_url('predictor/?p='. $user->login) .'">'. $user->name .'</a></h3>';
	            if (!empty($user->url)) $data .= '<strong>Website:</strong> <a href="'. $user->url .'">'. $user->url .'</a><br />';
	            if (!empty($user->description)) $data .= $user->description;
	    }
	    if ($echo) echo $data;
	    return $data;
	}
}