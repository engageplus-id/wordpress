<?php
/**
 * EngagePlus Email Providers Management Template
 *
 * @package EngagePlus
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$plugin = engageplus();
$api = $plugin->get_api_client();
$email_providers = $api->get_email_providers();
$error = is_wp_error($email_providers) ? $email_providers->get_error_message() : null;

$provider_types = array(
    'sendgrid' => array(
        'name' => 'SendGrid',
        'fields' => array('apiKey'),
    ),
    'mailgun' => array(
        'name' => 'Mailgun',
        'fields' => array('apiKey', 'domain'),
    ),
    'postmark' => array(
        'name' => 'Postmark',
        'fields' => array('serverToken'),
    ),
    'ses' => array(
        'name' => 'Amazon SES',
        'fields' => array('accessKeyId', 'secretAccessKey', 'region'),
    ),
    'smtp' => array(
        'name' => 'SMTP',
        'fields' => array('host', 'port', 'username', 'password', 'secure'),
    ),
);
?>

<div class="wrap engageplus-admin-wrap">
    <div class="engageplus-admin-header">
        <div class="engageplus-header-content">
            <h1><?php esc_html_e('Email Providers', 'engageplus'); ?></h1>
            <p><?php esc_html_e('Configure email delivery for OTP authentication and notifications.', 'engageplus'); ?></p>
        </div>
        <div class="engageplus-header-actions">
            <button type="button" class="button button-primary" id="engageplus-add-email-provider">
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
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="column-name"><?php esc_html_e('Provider', 'engageplus'); ?></th>
                    <th class="column-type"><?php esc_html_e('Type', 'engageplus'); ?></th>
                    <th class="column-from"><?php esc_html_e('From Address', 'engageplus'); ?></th>
                    <th class="column-default"><?php esc_html_e('Default', 'engageplus'); ?></th>
                    <th class="column-actions"><?php esc_html_e('Actions', 'engageplus'); ?></th>
                </tr>
            </thead>
            <tbody id="engageplus-email-providers-list">
                <?php if (!$error && !empty($email_providers)) : ?>
                    <?php foreach ($email_providers as $provider) : ?>
                    <tr data-id="<?php echo esc_attr($provider['id']); ?>">
                        <td class="column-name">
                            <strong><?php echo esc_html($provider['name']); ?></strong>
                        </td>
                        <td class="column-type">
                            <?php echo esc_html($provider_types[$provider['type']]['name'] ?? ucfirst($provider['type'])); ?>
                        </td>
                        <td class="column-from">
                            <code><?php echo esc_html($provider['config']['fromAddress'] ?? '—'); ?></code>
                        </td>
                        <td class="column-default">
                            <?php if (!empty($provider['isDefault'])) : ?>
                                <span class="engageplus-badge engageplus-badge-success"><?php esc_html_e('Default', 'engageplus'); ?></span>
                            <?php else : ?>
                                <span class="engageplus-badge engageplus-badge-secondary">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="column-actions">
                            <button type="button" class="button button-small engageplus-test-email-provider" 
                                    data-id="<?php echo esc_attr($provider['id']); ?>"
                                    title="<?php esc_attr_e('Test', 'engageplus'); ?>">
                                <span class="dashicons dashicons-email"></span>
                            </button>
                            <button type="button" class="button button-small engageplus-edit-email-provider" 
                                    data-provider='<?php echo esc_attr(wp_json_encode($provider)); ?>'
                                    title="<?php esc_attr_e('Edit', 'engageplus'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button type="button" class="button button-small engageplus-delete-email-provider" 
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
                            <?php esc_html_e('No email providers configured. Email OTP will not work until a provider is added.', 'engageplus'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="engageplus-card">
        <h2><?php esc_html_e('Supported Providers', 'engageplus'); ?></h2>
        <div class="engageplus-provider-grid">
            <?php foreach ($provider_types as $key => $type) : ?>
            <div class="engageplus-provider-item">
                <strong><?php echo esc_html($type['name']); ?></strong>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Email Provider Modal -->
<div id="engageplus-email-provider-modal" class="engageplus-modal" style="display: none;">
    <div class="engageplus-modal-content">
        <div class="engageplus-modal-header">
            <h2 id="engageplus-email-provider-modal-title"><?php esc_html_e('Add Email Provider', 'engageplus'); ?></h2>
            <button type="button" class="engageplus-modal-close">&times;</button>
        </div>
        <form id="engageplus-email-provider-form">
            <input type="hidden" name="email_provider_id" id="email-provider-id">
            
            <div class="engageplus-form-row">
                <label for="email-provider-type"><?php esc_html_e('Provider Type', 'engageplus'); ?></label>
                <select name="type" id="email-provider-type" required>
                    <?php foreach ($provider_types as $key => $type) : ?>
                    <option value="<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($type['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="engageplus-form-row">
                <label for="email-provider-name"><?php esc_html_e('Display Name', 'engageplus'); ?></label>
                <input type="text" name="name" id="email-provider-name" required placeholder="e.g., Production Email">
            </div>
            
            <div class="engageplus-form-row">
                <label for="email-provider-from-address"><?php esc_html_e('From Address', 'engageplus'); ?></label>
                <input type="email" name="config[fromAddress]" id="email-provider-from-address" required placeholder="noreply@example.com">
            </div>
            
            <div class="engageplus-form-row">
                <label for="email-provider-from-name"><?php esc_html_e('From Name', 'engageplus'); ?></label>
                <input type="text" name="config[fromName]" id="email-provider-from-name" placeholder="My App">
            </div>
            
            <!-- SendGrid / Mailgun / Postmark: API Key -->
            <div class="engageplus-form-row engageplus-provider-field" data-providers="sendgrid,mailgun,postmark">
                <label for="email-provider-api-key"><?php esc_html_e('API Key', 'engageplus'); ?></label>
                <input type="password" name="config[apiKey]" id="email-provider-api-key" autocomplete="new-password">
            </div>
            
            <!-- Mailgun: Domain -->
            <div class="engageplus-form-row engageplus-provider-field" data-providers="mailgun">
                <label for="email-provider-domain"><?php esc_html_e('Domain', 'engageplus'); ?></label>
                <input type="text" name="config[domain]" id="email-provider-domain" placeholder="mg.example.com">
            </div>
            
            <!-- SES -->
            <div class="engageplus-form-row engageplus-provider-field" data-providers="ses">
                <label for="email-provider-access-key"><?php esc_html_e('Access Key ID', 'engageplus'); ?></label>
                <input type="text" name="config[accessKeyId]" id="email-provider-access-key">
            </div>
            
            <div class="engageplus-form-row engageplus-provider-field" data-providers="ses">
                <label for="email-provider-secret-key"><?php esc_html_e('Secret Access Key', 'engageplus'); ?></label>
                <input type="password" name="config[secretAccessKey]" id="email-provider-secret-key" autocomplete="new-password">
            </div>
            
            <div class="engageplus-form-row engageplus-provider-field" data-providers="ses">
                <label for="email-provider-region"><?php esc_html_e('Region', 'engageplus'); ?></label>
                <input type="text" name="config[region]" id="email-provider-region" placeholder="us-east-1">
            </div>
            
            <!-- SMTP -->
            <div class="engageplus-form-row engageplus-provider-field" data-providers="smtp">
                <label for="email-provider-host"><?php esc_html_e('SMTP Host', 'engageplus'); ?></label>
                <input type="text" name="config[host]" id="email-provider-host" placeholder="smtp.example.com">
            </div>
            
            <div class="engageplus-form-row engageplus-provider-field" data-providers="smtp">
                <label for="email-provider-port"><?php esc_html_e('SMTP Port', 'engageplus'); ?></label>
                <input type="number" name="config[port]" id="email-provider-port" value="587" min="1" max="65535">
            </div>
            
            <div class="engageplus-form-row engageplus-provider-field" data-providers="smtp">
                <label for="email-provider-username"><?php esc_html_e('Username', 'engageplus'); ?></label>
                <input type="text" name="config[username]" id="email-provider-username">
            </div>
            
            <div class="engageplus-form-row engageplus-provider-field" data-providers="smtp">
                <label for="email-provider-password"><?php esc_html_e('Password', 'engageplus'); ?></label>
                <input type="password" name="config[password]" id="email-provider-password" autocomplete="new-password">
            </div>
            
            <div class="engageplus-form-row engageplus-provider-field" data-providers="smtp">
                <label>
                    <input type="checkbox" name="config[secure]" id="email-provider-secure" value="1" checked>
                    <?php esc_html_e('Use TLS', 'engageplus'); ?>
                </label>
            </div>
            
            <div class="engageplus-form-row">
                <label>
                    <input type="checkbox" name="isDefault" id="email-provider-default" value="1">
                    <?php esc_html_e('Set as default provider', 'engageplus'); ?>
                </label>
            </div>
            
            <div class="engageplus-modal-footer">
                <button type="button" class="button engageplus-modal-cancel"><?php esc_html_e('Cancel', 'engageplus'); ?></button>
                <button type="submit" class="button button-primary"><?php esc_html_e('Save Provider', 'engageplus'); ?></button>
            </div>
        </form>
    </div>
</div>
