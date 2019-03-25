<?php 
class Predictors {
	public static function render($attr) {
		$attr = shortcode_atts( array(
			'id' => 1,
		), $attr, 'predictors' );
		$ID = $attr['id'];
		
		$html  = '';
		$html .= '<style>.site-header{ display:none;}</style>';

		$args = ['role_in' => ['predictor']];
		$users = get_users($args);
		if ($users) {
			$html .= '<div class="box predictorsWrapper">';
				$html .= '<table>';
					$html .= '<tr> <th>##</th> <th>Name</th> </tr>';
					foreach ($users as $index => $user) {
						$url = site_url('predictor/?p='. $user->user_login);
						$html .= '<tr> <td>'. ($index + 1) .'</td> <td><a href="'. $url .'">'. $user->display_name .'</a></td> </tr>';
					}
				$html .= '</table>';
			$html .= '</div>';
		}
		
		// $html .= '<br><pre>'. print_r($users, true) .'</pre>';
		return $html;
	}
 }
add_shortcode( 'predictors', array( 'Predictors', 'render' ) ); 

?>