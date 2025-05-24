<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WPRating
 * @subpackage WPRating/public
 */
class WPRating_Public {

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

        // Register shortcode
        add_shortcode('wprating', array($this, 'render_shortcode'));
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            WPRATING_PLUGIN_URL . 'assets/css/wprating-public.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            WPRATING_PLUGIN_URL . 'assets/js/wprating-public.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script(
            $this->plugin_name,
            'wprating_public',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wprating_public_nonce'),
                'i18n' => array(
                    'rating_success' => __('Thank you for your rating!', 'wprating'),
                    'rating_error' => __('Error submitting rating. Please try again.', 'wprating'),
                    'login_required' => __('Please log in to submit a rating.', 'wprating'),
                    'average_rating' => __('Average Rating: %s / %d', 'wprating'),
                    'your_rating'    => __('Your Rating: %s / %d', 'wprating'),
                )
            )
        );
    }

    /**
     * Display the rating interface.
     *
     * @since    1.0.0
     * @param    string    $content    The post content.
     * @return   string                Modified post content.
     */
    public function display_rating($content) {
        if (!is_singular()) {
            return $content;
        }

        $post_id = get_the_ID();
        $settings = get_option('wprating_settings');
        
        // Check if rating should be displayed for this post type
        $post_types = isset($settings['post_types']) && is_array($settings['post_types']) ? $settings['post_types'] : array('post', 'page');
        if (!in_array(get_post_type(), $post_types)) {
            return $content;
        }

        // Check if user is logged in when required
        if ($settings['require_login'] && !is_user_logged_in()) {
            return $content . $this->get_login_message();
        }

        // Get current user's rating
        $user_rating = $this->get_user_rating($post_id);
        
        // Get average rating
        $average_rating = $this->get_average_rating($post_id);

        ob_start();
        include WPRATING_PLUGIN_DIR . 'public/partials/wprating-display.php';
        $rating_html = ob_get_clean();

        if ($settings['position'] === 'before_content') {
            return $rating_html . $content;
        }

        return $content . $rating_html;
    }

    /**
     * Handle rating submission via AJAX.
     *
     * @since    1.0.0
     */
    public function handle_rating_submission() {
        check_ajax_referer('wprating_public_nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $rating = isset($_POST['rating']) ? absint($_POST['rating']) : 0;
        $settings = get_option('wprating_settings');

        if (!$post_id || !$rating || $rating > $settings['number_of_stars']) {
            wp_send_json_error(__('Invalid rating data.', 'wprating'));
        }

        // Check if user is logged in when required
        if ($settings['require_login'] && !is_user_logged_in()) {
            wp_send_json_error(__('Please log in to submit a rating.', 'wprating'));
        }

        // Check if user has already rated
        if (!$settings['allow_multiple_ratings'] && $this->has_user_rated($post_id)) {
            wp_send_json_error(__('You have already rated this post.', 'wprating'));
        }

        // Save the rating
        $result = $this->save_rating($post_id, $rating);

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Thank you for your rating!', 'wprating'),
                'average' => $this->get_average_rating($post_id)
            ));
        } else {
            wp_send_json_error(__('Error saving rating. Please try again.', 'wprating'));
        }
    }

    /**
     * Get user's rating for a post.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @return   int|false            The user's rating or false if not rated.
     */
    private function get_user_rating($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wprating';
        $settings = get_option('wprating_settings');
        
        $where = array('post_id' => $post_id);
        
        if (is_user_logged_in()) {
            $where['user_id'] = get_current_user_id();
        } else {
            $where['ip'] = $this->get_client_ip();
        }

        $rating = $wpdb->get_var($wpdb->prepare(
            "SELECT rating FROM $table_name WHERE post_id = %d AND " . 
            (is_user_logged_in() ? "user_id = %d" : "ip = %s"),
            $post_id,
            is_user_logged_in() ? get_current_user_id() : $this->get_client_ip()
        ));

        return $rating ? (int) $rating : false;
    }

    /**
     * Get average rating for a post.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @return   float                 The average rating.
     */
    private function get_average_rating($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wprating';
        
        $average = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(rating) FROM $table_name WHERE post_id = %d",
            $post_id
        ));

        return $average ? round($average, 1) : 0;
    }

    /**
     * Check if user has already rated a post.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @return   bool                 Whether the user has rated.
     */
    private function has_user_rated($post_id) {
        return $this->get_user_rating($post_id) !== false;
    }

    /**
     * Save a rating.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @param    int       $rating     The rating value.
     * @return   bool                 Whether the rating was saved successfully.
     */
    private function save_rating($post_id, $rating) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wprating';
        
        $data = array(
            'post_id' => $post_id,
            'rating' => $rating,
            'ip' => $this->get_client_ip()
        );

        if (is_user_logged_in()) {
            $data['user_id'] = get_current_user_id();
        }

        return $wpdb->insert($table_name, $data);
    }

    /**
     * Get client IP address.
     *
     * @since    1.0.0
     * @return   string    The client IP address.
     */
    private function get_client_ip() {
        $ip = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * Get login message HTML.
     *
     * @since    1.0.0
     * @return   string    The login message HTML.
     */
    private function get_login_message() {
        return sprintf(
            '<div class="wprating-login-message">%s <a href="%s">%s</a></div>',
            __('Please', 'wprating'),
            wp_login_url(get_permalink()),
            __('log in', 'wprating')
        );
    }

    /**
     * Render the rating shortcode.
     *
     * @since    1.0.0
     * @param    array     $atts    Shortcode attributes.
     * @return   string             The rating HTML.
     */
    public function render_shortcode($atts) {
        // Ensure scripts/styles are enqueued
        if (!wp_script_is($this->plugin_name, 'enqueued')) {
            $this->enqueue_styles();
            $this->enqueue_scripts();
        }
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID(),
        ), $atts, 'wprating');

        $post_id = absint($atts['post_id']);
        if (!$post_id) {
            return '';
        }

        $settings = get_option('wprating_settings');
        
        // Check if user is logged in when required
        if ($settings['require_login'] && !is_user_logged_in()) {
            return $this->get_login_message();
        }

        // Get current user's rating
        $user_rating = $this->get_user_rating($post_id);
        
        // Get average rating
        $average_rating = $this->get_average_rating($post_id);

        ob_start();
        include WPRATING_PLUGIN_DIR . 'public/partials/wprating-display.php';
        return ob_get_clean();
    }
} 