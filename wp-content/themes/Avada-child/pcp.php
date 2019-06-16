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
// 	help($user);
    if ( !array_intersect( ['predictor'], (array) $user->roles ) ) {
        echo '<style>.unAuthorizedUser p{text-align:center; color:red;font-weight:bold;padding: 120px 0;font-size:20px;} </style>';
//         echo '<div class="unAuthorizedUser"><p>Don\'t have the permission</p></div>';
        echo '<div class="text-center">'; 
			echo '<div class="author-photo"> '. get_avatar( $user->user_email , '120 ') .' '. $ratingIcon .'</div>';
        	echo '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. $user->data->display_name .'</a></h3>';
			echo '<div class="OptionsForExpert">';
				echo '<a href="https://www.cricdiction.com/request-expert-panel/" class="btn fusion-button button-flat fusion-button-pill button-small button-default btn-violet block">Request for expert panel access</a>';
				echo '<a href="https://www.cricdiction.com/community/account/" class="btn fusion-button button-flat fusion-button-pill button-small button-default btn-purple block">Account settings</a>';
				echo '<a href="https://www.cricdiction.com/calendar/" class="btn fusion-button button-flat fusion-button-pill button-small button-default btn-blue block">Today match prediction</a>';
				echo '<a href="https://www.cricdiction.com/expert-predictors-overall/" class="btn fusion-button button-flat fusion-button-pill button-small button-default btn-blue block">Top experts</a>';
			echo '</div>';
    	echo '</div>';
	} else {
		$likes = get_the_author_meta('likes',$user->ID);

echo '<div class="profile-wrapper">';
    echo '<div class="row">';
        echo '<div class="col-md-4">';
            echo '<div class="card">';
                echo '<div class="author-profile-card">';
            echo '<div class="profile-info">';
                if ($user) {
                    echo '<div class="author-photo"> '. get_avatar( $user->user_email , '120 ') .' '. $ratingIcon .'</div>';
                    echo '<h3><a href="'. site_url('predictor/?p='. $user->user_login) .'">'. $user->data->display_name .'</a></h3>';
                    if ($user->user_url) echo '<strong>Website:</strong> <a href="'. $user->user_url .'">'. $user->user_url .'</a><br />';
                    if ($user->user_description) echo $user->user_description;
                }
				echo '<div style="text-align: center;"><a href="https://www.cricdiction.com/community/account/">Account Settings / Change Profile Picture</a></div>';
            echo '</div>';
        echo '</div>';
            echo '</div>';
        echo '</div>';
        echo '<div class="col-md-8">';
            echo '<div class="toprankingsection pcp">';
                echo '<div class="items">';
                    echo '<div class="card" style="padding-bottom: 40px;">';
                        echo '<h3>Overall Like</h3>';
                        echo '<div class="circle">';
                            echo '<h2><span class="countuserlike likeCounter_'.$user->ID.'">'. $likes .'</span></h2>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
//                 echo '<div class="items">';
//                     echo '<div class="card">';
//                         echo '<h3>Match Rank</h3>';
//                         echo '<div class="circle">';
//                             echo '<h2>10</h2>';
//                         echo '</div>';
//                     echo '</div>';
//                 echo '</div>';
//                 echo '<div class="items last">';
//                     echo '<div class="card">';
//                         echo '<h3>Toss Rank</h3>';
//                         echo '<div class="circle">';
//                             echo '<h2>3</h2>';
//                         echo '</div>';
//                     echo '</div>';
//                 echo '</div>';
            echo '</div>';
        echo '</div>';
    echo '</div>';
echo '</div>';

        
        $args['overall'] = [
            'user_id'=>$user->ID,'type'=>'overall','accuracy'=>50,'engagement'=>80,'participated'=>80,'item_s'=>'match or toss','item_p'=>'matches or tosses'];
        $args['match'] = [
            'user_id'=>$user->ID,'type'=>'overall_match','accuracy'=>50,'engagement'=>80,'participated'=>80,'item_s'=>'match','item_p'=>'matches'];
        $args['toss'] = [
            'user_id'=>$user->ID,'type'=>'overall_toss','accuracy'=>50,'engagement'=>50,'participated'=>80,'item_s'=>'toss','item_p'=>'tosses'];
        RoadToTop::render($args);
        echo calendarEvents($user);
        
    }
}
get_footer();