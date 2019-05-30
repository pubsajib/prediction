<?php 
function roadToTopMatch() {
    $min = ['avg' => 50, 'match' => 100, 'engagement' => 65];
    RoadToTopMatch::render('match', $min);
}
class RoadToTopMatch {
    static function render($type='all', $min=[], $user=null) {
        if (empty($user)) $user = wp_get_current_user();
        if ( !in_array( 'predictor', (array) $user->roles ) ) echo "Not a predictor";
        else{
            $html = '';
            if (!$min) $min = ['avg' => 50, 'match' => 70, 'engagement' => 40];
    		$ranking = getRakingFor('match');
    		$rankInfo = 1;
    		$UP = predictionsOf($user->ID);
    		$lifeTimeEvents = count(lifeTimePublished($user->ID, $type));
    		$toalPublished = count(totalPublished($type));
    		$engagement = 0;
    		if (isset($rankInfo['participated'])) {
    			if ($lifeTimeEvents) $engagement = ($rankInfo['participated'] / $lifeTimeEvents) * 100;
    			$engagement = number_format($engagement, 2);
    		}
    		$html .= '<div class="login-profile">';
        		// RANK
        		if ($rankInfo) {
        			$html .= '<div class="item">
        					<h3>My Rank</h3>
        					<div class="circle">
        						<p><strong>'. $rankInfo['rank'] .'</strong></p>
        					</div>
        					<div class="additional">
        						<span><strong>Among:</strong> '. count($ranking['all']) .' </span>
        					</div>
        			</div>';
        		}
        		// ACCURICY
        		if ($UP['avg']) {
        		    $class = $UP['avg'] >= $min['avg'] ? 'green' : 'red';
        			$html .= '<div class="item">
        					<h3>Accuracy</h3>
        					<div class="circle '. $class .'">
        						<p><strong>'. $UP['avg'][$type]['rate'] .'%</strong></p>
        					</div>
        					<div class="additional">
        						<span style="display: inline-block"><strong>Win:</strong> '. $UP['avg'][$type]['correct'] .', </span>
        						<span style="display: inline-block"><strong>Lose:</strong> '. $UP['avg'][$type]['incorrect'] .' </span>
        					</div>
        			</div>';
        		}
        		// ENGAGEMENT (red/green)
        		if ($lifeTimeEvents) {
        		    $class = $engagement >= $min['engagement'] ? 'green' : 'red';
        			$html .= '<div class="item">
        					<h3>Engagement</h3>
        					<div class="circle '. $class .'">
        						<p><strong>'. $engagement .'%</strong></p>
        					</div>
        					<div class="additional">
								<span>Your minimal engagement should be <strong>65%</strong></span>
        					</div>
        			</div>';
        		}
        
        		if ($rankInfo) {
                    if ($rankInfo['participated'] >= $min['match']) {
                        $message = 'You\'ve completed the milestone';
                        $class = 'green';
                    } else {
                        $need = $min['match']-$rankInfo['participated'];
                        $item = $need > 1 ? 'matches' : 'match';
                        $message = 'You need to predict <strong>'. $need .'</strong> more '. $item;
                        $class = 'red';
                    }
                    $html .= '<div class="item">
                        <h3>Participated</h3>
                        <div class="circle '. $class .'">
                            <p><strong>'. $rankInfo['participated'] .'</strong></p>
                        </div>
                        <div class="additional '. $class .'">
                            <span>'. $message .'</span>
                        </div>
                    </div>';
                }
    		$html .= '</div>';
    		$html .= '<div class="notice"><span class="small"><strong>'. $lifeTimeEvents .'</strong> Matches published since you join as an expert and <strong>'. $toalPublished .'</strong> matches published since the system was born on 1st Jan 2019.</span></div>';
            echo $html;
    	}
    }
}