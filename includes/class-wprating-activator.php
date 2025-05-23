<?php
/**
 * Fired during plugin activation.
 *
 * @since      1.0.0
 * @package    WPRating
 * @subpackage WPRating/includes
 */
class WPRating_Activator {

    /**
     * Create necessary database tables and initialize plugin settings.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'wprating';
      
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
} 