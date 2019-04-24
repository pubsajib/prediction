<?php 
date_default_timezone_set("Asia/Dhaka");

// GET ID FORM STRING
function predictor_id_from_string($string): string{
    $string = str_replace(['#', '[', '(', ')', '-', '+', '/', ']', ' ', '?', '\''], '_', strtolower(trim($string)));
    $string = str_replace(['&'], 'sand', $string);
    return $string;
}
// GET STRING FORM ID
function predictor_string_from_id($string): string{
    $string = ucwords(str_replace('_', ' ', trim($string)));
    $string = str_replace('sand', '&', $string);
    return $string;
}
function help($array=[], $key='', $echo=true) {
    if ($key) $data = '<br>'. $key .'<pre style="margin-top: 50px;">'. print_r($array, true) .'</pre>';
    else $data = '<br><pre style="margin-top: 50px;">'. print_r($array, true) .'</pre>';
    if ($echo) echo $data;
    else return $data;
}
function getUserNameByID($userID) {
    $user_info = get_userdata($userID);
    if ($user_info) return $user_info->user_nicename;
    return false;
}

function getValidUserID($type='viewer', $user='') {
    if (!is_user_logged_in()) return false;
    else {
        $user_id = get_current_user_id();
        $user_meta=get_userdata($user_id); 
        $user_roles=$user_meta->roles;
        if (is_array($type)) {
            if (!in_array_any($type, $user_roles)) return false;
            else return $user_id;
        } else {
            if (!in_array($type, $user_roles)) return false;
            else return $user_id;
        }
    }
}
function in_array_any($needles, $haystack) {
   return !empty(array_intersect($needles, $haystack));
}
function isValidOption($answer, $time) {
    // return true;
    if ($answer) return false;
    else if(new DateTime() >= new DateTime($time)) return false;
    else return true;
}
function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
function likesByEvent($eventID) {
    global $wpdb;
    $likesArr = [];
    $table = $wpdb->prefix.'predictor_likes';
    $likes = $wpdb->get_results("SELECT * FROM ".$table." WHERE event = ".$eventID, ARRAY_A );
    if ($likes) {
        foreach ($likes as $like) {
            if(!isset($likesArr[$like['user']])) $likesArr[$like['user']] = 0;
            $likesArr[$like['user']]++;
        }
    }
    return $likesArr;
}
function likeDislikeBtnFor($userID, $postID) {
    if (!empty($_COOKIE['cdpue'.$postID.'_'.$userID])) { $btnClass = ''; $likeTxt = 'LIKED'; }
    else { $btnClass = ' likeBtn'; $likeTxt = 'LIKE'; }
    return '<a href="javascript:;" event='. $postID .' user='.$userID.' class="fusion-button button-default button-small'. $btnClass.'">'.$likeTxt.'</a>';
}
function likesByPredictor($id, $count=false) {
    global $wpdb;
    if ($id) {
        $table = $wpdb->prefix.'predictor_likes';
        if ($count) return $wpdb->get_results("SELECT COUNT(*) as likes FROM ".$table." WHERE user = $id", OBJECT)[0]->likes;
        else return $wpdb->get_results( "SELECT * FROM ".$table." WHERE user = $id", OBJECT );
    }
    return false;
}
function increasePredictorLikes($userID) {
    $key = 'likes';
    $likes = (int) get_user_meta($userID, $key, true);
    if (!$likes) return add_user_meta( $userID, $key, $likes+1);
    else return update_user_meta( $userID, $key, $likes+1);
    return false;
}