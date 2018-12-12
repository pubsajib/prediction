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
require_once plugin_dir_path(__FILE__) . 'includes/helper.php';
require_once plugin_dir_path(__FILE__) . 'includes/helper-event.php';
// USER PROFILE FIELDS
require_once plugin_dir_path(__FILE__) . 'includes/class-profile.php';
// SHORTCODES
require_once plugin_dir_path(__FILE__) . 'shortcodes/prediction.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/predictors.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/answers.php';
/**
 * Add Codestar Framework.
 */
require_once plugin_dir_path(__FILE__) . 'includes/vendor/codestar/cs-framework.php';
define('CS_ACTIVE_SHORTCODE', false);
define('CS_ACTIVE_CUSTOMIZE', false);
/**
 * Check for updates.
 */
require_once plugin_dir_path(__FILE__) . 'includes/vendor/plugin-update-checker/plugin-update-checker.php';
$plugin_slug = Info::SLUG;
$update_url  = Info::UPDATE_URL;
$myUpdateChecker = \Puc_v4_Factory::buildUpdateChecker(
    $update_url . '?action=get_metadata&slug=' . $plugin_slug,
    __FILE__,
    $plugin_slug
);
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