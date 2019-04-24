<?php
/* Template Name: PCP */
get_header();
if (!is_user_logged_in()) {
//  echo '<style>.unAuthorizedUser p{text-align:center; color:red;font-weight:bold;padding: 120px 0;font-size:20px;} </style>';
//  echo '<div class="unAuthorizedUser"><p>Please login to access the page.</p></div>';
    echo do_shortcode('[profilepress-login id="1"]');
} else {
    echo '<link rel = "stylesheet" href = "https://cricdiction.com/wp-content/plugins/predictor/frontend/css/res-timeline.css"/>
    <script type = "text/javascript" src ="https://cricdiction.com/wp-content/plugins/predictor/frontend/js/calendar-jquery.min.js"></script>   
    <script type = "text/javascript" src="https://cricdiction.com/wp-content/plugins/predictor/frontend/js/res-timeline.js"></script>';
    
    $user = wp_get_current_user();
    if ( !array_intersect( ['predictor'], (array) $user->roles ) ) {
        echo '<style>.unAuthorizedUser p{text-align:center; color:red;font-weight:bold;padding: 120px 0;font-size:20px;} </style>';
        echo '<div class="unAuthorizedUser"><p>Don\'t have the permission</p></div>';
    } else {
        // RANKS
        $ranking    = getRakingFor();
        $rank       = getUserRankedDetails($ranking['all'], $user->ID)['rank'];
        $ranking    = getRakingFor('match');
        help($ranking);
        $rank       = getUserRankedDetails($ranking['all'], $user->ID)['rank'];
        $ranking    = getRakingFor('toss')['all'];
        $rank       = getUserRankedDetails($ranking, $user->ID)['rank'];
        // TOURNAMENT
        $predictors = getPredictorsList();
        $ranking    = getRakingForTournament('all', 12, $predictors, 1, 0);
        $rank       = userRankingStatusFor($user->ID, $ranking)['num'];

        // help($rank);
        $rankInfo = self::getUserRankedDetails($ranking['all'], $user->ID);
        echo '<div class="author-profile-card">';
            echo '<div class="profile-info">';
                profileInfo($user);
            echo '</div>';
        echo '</div>';
        
        echo '<div class="tabs tabs_default" id="Roadtotop">';
            echo '<ul class="horizontal">';
                echo '<li class="proli"><a href="#match">Match</a></li>';
                echo '<li class="proli"><a href="#toss">Toss</a></li>';
                echo '<li class="proli"><a href="#all">All</a></li>';
            echo '</ul>';
            echo '<div id="match">'; roadToTopMatch($user); echo '</div>';
            echo '<div id="toss">'; roadToTopToss($user); echo '</div>';
            echo '<div id="all">'; roadToTop($user); echo '</div>';
        echo '</div>';
        echo calendarEvents($user, true);
        
    }
}
get_footer();