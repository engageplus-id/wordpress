<?php
/**
 * EngagePlus Admin Settings Page Template
 *
 * @package EngagePlus
 * @since 1.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin = engageplus();
$org_id = $plugin->get_setting('org_id');
$api_key = $plugin->get_setting('api_key');
?>

<div class="wrap engageplus-admin-wrap">
    
    <!-- Header -->
    <div class="engageplus-admin-header">
        <div class="engageplus-header-content">
            <h1><?php esc_html_e('EngagePlus Settings', 'engageplus'); ?></h1>
            <p><?php esc_html_e('Configure social login for your WordPress site using any OIDC provider.', 'engageplus'); ?></p>
        </div>
    </div>
    
    <!-- Status -->
    <div class="engageplus-card">
        <h2><?php esc_html_e('Status', 'engageplus'); ?></h2>
        <div style="display: flex; gap: 16px; flex-wrap: wrap;">
            <?php if ($org_id) : ?>
                <span class="engageplus-badge engageplus-badge-success">
                    <?php esc_html_e('Organization ID ✓', 'engageplus'); ?>
                </span>
            <?php else : ?>
                <span class="engageplus-badge engageplus-badge-secondary">
                    <?php esc_html_e('Organization ID Required', 'engageplus'); ?>
                </span>
            <?php endif; ?>
            
            <?php if ($api_key) : ?>
                <span class="engageplus-badge engageplus-badge-success">
                    <?php esc_html_e('API Key ✓', 'engageplus'); ?>
                </span>
            <?php else : ?>
                <span class="engageplus-badge engageplus-badge-secondary">
                    <?php esc_html_e('API Key Optional', 'engageplus'); ?>
                </span>
            <?php endif; ?>
        </div>
        
        <?php if (!$api_key) : ?>
        <p style="margin-top: 12px; color: #6b7280; font-size: 13px;">
            <?php esc_html_e('Add an API Key to unlock Management features: configure providers, widget styling, webhooks, and more from WordPress.', 'engageplus'); ?>
        </p>
        <?php endif; ?>
    </div>
    
    <!-- Settings Form -->
    <div class="engageplus-card">
        <form method="post" action="options.php" class="engageplus-settings-form">
            <?php
            settings_fields('engageplus_settings_group');
            do_settings_sections('engageplus');
            submit_button(__('Save Settings', 'engageplus'));
            ?>
        </form>
    </div>
    
    <!-- Usage Info -->
    <div class="engageplus-card">
        <h2><?php esc_html_e('Widget Integration', 'engageplus'); ?></h2>
        <p><?php esc_html_e('Use the shortcode anywhere in your content, or add the "EngagePlus Login" widget to any widget area.', 'engageplus'); ?></p>
        
        <h3><?php esc_html_e('Shortcode', 'engageplus'); ?></h3>
        <code class="engageplus-shortcode">[engageplus]</code>
        
        <h3><?php esc_html_e('Options', 'engageplus'); ?></h3>
        <ul style="margin-left: 20px;">
            <li><code>[engageplus hide_logged_in="false"]</code> - <?php esc_html_e('Show for logged-in users', 'engageplus'); ?></li>
            <li><code>[engageplus show_logout="true"]</code> - <?php esc_html_e('Show logout button when logged in', 'engageplus'); ?></li>
        </ul>
        
        <h3><?php esc_html_e('Redirect URI', 'engageplus'); ?></h3>
        <p><?php esc_html_e('Add this URL as a redirect URI in your EngagePlus dashboard:', 'engageplus'); ?></p>
        <code class="engageplus-shortcode"><?php echo esc_url($plugin->get_callback_url()); ?></code>
    </div>
    
    <?php if ($api_key) : ?>
    <div class="engageplus-card">
        <h2><?php esc_html_e('Management Features', 'engageplus'); ?></h2>
        <p><?php esc_html_e('With your API Key configured, you can manage EngagePlus directly from WordPress:', 'engageplus'); ?></p>
        <div class="engageplus-quick-actions" style="margin-top: 16px;">
            <a href="<?php echo esc_url(admin_url('admin.php?page=engageplus-providers')); ?>" class="engageplus-action-card">
                <span class="dashicons dashicons-networking"></span>
                <span class="action-title"><?php esc_html_e('Providers', 'engageplus'); ?></span>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=engageplus-widget')); ?>" class="engageplus-action-card">
                <span class="dashicons dashicons-admin-appearance"></span>
                <span class="action-title"><?php esc_html_e('Widget', 'engageplus'); ?></span>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=engageplus-webhooks')); ?>" class="engageplus-action-card">
                <span class="dashicons dashicons-rss"></span>
                <span class="action-title"><?php esc_html_e('Webhooks', 'engageplus'); ?></span>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=engageplus-metrics')); ?>" class="engageplus-action-card">
                <span class="dashicons dashicons-chart-bar"></span>
                <span class="action-title"><?php esc_html_e('Metrics', 'engageplus'); ?></span>
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Quick Links -->
    <div class="engageplus-card">
        <h2><?php esc_html_e('Resources', 'engageplus'); ?></h2>
        <div class="engageplus-quick-links">
            <a href="https://engageplus.id" target="_blank" rel="noopener" class="engageplus-quick-link">
                <span class="dashicons dashicons-external"></span>
                <?php esc_html_e('EngagePlus Dashboard', 'engageplus'); ?>
            </a>
            <a href="https://engageplus.id/docs" target="_blank" rel="noopener" class="engageplus-quick-link">
                <span class="dashicons dashicons-book"></span>
                <?php esc_html_e('Documentation', 'engageplus'); ?>
            </a>
            <a href="https://engageplus.id/docs/api" target="_blank" rel="noopener" class="engageplus-quick-link">
                <span class="dashicons dashicons-rest-api"></span>
                <?php esc_html_e('API Reference', 'engageplus'); ?>
            </a>
            <a href="https://github.com/engageplus-id/wordpress" target="_blank" rel="noopener" class="engageplus-quick-link">
                <span class="dashicons dashicons-editor-code"></span>
                <?php esc_html_e('GitHub', 'engageplus'); ?>
            </a>
        </div>
    </div>
    
</div>
