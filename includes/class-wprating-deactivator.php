<?php
/**
 * Fired during plugin deactivation.
 *
 * @since      1.0.0
 * @package    WPRating
 * @subpackage WPRating/includes
 */
class WPRating_Deactivator {

    /**
     * Clean up plugin data during deactivation.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear permalinks
        flush_rewrite_rules();
    }
} 