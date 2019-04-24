<?php 
$prefix='pre-';
$meta_boxes = [
    [
        'id'        => 'dateOption',
        'title'     => 'Date Options',
        'pages'     => ['event'],
        'context'   => 'side', // normal, side
        'priority'  => 'high',
        'fields'    => [['id' => $prefix .'date', 'name' => '', 'type' => 'date', 'desc' => '', 'std' => '']]
    ],
    [
        'id'        => 'add',
        'title'     => 'Add Options',
        'pages'     => ['event'],
        'context'   => 'normal', // normal, side
        'priority'  => 'high',
        'fields'    => [['id' => $prefix .'add', 'name' => 'add', 'type' => 'textarea', 'desc' => '', 'std' => '']]
    ],
    [
        'id'        => 'control',
        'title'     => 'Controls',
        'pages'     => ['event'],
        'context'   => 'side', // normal, side
        'priority'  => 'high',
        'fields'    => [
            ['id'   => $prefix .'featured', 'name' => 'Featured', 'type' => 'checkbox', 'desc' => '', 'std' => ''],
            ['id'   => $prefix .'published', 'name' => 'Published', 'type' => 'checkbox', 'desc' => '', 'std' => ''],
        ]
    ]
];

foreach ($meta_boxes as $meta_box) { $my_box = new My_meta_box($meta_box); }
class My_meta_box {
    protected $_meta_box;
    // create meta box based on given data
    function __construct($meta_box) {
        $this->_meta_box = $meta_box;
        add_action('admin_menu', array(&$this, 'add'));
        add_action('save_post', array(&$this, 'save'));
    }
    /// Add meta box for multiple post types
    function add() {
        foreach ($this->_meta_box['pages'] as $page) {
            add_meta_box($this->_meta_box['id'], $this->_meta_box['title'], array(&$this, 'show'), $page, $this->_meta_box['context'], $this->_meta_box['priority']);
        }
    }
    // Callback function to show fields in meta box
    function show() {
        global $post;
        // Use nonce for verification
        echo '<input type="hidden" name="mytheme_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
        echo '<table class="form-table">';
        foreach ($this->_meta_box['fields'] as $field) {
            // get current post meta data
            $meta = get_post_meta($post->ID, $field['id'], true);
            echo '<tr>';
            if ($field['type'] == 'checkbox') {
                $colspan = ' colspan="2"';
            } else if (empty($field['name'])) {
                $colspan = ' colspan="2"';
            } else {
                echo '<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>';
                $colspan = '';
            }
            echo '<td'. $colspan .'>';
            switch ($field['type']) {
                case 'text':
                    echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : @$field['std'], '" size="30" style="width:97%" />',
                        '<br />', @$field['desc'];
                    break;
                case 'date':
                    echo '<input type="date" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : @$field['std'], '" size="30" style="width:97%" />',
                        '<br />', @$field['desc'];
                    break;

                // word press editor on text area
                case 'textarea':
                    $content = $meta ? $meta : $field['std'];
                    $editor_id = $field['id'];
                    $settings = array( 'media_buttons' => true, 'tinymce' => true );
                    wp_editor( $content, $editor_id, $settings );
                    break;

                case 'select':
                    echo '<select name="', $field['id'], '" id="', $field['id'], '">';
                    foreach ($field['options'] as $option) {
                        echo '<option', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
                    }
                    echo '</select>';
                    break;

                case 'radio':
                    foreach ($field['options'] as $option) {
                        echo '<input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'];
                    }
                    break;

                case 'checkbox':
                    echo '<label for="'. $field['id'] .'"><input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' /> '. $field['name'] .'</label>';
                    break;
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    // Save data from meta box
    function save($post_id) {
        // verify nonce
        // if (!wp_verify_nonce($_POST['mytheme_meta_box_nonce'], basename(__FILE__))) { return $post_id; }
        // check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return $post_id; }
        // check permissions
        if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) { return $post_id; }
        } elseif (!current_user_can('edit_post', $post_id)) { return $post_id; }
        foreach ($this->_meta_box['fields'] as $field) {
            $old = get_post_meta($post_id, $field['id'], true);
            $new = $_POST[$field['id']] ?? false;
            if ($new && $new != $old) { update_post_meta($post_id, $field['id'], $new); } 
            elseif ('' == $new && $old) { delete_post_meta($post_id, $field['id'], $old); }
        }
    }
}