<?php
/**
 * EngagePlus Providers Management Template
 *
 * @package EngagePlus
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$plugin = engageplus();
$api = $plugin->get_api_client();
$providers = $api->get_providers();
$error = is_wp_error($providers) ? $providers->get_error_message() : null;

$provider_types = array(
    'google' => array('name' => 'Google', 'issuer' => 'https://accounts.google.com'),
    'microsoft' => array('name' => 'Microsoft', 'issuer' => 'https://login.microsoftonline.com/common/v2.0'),
    'github' => array('name' => 'GitHub', 'issuer' => 'https://github.com'),
    'apple' => array('name' => 'Apple', 'issuer' => 'https://appleid.apple.com'),
    'facebook' => array('name' => 'Facebook', 'issuer' => 'https://www.facebook.com'),
    'linkedin' => array('name' => 'LinkedIn', 'issuer' => 'https://www.linkedin.com'),
    'discord' => array('name' => 'Discord', 'issuer' => 'https://discord.com'),
    'custom' => array('name' => 'Custom OIDC', 'issuer' => ''),
);
?>

<div class="wrap engageplus-admin-wrap">
    <div class="engageplus-admin-header">
        <div class="engageplus-header-content">
            <h1><?php esc_html_e('Identity Providers', 'engageplus'); ?></h1>
            <p><?php esc_html_e('Manage OIDC identity providers for authentication (Google, Microsoft, GitHub, etc.).', 'engageplus'); ?></p>
        </div>
        <div class="engageplus-header-actions">
            <button type="button" class="button button-primary" id="engageplus-add-provider">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_html_e('Add Provider', 'engageplus'); ?>
            </button>
        </div>
    </div>
    
    <?php if ($error) : ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="engageplus-card">
        <table class="wp-list-table widefat fixed striped engageplus-providers-table">
            <thead>
                <tr>
                    <th class="column-name"><?php esc_html_e('Provider', 'engageplus'); ?></th>
                    <th class="column-type"><?php esc_html_e('Type', 'engageplus'); ?></th>
                    <th class="column-status"><?php esc_html_e('Status', 'engageplus'); ?></th>
                    <th class="column-issuer"><?php esc_html_e('Issuer URL', 'engageplus'); ?></th>
                    <th class="column-actions"><?php esc_html_e('Actions', 'engageplus'); ?></th>
                </tr>
            </thead>
            <tbody id="engageplus-providers-list">
                <?php if (!$error && !empty($providers)) : ?>
                    <?php foreach ($providers as $provider) : ?>
                    <tr data-id="<?php echo esc_attr($provider['id']); ?>">
                        <td class="column-name">
                            <strong><?php echo esc_html($provider['name']); ?></strong>
                        </td>
                        <td class="column-type">
                            <?php echo esc_html(ucfirst($provider['type'] ?? 'custom')); ?>
                        </td>
                        <td class="column-status">
                            <?php if (!empty($provider['enabled'])) : ?>
                                <span class="engageplus-badge engageplus-badge-success"><?php esc_html_e('Enabled', 'engageplus'); ?></span>
                            <?php else : ?>
                                <span class="engageplus-badge engageplus-badge-secondary"><?php esc_html_e('Disabled', 'engageplus'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-issuer">
                            <code><?php echo esc_html($provider['issuerUrl'] ?? '—'); ?></code>
                        </td>
                        <td class="column-actions">
                            <button type="button" class="button button-small engageplus-test-provider" 
                                    data-id="<?php echo esc_attr($provider['id']); ?>"
                                    title="<?php esc_attr_e('Test Connection', 'engageplus'); ?>">
                                <span class="dashicons dashicons-admin-site"></span>
                            </button>
                            <button type="button" class="button button-small engageplus-edit-provider" 
                                    data-provider='<?php echo esc_attr(wp_json_encode($provider)); ?>'
                                    title="<?php esc_attr_e('Edit', 'engageplus'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button type="button" class="button button-small engageplus-delete-provider" 
                                    data-id="<?php echo esc_attr($provider['id']); ?>"
                                    title="<?php esc_attr_e('Delete', 'engageplus'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="engageplus-no-items">
                        <td colspan="5">
                            <?php esc_html_e('No providers configured yet. Click "Add Provider" to get started.', 'engageplus'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="engageplus-card">
        <h2><?php esc_html_e('Redirect URI', 'engageplus'); ?></h2>
        <p><?php esc_html_e('When configuring your identity provider, use this redirect URI:', 'engageplus'); ?></p>
        <code class="engageplus-shortcode">https://auth.engageplus.id/oauth/callback</code>
    </div>
</div>

<!-- Provider Modal -->
<div id="engageplus-provider-modal" class="engageplus-modal" style="display: none;">
    <div class="engageplus-modal-content">
        <div class="engageplus-modal-header">
            <h2 id="engageplus-provider-modal-title"><?php esc_html_e('Add Provider', 'engageplus'); ?></h2>
            <button type="button" class="engageplus-modal-close">&times;</button>
        </div>
        <form id="engageplus-provider-form">
            <input type="hidden" name="provider_id" id="provider-id">
            
            <div class="engageplus-form-row">
                <label for="provider-type"><?php esc_html_e('Provider Type', 'engageplus'); ?></label>
                <select name="type" id="provider-type" required>
                    <?php foreach ($provider_types as $key => $type) : ?>
                    <option value="<?php echo esc_attr($key); ?>" data-issuer="<?php echo esc_attr($type['issuer']); ?>">
                        <?php echo esc_html($type['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="engageplus-form-row">
                <label for="provider-name"><?php esc_html_e('Display Name', 'engageplus'); ?></label>
                <input type="text" name="name" id="provider-name" required placeholder="e.g., Google Login">
            </div>
            
            <div class="engageplus-form-row" id="issuer-row">
                <label for="provider-issuer"><?php esc_html_e('Issuer URL', 'engageplus'); ?></label>
                <input type="url" name="issuerUrl" id="provider-issuer" placeholder="https://...">
                <p class="description"><?php esc_html_e('The OIDC discovery URL (automatically set for common providers).', 'engageplus'); ?></p>
            </div>
            
            <div class="engageplus-form-row">
                <label for="provider-client-id"><?php esc_html_e('Client ID', 'engageplus'); ?></label>
                <input type="text" name="clientId" id="provider-client-id" required>
                <p class="description"><?php esc_html_e('From the identity provider\'s developer console.', 'engageplus'); ?></p>
            </div>
            
            <div class="engageplus-form-row">
                <label for="provider-client-secret"><?php esc_html_e('Client Secret', 'engageplus'); ?></label>
                <input type="password" name="clientSecret" id="provider-client-secret" autocomplete="new-password">
                <p class="description"><?php esc_html_e('From the identity provider\'s developer console.', 'engageplus'); ?></p>
            </div>
            
            <div class="engageplus-form-row">
                <label for="provider-scopes"><?php esc_html_e('Scopes', 'engageplus'); ?></label>
                <input type="text" name="scopes" id="provider-scopes" value="openid profile email">
                <p class="description"><?php esc_html_e('Space-separated list of OAuth scopes.', 'engageplus'); ?></p>
            </div>
            
            <div class="engageplus-form-row">
                <label>
                    <input type="checkbox" name="enabled" id="provider-enabled" value="1" checked>
                    <?php esc_html_e('Enable this provider', 'engageplus'); ?>
                </label>
            </div>
            
            <div class="engageplus-modal-footer">
                <button type="button" class="button engageplus-modal-cancel"><?php esc_html_e('Cancel', 'engageplus'); ?></button>
                <button type="submit" class="button button-primary"><?php esc_html_e('Save Provider', 'engageplus'); ?></button>
            </div>
        </form>
    </div>
</div>
