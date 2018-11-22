<?php 
function createPostType() {
    register_post_type('event', array(
        'labels' => array(
            'name' => _x('Event', 'post type general name', 'predictor'),
            'singular_name' => _x('Event', 'post type singular name', 'predictor'),
            'menu_name' => _x('Event', 'admin menu', 'predictor'),
            'name_admin_bar' => _x('Event', 'add new on admin bar', 'predictor'),
            'add_new' => _x('Add New', 'event', 'predictor'),
            'add_new_item' => __('Add New Event', 'predictor'),
            'new_item' => __('New Event', 'predictor'),
            'edit_item' => __('Edit Event', 'predictor'),
            'view_item' => __('View Event', 'predictor'),
            'all_items' => __('All Event', 'predictor'),
            'search_items' => __('Search Event', 'predictor'),
            'parent_item_colon' => __('Parent Event:', 'predictor'),
            'not_found' => __('No Event found.', 'predictor'),
            'not_found_in_trash' => __('No Event found in Trash.', 'predictor'),
        ),
        'public' => true,
        'exclude_from_search' => true,
        'supports' => array('title'),
    ));
}
add_action('init', 'createPostType');
function ui_new_role() {  
    add_role('predictor', 'Predictor', ['read'=> true, 'delete_posts' => false]);
}
add_action('admin_init', 'ui_new_role');
function predictor_answer_fields() {
    $id = @$_GET['post'];
    $meta = get_post_meta($id, 'event_ops', true);
    $data = [];

    if (!empty($meta['options'])) {
        foreach ($meta['options'] as $option) {
            if (!trim($option['title'])) continue;
            $optionID = 'default_' . predictor_id_from_string($option['title']);
            $data[] = ['id' => $optionID, 'type' => 'radio', 'title' => $option['title'], 'options' => radioItems($option['weight'])];
        }
    }
    // $data[] = array(
    //     'type' => 'notice',
    //     'class' => 'danger',
    //     // 'content' => '<pre>'. print_r($meta, true) .'</pre>',
    //     'content' => '<pre>'. print_r($meta['options'], true) .'</pre>',
    // );
    return $data;
}
function radioItems(array $weights) {
    $options = [];
    foreach ($weights as $weight) {
        if (!trim($weight['name'])) continue;
        $options[$weight['name']] = $weight['name'];
    }
    return $options;
}
function prediction_answers() {
    $id = @$_GET['post'];
    $html = '';
    $data = [];
    $meta = get_post_meta($id, 'event_ops', true);
    $ans = get_post_meta($id, 'event_ans', true);

    // GIVEN PREDICTIONS
    $html = answersHTML($meta, $ans);
    $data[] = array(
        'type' => 'notice',
        'class' => 'default',
        // 'content' => '<pre>'. print_r($meta, true) .'</pre>',
        'content' => $html,
    );
    
    return $data;
}


// generate id from string
function predictor_id_from_string($string): string{
    $string = str_replace(['#', '[', '(', ')', '-', '+', '/', ']', ' ', '?', '\''], '_', strtolower(trim($string)));
    $string = str_replace(['&'], 'sand', $string);
    return $string;
}
function predictor_string_from_id($string): string{
    $string = ucwords(str_replace('_', ' ', trim($string)));
    $string = str_replace('sand', '&', $string);
    return $string;
}
function help($array=[], $key='', $echo=true) {
    if ($key) $data = '<br>'. $key .'<pre style="margin-left: 250px; margin-top: 50px;">'. print_r($array, true) .'</pre>';
    else $data = '<br><pre style="margin-left: 250px; margin-top: 50px;">'. print_r($array, true) .'</pre>';
    if ($echo) echo $data;
    else return $data;
}
function getAllPredictorsID() {

}
function getUserNameByID($userID) {
    $user_info = get_userdata($userID);
    if ($user_info) return $user_info->user_login;
    return false;
}
function answersHTML($meta, $ans) {
    $html = '';
    if (!empty($ans)) {
        $html .= '<div class="box answersWrapper">';
        $html .= '<h3>All predictions</h3>';
        foreach ($ans as $uID => $answer) {
            $user = getUserNameByID($uID);
            $html .= '<div id="predictor_'. $uID .'" class="answerContainer">';
            $html .= '<h4>'. $user .'\'s prediction</h4>';
            foreach ($meta['options'] as $option) {
                $name = predictor_id_from_string($option['title']);
                $isCorrect = @$ans[$uID][$name] == @$meta['default_'. $name] ? '&#10003;' : '&#10005';
                // &#10003; gives a lighter one
                // &#10005 MULTIPLICATION X
                // &#10006 HEAVY MULTIPLICATION X
                // $html .= $ans[$uID][$name] .'=='. $meta['default_'. $name];
                $html .= '<div class="answer">'. @$option['title'] .' <span>'. @$answer[$name] .'</span> <span>'. $isCorrect .'</span></div>'; 
            }
            $html .= '</div>';
        }
        $html .= '</div>';
    } else {
        $html .= 'No answer given yet';
    }
    return $html;
}