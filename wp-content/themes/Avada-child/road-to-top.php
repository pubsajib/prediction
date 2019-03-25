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
        $events = calendarEvents($user);
        if ($events) {
            $data = '';
            $data .= '<div class = "jflatTimeline">';
                $data .= '<div class = "timeline-wrap">';
                    foreach ($events as $eventDate => $cats) {
                        if ($cats) {
                            $selected = date('Ymd') == date('Ymd', strtotime($eventDate)) ? 'selected' : '';
                            $data .= '<div class="event '. $selected .'" data-date="'. $eventDate .'">';
                            foreach ($cats as $catSlug => $cat) {
                                $catName = $cat['name'];
                                unset($cat['name']);
                                $data .= '<div class="eventWrapper">';
                                    $data .= '<div class="title">'. $catName .'</div>';
                                    if ($cat) {
                                        foreach ($cat as $eventSI => $event) {
                                            if ($event['match']) {
                                                foreach ($event['match'] as $item) {
                                                    if (isset($item['opt']['match']) && !empty($item['opt']['match'])) {
                                                        $discussion = $item['dis'] ?? false;
                                                        $subTitle   = !empty($item['sub']) ? $item['sub'] .', ' : '';
                                                        $mTitle     = $item['title'];
                                                        $matchID    = @$item['opt']['match']['ID'];
                                                        $tossID     = @$item['opt']['toss']['ID'];
                                                        $match      = @$item['opt']['match']['answer'] ?? 'N/A';
                                                        $toss       = @$item['opt']['toss']['answer'] ?? 'N/A';
                                                        $mStatus    = @$item['opt']['match']['status'] ?? '';
                                                        $tStatus    = @$item['opt']['toss']['status'] ?? '';
                                                        $published  = @$item['opt']['match']['published'] ?? '';
                                                        $data .= '<div class="item">';
                                                            $data .= $published ? '<div class="status">Result</div>' : '';
                                                            $data .= '<p>'. $mTitle .'</p>';
                                                            $data .= '<div class="time">'. $subTitle. $item['time'] .'</div>';
                                                            
                                                            // $data .= '<br>isvalid == '. $item['isValid'];
                                                            // $data .= '<br>status == '. $item['status'];
                                                            
                                                            $data .= '<div class="event-predict">';
                                                                $data .= '<p class="toss"><strong>Toss: </strong><span id="'. $tossID .'">'. $toss .'</span> '. $tStatus .' </p>';
                                                                $data .= '<p class="match"><strong>Match: </strong><span id="'. $matchID .'">'. $match .'</span> '. $mStatus .' </p>';
                                                            $data .= '</div>';
                                                            $data .= '<div class="footer">';
                                                                $data .= '<a href="javascript:;" event="'. $event['ID'] .'" class="fusion-button button-default button-small predictionFormBtn">Predict Now</a>';
                                                                $data .= '<a href="'. site_url('/event/'. $event['slug']) .'" class="fusion-button button-default button-small predict">View Prediction</a>';
                                                                if ($discussion) $data .= '<a href="'. $discussion .'" class="fusion-button button-default button-small">Discussion</a>';
                                                            $data .= '</div>';
                                                        $data .= '</div>';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                $data .= '</div>';
                            }
                        }
                        $data .= '</div>';
                    }
                $data .= '</div>';
            $data .= '</div>';
            echo $data;
        }
    }
}
get_footer();