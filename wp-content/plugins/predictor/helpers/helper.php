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

function getValidUserID($type='viewer') {
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