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

		    foreach($ranks as $user) {
		        $user = (array) $user;
		        // Ovarall
		        $matches[$user['overall_match_rank']] = $user; ksort($matches);
		        $tosses[$user['overall_toss_rank']] = $user; ksort($tosses);
				$cwc[$user['cwc2019_match_rank']] = $user; ksort($cwc);
				$cwc_toss[$user['cwc2019_toss_rank']] = $user; ksort($cwc_toss);
		    }
			$data .= '<div class="tabs tabs_default parent-ranking" id="TopPredictor">';
				$data .= '<ul class="horizontal">';
					$data .= '<li class="proli"><a href="#match">Match Experts</a></li>';
					$data .= '<li class="proli"><a href="#toss">Toss Experts</a></li>';
					$data .= '<li class="proli"><a href="#cwcmatch">CWC Match</a></li>';
					$data .= '<li class="proli"><a href="#cwctoss">CWC Toss</a></li>';
				$data .= '</ul>';
				// ================================== MATCH ===================================== //
				$data .= '<div id="match">'. self::slider($matches, $profilePage, $attr, 'overall_match') .'</div>';
				// ================================== TOSS ====================================== //
				$data .= '<div id="toss">'. self::slider($tosses, $profilePage, $attr, 'overall_toss') .'</div>';
				// ================================== IPL ======================================= //
				$data .= '<div id="cwcmatch">'. self::slider($cwc, $profilePage, $attr, 'cwc2019_match') .'</div>';
				$data .= '<div id="cwctoss">'. self::slider($cwc_toss, $profilePage, $attr, 'cwc2019_toss') .'</div>';
			$data .= '</div>';
		}
		return $data;
	}
	static function slider($supporters, $profilePage='', $attr, $type) {
        $data = $sliderItems = '';
        $counter = 0;
        if ($supporters) {
            foreach ($supporters as $userRank => $supporter) {
                if ($counter >= $attr['number']) break;
                if ($supporter[$type.'_eligibility'] > 80) { 
                    $ratingIcon = '<p>'. $userRank .'</p>';
                    $profileLink = $profilePage.'?p='. $supporter['login'];
                    $sliderItems .='<div class="item">';
                        $sliderItems .= '<div class="rank-float">' . $ratingIcon . '</div>';
                        $sliderItems .='<p><a href="'. $profileLink .'" target="_blank"><img style="border-radius:50%" src="'. $supporter['avatar'] .'"></a></p>';
                        $sliderItems .='<p style="text-align:center;">'. $supporter['name'] .'</p>';
                    $sliderItems .='</div>';
                    $counter++;   
                }
            }
            if ($sliderItems) $data .= '<div class="owl-carousel owl-theme '. $attr['class'] .'">'. $sliderItems .'</div>';
            else $data .= '<p class="text-center" style="color:#fff"> Nothing found </p>';
        }
        return $data;
    }
 }
add_shortcode('top', ['top', 'render']);