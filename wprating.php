<?php
/*
Plugin Name: WordPress Rating System
Plugin URI: https://objectcure.com/
Description: Manage Rating for WordPres posts
Version: 1.0
Author: Jp <ideas.to.jp@gmail.com
Author URI: https://objectcure.com/
*/

/**
 * Function for plugin activation hook
 * @param : global @wpdb
 * @return : activation hook
 */
function wpRatingActivate() 
{
    global $wpdb;
    $charsetCollate = $wpdb->get_charset_collate(); // get/set charset collate on table
    $tablename = $wpdb->prefix."wp_rating";
  
    // table structure
    $sql = "CREATE TABLE IF NOT EXISTS $tablename (
    id mediumint(11) NOT NULL AUTO_INCREMENT,
    ip varchar(80),
    course_id mediumint(6) NOT NULL,
    rating mediumint(6) NOT NULL,
    date_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (id)
    ) $charsetCollate;";
  
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta( $sql );
}
register_activation_hook(__FILE__, 'wpRatingActivate');


  /**
 * Function for plugin de activation hook
 * @param : global @wpdb
 * @return : de activation hook
 */
function wpRatingDeactivate() 
{
   // no action required.
}
register_deactivation_hook(__FILE__, 'wpRatingDeactivate'); // Deactivation hook

 /**
 * Function for plugin uninstall hook
 * @param : global @wpdb
 * @return : de activation hook
 */
function wpRatingUninstall() 
{
    global $wpdb;
    $tableName = $wpdb->prefix."wp_rating";
    $wpdb->query("DROP TABLE IF EXISTS {$tableName}");
}  
register_uninstall_hook(__FILE__, 'wpRatingUninstall'); // Uninstall hook
