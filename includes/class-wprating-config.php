<?php
/**
 * WP Rating Configuration Class
 *
 * @package WPRating
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WPRating_Config {
    /**
     * Instance of this class
     *
     * @var object
     */
    private static $instance = null;

    /**
     * Plugin settings
     *
     * @var array
     */
    private $settings = array();

    /**
     * Get instance of this class
     *
     * @return object
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_settings();
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'add_settings_page'));
    }

    /**
     * Initialize default settings
     */
    private function init_settings() {
        $this->settings = array(
            'number_of_stars' => 5,
            'require_login' => false,
            'user_identifier' => 'ip', // 'ip', 'user_id', or 'email'
            'enable_validation' => true,
            'allow_multiple_ratings' => false,
            'rating_timeout' => 24, // hours
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wprating_settings', 'wprating_settings', array($this, 'sanitize_settings'));
    }

    /**
     * Add settings page
     */
    public function add_settings_page() {
        add_submenu_page(
            'options-general.php',
            __('WP Rating Settings', 'wprating'),
            __('WP Rating', 'wprating'),
            'manage_options',
            'wprating-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Sanitize settings
     *
     * @param array $input
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['number_of_stars'] = absint($input['number_of_stars']);
        $sanitized['require_login'] = isset($input['require_login']) ? true : false;
        $sanitized['user_identifier'] = sanitize_text_field($input['user_identifier']);
        $sanitized['enable_validation'] = isset($input['enable_validation']) ? true : false;
        $sanitized['allow_multiple_ratings'] = isset($input['allow_multiple_ratings']) ? true : false;
        $sanitized['rating_timeout'] = absint($input['rating_timeout']);

        return $sanitized;
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        $settings = get_option('wprating_settings', $this->settings);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('WP Rating Settings', 'wprating'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('wprating_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Number of Stars', 'wprating'); ?></th>
                        <td>
                            <input type="number" name="wprating_settings[number_of_stars]" 
                                   value="<?php echo esc_attr($settings['number_of_stars']); ?>" 
                                   min="1" max="10" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Require User Login', 'wprating'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wprating_settings[require_login]" 
                                       value="1" <?php checked($settings['require_login']); ?> />
                                <?php echo esc_html__('Require users to be logged in to rate', 'wprating'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('User Identifier', 'wprating'); ?></th>
                        <td>
                            <select name="wprating_settings[user_identifier]">
                                <option value="ip" <?php selected($settings['user_identifier'], 'ip'); ?>>
                                    <?php echo esc_html__('IP Address', 'wprating'); ?>
                                </option>
                                <option value="user_id" <?php selected($settings['user_identifier'], 'user_id'); ?>>
                                    <?php echo esc_html__('User ID', 'wprating'); ?>
                                </option>
                                <option value="email" <?php selected($settings['user_identifier'], 'email'); ?>>
                                    <?php echo esc_html__('Email Address', 'wprating'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Enable Validation', 'wprating'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wprating_settings[enable_validation]" 
                                       value="1" <?php checked($settings['enable_validation']); ?> />
                                <?php echo esc_html__('Enable rating validation', 'wprating'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Allow Multiple Ratings', 'wprating'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wprating_settings[allow_multiple_ratings]" 
                                       value="1" <?php checked($settings['allow_multiple_ratings']); ?> />
                                <?php echo esc_html__('Allow users to rate multiple times', 'wprating'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Rating Timeout (hours)', 'wprating'); ?></th>
                        <td>
                            <input type="number" name="wprating_settings[rating_timeout]" 
                                   value="<?php echo esc_attr($settings['rating_timeout']); ?>" 
                                   min="1" />
                            <p class="description">
                                <?php echo esc_html__('Time in hours before a user can rate again (if multiple ratings are disabled)', 'wprating'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Get setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get_setting($key, $default = null) {
        $settings = get_option('wprating_settings', $this->settings);
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
}

// Initialize the configuration on plugins_loaded
add_action('plugins_loaded', array('WPRating_Config', 'get_instance')); 