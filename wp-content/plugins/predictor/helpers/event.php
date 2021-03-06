<?php 
// METABOX DYNAMIC FIELDS
function predictor_option_fields() {
    $id = !empty($_GET['post']) ? $_GET['post'] : 0;
    if ($id) {
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
                        ['id' => 'weight', 'type' => 'weight', 'title' => 'Weight'],
                        // ['id' => 'abandon', 'type'  => 'switcher', 'title' => 'Abandoned'],
                    ],
                );
            }
        }
        return $data;
    }
    return false;
}
function predictor_answer_fields() {
    $data = [];
    $id = !empty($_GET['post']) ? $_GET['post'] : false;
    if ($id) {
        $meta = get_post_meta($id, 'event_ops', true);
        // PUBLISH DEFAULT ANSWERS
        if (@$meta['teams']) {
            foreach ($meta['teams'] as $team) {
                // $data[] = ['type' => 'notice', 'class' => 'info', 'content' => $team['name']];
                $data[] = ['type' => 'heading', 'content' => $team['name']];
                $options = 'team_'. predictor_id_from_string($team['name']);
                if (@$meta[$options]) {
                    foreach ($meta[$options] as $option) {
                        $data[] = ['type' => 'notice', 'class' => 'info', 'content' => $option['title']];
                        // $data[] = ['type' => 'subheading', 'class' => 'info', 'content' => $option['title']];
                        $name = 'default_'. predictor_id_from_string($team['name']) .'_'. predictor_id_from_string($option['title']);
                        $data[] = ['id' => $name, 'type' => 'radio', 'title' => 'Correct answer', 'options' => radioItems($option['weight'])];
                        $data[] = ['id' => $name.'_published', 'type'  => 'switcher', 'title' => 'Publish result'];
                    }
                }
            }
        } else {
            $data[] = ['type' => 'notice', 'class' => 'danger', 'content' => 'Please fill the options first and save'];
            $data[] = ['type' => 'notice', 'class' => 'default', 'content' => 'Nothing found!'];
        }
    }
    return $data;
}
function radioItems(array $weights) {
    $options = [];
    foreach ($weights as $weight) {
        if (!trim($weight['name'])) continue;
        $options[$weight['name']] = $weight['name'];
    }
    $options['abandon'] = 'Abandon';
    return $options;
}
function prediction_answers() {
    $id = isset($_GET['post']) && !empty($_GET['post']) ? (int) $_GET['post'] : 0;
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