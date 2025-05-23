<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WPRating
 * @subpackage WPRating/admin
 */
class WPRating_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . '../../assets/css/wprating-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . '../../assets/js/wprating-admin.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script(
            $this->plugin_name,
            'wprating_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wprating_admin_nonce')
            )
        );
    }

    /**
     * Add plugin action links.
     *
     * @since    1.0.0
     * @param    array    $links    Plugin action links.
     * @return   array              Modified plugin action links.
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=wprating-settings') . '">' . __('Settings', 'wprating') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Add plugin row meta.
     *
     * @since    1.0.0
     * @param    array     $links    Plugin row meta.
     * @param    string    $file     Plugin base file.
     * @return   array               Modified plugin row meta.
     */
    public function add_plugin_row_meta($links, $file) {
        if (plugin_basename(WPRATING_PLUGIN_DIR . 'wprating.php') === $file) {
            $row_meta = array(
                'docs' => '<a href="' . esc_url('https://objectcure.com/docs/wprating/') . '" aria-label="' . esc_attr__('View WP Rating documentation', 'wprating') . '">' . esc_html__('Documentation', 'wprating') . '</a>',
                'support' => '<a href="' . esc_url('https://objectcure.com/support/') . '" aria-label="' . esc_attr__('Visit support forum', 'wprating') . '">' . esc_html__('Support', 'wprating') . '</a>'
            );
            return array_merge($links, $row_meta);
        }
        return $links;
    }
} 