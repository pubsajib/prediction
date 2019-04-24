<?php
namespace PLUGIN_NAME;
use Like;
class Frontend {
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
        // ANSWERS
        add_action('wp_ajax_load_events_answers', [$this, 'load_events_answers']);
        add_action('wp_ajax_nopriv_load_events_answers', [$this, 'load_events_answers']);
        // TOURNAMENT
        add_action('wp_ajax_load_tournament', [$this, 'load_tournament']);
        add_action('wp_ajax_nopriv_load_tournament', [$this, 'load_tournament']);
        add_action('wp_ajax_getpredictionform', [$this, 'getpredictionform']);
        // LIKE
        add_action('wp_ajax_like_event_user', [$this, 'like_event_user']);
        add_action('wp_ajax_nopriv_like_event_user', [$this, 'like_event_user']);
    }
    public function assets() {
        // CSS
        wp_enqueue_style('timeto-css',plugin_dir_url(__FILE__).'css/timeTo.css', [], $this->version);
        wp_enqueue_style('owl-css',plugin_dir_url(__FILE__).'css/owl.carousel.min.css', [], $this->version);
        wp_enqueue_style('owltheme-css',plugin_dir_url(__FILE__).'css/owl.theme.default.min.css', [], $this->version);
        wp_enqueue_style('iziModal-css',plugin_dir_url(__FILE__).'css/iziModal.min.css', [], $this->version);
        wp_enqueue_style('fullpage-modal',plugin_dir_url(__FILE__).'css/jquery.plugin.full-modal.min.css', [], $this->version);
        wp_enqueue_style('fullpage-tab',plugin_dir_url(__FILE__).'css/component.css', [], $this->version);
        // wp_enqueue_style('calendar',plugin_dir_url(__FILE__).'css/res-timeline.css', [], $this->version);
        wp_enqueue_style($this->plugin_slug, plugin_dir_url(__FILE__).'css/plugin-name-frontend.css', [], $this->version);
        // JS
        wp_enqueue_script('progressbar',plugin_dir_url(__FILE__).'js/jQuery-plugin-progressbar.js', ['jquery'], $this->version, true);
        wp_enqueue_script('tab',plugin_dir_url(__FILE__).'js/jquery.tabslet.min.js', ['jquery'], $this->version, true);
        wp_enqueue_script('tabi',plugin_dir_url(__FILE__).'js/initializers.js', ['jquery'], $this->version, true);
        wp_enqueue_script('owl-js',plugin_dir_url(__FILE__).'js/owl.carousel.min.js', ['jquery'], $this->version, true);
        wp_enqueue_script('iziModal-js',plugin_dir_url(__FILE__).'js/iziModal.min.js', ['jquery'], $this->version, true);
        wp_enqueue_script('timeto-js',plugin_dir_url(__FILE__).'js/jquery.time-to.min.js', ['jquery'], $this->version, true);
        wp_enqueue_script('fullpage-js',plugin_dir_url(__FILE__).'js/jquery.plugin.full-modal.min.js', ['jquery'], $this->version, true);
        wp_enqueue_script('fullpagetab-js',plugin_dir_url(__FILE__).'js/cbpFWTabs.js', ['jquery'], $this->version, true);
        // wp_enqueue_script('calendar-js',plugin_dir_url(__FILE__).'js/calendar-jquery.min.js', [], $this->version);
        wp_enqueue_script('calendar',plugin_dir_url(__FILE__).'js/res-timeline.js', ['jquery'], $this->version, true);
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
        if (is_wp_error($user)) echo false;
        else echo true;
        wp_die();
    }
    // SAVE ANSWERS
    function save_answer() {
        check_ajax_referer('predictor_nonce', 'security');
        $event          = $_POST['eventID'];
        $user           = $_POST['userID'] ?? get_current_user_id();
        $teamID         = $_POST['teamID'];
        $qid            = $_POST['qid'];
        $qans           = $_POST['qans'];
        $isUpdateable   = $this->isAnswerUpdateable($user, $event, $teamID, $qid);
        // echo json_encode([$user, $event, $teamID, $qid, $qans, $isUpdateable]); wp_die();
        if ($isUpdateable) {
            // if (true) echo 1;
            if ($this->updateAnswer($event, $user, $qid, $qans)) echo 1;
            else echo 0;
        } else {
            echo 3;
        }
        wp_die();
    }
    function isAnswerUpdateable($user, $event, $teamID, $qid) {
        $isValid = 0;
        $meta = get_post_meta($event, 'event_ops', true);
        $endTime = '';
        $test = [];
        if (!empty($meta['teams'])) {
            foreach ($meta['teams'] as $team) {
                $theID = 'team_'. predictor_id_from_string($team['name']);
                if(($theID == $teamID) && $team['end']) {
                    if (isset($meta[$theID])) {
                        foreach ($meta[$theID] as $match) {
                            $currentQID = $theID .'_'.predictor_id_from_string($match['title']);
                            if ($currentQID == $qid) {
                                $qtype = $match['id'];
                                if ($qtype == 'toss') $endTime = strtotime($team['end']) - ($match['time'] * 60);
                                else $endTime = strtotime($team['end']);
                            }
                        }
                    }
                }
            }
        }
        // $endTime = $endTime - (460 * 60);
        if (time() < $endTime) $isValid = 1;
        return $isValid;
    }
    // LOAD ANSWERS
    function load_events_answers() {
        check_ajax_referer('predictor_nonce', 'security');
        $html  = $answers = '';
        $events  = $_POST['events'] ? explode('_', $_POST['events']) : [];
        if ($events) {
            foreach ($events as $event) {
                $answers .= self::eventAnswersHTML($event);
            }
        }
        if ($answers) {
            $html .= '<span class="eventsRefreshButton fusion-button button-default button-small" event="'. str_replace('_', ',', $_POST['events']) .'">Reload</span>';
            $html .= $answers;
        }
        echo $html;
        wp_die();
    }
    public static function eventAnswersHTML($eventID) {
        $answers        = '';
        if (get_post_type($eventID) == 'event') {
            $answers        = '';
            $event          = get_post($eventID);
            $meta           = get_post_meta($eventID, 'event_ops', true);
            $ans            = get_post_meta($eventID, 'event_ans', true);
            $answerGiven    = @$meta['answers'];
            if (isset($ans[0])) unset($ans[0]);
            // GIVEN PREDICTIONS
            if (!$meta['restricted']) $answers = getFavoriteTeamForThisEvent($meta, $ans, $eventID);
            else {
                if (is_user_logged_in() && getValidUserID(['viewer', 'predictor', 'administrator']) && $meta['restricted']) {
                    $answers = getFavoriteTeamForThisEvent($meta, $ans, $eventID);
                }
            }
        }
        return $answers;
    }
    
    function load_answers() {
        // check_ajax_referer('predictor_nonce', 'security');
        $data           = '';
        $ID             = (int) $_POST['ID'];
        $ditems         = (int) $_POST['ditems'];
        $html           = $_POST['html'];
        $avatarslider   = (int) $_POST['avatarslider'];
        if (get_post_type($ID) == 'event') {
            $answers        = '';
            $event          = get_post($ID);
            $meta           = get_post_meta($ID, 'event_ops', true);
            $ans            = get_post_meta($ID, 'event_ans', true);
            $answerGiven    = @$meta['answers'];
            if (isset($ans[0])) unset($ans[0]);
            if ($html == 'box') {
                // GIVEN PREDICTIONS FOR SINGLE PAGE
                if (!$meta['restricted']) $answers = answersHTML($meta, $ans, $ID, $ditems);
                else {
                    if (is_user_logged_in() && getValidUserID(['viewer', 'predictor', 'administrator']) && $meta['restricted']) {
                        $answers = answersHTML($meta, $ans, $ID, $ditems);
                    }
                }
                $data .= '<span class="refreshButton fusion-button button-default button-small" event="'. $ID .'">Reload</span>';
                if ($answers) $data .= $answers;
                else $data .= 'No one predicted this event yet. If you are an expert you may <a href="'. site_url('log-in') .'">Login</a> here.';
            } else {
                // GIVEN PREDICTIONS FOR OTHER PAGES
                if (!$meta['restricted']) $answers = getFavoriteTeamForThisEvent($meta, $ans, $ID, false, $avatarslider);
                else {
                    if (is_user_logged_in() && getValidUserID(['viewer', 'predictor', 'administrator']) && $meta['restricted']) {
                        $answers = getFavoriteTeamForThisEvent($meta, $ans, $ID, false, $avatarslider);
                    }
                }
                $data .= '<span class="refreshButton fusion-button button-default button-small" event="'. $ID .'">Reload</span>';
                if ($answers) $data .= $answers;
                else $data .= 'No one predicted this event yet. If you are an expert you may <a href="'. site_url('log-in') .'">Login</a> here.'; 
            }
        }
        echo $data;
        wp_die();
    }
    function updateAnswer($ID, $user, $qid, $qans) {
        $prevAns = [];
        $answers = (array) get_post_meta($ID, 'event_ans', true);
        if ($answers[$user]) $prevAns = $answers[$user];
        $answers[$user] = array_merge($prevAns,  [$qid => $qans]);
        if ($answers) return update_post_meta($ID, 'event_ans', $answers);
        else return add_post_meta($ID, 'event_ans', $answers);
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
    function getpredictionform() {
        $html = '';
        $data = '';
        $ID = $_POST['event'];
        $cTeam = $_POST['team'];
        $event = get_post($ID);
        $meta  = get_post_meta($ID, 'event_ops', true);
        $ans   = get_post_meta($ID, 'event_ans', true);
        if ($userID = getValidUserID(['predictor', 'administrator'])) {
            // PREDICTIN FORM
            if (@!$meta['published']) {
                if ($meta['teams']) {
                    foreach ($meta['teams'] as $team) {
                        $teamID = predictor_id_from_string($team['name']);
                        // if ($teamID != $cTeam) continue;
                        $options = 'team_'. $teamID;
                        if (@isValidOption($ans[$userID][$options], $team['end'])) {
                            $questions = '';
                            if ($meta[$options]) {
                                foreach ($meta[$options] as $option) {
                                    $question = $tossTime = '';
                                    $name = $options .'_'. predictor_id_from_string($option['title']);
                                    if (@ !$ans[$userID][$name]) {
                                        if ($option['id'] == 'toss') {
                                            $tossTime =  $option['time'] ? $option['time'] : 30;
                                            $tossTime =  date('Y-m-d H:i:s',strtotime("-". $tossTime ." minutes",strtotime($team['end'])));
                                            if (!isValidOption('', $tossTime)) continue;
                                        }
                                        $question .= '<div class="predictionContainer" id="'. $name .'">';
                                            if ($option['weight']) {
                                                $question .= '<h4 class="title">'. $option['title'] .'</h4>';
                                                foreach ($option['weight'] as $weight) {
                                                    if (!$weight['name']) continue;
                                                    $question .= '<label style="font-weight:normal; display:block;font-size:15px;margin:0;"><input style="margin-right:6px;;" type="radio" name="'. $name .'" value="'. $weight['name'] .'">'. $weight['name'] .'</label>';
                                                }
                                            }
                                            $question .= '<button type="button" class="btn btn-green saveModalQAns">Submit</button>';
                                        $question .= '</div>';
                                    }
                                    $questions .= $question;
                                }
                            }
                            if ($question) {
                                $data .= '<div class="teamQuestionContainer" id="'. $options .'">';
                                    $data .= '<div class="titleContainer">';
                                        $data .= '<div class="teamName half left"><strong>'. $team['name'] .'</strong></div>';
                                    $data .= '</div>';
                                    $data .= $questions;
                                $data .= '</div>';
                            }
                        }
                    } // teamQuestionContainer
                }
            }
        }
        if (trim($data)) {
            $html = '';
            $html .= '<div class="predictionWrapper" event='. $ID .'>';
                $html .= '<form action="" method="post">';
                    $html .= '<div class="teamQuestionWrapper">'. $data .'</div>';
                $html .= '</form>';
            $html .= '</div>'; // predictionWrapper end
        }
        echo $html;
        wp_die();
    }
    function like_event_user() {
        check_ajax_referer('predictor_nonce', 'security');
        global $wpdb;
        $event = $_POST['event'];
        $user = $_POST['user'];        if ($event && $user) {
            $table = $wpdb->prefix.'predictor_likes';
            if (!empty($_COOKIE['cdpue'.$event.'_'.$user])) {echo 202;}
            else {
                try {
                    $wpdb->insert($table, ['user'=>$user,'event'=>$event], ['%d', '%d']);
                    increasePredictorLikes($user);
                    setcookie('cdpue'.$event.'_'.$user, 1, time() + (86400 * 365), "/");
                    echo 200;
                } catch (Exception $e) {
                   echo 201; 
                } 
            }
        } else echo 203;
        wp_die();
    }
}