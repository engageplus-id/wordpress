<?php
/**
 * Plugin Name: EngagePlus
 * Plugin URI: https://engageplus.id
 * Description: Add social login to your WordPress site using any OIDC provider with EngagePlus - a lightweight, data-agnostic authentication platform.
 * Version: 1.2.0
 * Author: EngagePlus Team
 * Author URI: https://engageplus.id
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: engageplus
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('ENGAGEPLUS_VERSION', '1.2.0');
define('ENGAGEPLUS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ENGAGEPLUS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ENGAGEPLUS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main EngagePlus Plugin Class
 */
class EngagePlus {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Plugin settings
     */
    private $settings = array();
    
    /**
     * Get single instance of plugin
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
        $this->load_settings();
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Load plugin settings
     */
    private function load_settings() {
        $defaults = array(
            'org_id' => '',
            'api_key' => '',
            'widget_url' => 'https://auth.engageplus.id/public/pkce.js',
            'auto_create_users' => true,
            'default_role' => 'subscriber',
            'username_pattern' => 'email',
            'skip_email_verification' => true,
            'redirect_after_login' => '',
            'debug_mode' => false,
        );
        
        $saved_settings = get_option('engageplus_settings', array());
        $this->settings = wp_parse_args($saved_settings, $defaults);
    }
    
    /**
     * Get a setting value
     */
    public function get_setting($key, $default = null) {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }
    
    /**
     * Get all settings
     */
    public function get_settings() {
        return $this->settings;
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('init', array($this, 'register_shortcodes'));
        add_action('widgets_init', array($this, 'register_widgets'));
        
        // AJAX hooks for authentication
        add_action('wp_ajax_engageplus_auth', array($this, 'handle_auth_callback'));
        add_action('wp_ajax_nopriv_engageplus_auth', array($this, 'handle_auth_callback'));
        add_action('wp_ajax_engageplus_logout', array($this, 'handle_logout'));
        
        // AJAX hooks for Management API
        add_action('wp_ajax_engageplus_api_request', array($this, 'handle_api_request'));
        
        // REST API endpoint for callback
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // Login form integration
        add_action('login_form', array($this, 'add_login_form_widget'));
        add_action('register_form', array($this, 'add_login_form_widget'));
        
        // Add settings link to plugins page
        add_filter('plugin_action_links_' . ENGAGEPLUS_PLUGIN_BASENAME, array($this, 'add_settings_link'));
    }
    
    /**
     * Load dependencies
     */
    private function load_dependencies() {
        require_once ENGAGEPLUS_PLUGIN_DIR . 'includes/class-engageplus-widget.php';
        require_once ENGAGEPLUS_PLUGIN_DIR . 'includes/class-engageplus-user-handler.php';
        require_once ENGAGEPLUS_PLUGIN_DIR . 'includes/class-engageplus-api-client.php';
        
        // Load admin classes if in admin
        if (is_admin()) {
            require_once ENGAGEPLUS_PLUGIN_DIR . 'includes/class-engageplus-admin.php';
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        if (!get_option('engageplus_settings')) {
            add_option('engageplus_settings', array(
                'org_id' => '',
                'api_key' => '',
                'widget_url' => 'https://auth.engageplus.id/public/pkce.js',
                'auto_create_users' => true,
                'default_role' => 'subscriber',
                'username_pattern' => 'email',
                'skip_email_verification' => true,
                'redirect_after_login' => '',
                'debug_mode' => false,
            ));
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('EngagePlus', 'engageplus'),
            __('EngagePlus', 'engageplus'),
            'manage_options',
            'engageplus',
            array($this, 'render_dashboard_page'),
            'dashicons-shield-alt',
            80
        );
        
        // Dashboard submenu (same as parent)
        add_submenu_page(
            'engageplus',
            __('Dashboard', 'engageplus'),
            __('Dashboard', 'engageplus'),
            'manage_options',
            'engageplus',
            array($this, 'render_dashboard_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'engageplus',
            __('Settings', 'engageplus'),
            __('Settings', 'engageplus'),
            'manage_options',
            'engageplus-settings',
            array($this, 'render_settings_page')
        );
        
        // Only show management pages if API key is configured
        if (!empty($this->get_setting('api_key'))) {
            // Providers submenu
            add_submenu_page(
                'engageplus',
                __('Providers', 'engageplus'),
                __('Providers', 'engageplus'),
                'manage_options',
                'engageplus-providers',
                array($this, 'render_providers_page')
            );
            
            // Widget submenu
            add_submenu_page(
                'engageplus',
                __('Widget', 'engageplus'),
                __('Widget', 'engageplus'),
                'manage_options',
                'engageplus-widget',
                array($this, 'render_widget_page')
            );
            
            // Webhooks submenu
            add_submenu_page(
                'engageplus',
                __('Webhooks', 'engageplus'),
                __('Webhooks', 'engageplus'),
                'manage_options',
                'engageplus-webhooks',
                array($this, 'render_webhooks_page')
            );
            
            // Integrations submenu
            add_submenu_page(
                'engageplus',
                __('Integrations', 'engageplus'),
                __('Integrations', 'engageplus'),
                'manage_options',
                'engageplus-integrations',
                array($this, 'render_integrations_page')
            );
            
            // Email Providers submenu
            add_submenu_page(
                'engageplus',
                __('Email Providers', 'engageplus'),
                __('Email', 'engageplus'),
                'manage_options',
                'engageplus-email',
                array($this, 'render_email_page')
            );
            
            // Metrics submenu
            add_submenu_page(
                'engageplus',
                __('Metrics', 'engageplus'),
                __('Metrics', 'engageplus'),
                'manage_options',
                'engageplus-metrics',
                array($this, 'render_metrics_page')
            );
        }
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include ENGAGEPLUS_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }
    
    /**
     * Render providers page
     */
    public function render_providers_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include ENGAGEPLUS_PLUGIN_DIR . 'templates/admin-providers.php';
    }
    
    /**
     * Render widget configuration page
     */
    public function render_widget_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include ENGAGEPLUS_PLUGIN_DIR . 'templates/admin-widget-config.php';
    }
    
    /**
     * Render webhooks page
     */
    public function render_webhooks_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include ENGAGEPLUS_PLUGIN_DIR . 'templates/admin-webhooks.php';
    }
    
    /**
     * Render integrations page
     */
    public function render_integrations_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include ENGAGEPLUS_PLUGIN_DIR . 'templates/admin-integrations.php';
    }
    
    /**
     * Render email providers page
     */
    public function render_email_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include ENGAGEPLUS_PLUGIN_DIR . 'templates/admin-email.php';
    }
    
    /**
     * Render metrics page
     */
    public function render_metrics_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        include ENGAGEPLUS_PLUGIN_DIR . 'templates/admin-metrics.php';
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('engageplus_settings_group', 'engageplus_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings'),
        ));
        
        // API Settings Section
        add_settings_section(
            'engageplus_api_section',
            __('API Configuration', 'engageplus'),
            array($this, 'render_api_section'),
            'engageplus'
        );
        
        add_settings_field(
            'org_id',
            __('Organization ID', 'engageplus'),
            array($this, 'render_text_field'),
            'engageplus',
            'engageplus_api_section',
            array('field' => 'org_id', 'description' => __('Your EngagePlus Organization ID from the dashboard.', 'engageplus'))
        );
        
        add_settings_field(
            'api_key',
            __('API Key', 'engageplus'),
            array($this, 'render_password_field'),
            'engageplus',
            'engageplus_api_section',
            array('field' => 'api_key', 'description' => __('Your EngagePlus API Key for Management API access. Create one in Settings > API Keys in the EngagePlus dashboard.', 'engageplus'))
        );
        
        // User Settings Section
        add_settings_section(
            'engageplus_user_section',
            __('User Settings', 'engageplus'),
            array($this, 'render_user_section'),
            'engageplus'
        );
        
        add_settings_field(
            'auto_create_users',
            __('Auto Create Users', 'engageplus'),
            array($this, 'render_checkbox_field'),
            'engageplus',
            'engageplus_user_section',
            array('field' => 'auto_create_users', 'description' => __('Automatically create WordPress accounts for new OAuth users.', 'engageplus'))
        );
        
        add_settings_field(
            'default_role',
            __('Default Role', 'engageplus'),
            array($this, 'render_role_field'),
            'engageplus',
            'engageplus_user_section',
            array('field' => 'default_role', 'description' => __('Role to assign to new users created via EngagePlus.', 'engageplus'))
        );
        
        add_settings_field(
            'username_pattern',
            __('Username Pattern', 'engageplus'),
            array($this, 'render_select_field'),
            'engageplus',
            'engageplus_user_section',
            array(
                'field' => 'username_pattern',
                'options' => array(
                    'email' => __('Email Address', 'engageplus'),
                    'name' => __('Display Name', 'engageplus'),
                ),
                'description' => __('How to generate usernames for new users.', 'engageplus'),
            )
        );
        
        add_settings_field(
            'skip_email_verification',
            __('Skip Email Verification', 'engageplus'),
            array($this, 'render_checkbox_field'),
            'engageplus',
            'engageplus_user_section',
            array('field' => 'skip_email_verification', 'description' => __('Trust OAuth provider email verification (recommended).', 'engageplus'))
        );
        
        // Redirect Settings Section
        add_settings_section(
            'engageplus_redirect_section',
            __('Redirect Settings', 'engageplus'),
            array($this, 'render_redirect_section'),
            'engageplus'
        );
        
        add_settings_field(
            'redirect_after_login',
            __('Redirect After Login', 'engageplus'),
            array($this, 'render_text_field'),
            'engageplus',
            'engageplus_redirect_section',
            array('field' => 'redirect_after_login', 'description' => __('URL to redirect to after login. Leave empty to stay on current page.', 'engageplus'))
        );
        
        // Debug Section
        add_settings_section(
            'engageplus_debug_section',
            __('Debug Settings', 'engageplus'),
            array($this, 'render_debug_section'),
            'engageplus'
        );
        
        add_settings_field(
            'debug_mode',
            __('Debug Mode', 'engageplus'),
            array($this, 'render_checkbox_field'),
            'engageplus',
            'engageplus_debug_section',
            array('field' => 'debug_mode', 'description' => __('Enable debug logging for troubleshooting.', 'engageplus'))
        );
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['org_id'] = sanitize_text_field($input['org_id'] ?? '');
        $sanitized['api_key'] = sanitize_text_field($input['api_key'] ?? '');
        $sanitized['widget_url'] = esc_url_raw($input['widget_url'] ?? 'https://auth.engageplus.id/public/pkce.js');
        $sanitized['auto_create_users'] = !empty($input['auto_create_users']);
        $sanitized['default_role'] = sanitize_text_field($input['default_role'] ?? 'subscriber');
        $sanitized['username_pattern'] = in_array($input['username_pattern'] ?? 'email', array('email', 'name')) ? $input['username_pattern'] : 'email';
        $sanitized['skip_email_verification'] = !empty($input['skip_email_verification']);
        $sanitized['redirect_after_login'] = sanitize_text_field($input['redirect_after_login'] ?? '');
        $sanitized['debug_mode'] = !empty($input['debug_mode']);
        
        return $sanitized;
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        include ENGAGEPLUS_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    /**
     * Section render callbacks
     */
    public function render_api_section() {
        echo '<p>' . esc_html__('Configure your EngagePlus credentials. Get your Organization ID and API Key from the EngagePlus dashboard.', 'engageplus') . '</p>';
        echo '<p>' . esc_html__('With an API Key configured, you can manage providers, widget styling, webhooks, integrations, and more directly from WordPress.', 'engageplus') . '</p>';
    }
    
    public function render_user_section() {
        echo '<p>' . esc_html__('Configure how users are created and managed when authenticating via EngagePlus.', 'engageplus') . '</p>';
    }
    
    public function render_redirect_section() {
        echo '<p>' . esc_html__('Configure where users are redirected after successful authentication.', 'engageplus') . '</p>';
    }
    
    public function render_debug_section() {
        echo '<p>' . esc_html__('Enable debug mode for troubleshooting authentication issues.', 'engageplus') . '</p>';
    }
    
    /**
     * Field render callbacks
     */
    public function render_text_field($args) {
        $field = $args['field'];
        $value = $this->get_setting($field);
        $description = $args['description'] ?? '';
        
        printf(
            '<input type="text" id="%s" name="engageplus_settings[%s]" value="%s" class="regular-text">',
            esc_attr($field),
            esc_attr($field),
            esc_attr($value)
        );
        
        if ($description) {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }
    
    public function render_checkbox_field($args) {
        $field = $args['field'];
        $value = $this->get_setting($field);
        $description = $args['description'] ?? '';
        
        printf(
            '<input type="checkbox" id="%s" name="engageplus_settings[%s]" value="1" %s>',
            esc_attr($field),
            esc_attr($field),
            checked($value, true, false)
        );
        
        if ($description) {
            printf('<label for="%s" class="description">%s</label>', esc_attr($field), esc_html($description));
        }
    }
    
    public function render_select_field($args) {
        $field = $args['field'];
        $value = $this->get_setting($field);
        $options = $args['options'] ?? array();
        $description = $args['description'] ?? '';
        
        printf('<select id="%s" name="engageplus_settings[%s]">', esc_attr($field), esc_attr($field));
        foreach ($options as $key => $label) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($key),
                selected($value, $key, false),
                esc_html($label)
            );
        }
        echo '</select>';
        
        if ($description) {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }
    
    public function render_role_field($args) {
        $field = $args['field'];
        $value = $this->get_setting($field);
        $description = $args['description'] ?? '';
        
        wp_dropdown_roles($value);
        echo '<script>document.querySelector("select[name=\'role\']").setAttribute("name", "engageplus_settings[default_role]");</script>';
        
        if ($description) {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }
    
    public function render_password_field($args) {
        $field = $args['field'];
        $value = $this->get_setting($field);
        $description = $args['description'] ?? '';
        
        $masked = !empty($value) ? str_repeat('•', 20) : '';
        
        printf(
            '<input type="password" id="%s" name="engageplus_settings[%s]" value="%s" class="regular-text" autocomplete="new-password">',
            esc_attr($field),
            esc_attr($field),
            esc_attr($value)
        );
        
        if (!empty($value)) {
            echo '<span class="engageplus-api-key-status" style="margin-left: 10px; color: #46b450;">✓ Configured</span>';
        }
        
        if ($description) {
            printf('<p class="description">%s</p>', esc_html($description));
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Check if we're on an EngagePlus admin page
        if (strpos($hook, 'engageplus') === false && strpos($hook, 'toplevel_page_engageplus') === false) {
            return;
        }
        
        wp_enqueue_style(
            'engageplus-admin',
            ENGAGEPLUS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            ENGAGEPLUS_VERSION
        );
        
        // Enqueue Chart.js for metrics page
        if (strpos($hook, 'engageplus-metrics') !== false) {
            wp_enqueue_script(
                'chartjs',
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
                array(),
                '4.4.1',
                true
            );
        }
        
        // Admin JavaScript
        wp_enqueue_script(
            'engageplus-admin-js',
            ENGAGEPLUS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            ENGAGEPLUS_VERSION,
            true
        );
        
        wp_localize_script('engageplus-admin-js', 'engageplusAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('engageplus_admin'),
            'apiConfigured' => !empty($this->get_setting('api_key')),
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this item?', 'engageplus'),
                'saving' => __('Saving...', 'engageplus'),
                'saved' => __('Saved!', 'engageplus'),
                'error' => __('An error occurred. Please try again.', 'engageplus'),
                'testing' => __('Testing...', 'engageplus'),
                'testSuccess' => __('Connection successful!', 'engageplus'),
                'testFailed' => __('Connection failed.', 'engageplus'),
            ),
        ));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load if Organization ID is configured
        if (empty($this->get_setting('org_id'))) {
            return;
        }
        
        // EngagePlus PKCE Widget Script
        wp_enqueue_script(
            'engageplus-pkce',
            $this->get_setting('widget_url'),
            array(),
            null,
            true
        );
        
        // Plugin JS
        wp_enqueue_script(
            'engageplus-main',
            ENGAGEPLUS_PLUGIN_URL . 'assets/js/engageplus.js',
            array('jquery', 'engageplus-pkce'),
            ENGAGEPLUS_VERSION,
            true
        );
        
        // Localize script with settings
        wp_localize_script('engageplus-main', 'engageplusConfig', array(
            'orgId' => $this->get_setting('org_id'),
            'redirectUri' => $this->get_callback_url(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('engageplus_auth'),
            'isLoggedIn' => is_user_logged_in(),
            'redirectAfterLogin' => $this->get_setting('redirect_after_login'),
            'debugMode' => $this->get_setting('debug_mode'),
        ));
        
        // Plugin CSS
        wp_enqueue_style(
            'engageplus-style',
            ENGAGEPLUS_PLUGIN_URL . 'assets/css/engageplus.css',
            array(),
            ENGAGEPLUS_VERSION
        );
    }
    
    /**
     * Get the callback URL for OAuth
     */
    public function get_callback_url() {
        return home_url('/wp-json/engageplus/v1/callback');
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('engageplus', array($this, 'render_shortcode'));
        add_shortcode('engageplus_login', array($this, 'render_shortcode'));
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 'engageplus-widget-' . uniqid(),
            'hide_logged_in' => 'true',
            'show_logout' => 'false',
        ), $atts, 'engageplus');
        
        // Hide widget for logged-in users if configured
        if ($atts['hide_logged_in'] === 'true' && is_user_logged_in()) {
            if ($atts['show_logout'] === 'true') {
                return sprintf(
                    '<div class="engageplus-logout-wrapper"><a href="%s" class="engageplus-logout-btn">%s</a></div>',
                    esc_url(wp_logout_url(get_permalink())),
                    esc_html__('Logout', 'engageplus')
                );
            }
            return '';
        }
        
        // Check if Organization ID is configured
        if (empty($this->get_setting('org_id'))) {
            if (current_user_can('manage_options')) {
                return '<div class="engageplus-notice">' . 
                    sprintf(
                        __('EngagePlus: Please <a href="%s">configure your Organization ID</a> to enable the widget.', 'engageplus'),
                        admin_url('options-general.php?page=engageplus')
                    ) . 
                    '</div>';
            }
            return '';
        }
        
        return sprintf(
            '<div id="%s" class="engageplus-widget"></div>',
            esc_attr($atts['id'])
        );
    }
    
    /**
     * Register WordPress widget
     */
    public function register_widgets() {
        register_widget('EngagePlus_Widget');
    }
    
    /**
     * Handle authentication callback
     */
    public function handle_auth_callback() {
        check_ajax_referer('engageplus_auth', 'nonce');
        
        $tokens = json_decode(stripslashes($_POST['tokens'] ?? '{}'), true);
        
        if (empty($tokens)) {
            wp_send_json_error(array('message' => __('Invalid tokens received.', 'engageplus')));
        }
        
        $this->log('Authentication callback received', array('has_id_token' => !empty($tokens['id_token'])));
        
        // Decode user data from ID token
        $user_data = $this->decode_id_token($tokens);
        
        if (empty($user_data) || empty($user_data['email'])) {
            wp_send_json_error(array('message' => __('Could not extract user data from token.', 'engageplus')));
        }
        
        // Use the user handler to create or login the user
        $user_handler = new EngagePlus_User_Handler($this);
        $result = $user_handler->handle_authentication($user_data);
        
        if (is_wp_error($result)) {
            $this->log('Authentication failed', array('error' => $result->get_error_message()));
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        $this->log('Authentication successful', array('user_id' => $result['user_id']));
        
        wp_send_json_success(array(
            'message' => __('Login successful!', 'engageplus'),
            'redirect' => $result['redirect_url'],
        ));
    }
    
    /**
     * Decode ID token to extract user data
     */
    private function decode_id_token($tokens) {
        $id_token = $tokens['id_token'] ?? '';
        
        if (empty($id_token)) {
            return null;
        }
        
        // JWT has 3 parts separated by dots
        $parts = explode('.', $id_token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        // Decode the payload (second part)
        $payload = $parts[1];
        
        // Add padding if needed
        $payload = str_replace(['-', '_'], ['+', '/'], $payload);
        $padding = strlen($payload) % 4;
        if ($padding) {
            $payload .= str_repeat('=', 4 - $padding);
        }
        
        $decoded = base64_decode($payload);
        
        if (!$decoded) {
            return null;
        }
        
        $user_data = json_decode($decoded, true);
        
        return $user_data;
    }
    
    /**
     * Handle logout
     */
    public function handle_logout() {
        check_ajax_referer('engageplus_auth', 'nonce');
        
        wp_logout();
        
        wp_send_json_success(array(
            'message' => __('Logged out successfully.', 'engageplus'),
            'redirect' => home_url(),
        ));
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('engageplus/v1', '/callback', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_auth_callback'),
            'permission_callback' => '__return_true',
        ));
    }
    
    /**
     * REST API callback handler
     */
    public function rest_auth_callback($request) {
        $user_data = $request->get_json_params();
        
        if (empty($user_data) || empty($user_data['email'])) {
            return new WP_Error('invalid_data', __('Invalid user data received.', 'engageplus'), array('status' => 400));
        }
        
        $user_handler = new EngagePlus_User_Handler($this);
        $result = $user_handler->handle_authentication($user_data);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'redirect' => $result['redirect_url'],
        ));
    }
    
    /**
     * Add widget to login form
     */
    public function add_login_form_widget() {
        if (empty($this->get_setting('org_id'))) {
            return;
        }
        
        echo '<div class="engageplus-login-form-widget">';
        echo '<p class="engageplus-separator"><span>' . esc_html__('Or sign in with', 'engageplus') . '</span></p>';
        echo do_shortcode('[engageplus hide_logged_in="false"]');
        echo '</div>';
    }
    
    /**
     * Add settings link to plugins page
     */
    public function add_settings_link($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=engageplus-settings'),
            __('Settings', 'engageplus')
        );
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Handle Management API AJAX requests
     */
    public function handle_api_request() {
        check_ajax_referer('engageplus_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'engageplus')));
        }
        
        $action = sanitize_text_field($_POST['api_action'] ?? '');
        $data = isset($_POST['data']) ? json_decode(stripslashes($_POST['data']), true) : array();
        
        $api = new EngagePlus_API_Client($this);
        
        if (!$api->is_configured()) {
            wp_send_json_error(array('message' => __('API key not configured.', 'engageplus')));
        }
        
        $result = null;
        
        switch ($action) {
            // Providers
            case 'get_providers':
                $result = $api->get_providers();
                break;
            case 'create_provider':
                $result = $api->create_provider($data);
                break;
            case 'update_provider':
                $result = $api->update_provider($data['id'], $data);
                break;
            case 'delete_provider':
                $result = $api->delete_provider($data['id']);
                break;
            case 'test_provider':
                $result = $api->test_provider($data['id']);
                break;
                
            // Widget
            case 'get_widget':
                $result = $api->get_widget_config();
                break;
            case 'update_widget':
                $result = $api->patch_widget_config($data);
                break;
                
            // Webhooks
            case 'get_webhooks':
                $result = $api->get_webhooks();
                break;
            case 'create_webhook':
                $result = $api->create_webhook($data);
                break;
            case 'update_webhook':
                $result = $api->update_webhook($data['id'], $data);
                break;
            case 'delete_webhook':
                $result = $api->delete_webhook($data['id']);
                break;
                
            // Integrations
            case 'get_integrations':
                $result = $api->get_integrations();
                break;
            case 'create_integration':
                $result = $api->create_integration($data);
                break;
            case 'update_integration':
                $result = $api->update_integration($data['id'], $data);
                break;
            case 'delete_integration':
                $result = $api->delete_integration($data['id']);
                break;
                
            // Email Providers
            case 'get_email_providers':
                $result = $api->get_email_providers();
                break;
            case 'create_email_provider':
                $result = $api->create_email_provider($data);
                break;
            case 'update_email_provider':
                $result = $api->update_email_provider($data['id'], $data);
                break;
            case 'delete_email_provider':
                $result = $api->delete_email_provider($data['id']);
                break;
            case 'test_email_provider':
                $result = $api->test_email_provider($data['id']);
                break;
                
            // Metrics
            case 'get_metrics':
                $days = intval($data['days'] ?? 30);
                $result = $api->get_metrics($days);
                break;
            case 'get_provider_metrics':
                $days = intval($data['days'] ?? 30);
                $result = $api->get_provider_metrics($days);
                break;
                
            // Organization
            case 'get_organization':
                $result = $api->get_organization();
                break;
            case 'add_redirect_uri':
                $result = $api->add_redirect_uri($data['uri']);
                break;
                
            default:
                wp_send_json_error(array('message' => __('Unknown action.', 'engageplus')));
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * Get API client instance
     */
    public function get_api_client() {
        return new EngagePlus_API_Client($this);
    }
    
    /**
     * Log debug messages
     */
    public function log($message, $data = array()) {
        if (!$this->get_setting('debug_mode')) {
            return;
        }
        
        $log_message = sprintf('[EngagePlus] %s', $message);
        if (!empty($data)) {
            $log_message .= ' | Data: ' . wp_json_encode($data);
        }
        
        error_log($log_message);
    }
}

// Initialize the plugin
function engageplus() {
    return EngagePlus::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'engageplus');

