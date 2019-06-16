<?php 
/**
 * Road to top
 */
// use Rank;
class RoadToTop {
	static function render($args=[]) {
		$ranksHTML = '';
		$ranksHTML .= '<div class="RoadToTopsection"><div class="tabs tabs_default" id="Roadtotop">';
            $ranksHTML .= '<ul class="horizontal">';
                $ranksHTML .= '<li class="proli"><a href="#match">Match</a></li>';
                $ranksHTML .= '<li class="proli"><a href="#toss">Toss</a></li>';
                $ranksHTML .= '<li class="proli"><a href="#all">All</a></li>';
            $ranksHTML .= '</ul>';
            $ranksHTML .= '<div id="match">'. self::ovarall($args['match']) .'</div>';
            $ranksHTML .= '<div id="toss">'. self::ovarall($args['toss']) .'</div>';
            $ranksHTML .= '<div id="all">'. self::ovarall($args['overall']) .'</div>';
        $ranksHTML .= '</div></div>';
		echo $ranksHTML;
	}
	static function ovarall($args=[]) {
		if (empty($args)) {
			$args['user_id'] = wp_get_current_user()->ID;
			$args['type'] = 'overall';
			$args['accuracy'] = 50;
			$args['engagement'] = 80;
			$args['participated'] = 80;
			$args['item_s'] = 'match or toss';
			$args['item_p'] = 'matches or tosses';
		}
		$predictor = self::userRank($args);
		return self::content($predictor, $args);
	}
	static function match($args=[]) {
		if (empty($args)) {
			$args['user_id'] = wp_get_current_user()->ID;
			$args['type'] = 'overall_match';
			$args['accuracy'] = 50;
			$args['engagement'] = 80;
			$args['participated'] = 80;
			$args['item_s'] = 'match';
			$args['item_p'] = 'matches';
		}
		$predictor = self::userRank($args);
		echo self::content($predictor, $args);
	}
	static function toss($args=[]) {
		if (empty($args)) {
			$args['user_id'] = wp_get_current_user()->ID;
			$args['type'] = 'overall_toss';
			$args['accuracy'] = 50;
			$args['engagement'] = 80;
			$args['participated'] = 80;
			$args['item_s'] = 'toss';
			$args['item_p'] = 'tosses';
		}
		$predictor = self::userRank($args);
		echo self::content($predictor, $args);
	}
	
	static function content($predictor, $args=[]) {
		$html = '';
		$html .= '<div class="login-profile">';
		$html .= self::rankInfo($predictor,$args);
		$html .= self::accuracy($predictor,$args);
		$html .= self::engagement($predictor,$args);
		$html .= self::suggestion($predictor,$args);
		$html .= '</div>';
		return $html;
	}
	static function rankInfo($predictor, $args) {
		$html = '';
		$html .= '<div class="item">
				<h3>My Rank</h3>
				<div class="circle">
					<p><strong>'. $predictor[$args['type'] .'_rank'] .'</strong></p>
				</div>
				<div class="additional">
					<span><strong>Among:</strong> '. $predictor['total'] .' </span>
				</div>
		</div>';
		return $html;
	}
	static function accuracy($predictor, $args) {
		$html = '';
		$class = $predictor[$args['type'] .'_accuracy'] >= $args['accuracy'] ? 'green' : 'red';
		$html .= '<div class="item">
				<h3>Accuracy</h3>
				<div class="circle '. $class .'">
					<p><strong>'. $predictor[$args['type'] .'_accuracy'] .'%</strong></p>
				</div>
				<div class="additional">
					<span style="display: inline-block"><strong>Win:</strong> '. $predictor[$args['type'] .'_win'] .', </span>
					<span style="display: inline-block"><strong>Lose:</strong> '. $predictor[$args['type'] .'_lose'] .' </span>
				</div>
		</div>';
		return $html;
	}
	static function engagement($predictor, $args) {
		$html = '';
		$class = $predictor[$args['type'] .'_engagement'] >= $args['engagement'] ? 'green' : 'red';
		$html .= '<div class="item">
				<h3>Engagement</h3>
				<div class="circle '. $class .'">
					<p><strong>'. $predictor[$args['type'] .'_engagement'] .'%</strong></p>
				</div>
				<div class="additional">
					<span>Your minimal engagement should be <strong>65%</strong></span>
				</div>
		</div>';
		return $html;
	}
	static function suggestion($predictor, $args) {
		$html = '';
		if ($predictor[$args['type'] .'_participated'] >= $args['participated']) {
            $message = 'You\'ve completed the milestone';
            $class = 'green';
        } else {
            $need = $args['participated'] - $predictor[$args['type'] .'_participated'];
            $item = $need > 1 ? $args['item_p'] : $args['item_s'];
            $message = 'You need to predict <strong>'. $need .'</strong> more '. $item;
            $class = 'red';
        }
        $html .= '<div class="item">
            <h3>Participated</h3>
            <div class="circle '. $class .'">
                <p><strong>'. $predictor[$args['type'] .'_participated'] .'</strong></p>
            </div>
            <div class="additional '. $class .'">
                <span>'. $message .'</span>
            </div>
        </div>';
		return $html;
	}
	static function new($predictor, $args) {
		$html = '';
		return $html;
	}
	static function userRank($args) {
		$userRank = [];
		if (empty($args['user_id'])) $args['user_id'] = wp_get_current_user()->ID;
		if ($ranks = Ranks::all()) {
			foreach ($ranks as $rank) {
				if ($args['user_id'] == $rank->user_id) {
					$userRank = $rank;
					break;
				}
			}
			$userRank->total = count($ranks);
		}
		return (array) $userRank;
	}
}