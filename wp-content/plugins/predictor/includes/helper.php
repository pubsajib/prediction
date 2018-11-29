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
    add_role('viewer', 'Viewer', ['read'=> true, 'delete_posts' => false]);
}
add_action('admin_init', 'ui_new_role');
// METABOX DYNAMIC FIELDS
function predictor_option_fields() {
    $id = @$_GET['post'];
    $meta = get_post_meta($id, 'event_ops', true);
    $data = [];
    $data[] = array(
        'type' => 'notice',
        'class' => 'danger',
        'content' => 'Enter section information and save. Then go to next tab',
    );
    if (!empty($meta['teams'])) {
        foreach ($meta['teams'] as $team) {
            $data[] = array(
                'id' => 'team_'. predictor_id_from_string($team['name']),
                'type' => 'group',
                'title' => $team['name'],
                'desc' => 'Each section name should be unique',
                'button_title' => 'Add New',
                'accordion_title' => 'Add New section',
                'fields' => array(
                    array(
                        'id' => 'title',
                        'type' => 'text',
                        'title' => 'Title',
                    ),
                    array(
                        'id' => 'weight',
                        'type' => 'weight',
                        'title' => 'Weight',
                    ),
                ),
            );
        }
    }
    return $data;
}
function predictor_answer_fields() {
    $id = @$_GET['post'];
    $meta = get_post_meta($id, 'event_ops', true);
    $data = [];
    // PUBLISH DEFAULT ANSWERS
    $data[] = ['id' => 'published', 'type'  => 'switcher', 'title' => 'Publish result'];
    if ($meta['teams']) {
        foreach ($meta['teams'] as $team) {
            $data[] = ['type' => 'notice', 'class' => 'info', 'content' => $team['name']];
            $options = 'team_'. predictor_id_from_string($team['name']);
            if ($meta[$options]) {
                foreach ($meta[$options] as $option) {
                    $name = 'default_'. predictor_id_from_string($team['name']) .'_'. predictor_id_from_string($option['title']);
                    $data[] = ['id' => $name, 'type' => 'radio', 'title' => $option['title'], 'options' => radioItems($option['weight'])];
                }
            }
        }
    } else {
        $data[] = ['type' => 'notice', 'class' => 'danger', 'content' => 'Please fill the options first and save'];
        $data[] = ['type' => 'notice', 'class' => 'default', 'content' => 'Nothing found!'];
    }
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
    $data[] = ['type' => 'notice', 'class' => 'info', 'content' => '<h3 style="margin:0;">Predictions</h3>'];
    $data[] = ['type' => 'notice', 'class' => 'default', 'content' => $html];
    // SHORTCODE
    $data[] = ['type' => 'notice', 'class' => 'info', 'content' => '<h3 style="margin:0;">Short code</h3>'];
    $data[] = ['type' => 'notice', 'class' => 'default', 'content' => '[prediction id='. $id .']'];
    return $data;
}
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
    help($user_info);
    if ($user_info) return $user_info->user_nicename;
    return false;
}

function answersHTML($meta, $ans) {
    $html = '';
    if (!empty($ans)) {
        $html .= '<div class="answersWrapper">';
        foreach ($ans as $uID => $answer) {
            if ($answer) {
                $user = get_userdata($uID);
                $html .= '<div id="predictor_'. $uID .'" class="answerContainer item">';
                $html .= '<h4>'. get_avatar( $user->user_email , '70 ') .' '. get_the_author_meta('nickname',$uID) .'</h4>';
                if ($meta['teams']) {
                    $html .= '<div class="box teamAnsWrapper">';
                    foreach ($meta['teams'] as $team) {
                        $html .= '<div class="box teamAnsContainer">';
                        $html .= '<h3 class="teamName">'. $team['name'] .'</h3>';
                        $teamID = predictor_id_from_string($team['name']);
                        $options = 'team_'. $teamID;
                        if ($meta[$options]) {
                            foreach ($meta[$options] as $option) {
                                $ansID = $options.'_'.predictor_id_from_string($option['title']);
                                $default = 'default_'. $teamID .'_'. predictor_id_from_string($option['title']);
                                if (!$answer[$ansID]) continue;
                                $isCorrect = '';
                                if ($meta['published']) {
                                    $isCorrect = @$ans[$uID][$ansID]== @$meta[$default] ? '<img src="http://cricdiction.com/wp-content/uploads/2018/11/checked.png">' : '<img src="http://cricdiction.com/wp-content/uploads/2018/11/delete.png">';
                                }
                                // $html .= $ans[$uID][$ansID] .'=='. $meta[$default];
                                $html .= '<div class="answer">'. @$option['title'] .' <br><strong><span>'. @$answer[$ansID] .'</span></strong>&nbsp;&nbsp;&nbsp;<span>'. $isCorrect .'</span></div>'; 
                            }
                        }
                        $html .= '</div>';
                    }
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
        }
        $html .= '</div>';
    } else {
        $html .= 'No answer given yet';
    }
    // $html .= '<br><pre>'. print_r($ans, true) .'</pre>';
    // $html .= '<br><pre>'. print_r($meta, true) .'</pre>';
    return $html;
}
function getValidUserID($type='viewer') {
    if (!is_user_logged_in()) {
        return false;
    } else {
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