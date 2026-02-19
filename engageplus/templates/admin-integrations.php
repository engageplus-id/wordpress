<?php
/**
 * EngagePlus Data Integrations Management Template
 *
 * @package EngagePlus
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$plugin = engageplus();
$api = $plugin->get_api_client();
$integrations = $api->get_integrations();
$error = is_wp_error($integrations) ? $integrations->get_error_message() : null;

$integration_types = array(
    'supabase' => array(
        'name' => 'Supabase',
        'description' => __('Sync user data to Supabase database', 'engageplus'),
    ),
    'airtable' => array(
        'name' => 'Airtable',
        'description' => __('Sync user data to Airtable base', 'engageplus'),
    ),
);
?>

<div class="wrap engageplus-admin-wrap">
    <div class="engageplus-admin-header">
        <div class="engageplus-header-content">
            <h1><?php esc_html_e('Data Integrations', 'engageplus'); ?></h1>
            <p><?php esc_html_e('Sync user data to external services like Supabase or Airtable.', 'engageplus'); ?></p>
        </div>
        <div class="engageplus-header-actions">
            <button type="button" class="button button-primary" id="engageplus-add-integration">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_html_e('Add Integration', 'engageplus'); ?>
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
                    <th class="column-name"><?php esc_html_e('Integration', 'engageplus'); ?></th>
                    <th class="column-type"><?php esc_html_e('Type', 'engageplus'); ?></th>
                    <th class="column-settings"><?php esc_html_e('Settings', 'engageplus'); ?></th>
                    <th class="column-status"><?php esc_html_e('Status', 'engageplus'); ?></th>
                    <th class="column-actions"><?php esc_html_e('Actions', 'engageplus'); ?></th>
                </tr>
            </thead>
            <tbody id="engageplus-integrations-list">
                <?php if (!$error && !empty($integrations)) : ?>
                    <?php foreach ($integrations as $integration) : ?>
                    <tr data-id="<?php echo esc_attr($integration['id']); ?>">
                        <td class="column-name">
                            <strong><?php echo esc_html($integration['name']); ?></strong>
                        </td>
                        <td class="column-type">
                            <?php echo esc_html($integration_types[$integration['type']]['name'] ?? ucfirst($integration['type'])); ?>
                        </td>
                        <td class="column-settings">
                            <?php 
                            $settings = array();
                            if (!empty($integration['storeUserProfiles'])) $settings[] = __('Users', 'engageplus');
                            if (!empty($integration['handleEvents'])) $settings[] = __('Events', 'engageplus');
                            echo esc_html(implode(', ', $settings) ?: '—');
                            ?>
                        </td>
                        <td class="column-status">
                            <?php if (!empty($integration['enabled'])) : ?>
                                <span class="engageplus-badge engageplus-badge-success"><?php esc_html_e('Active', 'engageplus'); ?></span>
                            <?php else : ?>
                                <span class="engageplus-badge engageplus-badge-secondary"><?php esc_html_e('Inactive', 'engageplus'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-actions">
                            <button type="button" class="button button-small engageplus-edit-integration" 
                                    data-integration='<?php echo esc_attr(wp_json_encode($integration)); ?>'
                                    title="<?php esc_attr_e('Edit', 'engageplus'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button type="button" class="button button-small engageplus-delete-integration" 
                                    data-id="<?php echo esc_attr($integration['id']); ?>"
                                    title="<?php esc_attr_e('Delete', 'engageplus'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="engageplus-no-items">
                        <td colspan="5">
                            <?php esc_html_e('No integrations configured. Add an integration to sync user data to external services.', 'engageplus'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="engageplus-integrations-grid">
        <?php foreach ($integration_types as $key => $type) : ?>
        <div class="engageplus-card engageplus-integration-card">
            <h3><?php echo esc_html($type['name']); ?></h3>
            <p><?php echo esc_html($type['description']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Integration Modal -->
<div id="engageplus-integration-modal" class="engageplus-modal" style="display: none;">
    <div class="engageplus-modal-content">
        <div class="engageplus-modal-header">
            <h2 id="engageplus-integration-modal-title"><?php esc_html_e('Add Integration', 'engageplus'); ?></h2>
            <button type="button" class="engageplus-modal-close">&times;</button>
        </div>
        <form id="engageplus-integration-form">
            <input type="hidden" name="integration_id" id="integration-id">
            
            <div class="engageplus-form-row">
                <label for="integration-type"><?php esc_html_e('Integration Type', 'engageplus'); ?></label>
                <select name="type" id="integration-type" required>
                    <?php foreach ($integration_types as $key => $type) : ?>
                    <option value="<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($type['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="engageplus-form-row">
                <label for="integration-name"><?php esc_html_e('Display Name', 'engageplus'); ?></label>
                <input type="text" name="name" id="integration-name" required placeholder="e.g., User Database">
            </div>
            
            <!-- Supabase Fields -->
            <div class="engageplus-form-row engageplus-integration-field" data-types="supabase">
                <label for="integration-project-url"><?php esc_html_e('Project URL', 'engageplus'); ?></label>
                <input type="url" name="settings[projectUrl]" id="integration-project-url" placeholder="https://xxx.supabase.co">
            </div>
            
            <div class="engageplus-form-row engageplus-integration-field" data-types="supabase">
                <label for="integration-anon-key"><?php esc_html_e('Anon Key', 'engageplus'); ?></label>
                <input type="password" name="credentials[apiKey]" id="integration-anon-key" autocomplete="new-password">
            </div>
            
            <div class="engageplus-form-row engageplus-integration-field" data-types="supabase">
                <label for="integration-service-key"><?php esc_html_e('Service Role Key', 'engageplus'); ?></label>
                <input type="password" name="credentials[serviceRoleKey]" id="integration-service-key" autocomplete="new-password">
            </div>
            
            <div class="engageplus-form-row engageplus-integration-field" data-types="supabase">
                <label for="integration-users-table"><?php esc_html_e('Users Table', 'engageplus'); ?></label>
                <input type="text" name="settings[usersTable]" id="integration-users-table" value="oidc_users">
            </div>
            
            <div class="engageplus-form-row engageplus-integration-field" data-types="supabase">
                <label for="integration-events-table"><?php esc_html_e('Events Table', 'engageplus'); ?></label>
                <input type="text" name="settings[eventsTable]" id="integration-events-table" value="oidc_auth_events">
            </div>
            
            <!-- Airtable Fields -->
            <div class="engageplus-form-row engageplus-integration-field" data-types="airtable">
                <label for="integration-airtable-key"><?php esc_html_e('API Key', 'engageplus'); ?></label>
                <input type="password" name="credentials[apiKey]" id="integration-airtable-key" autocomplete="new-password">
            </div>
            
            <div class="engageplus-form-row engageplus-integration-field" data-types="airtable">
                <label for="integration-base-id"><?php esc_html_e('Base ID', 'engageplus'); ?></label>
                <input type="text" name="settings[baseId]" id="integration-base-id" placeholder="appXXXXXXXXXXXXXX">
            </div>
            
            <div class="engageplus-form-row engageplus-integration-field" data-types="airtable">
                <label for="integration-table-name"><?php esc_html_e('Table Name', 'engageplus'); ?></label>
                <input type="text" name="settings[tableName]" id="integration-table-name" placeholder="Users">
            </div>
            
            <!-- Common Settings -->
            <div class="engageplus-form-row">
                <label>
                    <input type="checkbox" name="storeUserProfiles" id="integration-store-users" value="1" checked>
                    <?php esc_html_e('Store user profiles', 'engageplus'); ?>
                </label>
            </div>
            
            <div class="engageplus-form-row">
                <label>
                    <input type="checkbox" name="handleEvents" id="integration-handle-events" value="1" checked>
                    <?php esc_html_e('Record auth events', 'engageplus'); ?>
                </label>
            </div>
            
            <div class="engageplus-form-row">
                <label>
                    <input type="checkbox" name="includePii" id="integration-include-pii" value="1">
                    <?php esc_html_e('Include PII (email, name)', 'engageplus'); ?>
                </label>
            </div>
            
            <div class="engageplus-modal-footer">
                <button type="button" class="button engageplus-modal-cancel"><?php esc_html_e('Cancel', 'engageplus'); ?></button>
                <button type="submit" class="button button-primary"><?php esc_html_e('Save Integration', 'engageplus'); ?></button>
            </div>
        </form>
    </div>
</div>
