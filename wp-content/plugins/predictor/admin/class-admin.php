<?php
namespace PLUGIN_NAME;
use PredictionCron;
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
        add_action('admin_init', [$this, 'ui_new_role']);
        add_action('init', [$this, 'createPostType']);
        add_action( 'init', [$this, 'pcreate_event_term'] );
        add_filter('manage_event_posts_columns', [$this,'addCustomColumnHead']);
        add_action( 'manage_event_posts_custom_column', [$this,'addCustomColumnBody'], 10, 2 );
        // add_filter('request', [$this, 'customColumnSort']);
        add_action('pre_get_posts', [$this, 'customColumnSort']);
        add_filter('manage_event_sortable_columns', [$this,'my_sortable_cake_column']);
        add_action('wp_ajax_delete_answers', [$this, 'delete_answers']);
        add_action('wp_ajax_cron_options', [$this, 'cron_options']);
        add_action('wp_ajax_run_cron', [$this, 'run_cron']);
    }
    function my_sortable_cake_column($columns ) {
        $columns['featured'] = 'Featured';
        //To make a column 'un-sortable' remove it from the array
        //unset($columns['date']);
        return $columns;
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
            'supports' => array('title','editor'),
        ));
    }
    function addCustomColumnHead($defaults) {
        $date = $defaults['date'];
        unset($defaults['date']);
        $defaults['category']   = 'category';
        $defaults['featured']   = 'Featured';
        $defaults['status']     = 'Published';
        $defaults['author']     = 'Added By';
        $defaults['date']       = $date;
        return $defaults;
    }
    function addCustomColumnBody( $column_name, $post_id ) {
        if ($column_name == 'category') {
            echo  get_the_term_list(get_the_ID(), 'tournament', '', ', ', '');
        }
        if ($column_name == 'featured') {
            if (get_post_meta( $post_id, 'pre-featured', true )) {
                echo '<span style="color:green;font-weight:bold;">Yes</span>';
            } else {
                echo '<span class="na" style="color:grey;"><em>No</em></span>';
            }
        }
        if ($column_name == 'status') {
            if (get_post_meta( $post_id, 'pre-published', true )) {
                echo '<span style="color:green;font-weight:bold;">Yes</span>';
            } else {
                echo '<span class="na" style="color:grey;"><em>No</em></span>';
            }
        }
    }
    function customColumnSort( $query ) {
        if( ! is_admin() )
        return;
        $orderby = $query->get( 'orderby');
        if( 'slice' == $orderby ) {
            $query->set('meta_key','slices');
            $query->set('orderby','meta_value_num');
        }
        // if( array_key_exists('orderby', $vars )) {
        //     // if('author' == $vars['orderby']) {
        //         $vars['orderby'] = 'featured';
        //         $vars['meta_key'] = 'pre-featured';
        //     // }
        // }
        // return $vars;
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
    // private function custom_settings_fields($field_args, $settings) {
    //     $output = '';
    //     foreach ($field_args as $field) {
    //         $slug = $field['slug'];
    //         $setting = $this->option_name.'['.$slug.']';
    //         $label = esc_attr__($field['label'], 'plugin-name');
    //         $output .= '<h3><label for="'.$setting.'">'.$label.'</label></h3>';
    //         if ($field['type'] === 'text') {
    //             $output .= '<p><input type="text" id="'.$setting.'" name="'.$setting.'" value="'.$settings[$slug].'"></p>';
    //         } elseif ($field['type'] === 'textarea') {
    //             $output .= '<p><textarea id="'.$setting.'" name="'.$setting.'" rows="10">'.$settings[$slug].'</textarea></p>';
    //         }
    //     }
    //     return $output;
    // }
    public function assets() {
        wp_enqueue_style($this->plugin_slug, plugin_dir_url(__FILE__).'css/plugin-name-admin.css', [], $this->version);
        wp_enqueue_script($this->plugin_slug, plugin_dir_url(__FILE__).'js/plugin-name-admin.js', ['jquery'], $this->version, true);
        wp_localize_script($this->plugin_slug, 'object', ['ajaxurl' => admin_url('admin-ajax.php'), 'home_url' => home_url(), 'ajax_nonce' => wp_create_nonce('predictor_nonce')]);
    }
    public function register_settings() {
        register_setting($this->settings_group, $this->option_name);
    }
    public function add_menus() {
        $plugin_name = Info::get_plugin_title();
        // add_options_page( 'My Plugin Options', 'My Plugin', 'manage_options', $this->plugin_slug, $this->settings);
        add_menu_page('Predictor', 'Predictor', 'manage_options', $this->plugin_slug, [$this, 'render']);
        add_submenu_page('options-general.php', $plugin_name, $plugin_name, 'manage_options', $this->plugin_slug, [$this, 'render'] );
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
        // $fields = $this->custom_settings_fields($field_args, $settings);
        $settings_group = $this->settings_group;
        $heading = Info::get_plugin_title();
        $submit_text = esc_attr__('Submit', 'plugin-name');
        // View
        require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/view.php';
    }
    function wpc_custom_table_head( $defaults ) {
        $defaults['event_date']  = 'Event Date';
        $defaults['ticket_status']    = 'Ticket Status';
        $defaults['venue']   = 'Venue';
        $defaults['author'] = 'Added By';
        return $defaults;
    }
    // change the _event_ part in the filter name to your CPT slug
    // now let's fill our new columns with post meta content
    function wpc_custom_table_content( $column_name, $post_id ) {
        if ($column_name == 'event_date') { // refering name to our table header $defaults
            $event_date = get_post_meta( $post_id, 'meta_event_date', true );
                echo  date( _x( 'F d, Y', 'Event date format', 'textdomain' ), strtotime( $event_date ) );
        }
        if ($column_name == 'ticket_status') {
            $status = get_post_meta( $post_id, 'meta_event_ticket_status', true ); // grab the name of your meta box field name
            echo $status;
        }
        if ($column_name == 'venue') {
            echo get_post_meta( $post_id, 'meta_event_venue', true );
        }
    }
    function delete_answers() {
        check_ajax_referer('predictor_nonce', 'security');
        $event  = $_POST['event'];
        $answerID  = $_POST['answerid'];
        $user  = $_POST['user'];
        if (get_post_type($event) == 'event') {
            // DELETE PREDICTIONS
            $answers = get_post_meta($event, 'event_ans', true);
            if (isset($answers[$user][$answerID])) unset($answers[$user][$answerID]);
            else wp_die('answer is not exists for index : '. $answers[$user][$answerID]);
            $deleted = update_post_meta($event, 'event_ans', $answers);
        } else wp_die('Not an event type post');
        echo "deleted : $deleted";
        wp_die();
    }
    function cron_options() {
        check_ajax_referer('predictor_nonce', 'security');
        $tournaments = !empty($_POST['tournaments']) ? $_POST['tournaments'] : [];
        $optionName = 'predictor_cron_options';
        if (PredictionCron::createRatingSummeryTable($tournaments)) {
            if ( get_option( $optionName ) !== false ) {
                echo update_option( $optionName, $tournaments );
            } else {
                $deprecated = null;
                $autoload = 'no';
                echo add_option( $optionName, $tournaments, $deprecated, $autoload );
            }
        } else echo 0;
        wp_die();
    }
    function run_cron() {
        check_ajax_referer('predictor_nonce', 'security');
        $type = !empty($_POST['type']) ? $_POST['type'] : false;
        if ($type != 'summery' && PredictionCron::rankingCronFor($type)) {
            echo date('M d, Y', time());
        } else if ($type == 'summery') {
            echo PredictionCron::insertIntoRankinSummeryTable();
        } else echo 0;
        wp_die();
    }
}