<?php
/**
 * EngagePlus Webhooks Management Template
 *
 * @package EngagePlus
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$plugin = engageplus();
$api = $plugin->get_api_client();
$webhooks = $api->get_webhooks();
$error = is_wp_error($webhooks) ? $webhooks->get_error_message() : null;

$webhook_events = array(
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
    'client.created' => __('Client Created', 'engageplus'),
    'client.updated' => __('Client Updated', 'engageplus'),
    'client.deleted' => __('Client Deleted', 'engageplus'),
    'idp_config.created' => __('IDP Config Created', 'engageplus'),
    'idp_config.updated' => __('IDP Config Updated', 'engageplus'),
    'idp_config.deleted' => __('IDP Config Deleted', 'engageplus'),
);
?>

<div class="wrap engageplus-admin-wrap">
    <div class="engageplus-admin-header">
        <div class="engageplus-header-content">
            <h1><?php esc_html_e('Webhooks', 'engageplus'); ?></h1>
            <p><?php esc_html_e('Receive real-time notifications for authentication events.', 'engageplus'); ?></p>
        </div>
        <div class="engageplus-header-actions">
            <button type="button" class="button button-primary" id="engageplus-add-webhook">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_html_e('Add Webhook', 'engageplus'); ?>
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
                    <th class="column-name"><?php esc_html_e('Name', 'engageplus'); ?></th>
                    <th class="column-url"><?php esc_html_e('URL', 'engageplus'); ?></th>
                    <th class="column-events"><?php esc_html_e('Events', 'engageplus'); ?></th>
                    <th class="column-status"><?php esc_html_e('Status', 'engageplus'); ?></th>
                    <th class="column-actions"><?php esc_html_e('Actions', 'engageplus'); ?></th>
                </tr>
            </thead>
            <tbody id="engageplus-webhooks-list">
                <?php if (!$error && !empty($webhooks)) : ?>
                    <?php foreach ($webhooks as $webhook) : ?>
                    <tr data-id="<?php echo esc_attr($webhook['id']); ?>">
                        <td class="column-name">
                            <strong><?php echo esc_html($webhook['name']); ?></strong>
                        </td>
                        <td class="column-url">
                            <code><?php echo esc_html($webhook['webhookUrl']); ?></code>
                        </td>
                        <td class="column-events">
                            <?php 
                            $events = $webhook['events'] ?? array();
                            echo esc_html(count($events) . ' ' . _n('event', 'events', count($events), 'engageplus'));
                            ?>
                        </td>
                        <td class="column-status">
                            <?php if (!empty($webhook['enabled'])) : ?>
                                <span class="engageplus-badge engageplus-badge-success"><?php esc_html_e('Active', 'engageplus'); ?></span>
                            <?php else : ?>
                                <span class="engageplus-badge engageplus-badge-secondary"><?php esc_html_e('Inactive', 'engageplus'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-actions">
                            <button type="button" class="button button-small engageplus-edit-webhook" 
                                    data-webhook='<?php echo esc_attr(wp_json_encode($webhook)); ?>'
                                    title="<?php esc_attr_e('Edit', 'engageplus'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button type="button" class="button button-small engageplus-delete-webhook" 
                                    data-id="<?php echo esc_attr($webhook['id']); ?>"
                                    title="<?php esc_attr_e('Delete', 'engageplus'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="engageplus-no-items">
                        <td colspan="5">
                            <?php esc_html_e('No webhooks configured yet. Click "Add Webhook" to get started.', 'engageplus'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="engageplus-card">
        <h2><?php esc_html_e('WordPress Webhook Endpoint', 'engageplus'); ?></h2>
        <p><?php esc_html_e('You can also receive webhooks directly in WordPress at:', 'engageplus'); ?></p>
        <code class="engageplus-shortcode"><?php echo esc_url(home_url('/wp-json/engageplus/v1/webhook')); ?></code>
    </div>
</div>

<!-- Webhook Modal -->
<div id="engageplus-webhook-modal" class="engageplus-modal" style="display: none;">
    <div class="engageplus-modal-content">
        <div class="engageplus-modal-header">
            <h2 id="engageplus-webhook-modal-title"><?php esc_html_e('Add Webhook', 'engageplus'); ?></h2>
            <button type="button" class="engageplus-modal-close">&times;</button>
        </div>
        <form id="engageplus-webhook-form">
            <input type="hidden" name="webhook_id" id="webhook-id">
            
            <div class="engageplus-form-row">
                <label for="webhook-name"><?php esc_html_e('Name', 'engageplus'); ?></label>
                <input type="text" name="name" id="webhook-name" required placeholder="e.g., Production Webhook">
            </div>
            
            <div class="engageplus-form-row">
                <label for="webhook-url"><?php esc_html_e('Webhook URL', 'engageplus'); ?></label>
                <input type="url" name="webhookUrl" id="webhook-url" required placeholder="https://example.com/webhook">
            </div>
            
            <div class="engageplus-form-row">
                <label><?php esc_html_e('Events', 'engageplus'); ?></label>
                <div class="engageplus-events-grid">
                    <?php foreach ($webhook_events as $event_key => $event_label) : ?>
                    <label class="engageplus-event-checkbox">
                        <input type="checkbox" name="events[]" value="<?php echo esc_attr($event_key); ?>">
                        <?php echo esc_html($event_label); ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="engageplus-form-row">
                <label for="webhook-secret"><?php esc_html_e('Secret (for HMAC signing)', 'engageplus'); ?></label>
                <input type="password" name="authentication[secret]" id="webhook-secret" autocomplete="new-password" placeholder="whsec_...">
                <p class="description"><?php esc_html_e('Optional. Used to sign webhook payloads for verification.', 'engageplus'); ?></p>
            </div>
            
            <div class="engageplus-form-row">
                <label>
                    <input type="checkbox" name="enabled" id="webhook-enabled" value="1" checked>
                    <?php esc_html_e('Enable this webhook', 'engageplus'); ?>
                </label>
            </div>
            
            <div class="engageplus-modal-footer">
                <button type="button" class="button engageplus-modal-cancel"><?php esc_html_e('Cancel', 'engageplus'); ?></button>
                <button type="submit" class="button button-primary"><?php esc_html_e('Save Webhook', 'engageplus'); ?></button>
            </div>
        </form>
    </div>
</div>
