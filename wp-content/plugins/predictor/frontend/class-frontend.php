<?php
namespace PLUGIN_NAME;
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
    }
    public function assets() {
        // CSS
        wp_enqueue_style('timeto-css',plugin_dir_url(__FILE__).'css/timeTo.css', [], $this->version);
        wp_enqueue_style('owl-css',plugin_dir_url(__FILE__).'css/owl.carousel.min.css', [], $this->version);
        wp_enqueue_style('owltheme-css',plugin_dir_url(__FILE__).'css/owl.theme.default.min.css', [], $this->version);
        wp_enqueue_style('iziModal-css',plugin_dir_url(__FILE__).'css/iziModal.min.css', [], $this->version);
        wp_enqueue_style('fullpage-modal',plugin_dir_url(__FILE__).'css/jquery.plugin.full-modal.min.css', [], $this->version);
        wp_enqueue_style('fullpage-tab',plugin_dir_url(__FILE__).'css/component.css', [], $this->version);
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
        $event = $_POST['eventID'];
        $user = $_POST['userID'];
        $qid  = $_POST['qid'];
        $qans  = $_POST['qans'];
        $teamID  = $_POST['teamID'];
        $isUpdateable = $this->isAnswerUpdateable($user, $event, $teamID, $qid);
        // $_POST['updateable'] = $isUpdateable; echo json_encode($_POST); wp_die();
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
        // $endTime = $endTime - (46 * 60);
        if (time() < $endTime) $isValid = 1;
        return $isValid;
    }
    // LOAD ANSWERS
    function load_events_answers() {
        check_ajax_referer('predictor_nonce', 'security');
        $html  = '';
        $events  = $_POST['events'] ? explode('_', $_POST['events']) : [];
        // $events  = [$events[2]];
        $ditems  = $_POST['ditems'];
        $contributedUsers = [];
        if ($events) {
            foreach ($events as $event) {
                $ans = get_post_meta($event, 'event_ans', true);
                if (isset($ans[0])) unset($ans[0]);
                // array_push($contributedUsers, [$event,12]);
                if ($usersAnsweredPerEvent = array_keys($ans)) {
                    $contributedUsers = array_merge($contributedUsers, $usersAnsweredPerEvent);
                }
            }
            $contributedUsers = $contributedUsers ? array_values(array_unique($contributedUsers)) : [];
        }
        // wp_die(count($contributedUsers).' === '.json_encode($contributedUsers));
        // GIVEN ANSWERS
        $userBasedAns = [];
        if ($events) {
            foreach ($events as $event) {
                $userBasedAns = self::eventAnswersHTML($event, $userBasedAns);
            }
        }
        // echo '<br> ==========================================<br>'; echo count($userBasedAns).' === '.json_encode($userBasedAns); wp_die();
        // SLIDER HEADER & FOOTER
        if ($contributedUsers) {
            $header = $footer = [];
            $ranking = getRakingFor();
            foreach ($contributedUsers as $uID) {
                $ratingIcon = '';
                $rank = userRankingStatusFor($uID, $ranking);
                if (!empty($rank['num'])) $ratingIcon = '<p>'. $rank['num'] .'</p>';
                $country = get_the_author_meta( 'country', $uID );
                $highlight = get_the_author_meta( 'highlight', $uID ) ? ' highlighted' : '';
                $user = get_userdata($uID);
                if ($user) {
                    $header[$uID] = '';
                    $header[$uID] .= '<div class="dashboard-user text-center">';
                        $header[$uID] .= '<div class="user-avater">'.get_avatar( $user->user_email , '90') .''. $ratingIcon .'</div>';
                        $header[$uID] .= '<div class="user-information">';
                            $header[$uID] .= '<h4>';
                                $header[$uID] .= '<a href="'. site_url('predictor/?p='. $user->user_login) .'"  target="_blank">'. get_the_author_meta('nickname',$uID) .'</a>';
                                if ($country) $header[$uID] .= '<img class="countryFlag" src="'. PREDICTOR_URL .'frontend/img/'. $country .'.png" alt="country">';
                            $header[$uID] .= '</h4><br>';
                                $header[$uID] .= get_user_meta($user->ID, 'description', true);
                        $header[$uID] .= '</div>';
                    $header[$uID] .= '</div>';
                    $footer[$uID] = '';
                    $footer[$uID] .= '<a class="userNavItem'. $rank['class'] .'" href="#'.$uID.'">'.get_avatar( $user->user_email , '40 ') . '</a>';;
                }
            }
        }
        // echo '<br> ==========================================<br>'; echo count($header).' === '.json_encode($header); wp_die();
        $html = $userNav = '';
        $owlSelector = 'owlCarousel_'. $_POST['events'];
        $html .= '<div class="owl-carousel '. $owlSelector .' owl-theme">';
        if ($contributedUsers) {
            foreach ($contributedUsers as $uID) {
                $html .= '<div id="predictor_'. $uID .'" class="answerContainer item'. $highlight . $rank['class'] .'" data-hash="'.$uID.'">';
                    $html .= $header[$uID];
                    $html .= $userBasedAns[$uID];
                    $userNav .= $footer[$uID];
                $html .= '</div>';
            }
        }
        $html .= '</div>';
        $html .= '<ul class="menuSlider">'. $userNav .'</ul>';
        if ($html) {
            $html .= '<script> jQuery(".'. $owlSelector .'").owlCarousel({loop:true, margin: 10, nav: true, autoplay:true, autoplayTimeout:15000, URLhashListener:true, autoplayHoverPause:true, startPosition: "URLHash", responsive: {0: {items: 1 }, 600: {items: 1 }, 1000: {items: '. $ditems .' } } }) </script>';
            $html .= '<span class="eventsRefreshButton fusion-button button-default button-small" event="'. str_replace('_', ',', $_POST['events']) .'" ditems='. $ditems .'>Reload</span>';
        }
        echo $html;
        wp_die();
    }
    public static function eventAnswersHTML($eventID, $userBasedAns) {
        $event          = get_post($eventID);
        $meta           = get_post_meta($eventID, 'event_ops', true);
        $ans            = get_post_meta($eventID, 'event_ans', true);
        $answerGiven    = @$meta['answers'];
        if (isset($ans[0])) unset($ans[0]);
        $html = '';
        foreach ($ans as $uID => $answer) {
            if ($answer) {
                if (!array_key_exists($uID, $userBasedAns)) $userBasedAns[$uID] = ' Data : ';
                $ratingIcon = '';
                $rank = userRankingStatusFor($uID, $ranking);
                if (!empty($rank['num'])) $ratingIcon = '<p>'. $rank['num'] .'</p>';
                $country    = get_the_author_meta( 'country', $uID );
                $highlight  = get_the_author_meta( 'highlight', $uID ) ? ' highlighted' : '';
                $user       = get_userdata($uID);  
                // ANSWERS GIVEN
                if (!empty($meta['teams'])) {
                    $html .= '<div class="teamAnsWrapper">';
                        foreach ($meta['teams'] as $team) {
                            $givenAnswers = '';
                            $teamID = predictor_id_from_string($team['name']);
                            $options = 'team_'. $teamID;
                            // GIVEN ANSWERS
                            if ($meta[$options]) {
                                foreach ($meta[$options] as $option) {
                                    $ansID = $options.'_'.predictor_id_from_string($option['title']);
                                    if (empty($answer[$ansID])) continue;
                                    $defaultID = 'default_'. $teamID .'_'. predictor_id_from_string($option['title']);
                                    $defaultAns = $meta[$defaultID] ?? '';
                                    $published = $meta[$defaultID.'_published'];
                                    $isCorrect = '';
                                    if ($published) {
                                        if ($defaultAns == 'abandon') {
                                            $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/unhappy.png">';
                                        } else if ($ans[$uID][$ansID]== $defaultAns) $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/happy.png">';
                                        else $isCorrect = '<img src="'. PREDICTOR_URL .'frontend/img/sad.png">';
                                    }
                                    // $html .= '<br>published: '.$published.' == givenAns: '.$ans[$uID][$ansID] .' == DefaultAns: '. $defaultAns;
                                    $ansWeight = getWeightFromValue($option['weight'], $answer[$ansID]);
                                    $givenAnswers .= '<div class="answer">'; 
                                        $givenAnswers .= @$option['title'];
                                        if ($defaultAns == 'abandon') $givenAnswers .= ' <span class="text-danger noResult"></span>';
                                        $givenAnswers .= '<br><strong>'; 
                                            $givenAnswers .= '<span class="ansTxt">'. @$answer[$ansID] .'</span>'; 
                                            if ($ansWeight) {
                                                $givenAnswers .= ' @ <span class="ansWeight">'. $ansWeight .'</span>'; 
                                            }
                                        $givenAnswers .= '</strong>&nbsp;'; 
                                        $givenAnswers .= '<span>'. $isCorrect .'</span>'; 
                                    $givenAnswers .= '</div>'; 
                                }
                            }
                            if ($givenAnswers) {
                                $html .= '<div class="teamAnsContainer">';
                                $html .= '<h3 class="teamName">'. $team['name'] .'</h3>';
                                $html .= $givenAnswers;
                                $html .= '</div>';
                            }
                        }
                    $html .= '</div>';
                }
            }
            $userBasedAns[$uID] = $html;
        }
        return $userBasedAns;
    }
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
}