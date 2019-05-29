<?php 
// [top number=12]
class top {
	public static function render($attr) {
		$attr = shortcode_atts(['number' => 3, 'class' => 'eventTopSupperters'], $attr, 'top');
		return self::content($attr);
	}
	static function content($attr) {
		$data = '';
		$ranks = Ranks::all();
		if ($ranks) {
		    $profilePage = esc_url( site_url('predictor/'));
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
		    }
			$data .= '<div class="tabs tabs_default parent-ranking" id="TopPredictor">';
				$data .= '<ul class="horizontal">';
					$data .= '<li class="proli"><a href="#match">Match Experts</a></li>';
					$data .= '<li class="proli"><a href="#toss">Toss Experts</a></li>';
				// 	$data .= '<li class="proli"><a href="#ipl">IPL</a></li>';
				$data .= '</ul>';
				// ================================== MATCH ===================================== //
				$data .= '<div id="match">';
		            $data .= self::slider($matches, $profilePage, $attr, 'match');
				$data .= '</div>';
				// ================================== TOSS ====================================== //
				$data .= '<div id="toss">';
					$data .= self::slider($tosses, $profilePage, $attr, 'toss');
				$data .= '</div>';
				// ================================== IPL ======================================= //
				// $data .= '<div id="ipl">'; $data .= self::slider($ipl, $profilePage, $attr); $data .= '</div>';
			$data .= '</div>';
		}
		return $data;
	}
	static function slider($supporters, $profilePage='', $attr, $type) {
        $data = '';
        $counter = 0;
        if ($supporters) {
            $data .= '<div class="owl-carousel owl-theme '. $attr['class'] .'">';
                foreach ($supporters as $userRank => $supporter) {
                    if ($counter >= $attr['number']) break;
                    $desc['all'] = json_decode($supporter['all_desc'], true);
                    $desc['match'] = json_decode($supporter['match_desc'], true);
                    $desc['toss'] = json_decode($supporter['toss_desc'], true);
                    $ratingIcon = '';
	                if ($desc[$type]['eligibility'] > 80) { $ratingIcon = '<p>'. $userRank .'</p>';}
	                
                    $profileLink = $profilePage.'?p='. $supporter['login'];
                    $data .='<div class="item">';
                        $data .= '<div class="rank-float">' . $ratingIcon . '</div>';
                        $data .='<p><a href="'. $profileLink .'" target="_blank"><img style="border-radius:50%" src="'. $supporter['avatar'] .'"></a></p>';
                        $data .='<p style="text-align:center;">'. $supporter['name'] .'</p>';
                    $data .='</div>';
                    $counter++;
                }
            $data .= '</div>';
        }
        return $data;
    }
 }
add_shortcode('top', ['top', 'render']);