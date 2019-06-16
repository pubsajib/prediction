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
require_once plugin_dir_path(__FILE__) . 'helpers/follower.php';
require_once plugin_dir_path(__FILE__) . 'helpers/notification.php';
// USER PROFILE FIELDS
require_once plugin_dir_path(__FILE__) . 'includes/class-profile.php';
require_once plugin_dir_path(__FILE__) . 'includes/meta_box_multiple_post.php';
// SHORTCODES
// require_once plugin_dir_path(__FILE__) . 'shortcodes/header-notification.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/reputation.php';
// require_once plugin_dir_path(__FILE__) . 'shortcodes/range.php';
// require_once plugin_dir_path(__FILE__) . 'shortcodes/top.php';

// ENHANCEMENT
require_once plugin_dir_path(__FILE__) . 'enhancement/header-notification.php';
require_once plugin_dir_path(__FILE__) . 'enhancement/load_answers.php';
require_once plugin_dir_path(__FILE__) . 'enhancement/road_to_top.php';
require_once plugin_dir_path(__FILE__) . 'enhancement/range.php';
require_once plugin_dir_path(__FILE__) . 'enhancement/calendar.php';
require_once plugin_dir_path(__FILE__) . 'enhancement/cron.php';
require_once plugin_dir_path(__FILE__) . 'enhancement/rank.php';
require_once plugin_dir_path(__FILE__) . 'enhancement/top.php';

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