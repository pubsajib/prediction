<?php 
// [header-notification tournaments="7:BBL,13:BANGLADESH PREMIER LEAGUE"]
class headerNotification {
	public static function render($attr) {
		$html = $tabNavigation =  $tabNavigationItems = $tabContent = '';
		$allTournaments = '';
		$defaults = ['tournaments'=>'7:BBL,13:BANGLADESH PREMIER LEAGUE'];
		$attr = shortcode_atts($defaults, $attr, 'headerNotification');
		$tournaments = self::tournaments($attr['tournaments']);
		$owlSelector = 'owlCarousel_headerNotification';
		if ($tournaments) {
			foreach ($tournaments as $tournamentID => $tournament) {
				$tournamentMatches = self::html(recentMatches($tournamentID));
				$allTournaments .= $tournamentMatches; 
				$tabNavigationItems .= '<li class="proli"><a href="#tournamentID-'. $tournamentID .'">'. $tournament .'</a></li>';
				$tabContent .= '<div id="tournamentID-'. $tournamentID .'">';
					$tabContent .=  '<div class="owl-carousel '.$owlSelector.' owl-theme">';
						$tabContent .=  $tournamentMatches;
					$tabContent .=  '</div>';
				$tabContent .=  '</div>';
			}
			// NAVIGATION
			$tabNavigation .= '<ul class="horizontal">';
			$tabNavigation .= '<li class="proli"><a href="#tournament-all">All</a></li>';
			$tabNavigation .= $tabNavigationItems;
			$tabNavigation .= '</ul>';
			// TAB CONTENT
			$html .= '<div class="tabs tabs_default" id="headerNotification">';
				$html .= $tabNavigation;
				$html .= '<div id="tournament-all">';
					$html .=  '<div class="owl-carousel '.$owlSelector.' owl-theme">';
						$html .=  $allTournaments;
					$html .=  '</div>';
				$html .=  '</div>';
				$html .= $tabContent;
			$html .= '</div>';
		}
		// $html .= help($allTournaments, false);
		return $html;
	}
	public static function tournaments($tournaments) {
		$cats = [];
		if ($tournaments) {
			$tournaments = explode(',', $tournaments);
			if ($tournaments) {
				foreach ($tournaments as $tournament) {
					$tmp = explode(':', $tournament);
					$cats[$tmp[0]] = $tmp[1];
				}
			}
		}
		return $cats;
	}
	public static function html($matches) {
		$html = '';
		if ($matches) {
			// $html .= '<div class="row">';
				foreach ($matches as $match) {
					// $html .= '<div class="col-sm-4">';
					$html .= '<div class="item">';
						$html .= '<div class="matchContainer" style="border:2px solid red; margin: 0 0 20px 0; padding:5px 10px; height: 140px;">';
							$html .= '<a style="display:block;" href="'. $match['slug'] .'">';
								$html .= '<h3 style="margin:0;font-weight:700;">'. $match['title'] .'</h3>';
								$html .= '<small class="info">'. $match['cats'] .'</small>';
								if ($items = $match['item']) {
									$html .= '<div class="row">';
										foreach ($items as $item) {
											$html .= '<div class="col-sm-6 toss"><strong>'. $item['title'] .': </strong>'. $item['default'] .'</div>';
										}
									$html .= '</div>';
								}
							$html .= '</a>';
						$html .= '</div>';
					$html .= '</div>';
				}
			// $html .= '</div>';
		}
		return $html;
	}
 }
add_shortcode('header-notification', ['headerNotification', 'render']);