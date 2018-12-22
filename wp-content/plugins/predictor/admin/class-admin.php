<?php

namespace PLUGIN_NAME;

/**
 * The code used in the admin.
 */
class Admin
{
    private $plugin_slug;
    private $version;
    private $option_name;
    private $settings;
    private $settings_group;

    public function __construct($plugin_slug, $version, $option_name) {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        $this->option_name = $option_name;
        $this->settings = get_option($this->option_name);
        $this->settings_group = $this->option_name.'_group';

        add_action('wp_ajax_delete_answers', [$this, 'delete_answers']);
        add_action('admin_init', [$this, 'ui_new_role']);
        add_action('init', [$this, 'createPostType']);
        add_action( 'init', [$this, 'pcreate_event_term'] );
    }
    function ui_new_role() {  
        add_role('predictor', 'Predictor', ['read'=> true, 'delete_posts' => false]);
        add_role('viewer', 'Viewer', ['read'=> true, 'delete_posts' => false]);
    }
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
    function pcreate_event_term() {
        register_taxonomy(
            'tournament',
            'event',
            [
                'label' => __( 'Tournament' ),
                'rewrite' => array( 'slug' => 'tournament' ),
                'hierarchical' => true
            ]
        );
    }
    private function custom_settings_fields($field_args, $settings) {
        $output = '';

        foreach ($field_args as $field) {
            $slug = $field['slug'];
            $setting = $this->option_name.'['.$slug.']';
            $label = esc_attr__($field['label'], 'plugin-name');
            $output .= '<h3><label for="'.$setting.'">'.$label.'</label></h3>';

            if ($field['type'] === 'text') {
                $output .= '<p><input type="text" id="'.$setting.'" name="'.$setting.'" value="'.$settings[$slug].'"></p>';
            } elseif ($field['type'] === 'textarea') {
                $output .= '<p><textarea id="'.$setting.'" name="'.$setting.'" rows="10">'.$settings[$slug].'</textarea></p>';
            }
        }

        return $output;
    }

    public function assets() {
        wp_enqueue_style($this->plugin_slug, plugin_dir_url(__FILE__).'css/plugin-name-admin.css', [], $this->version);
        wp_enqueue_script($this->plugin_slug, plugin_dir_url(__FILE__).'js/plugin-name-admin.js', ['jquery'], $this->version, true);
        wp_localize_script($this->plugin_slug, 'object', ['ajaxurl' => admin_url('admin-ajax.php'), 'home_url' => home_url(), 'ajax_nonce' => wp_create_nonce('predictor_nonce')]);
    }

    public function register_settings() {
        register_setting($this->settings_group, $this->option_name);
    }

    public function add_menus() {
        // $plugin_name = Info::get_plugin_title();
        // add_submenu_page('options-general.php', $plugin_name, $plugin_name, 'manage_options', $this->plugin_slug, [$this, 'render'] );
    }

    /**
     * Render the view using MVC pattern.
     */
    public function render() {

        // Generate the settings fields
        $field_args = [
            ['label' => 'Text Label', 'slug'  => 'text-slug', 'type'  => 'text'], 
            ['label' => 'Textarea Label', 'slug'  => 'textarea-slug', 'type'  => 'textarea']
        ];

        // Model
        $settings = $this->settings;

        // Controller
        $fields = $this->custom_settings_fields($field_args, $settings);
        $settings_group = $this->settings_group;
        $heading = Info::get_plugin_title();
        $submit_text = esc_attr__('Submit', 'plugin-name');

        // View
        require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/view.php';
    }
    function delete_answers() {
        check_ajax_referer('predictor_nonce', 'security');
        $event  = $_POST['event'];
        $user  = $_POST['user'];
        if (get_post_type($event) == 'event') {
            // DELETE PREDICTIONS
            $answers = get_post_meta($event, 'event_ans', true);
            unset($answers[$user]);
            $deleted = update_post_meta($event, 'event_ans', $answers);
        }
        echo $deleted;
        wp_die();
    }
}
