<?php
/**
 * EngagePlus User Handler
 *
 * Handles user creation, authentication, and management
 *
 * @package EngagePlus
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * EngagePlus User Handler Class
 */
class EngagePlus_User_Handler {
    
    /**
     * Plugin instance
     */
    private $plugin;
    
    /**
     * Constructor
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;
    }
    
    /**
     * Handle user authentication
     *
     * @param array $user_data User data from OAuth provider
     * @return array|WP_Error Result array with user_id and redirect_url, or WP_Error on failure
     */
    public function handle_authentication($user_data) {
        $email = sanitize_email($user_data['email'] ?? '');
        $name = sanitize_text_field($user_data['name'] ?? '');
        $first_name = sanitize_text_field($user_data['given_name'] ?? $user_data['first_name'] ?? '');
        $last_name = sanitize_text_field($user_data['family_name'] ?? $user_data['last_name'] ?? '');
        $picture = esc_url_raw($user_data['picture'] ?? $user_data['avatar'] ?? '');
        $provider = sanitize_text_field($user_data['provider'] ?? 'engageplus');
        $provider_id = sanitize_text_field($user_data['sub'] ?? $user_data['id'] ?? '');
        
        if (empty($email)) {
            return new WP_Error('missing_email', __('Email address is required for authentication.', 'engageplus'));
        }
        
        // Check if user exists by email
        $user = get_user_by('email', $email);
        
        if ($user) {
            // User exists, log them in
            return $this->login_user($user, $user_data);
        }
        
        // Check if auto-create is enabled
        if (!$this->plugin->get_setting('auto_create_users')) {
            return new WP_Error('registration_disabled', __('User registration is disabled. Please contact an administrator.', 'engageplus'));
        }
        
        // Check if WordPress registration is enabled
        if (!get_option('users_can_register') && !$this->plugin->get_setting('auto_create_users')) {
            return new WP_Error('registration_disabled', __('User registration is disabled on this site.', 'engageplus'));
        }
        
        // Create new user
        return $this->create_user($user_data);
    }
    
    /**
     * Create a new WordPress user
     *
     * @param array $user_data User data from OAuth provider
     * @return array|WP_Error Result array with user_id and redirect_url, or WP_Error on failure
     */
    private function create_user($user_data) {
        $email = sanitize_email($user_data['email']);
        $name = sanitize_text_field($user_data['name'] ?? '');
        $first_name = sanitize_text_field($user_data['given_name'] ?? $user_data['first_name'] ?? '');
        $last_name = sanitize_text_field($user_data['family_name'] ?? $user_data['last_name'] ?? '');
        $picture = esc_url_raw($user_data['picture'] ?? $user_data['avatar'] ?? '');
        $provider = sanitize_text_field($user_data['provider'] ?? 'engageplus');
        $provider_id = sanitize_text_field($user_data['sub'] ?? $user_data['id'] ?? '');
        
        // Generate username
        $username = $this->generate_username($user_data);
        
        // Generate random password
        $password = wp_generate_password(24, true, true);
        
        // Get default role
        $role = $this->plugin->get_setting('default_role', 'subscriber');
        
        // Create user data array
        $userdata = array(
            'user_login' => $username,
            'user_pass' => $password,
            'user_email' => $email,
            'display_name' => $name ?: $username,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => $role,
        );
        
        // Create the user
        $user_id = wp_insert_user($userdata);
        
        if (is_wp_error($user_id)) {
            $this->plugin->log('Failed to create user', array('error' => $user_id->get_error_message()));
            return $user_id;
        }
        
        // Store EngagePlus meta data
        update_user_meta($user_id, 'engageplus_provider', $provider);
        update_user_meta($user_id, 'engageplus_provider_id', $provider_id);
        update_user_meta($user_id, 'engageplus_created', current_time('mysql'));
        
        // Store profile picture URL
        if ($picture) {
            update_user_meta($user_id, 'engageplus_picture', $picture);
        }
        
        // Skip email verification if configured
        if ($this->plugin->get_setting('skip_email_verification')) {
            // Mark email as confirmed (for plugins that check this)
            update_user_meta($user_id, 'email_verified', true);
            update_user_meta($user_id, 'engageplus_email_verified', true);
        }
        
        /**
         * Action fired after a user is created via EngagePlus
         *
         * @param int $user_id The new user's ID
         * @param array $user_data User data from OAuth provider
         */
        do_action('engageplus_user_created', $user_id, $user_data);
        
        $this->plugin->log('User created successfully', array('user_id' => $user_id, 'email' => $email));
        
        // Log the user in
        $user = get_user_by('id', $user_id);
        return $this->login_user($user, $user_data);
    }
    
    /**
     * Log in an existing user
     *
     * @param WP_User $user The user object
     * @param array $user_data User data from OAuth provider
     * @return array Result array with user_id and redirect_url
     */
    private function login_user($user, $user_data) {
        // Update last login meta
        update_user_meta($user->ID, 'engageplus_last_login', current_time('mysql'));
        
        // Update provider info if not already set
        $provider = sanitize_text_field($user_data['provider'] ?? 'engageplus');
        $provider_id = sanitize_text_field($user_data['sub'] ?? $user_data['id'] ?? '');
        
        if (!get_user_meta($user->ID, 'engageplus_provider', true)) {
            update_user_meta($user->ID, 'engageplus_provider', $provider);
        }
        
        if (!get_user_meta($user->ID, 'engageplus_provider_id', true) && $provider_id) {
            update_user_meta($user->ID, 'engageplus_provider_id', $provider_id);
        }
        
        // Update profile picture if provided
        $picture = esc_url_raw($user_data['picture'] ?? $user_data['avatar'] ?? '');
        if ($picture) {
            update_user_meta($user->ID, 'engageplus_picture', $picture);
        }
        
        // Log the user in
        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        /**
         * Action fired after a user logs in via EngagePlus
         *
         * @param WP_User $user The user object
         * @param array $user_data User data from OAuth provider
         */
        do_action('engageplus_user_login', $user, $user_data);
        
        $this->plugin->log('User logged in', array('user_id' => $user->ID, 'email' => $user->user_email));
        
        // Determine redirect URL
        $redirect_url = $this->get_redirect_url();
        
        return array(
            'user_id' => $user->ID,
            'redirect_url' => $redirect_url,
        );
    }
    
    /**
     * Generate a unique username
     *
     * @param array $user_data User data from OAuth provider
     * @return string Unique username
     */
    private function generate_username($user_data) {
        $pattern = $this->plugin->get_setting('username_pattern', 'email');
        $email = sanitize_email($user_data['email']);
        $name = sanitize_text_field($user_data['name'] ?? '');
        
        if ($pattern === 'name' && !empty($name)) {
            $base_username = sanitize_user($name, true);
        } else {
            // Use email part before @
            $base_username = sanitize_user(strstr($email, '@', true), true);
        }
        
        // Ensure username is not empty
        if (empty($base_username)) {
            $base_username = 'user';
        }
        
        // Make username unique
        $username = $base_username;
        $suffix = 1;
        
        while (username_exists($username)) {
            $username = $base_username . $suffix;
            $suffix++;
        }
        
        return $username;
    }
    
    /**
     * Get redirect URL after login
     *
     * @return string Redirect URL
     */
    private function get_redirect_url() {
        $redirect = $this->plugin->get_setting('redirect_after_login');
        
        if (empty($redirect)) {
            // Check if there's a redirect_to parameter
            if (isset($_REQUEST['redirect_to'])) {
                return esc_url_raw($_REQUEST['redirect_to']);
            }
            
            // Check HTTP referer
            $referer = wp_get_referer();
            if ($referer) {
                return $referer;
            }
            
            // Default to home
            return home_url();
        }
        
        // Handle special redirect values
        if ($redirect === '<front>') {
            return home_url();
        }
        
        if ($redirect === '<admin>') {
            return admin_url();
        }
        
        if ($redirect === '<profile>') {
            return admin_url('profile.php');
        }
        
        // Handle relative URLs
        if (strpos($redirect, '/') === 0) {
            return home_url($redirect);
        }
        
        return esc_url($redirect);
    }
}

