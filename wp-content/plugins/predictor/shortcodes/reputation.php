<?php 
// [reputation number=12]
class Reputation {
	public static function render($attr) {
		$attr = shortcode_atts(['number' => 3, 'class' => 'reputedPredictors'], $attr, 'reputation');
		return self::content($attr);
	}
	static function content($attr) {
		$data = '';
		$ranks = Ranks::all();
		if ($ranks) {
		    $profilePage = esc_url( site_url('predictor/'));
		    foreach($ranks as $user) {
		        $user = (array) $user;
		        $classes[$user['class']][$user['overall_rank']] = $user;
		    }
			$data .= '<div class="tabs tabs_default parent-ranking" id="reputation">';
				$data .= '<ul class="horizontal">';
					foreach ($classes as $className => $class) {
		        	    if(empty($className)) continue;
		            	$data .= '<li class="proli"><a href="#'. $className .'">'. $className .'</a></li>';
		        	}
				$data .= '</ul>';
				foreach ($classes as $className => $class) {
	            	if(empty($className)) continue;
	            	ksort($class);
	            	// $data .= help($class);
					$data .= '<div id="'. $className .'">'. self::slider($class, $profilePage, $attr, 'overall_match') .'</div>';
	        	}
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
                    $profileLink = $profilePage.'?p='. $supporter['login'];
                    $data .='<div class="item">';
                        $data .='<p><a href="'. $profileLink .'" target="_blank"><img style="border-radius:50%" src="'. $supporter['avatar'] .'"></a></p>';
                        $data .='<p style="text-align:center;">'. $supporter['name'] .'</p>';
                        $data .='<p style="text-align:center;">Overall : '. $supporter['overall_rank'] .'</p>';
                        $data .='<p style="text-align:center;">Match : '. $supporter['overall_match_rank'] .'</p>';
                        $data .='<p style="text-align:center;">Toss : '. $supporter['overall_toss_rank'] .'</p>';
                        $data .='<p style="text-align:center;">Likes : '. $supporter['likes'] .'</p>';
                    $data .='</div>';
                    $counter++;
                }
            $data .= '</div>';
        }
        return $data;
    }
 }
add_shortcode('reputation', ['Reputation', 'render']);