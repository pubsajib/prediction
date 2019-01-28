<?php
/**
 * Plugin Name:       Predictor
 * Plugin URI:        http://livedemolink.com
 * Description:       Predict the teams result.
 * Version:           1.0
 * Author:            Phase3 solutions
 * Author URI:        pubsajib@gmail.com
 * Text Domain:       predictor
 */
namespace PLUGIN_NAME;
// If this file is called directly, abort.
if (!defined('WPINC')) { die; }
// CONSTANTS
define('PREDICTOR_URL', plugin_dir_url( __FILE__ ));

// The class that contains the plugin info.
require_once plugin_dir_path(__FILE__) . 'includes/class-info.php';
/**
 * The code that runs during plugin activation.
 */
function activation() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-activator.php';
    Activator::activate();
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\activation');
// HELPER FUNCTIONS
require_once plugin_dir_path(__FILE__) . 'helpers/helper.php';
require_once plugin_dir_path(__FILE__) . 'helpers/event.php';
require_once plugin_dir_path(__FILE__) . 'helpers/answer.php';
require_once plugin_dir_path(__FILE__) . 'helpers/profile.php';
require_once plugin_dir_path(__FILE__) . 'helpers/tournament.php';
require_once plugin_dir_path(__FILE__) . 'helpers/win_lose.php';
require_once plugin_dir_path(__FILE__) . 'helpers/ranking.php';
require_once plugin_dir_path(__FILE__) . 'helpers/blog.php';
require_once plugin_dir_path(__FILE__) . 'helpers/ranking-tournament.php';
require_once plugin_dir_path(__FILE__) . 'helpers/road-to-top.php';
require_once plugin_dir_path(__FILE__) . 'helpers/latest-events.php';
require_once plugin_dir_path(__FILE__) . 'helpers/favourite-team.php';
// USER PROFILE FIELDS
require_once plugin_dir_path(__FILE__) . 'includes/class-profile.php';
// SHORTCODES
require_once plugin_dir_path(__FILE__) . 'shortcodes/prediction.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/prediction-events.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/predictors.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/answers.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/top.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/top-tournament.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/header-notification.php';

/**
 * Add Codestar Framework.
 */
require_once plugin_dir_path(__FILE__) . 'includes/codestar/cs-framework.php';
define('CS_ACTIVE_SHORTCODE', false);
define('CS_ACTIVE_CUSTOMIZE', false);

/**
 * Run the plugin.
 */
function run() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-plugin.php';
    $plugin = new Plugin();
    $plugin->run();
}
run();
// function custom_rewrite_basic() {
//   add_rewrite_rule('^leaf/([0-9]+)/?', 'index.php?page_id=$matches[1]', 'top');
// }
// add_action('init', 'custom_rewrite_basic');