<?php
/* Template Name: PCP */
get_header();
if (!is_user_logged_in()) {
//  echo '<style>.unAuthorizedUser p{text-align:center; color:red;font-weight:bold;padding: 120px 0;font-size:20px;} </style>';
//  echo '<div class="unAuthorizedUser"><p>Please login to access the page.</p></div>';
    echo do_shortcode('[profilepress-login id="1"]');
} else {
    // echo '<link rel = "stylesheet" href = "https://cricdiction.com/wp-content/plugins/predictor/frontend/css/res-timeline.css"/>
    // <script type = "text/javascript" src ="https://cricdiction.com/wp-content/plugins/predictor/frontend/js/calendar-jquery.min.js"></script>   
    // <script type = "text/javascript" src="https://cricdiction.com/wp-content/plugins/predictor/frontend/js/res-timeline.js"></script>';
    
    $user = wp_get_current_user();
    if ( !array_intersect( ['predictor'], (array) $user->roles ) ) {
        echo '<style>.unAuthorizedUser p{text-align:center; color:red;font-weight:bold;padding: 120px 0;font-size:20px;} </style>';
        echo '<div class="unAuthorizedUser"><p>Don\'t have the permission</p></div>';
    } else {
        echo '<div class="author-profile-card">';
            echo '<div class="profile-info">';
                if ($user) {
                    echo '<div class="author-photo"> '. get_avatar( $user->user_email , '120 ') .' '. $ratingIcon .'</div>';
                    echo '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. get_the_author_meta('nickname',$user->ID) .'</a></h3>';
                    if ($user->user_url) echo '<strong>Website:</strong> <a href="'. $user->user_url .'">'. $user->user_url .'</a><br />';
                    if ($user->user_description) echo $user->user_description;
                }
            echo '</div>';
        echo '</div>';
        
        // echo '<div class="tabs tabs_default" id="Roadtotop">';
        //     echo '<ul class="horizontal">';
        //         echo '<li class="proli"><a href="#match">Match</a></li>';
        //         echo '<li class="proli"><a href="#toss">Toss</a></li>';
        //         echo '<li class="proli"><a href="#all">All</a></li>';
        //     echo '</ul>';
        //     echo '<div id="match">'; roadToTopMatch($user); echo '</div>';
        //     echo '<div id="toss">'; roadToTopToss($user); echo '</div>';
        //     echo '<div id="all">'; roadToTop($user); echo '</div>';
        // echo '</div>';
        echo calendarEvents($user);
        $args['overall'] = [
            'user_id'=>$user->ID,'type'=>'overall','accuracy'=>50,'engagement'=>80,'participated'=>80,'item_s'=>'match or toss','item_p'=>'matches or tosses'];
        $args['match'] = [
            'user_id'=>$user->ID,'type'=>'overall_match','accuracy'=>50,'engagement'=>80,'participated'=>80,'item_s'=>'match','item_p'=>'matches'];
        $args['toss'] = [
            'user_id'=>$user->ID,'type'=>'overall_toss','accuracy'=>50,'engagement'=>80,'participated'=>80,'item_s'=>'toss','item_p'=>'tosses'];
        RoadToTop::render($args);
    }
}
get_footer();