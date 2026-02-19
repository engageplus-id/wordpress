<?php
/**
 * EngagePlus Admin Class
 *
 * Admin-specific functionality
 *
 * @package EngagePlus
 * @since 1.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * EngagePlus Admin Class
 */
class EngagePlus_Admin {
    
    /**
     * Plugin instance
     */
    private $plugin;
    
    /**
     * Constructor
     */
    public function __construct($plugin = null) {
        $this->plugin = $plugin ?: engageplus();
    }
    
    /**
     * Check if Management API is configured
     */
    public function is_api_configured() {
        return !empty($this->plugin->get_setting('api_key')) && 
               !empty($this->plugin->get_setting('org_id'));
    }
    
    /**
     * Get available webhook events
     */
    public static function get_webhook_events() {
        return array(
            'auth.login.success' => __('Login Success', 'engageplus'),
            'auth.login.failure' => __('Login Failure', 'engageplus'),
            'auth.logout' => __('Logout', 'engageplus'),
            'auth.token.issued' => __('Token Issued', 'engageplus'),
            'auth.token.refreshed' => __('Token Refreshed', 'engageplus'),
            'auth.token.revoked' => __('Token Revoked', 'engageplus'),
            'session.created' => __('Session Created', 'engageplus'),
            'session.expired' => __('Session Expired', 'engageplus'),
            'session.terminated' => __('Session Terminated', 'engageplus'),
            'account.linked' => __('Account Linked', 'engageplus'),
            'account.unlinked' => __('Account Unlinked', 'engageplus'),
        );
    }
    
    /**
     * Get provider types
     */
    public static function get_provider_types() {
        return array(
            'google' => array('name' => 'Google', 'issuer' => 'https://accounts.google.com'),
            'microsoft' => array('name' => 'Microsoft', 'issuer' => 'https://login.microsoftonline.com/common/v2.0'),
            'github' => array('name' => 'GitHub', 'issuer' => 'https://github.com'),
            'apple' => array('name' => 'Apple', 'issuer' => 'https://appleid.apple.com'),
            'facebook' => array('name' => 'Facebook', 'issuer' => 'https://www.facebook.com'),
            'linkedin' => array('name' => 'LinkedIn', 'issuer' => 'https://www.linkedin.com'),
            'discord' => array('name' => 'Discord', 'issuer' => 'https://discord.com'),
            'custom' => array('name' => 'Custom OIDC', 'issuer' => ''),
        );
    }
    
    /**
     * Get email provider types
     */
    public static function get_email_provider_types() {
        return array(
            'sendgrid' => array('name' => 'SendGrid'),
            'mailgun' => array('name' => 'Mailgun'),
            'postmark' => array('name' => 'Postmark'),
            'ses' => array('name' => 'Amazon SES'),
            'smtp' => array('name' => 'SMTP'),
        );
    }
    
    /**
     * Get integration types
     */
    public static function get_integration_types() {
        return array(
            'supabase' => array(
                'name' => 'Supabase',
                'description' => __('Sync user data to Supabase database', 'engageplus'),
            ),
            'airtable' => array(
                'name' => 'Airtable',
                'description' => __('Sync user data to Airtable base', 'engageplus'),
            ),
        );
    }
}
