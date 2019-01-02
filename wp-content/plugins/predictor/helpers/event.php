<?php 
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
                'fields' => [
                    ['id' => 'title', 'type' => 'text', 'title' => 'Title'],
                    ['id' => 'id', 'type' => 'select', 'title' => 'ID', 'options' => eventCriterias()],
                    ['id' => 'time', 'type' => 'number', 'title' => 'Time (min)', 'default' => 30, 'dependency' => array( 'id', '==', 'toss' ),],
                    ['id' => 'weight', 'type' => 'weight', 'title' => 'Weight']
                ],
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
    if (@$meta['teams']) {
        foreach ($meta['teams'] as $team) {
            $data[] = ['type' => 'notice', 'class' => 'info', 'content' => $team['name']];
            $options = 'team_'. predictor_id_from_string($team['name']);
            if (@$meta[$options]) {
                foreach ($meta[$options] as $option) {
                    $name = 'default_'. predictor_id_from_string($team['name']) .'_'. predictor_id_from_string($option['title']);
                    $data[] = ['id' => $name, 'type' => 'checkbox', 'title' => $option['title'], 'options' => radioItems($option['weight'])];
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
    $html = adminAnswersHTML($meta, $ans);
    $data[] = ['type' => 'notice', 'class' => 'info', 'content' => '<h3 style="margin:0;">Predictions</h3>'];
    $data[] = ['type' => 'notice', 'class' => 'default', 'content' => $html];
    // SHORTCODE
    $data[] = ['type' => 'notice', 'class' => 'info', 'content' => '<h3 style="margin:0;">Short code</h3>'];
    $data[] = ['type' => 'notice', 'class' => 'default', 'content' => '[prediction id='. $id .']'];
    return $data;
}
function eventCriterias() {
    $options = [];
    $criterias = cs_get_option('criteria_event');
    if ($criterias) {
        $options[''] = 'Select Type';
        foreach ($criterias as $criteria) {
            $optionID = predictor_id_from_string($criteria['name']);
            $options[$optionID] = $criteria['name'];
        }
    }
    return $options;
}