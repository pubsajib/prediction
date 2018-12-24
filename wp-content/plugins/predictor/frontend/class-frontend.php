<?php

namespace PLUGIN_NAME;

/**
 * The code used on the frontend.
 */
class Frontend
{
    private $plugin_slug;
    private $version;
    private $option_name;
    private $settings;

    public function __construct($plugin_slug, $version, $option_name) {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        $this->option_name = $option_name;
        $this->settings = get_option($this->option_name);

        add_action('wp_ajax_nopriv_user_login', [$this, 'ajax_login']);
        add_action('wp_ajax_save_answer', [$this, 'save_answer']);
        // ANSWERS
        add_action('wp_ajax_load_answers', [$this, 'load_answers']);
        add_action('wp_ajax_nopriv_load_answers', [$this, 'load_answers']);
        // TOURNAMENT
        add_action('wp_ajax_load_tournament', [$this, 'load_tournament']);
        add_action('wp_ajax_nopriv_load_tournament', [$this, 'load_tournament']);
    }

    public function assets() {
        // CSS
        wp_enqueue_style('timeto-css',plugin_dir_url(__FILE__).'css/timeTo.css', [], $this->version);
        wp_enqueue_style('owl-css',plugin_dir_url(__FILE__).'css/owl.carousel.min.css', [], $this->version);
        wp_enqueue_style('owltheme-css',plugin_dir_url(__FILE__).'css/owl.theme.default.min.css', [], $this->version);
        wp_enqueue_style('iziModal-css',plugin_dir_url(__FILE__).'css/iziModal.min.css', [], $this->version);
        wp_enqueue_style($this->plugin_slug, plugin_dir_url(__FILE__).'css/plugin-name-frontend.css', [], $this->version);
        // JS
        wp_enqueue_script('progressbar',plugin_dir_url(__FILE__).'js/jQuery-plugin-progressbar.js', ['jquery'], $this->version, true);
        wp_enqueue_script('owl-js',plugin_dir_url(__FILE__).'js/owl.carousel.min.js', ['jquery'], $this->version, true);
        wp_enqueue_script('iziModal-js',plugin_dir_url(__FILE__).'js/iziModal.min.js', ['jquery'], $this->version, true);
        wp_enqueue_script('timeto-js',plugin_dir_url(__FILE__).'js/jquery.time-to.min.js', ['jquery'], $this->version, true);
        wp_enqueue_script($this->plugin_slug, plugin_dir_url(__FILE__).'js/plugin-name-frontend.js', ['jquery'], $this->version, true);

        // AJAX
        wp_localize_script($this->plugin_slug, 'object', ['ajaxurl' => admin_url('admin-ajax.php'), 'home_url' => home_url(), 'ajax_nonce' => wp_create_nonce('predictor_nonce')]);
    }

    /**
     * Render the view using MVC pattern.
     */
    public function render() {

        // Model
        $settings = $this->settings;
        // View
        if (locate_template('partials/' . $this->plugin_slug . '.php')) {
            require_once(locate_template('partials/' . $this->plugin_slug . '.php'));
        } else {
            require_once plugin_dir_path(dirname(__FILE__)).'frontend/partials/view.php';
        }
    }
    // AJAX LOGIN
    function ajax_login() {
        check_ajax_referer('predictor_nonce', 'security');
        $creds = array();
        $creds['user_login'] = $_REQUEST['email'];
        $creds['user_password'] = $_REQUEST['pass'];
        $creds['remember'] = $_REQUEST['remember'];
        $user = wp_signon($creds, false);
        if (is_wp_error($user)) {
            echo false;
        } else {
            echo true;
        }
        wp_die();
    }
    // SAVE ANSWERS
    function save_answer() {
        check_ajax_referer('predictor_nonce', 'security');
        $event = $_POST['eventID'];
        $user = $_POST['userID'];
        $ans  = $_POST['answer'];
        if ($this->updateAnswer($event, $user, $ans)) echo 1;
        else echo 0;
        wp_die();
    }
    // LOAD ANSWERS
    function load_answers() {
        check_ajax_referer('predictor_nonce', 'security');
        $html  = '';
        $ID  = $_POST['ID'];
        $ditems  = $_POST['ditems'];
        if (get_post_type($ID) == 'event') {
            $answers        = '';
            $event          = get_post($ID);
            $meta           = get_post_meta($ID, 'event_ops', true);
            $ans            = get_post_meta($ID, 'event_ans', true);
            $answerGiven    = @$meta['answers'];
            if (isset($ans[0])) unset($ans[0]);
            // GIVEN PREDICTIONS
            if (!$meta['restricted']) $answers = answersHTML($meta, $ans, $ID, $ditems);
            else {
                if (is_user_logged_in() && getValidUserID(['viewer', 'predictor', 'administrator']) && $meta['restricted']) {
                    $answers = answersHTML($meta, $ans, $ID, $ditems);
                }
            }

            if ($answers) {
                $html .= '<span class="refreshButton fusion-button button-default button-small" event="'. $ID .'">Reload</span>';
                $html .= $answers;
            }

        }
        echo $html;
        wp_die();
    }
    function updateAnswer($ID, $user, $ans) {
        $answers = (array) get_post_meta($ID, 'event_ans', true);
        if ($answers[$user]) $prevAns = $answers[$user];
        else $prevAns = [];
        $answers[$user] = array_merge($prevAns,  $ans);
        if ($answers) {
            return update_post_meta($ID, 'event_ans', $answers);
        } else {
            return add_post_meta($ID, 'event_ans', $answers);
        }
    }
    function load_tournament() {
        check_ajax_referer('predictor_nonce', 'security');
        $html  = '';
        $tournamentID  = $_POST['tournamentID'];
        $userID  = $_POST['userID'];
        $userID  = $_POST['userID'];
        $summery = tournamentData($userID, $tournamentID);
        if ($summery) {
            $html .= '<div class="prediction-summery">';
                $html .= '<div class="win-accuracy">';
                    //$html .= '<h3 class="title">Accuracy By Win / Loss </h3>';
                    $html .= '<ul class="prediction-full-result">';
                        $html .= '<li>';
                            $html .= '<strong>Total Rate</strong><br>';
                            $html .= '<div class="progress-bar" value="'. $summery['win_rate'] .'" data-percent="'. number_format((float)$summery['win_rate'], 2, '.', '').'" max="100"></div>';
                        $html .= '</li>';
                        $html .= '<li>';
                            $html .= '<strong>Participated</strong><br>';
                            $html .= '<div class="common">'. $summery['participated'] .'</div>';
                        $html .= '</li>';
                        $html .= '<li>';
                            $html .= '<strong>Match Win</strong><br>';
                            $html .= '<div class="common">'. $summery['correct'] .'</div>';
                        $html .= '</li>';
                        $html .= '<li>';
                            $html .= '<strong>Match lose</strong><br>';
                            $html .= '<div class="common red">'. $summery['incorrect'] .'</div>';
                        $html .= '</li>';
                    $html .= '</ul>';
            $html .= '</div>';
        }
        // echo json_encode($summery);
        echo $html;
        wp_die();
    }
}
