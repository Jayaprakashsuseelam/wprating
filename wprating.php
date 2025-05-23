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

/**
 * The code that runs during plugin activation.
 *
 * @since 1.0.0
 * @return void
 */
function wprating_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'wp_rating';
  
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(11) NOT NULL AUTO_INCREMENT,
        ip varchar(80) DEFAULT NULL,
        user_id bigint(20) DEFAULT NULL,
        email varchar(100) DEFAULT NULL,
        post_id bigint(20) NOT NULL,
        rating mediumint(6) NOT NULL,
        date_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY post_id (post_id),
        KEY user_id (user_id),
        KEY ip (ip)
    ) $charset_collate;";
  
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Initialize default settings
    $default_settings = array(
        'number_of_stars' => 5,
        'require_login' => false,
        'user_identifier' => 'ip',
        'enable_validation' => true,
        'allow_multiple_ratings' => false,
        'rating_timeout' => 24,
        'post_types' => array('post', 'page'),
        'show_in_loop' => true,
        'show_in_single' => true,
        'position' => 'after_content'
    );
    add_option('wprating_settings', $default_settings);

    // Clear permalinks
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wprating_activate');

/**
 * The code that runs during plugin deactivation.
 *
 * @since 1.0.0
 * @return void
 */
function wprating_deactivate() {
    // Clear permalinks
    flush_rewrite_rules();
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
    $table_name = $wpdb->prefix . 'wp_rating';
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
    require_once WPRATING_PLUGIN_DIR . 'includes/class-wprating-config.php';
    require_once WPRATING_PLUGIN_DIR . 'includes/class-wprating.php';
    require_once WPRATING_PLUGIN_DIR . 'includes/class-wprating-activator.php';
    require_once WPRATING_PLUGIN_DIR . 'includes/class-wprating-deactivator.php';
    require_once WPRATING_PLUGIN_DIR . 'includes/class-wprating-loader.php';
    require_once WPRATING_PLUGIN_DIR . 'includes/class-wprating-i18n.php';
    require_once WPRATING_PLUGIN_DIR . 'includes/class-wprating-admin.php';
    require_once WPRATING_PLUGIN_DIR . 'includes/class-wprating-public.php';
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
