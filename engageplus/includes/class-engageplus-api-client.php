<?php
/**
 * EngagePlus Management API Client
 *
 * Handles all API requests to the EngagePlus Management API
 *
 * @package EngagePlus
 * @since 1.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * EngagePlus API Client Class
 */
class EngagePlus_API_Client {
    
    /**
     * API Base URL
     */
    const API_BASE_URL = 'https://api.engageplus.id';
    
    /**
     * API Key
     */
    private $api_key;
    
    /**
     * Organization ID
     */
    private $org_id;
    
    /**
     * Plugin instance
     */
    private $plugin;
    
    /**
     * Last error
     */
    private $last_error = null;
    
    /**
     * Constructor
     */
    public function __construct($plugin = null) {
        $this->plugin = $plugin ?: engageplus();
        $this->api_key = $this->plugin->get_setting('api_key');
        $this->org_id = $this->plugin->get_setting('org_id');
    }
    
    /**
     * Check if API is configured
     */
    public function is_configured() {
        return !empty($this->api_key) && !empty($this->org_id);
    }
    
    /**
     * Get last error
     */
    public function get_last_error() {
        return $this->last_error;
    }
    
    /**
     * Make API request
     */
    private function request($method, $endpoint, $data = null) {
        if (!$this->is_configured()) {
            $this->last_error = __('API key and Organization ID are required.', 'engageplus');
            return new WP_Error('not_configured', $this->last_error);
        }
        
        $url = self::API_BASE_URL . str_replace('{orgId}', $this->org_id, $endpoint);
        
        $args = array(
            'method' => $method,
            'headers' => array(
                'X-Api-Key' => $this->api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ),
            'timeout' => 30,
        );
        
        if ($data !== null && in_array($method, array('POST', 'PUT', 'PATCH'))) {
            $args['body'] = wp_json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            $this->last_error = $response->get_error_message();
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        
        // Handle error responses
        if ($status_code >= 400) {
            $error_message = isset($decoded['message']) ? $decoded['message'] : 
                            (isset($decoded['error_description']) ? $decoded['error_description'] : 
                            __('Unknown API error', 'engageplus'));
            $this->last_error = $error_message;
            return new WP_Error(
                isset($decoded['error']) ? $decoded['error'] : 'api_error',
                $error_message,
                array('status' => $status_code, 'response' => $decoded)
            );
        }
        
        // 204 No Content
        if ($status_code === 204) {
            return true;
        }
        
        $this->last_error = null;
        return $decoded;
    }
    
    // ==========================================
    // PROVIDERS API
    // ==========================================
    
    /**
     * List all providers
     */
    public function get_providers() {
        $result = $this->request('GET', '/api/v1/organizations/{orgId}/providers');
        return is_wp_error($result) ? $result : ($result['data'] ?? array());
    }
    
    /**
     * Create a provider
     */
    public function create_provider($data) {
        return $this->request('POST', '/api/v1/organizations/{orgId}/providers', $data);
    }
    
    /**
     * Update a provider
     */
    public function update_provider($provider_id, $data) {
        return $this->request('PATCH', "/api/v1/organizations/{orgId}/providers/{$provider_id}", $data);
    }
    
    /**
     * Delete a provider
     */
    public function delete_provider($provider_id) {
        return $this->request('DELETE', "/api/v1/organizations/{orgId}/providers/{$provider_id}");
    }
    
    /**
     * Test provider connectivity
     */
    public function test_provider($provider_id) {
        return $this->request('POST', "/api/v1/organizations/{orgId}/providers/{$provider_id}/test");
    }
    
    // ==========================================
    // WIDGET API
    // ==========================================
    
    /**
     * Get widget configuration
     */
    public function get_widget_config() {
        return $this->request('GET', '/api/v1/organizations/{orgId}/widget');
    }
    
    /**
     * Update widget configuration (full replace)
     */
    public function update_widget_config($data) {
        return $this->request('PUT', '/api/v1/organizations/{orgId}/widget', $data);
    }
    
    /**
     * Patch widget configuration (partial update)
     */
    public function patch_widget_config($data) {
        return $this->request('PATCH', '/api/v1/organizations/{orgId}/widget', $data);
    }
    
    // ==========================================
    // WEBHOOKS API
    // ==========================================
    
    /**
     * List all webhooks
     */
    public function get_webhooks() {
        $result = $this->request('GET', '/api/v1/organizations/{orgId}/webhooks');
        return is_wp_error($result) ? $result : ($result['data'] ?? array());
    }
    
    /**
     * Create a webhook
     */
    public function create_webhook($data) {
        return $this->request('POST', '/api/v1/organizations/{orgId}/webhooks', $data);
    }
    
    /**
     * Update a webhook
     */
    public function update_webhook($webhook_id, $data) {
        return $this->request('PATCH', "/api/v1/organizations/{orgId}/webhooks/{$webhook_id}", $data);
    }
    
    /**
     * Delete a webhook
     */
    public function delete_webhook($webhook_id) {
        return $this->request('DELETE', "/api/v1/organizations/{orgId}/webhooks/{$webhook_id}");
    }
    
    // ==========================================
    // INTEGRATIONS API
    // ==========================================
    
    /**
     * List all integrations
     */
    public function get_integrations() {
        $result = $this->request('GET', '/api/v1/organizations/{orgId}/integrations');
        return is_wp_error($result) ? $result : ($result['data'] ?? array());
    }
    
    /**
     * Create an integration
     */
    public function create_integration($data) {
        return $this->request('POST', '/api/v1/organizations/{orgId}/integrations', $data);
    }
    
    /**
     * Update an integration
     */
    public function update_integration($integration_id, $data) {
        return $this->request('PATCH', "/api/v1/organizations/{orgId}/integrations/{$integration_id}", $data);
    }
    
    /**
     * Delete an integration
     */
    public function delete_integration($integration_id) {
        return $this->request('DELETE', "/api/v1/organizations/{orgId}/integrations/{$integration_id}");
    }
    
    // ==========================================
    // EMAIL PROVIDERS API
    // ==========================================
    
    /**
     * List all email providers
     */
    public function get_email_providers() {
        $result = $this->request('GET', '/api/v1/organizations/{orgId}/email-providers');
        return is_wp_error($result) ? $result : ($result['data'] ?? array());
    }
    
    /**
     * Create an email provider
     */
    public function create_email_provider($data) {
        return $this->request('POST', '/api/v1/organizations/{orgId}/email-providers', $data);
    }
    
    /**
     * Update an email provider
     */
    public function update_email_provider($provider_id, $data) {
        return $this->request('PATCH', "/api/v1/organizations/{orgId}/email-providers/{$provider_id}", $data);
    }
    
    /**
     * Delete an email provider
     */
    public function delete_email_provider($provider_id) {
        return $this->request('DELETE', "/api/v1/organizations/{orgId}/email-providers/{$provider_id}");
    }
    
    /**
     * Test email provider
     */
    public function test_email_provider($provider_id) {
        return $this->request('POST', "/api/v1/organizations/{orgId}/email-providers/{$provider_id}/test");
    }
    
    // ==========================================
    // LOG FORWARDERS API
    // ==========================================
    
    /**
     * List all log forwarders
     */
    public function get_log_forwarders() {
        $result = $this->request('GET', '/api/v1/organizations/{orgId}/log-forwarders');
        return is_wp_error($result) ? $result : ($result['data'] ?? array());
    }
    
    /**
     * Create a log forwarder
     */
    public function create_log_forwarder($data) {
        return $this->request('POST', '/api/v1/organizations/{orgId}/log-forwarders', $data);
    }
    
    /**
     * Update a log forwarder
     */
    public function update_log_forwarder($forwarder_id, $data) {
        return $this->request('PATCH', "/api/v1/organizations/{orgId}/log-forwarders/{$forwarder_id}", $data);
    }
    
    /**
     * Delete a log forwarder
     */
    public function delete_log_forwarder($forwarder_id) {
        return $this->request('DELETE', "/api/v1/organizations/{orgId}/log-forwarders/{$forwarder_id}");
    }
    
    // ==========================================
    // METRICS API
    // ==========================================
    
    /**
     * Get aggregated metrics summary
     */
    public function get_metrics($days = 30) {
        $result = $this->request('GET', "/api/v1/organizations/{orgId}/metrics?days={$days}");
        return is_wp_error($result) ? $result : ($result['data'] ?? array());
    }
    
    /**
     * Get login metrics
     */
    public function get_login_metrics($params = array()) {
        $query = http_build_query($params);
        $endpoint = "/api/v1/organizations/{orgId}/metrics/logins";
        if ($query) {
            $endpoint .= "?{$query}";
        }
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Get provider metrics
     */
    public function get_provider_metrics($days = 30) {
        return $this->request('GET', "/api/v1/organizations/{orgId}/metrics/providers?days={$days}");
    }
    
    /**
     * Get webhook metrics
     */
    public function get_webhook_metrics($days = 30) {
        return $this->request('GET', "/api/v1/organizations/{orgId}/metrics/webhooks?days={$days}");
    }
    
    /**
     * Get integration metrics
     */
    public function get_integration_metrics($days = 30) {
        return $this->request('GET', "/api/v1/organizations/{orgId}/metrics/integrations?days={$days}");
    }
    
    /**
     * Get email metrics
     */
    public function get_email_metrics($days = 30) {
        return $this->request('GET', "/api/v1/organizations/{orgId}/metrics/emails?days={$days}");
    }
    
    /**
     * Get all service metrics
     */
    public function get_service_metrics($days = 30) {
        return $this->request('GET', "/api/v1/organizations/{orgId}/metrics/services?days={$days}");
    }
    
    // ==========================================
    // ORGANIZATIONS API
    // ==========================================
    
    /**
     * Get organization details
     */
    public function get_organization() {
        return $this->request('GET', '/api/v1/organizations/{orgId}');
    }
    
    /**
     * Update organization (redirect URIs)
     */
    public function update_organization($data) {
        return $this->request('PATCH', '/api/v1/organizations/{orgId}', $data);
    }
    
    /**
     * Add redirect URI to organization
     */
    public function add_redirect_uri($uri) {
        $org = $this->get_organization();
        if (is_wp_error($org)) {
            return $org;
        }
        
        $uris = $org['allowedRedirectUris'] ?? array();
        if (!in_array($uri, $uris)) {
            $uris[] = $uri;
            return $this->update_organization(array('allowedRedirectUris' => $uris));
        }
        
        return $org;
    }
}
