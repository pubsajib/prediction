<?php 
// [range id=123]
add_shortcode( 'range', array( 'Range', 'render' ) );
class Range {
    public static function render($attr) {
        $attr = shortcode_atts( ['id' => 1], $attr, 'range' );
        $html  = '';
        $eventID = $attr['id'];
        if (get_post_type($eventID) != 'event') $html .= 'May be your given EVENT ID is wrong';
        else {
            $html .= '<div class="progressContainer" style="position:relative">';
                $html .= '<span class="rangeRefreshBtn fusion-button button-default button-small" event="'.$eventID.'">Reload</span>';
                $html .= '<div id="progressWrapper_'.$eventID.'" class="progressWrapper">';
                    $html .= self::contentHTML($eventID);
                $html .= '</div>';
            $html .= '</div>';
        }
        return $html;
    }
    static function contentHTML($eventID) {
        $html  = '';
        $ans   = (array) get_post_meta($eventID, 'event_ans', true); 
        if (isset($ans[0])) unset($ans[0]);
        // GIVEN PREDICTIONS
        $html .= self::getFavoriteTeamForThisEvent($ans, $eventID, true);
        return $html;
    }
    static function getFavoriteTeamForThisEvent($answers, $eventID, $showTab=false) : string {
        $data       = '';
        $eventLink = get_permalink($eventID);
        $users = self::getAnsweredUsers($answers);
        $data .= self::teams($eventID);
        $data .= self::getFavoriteEventAllSupportersSlider($users, $eventLink);
        $data .= '<div class="text-center viewEventBtn"><a href="'. $eventLink .'" target="_blank" class="fusion-button button-flat fusion-button-pill button-large button-default predict">view expert predictions</a></div>';
        return $data;
    }
    static function getFavoriteEventAllSupportersSlider($supporters, $eventLink=false) {
        $data = '';
        if ($supporters) {
            $data .= '<div class="owl-carousel owl-theme eventSupperters">';
                foreach ($supporters as $supporter) {
                    if ($eventLink) {
                        $profileLink = $eventLink .'#'. $supporter['id'];
                        $data .= '<div class="item">';
                            $data .= '<a href="'. $profileLink .'" target="_blank">';
                                $data .= '<p><img style="border-radius:50%" src="'. $supporter['avatar'] .'"></p>';
                                $data .= '<p style="text-align:center;">'. $supporter['nickname'] .'</p>';
                            $data .= '</a>';
                        $data .= '</div>';
                    }
                }
            $data .= '</div>';
        }
        return $data;
    }
    static function getAnsweredUsers($ans) {
        global $wpdb;
        $predictors = [];
        $meta = ['nickname'=>'','avatar'=>''];
        $userIDs = implode(',', array_keys($ans));
        $users = $wpdb->get_results( "SELECT id, user_login, user_email FROM $wpdb->users WHERE ID IN ($userIDs)" );

        if ($users) {
            foreach ($users as $user) {
            $sql = "SELECT umeta_id, user_id, meta_key,`meta_value` FROM $wpdb->usermeta WHERE `user_id`= {$user->id} AND `meta_key` IN ('nickname')";
            $umetas = $wpdb->get_results( $sql );
            $meta['avatar'] = get_avatar_url( $user->user_email);
            if ($umetas) {
                foreach ($umetas as $umeta) {
                    $meta[$umeta->meta_key] = $umeta->meta_value;
                }
            }
                $predictors[$user->id] = array_merge((array)$user, $meta);
            }
        }
        return $predictors;
    }
    static function teams($eventID) {
        $data  = '';
        $meta  = (array) get_post_meta($eventID, 'event_ops', true);
        if (!empty($meta['teams'])) {
            foreach ($meta['teams'] as $team) $data .= !empty($team['name']) ? $team['name'] .', ' : '';
        }
        if ($data) return '<h2 class="eventTitles text-left">'. rtrim($data, ', ') .'</h2>';
        else return false;
    }
}