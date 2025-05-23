<?php
/**
 * Plugin Name: WordPress Rating System
 * Plugin URI: https://objectcure.com/
 * Description: A flexible and customizable rating system for WordPress posts, pages, and custom post types. Features include star ratings, user authentication options, and rating validation.
 * Version: 1.0.0
 * Requires at least: 5.6
 * Requires PHP: 7.2
 * Author: Jp
 * Author URI: https://objectcure.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wprating
 * Domain Path: /languages
 *
 * @package WPRating
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('WPRATING_VERSION', '1.0.0');
define('WPRATING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPRATING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPRATING_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once WPRATING_PLUGIN_DIR . 'includes/class-wprating-activator.php';
require_once WPRATING_PLUGIN_DIR . 'includes/class-wprating-deactivator.php';

/**
 * The code that runs during plugin activation.
 *
 * @since 1.0.0
 * @return void
 */
function wprating_activate() {
    WPRating_Activator::activate();
}
register_activation_hook(__FILE__, 'wprating_activate');

/**
 * The code that runs during plugin deactivation.
 *
 * @since 1.0.0
 * @return void
 */
function wprating_deactivate() {
    WPRating_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'wprating_deactivate');

/**
 * The code that runs during plugin uninstall.
 *
 * @since 1.0.0
 * @return void
 */
function wprating_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wprating';
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    
    // Remove plugin settings
    delete_option('wprating_settings');
}  
register_uninstall_hook(__FILE__, 'wprating_uninstall');

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 * @return void
 */
function wprating_init() {
    // Load text domain for translations
    load_plugin_textdomain('wprating', false, dirname(WPRATING_PLUGIN_BASENAME) . '/languages');

    // Include required files
    $required_files = array(
        'includes/class-wprating-config.php',
        'includes/class-wprating.php',
        'includes/class-wprating-loader.php',
        'includes/class-wprating-i18n.php',
        'includes/class-wprating-admin.php',
        'includes/class-wprating-public.php'
    );

    foreach ($required_files as $file) {
        $file_path = WPRATING_PLUGIN_DIR . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
        } else {
            // Log error if file is missing
            error_log(sprintf('WP Rating: Required file %s is missing.', $file));
        }
    }
}
add_action('plugins_loaded', 'wprating_init');

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 * @return void
 */
function run_wprating() {
    $plugin = new WPRating();
    $plugin->run();
}
run_wprating();
