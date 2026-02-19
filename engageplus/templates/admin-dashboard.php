<?php
/**
 * EngagePlus Admin Dashboard Template
 *
 * @package EngagePlus
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$plugin = engageplus();
$org_id = $plugin->get_setting('org_id');
$api_key = $plugin->get_setting('api_key');
$api_configured = !empty($org_id) && !empty($api_key);

// Get metrics if API is configured
$metrics = null;
$organization = null;
if ($api_configured) {
    $api = $plugin->get_api_client();
    $metrics = $api->get_metrics(30);
    $organization = $api->get_organization();
}
?>

<div class="wrap engageplus-admin-wrap">
    <div class="engageplus-admin-header">
        <div class="engageplus-header-content">
            <h1><?php esc_html_e('EngagePlus Dashboard', 'engageplus'); ?></h1>
            <p><?php esc_html_e('Manage your authentication providers, widget configuration, and view analytics.', 'engageplus'); ?></p>
        </div>
    </div>
    
    <?php if (!$org_id) : ?>
        <div class="engageplus-notice engageplus-notice-warning">
            <h3><?php esc_html_e('Setup Required', 'engageplus'); ?></h3>
            <p><?php esc_html_e('Please configure your Organization ID to enable the login widget.', 'engageplus'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=engageplus-settings')); ?>" class="button button-primary">
                <?php esc_html_e('Go to Settings', 'engageplus'); ?>
            </a>
        </div>
    <?php elseif (!$api_key) : ?>
        <div class="engageplus-notice engageplus-notice-info">
            <h3><?php esc_html_e('Enable Management Features', 'engageplus'); ?></h3>
            <p><?php esc_html_e('Add your API Key to manage providers, widget styling, webhooks, and more directly from WordPress.', 'engageplus'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=engageplus-settings')); ?>" class="button button-primary">
                <?php esc_html_e('Add API Key', 'engageplus'); ?>
            </a>
            <a href="https://engageplus.id/docs/api" target="_blank" rel="noopener" class="button">
                <?php esc_html_e('View API Docs', 'engageplus'); ?>
            </a>
        </div>
    <?php endif; ?>
    
    <?php if ($api_configured) : ?>
        <!-- Organization Info -->
        <?php if ($organization && !is_wp_error($organization)) : ?>
        <div class="engageplus-card">
            <h2><?php esc_html_e('Organization', 'engageplus'); ?></h2>
            <div class="engageplus-org-info">
                <div class="engageplus-org-detail">
                    <span class="label"><?php esc_html_e('Name', 'engageplus'); ?></span>
                    <span class="value"><?php echo esc_html($organization['displayName'] ?? $organization['name'] ?? 'N/A'); ?></span>
                </div>
                <div class="engageplus-org-detail">
                    <span class="label"><?php esc_html_e('Tier', 'engageplus'); ?></span>
                    <span class="value engageplus-badge"><?php echo esc_html(ucfirst($organization['tier'] ?? 'free')); ?></span>
                </div>
                <div class="engageplus-org-detail">
                    <span class="label"><?php esc_html_e('Status', 'engageplus'); ?></span>
                    <span class="value engageplus-badge engageplus-badge-success"><?php echo esc_html(ucfirst($organization['status'] ?? 'active')); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Metrics Summary -->
        <?php if ($metrics && !is_wp_error($metrics)) : ?>
        <div class="engageplus-metrics-grid">
            <div class="engageplus-metric-card">
                <div class="engageplus-metric-value"><?php echo esc_html(number_format($metrics['totalLogins'] ?? 0)); ?></div>
                <div class="engageplus-metric-label"><?php esc_html_e('Total Logins (30 days)', 'engageplus'); ?></div>
            </div>
            <div class="engageplus-metric-card">
                <div class="engageplus-metric-value"><?php echo esc_html(number_format($metrics['dailyAverage'] ?? 0, 1)); ?></div>
                <div class="engageplus-metric-label"><?php esc_html_e('Daily Average', 'engageplus'); ?></div>
            </div>
            <?php if (isset($metrics['services']['webhooks'])) : ?>
            <div class="engageplus-metric-card">
                <div class="engageplus-metric-value"><?php echo esc_html($metrics['services']['webhooks']['successRate'] ?? 0); ?>%</div>
                <div class="engageplus-metric-label"><?php esc_html_e('Webhook Success Rate', 'engageplus'); ?></div>
            </div>
            <?php endif; ?>
            <?php if (isset($metrics['services']['emails'])) : ?>
            <div class="engageplus-metric-card">
                <div class="engageplus-metric-value"><?php echo esc_html($metrics['services']['emails']['successRate'] ?? 0); ?>%</div>
                <div class="engageplus-metric-label"><?php esc_html_e('Email Delivery Rate', 'engageplus'); ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Provider Breakdown -->
        <?php if (!empty($metrics['byProvider'])) : ?>
        <div class="engageplus-card">
            <h2><?php esc_html_e('Logins by Provider', 'engageplus'); ?></h2>
            <div class="engageplus-provider-stats">
                <?php foreach ($metrics['byProvider'] as $provider => $count) : ?>
                <div class="engageplus-provider-stat">
                    <span class="provider-name"><?php echo esc_html(ucfirst($provider)); ?></span>
                    <span class="provider-count"><?php echo esc_html(number_format($count)); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="engageplus-card-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=engageplus-metrics')); ?>">
                    <?php esc_html_e('View detailed metrics →', 'engageplus'); ?>
                </a>
            </p>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        
    <?php endif; ?>
    
    <!-- Quick Actions -->
    <div class="engageplus-card">
        <h2><?php esc_html_e('Quick Actions', 'engageplus'); ?></h2>
        <div class="engageplus-quick-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=engageplus-settings')); ?>" class="engageplus-action-card">
                <span class="dashicons dashicons-admin-generic"></span>
                <span class="action-title"><?php esc_html_e('Settings', 'engageplus'); ?></span>
                <span class="action-desc"><?php esc_html_e('Configure plugin settings', 'engageplus'); ?></span>
            </a>
            
            <?php if ($api_configured) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=engageplus-providers')); ?>" class="engageplus-action-card">
                <span class="dashicons dashicons-networking"></span>
                <span class="action-title"><?php esc_html_e('Providers', 'engageplus'); ?></span>
                <span class="action-desc"><?php esc_html_e('Manage OIDC providers', 'engageplus'); ?></span>
            </a>
            
            <a href="<?php echo esc_url(admin_url('admin.php?page=engageplus-widget')); ?>" class="engageplus-action-card">
                <span class="dashicons dashicons-admin-appearance"></span>
                <span class="action-title"><?php esc_html_e('Widget', 'engageplus'); ?></span>
                <span class="action-desc"><?php esc_html_e('Customize appearance', 'engageplus'); ?></span>
            </a>
            
            <a href="<?php echo esc_url(admin_url('admin.php?page=engageplus-webhooks')); ?>" class="engageplus-action-card">
                <span class="dashicons dashicons-rss"></span>
                <span class="action-title"><?php esc_html_e('Webhooks', 'engageplus'); ?></span>
                <span class="action-desc"><?php esc_html_e('Configure event notifications', 'engageplus'); ?></span>
            </a>
            <?php endif; ?>
            
            <a href="https://engageplus.id" target="_blank" rel="noopener" class="engageplus-action-card">
                <span class="dashicons dashicons-external"></span>
                <span class="action-title"><?php esc_html_e('Dashboard', 'engageplus'); ?></span>
                <span class="action-desc"><?php esc_html_e('Open EngagePlus dashboard', 'engageplus'); ?></span>
            </a>
            
            <a href="https://engageplus.id/docs" target="_blank" rel="noopener" class="engageplus-action-card">
                <span class="dashicons dashicons-book"></span>
                <span class="action-title"><?php esc_html_e('Documentation', 'engageplus'); ?></span>
                <span class="action-desc"><?php esc_html_e('View guides and API docs', 'engageplus'); ?></span>
            </a>
        </div>
    </div>
    
    <!-- Widget Integration Info -->
    <div class="engageplus-card">
        <h2><?php esc_html_e('Widget Integration', 'engageplus'); ?></h2>
        <p><?php esc_html_e('Use the shortcode below to add the login widget to any page or post:', 'engageplus'); ?></p>
        <code class="engageplus-shortcode">[engageplus]</code>
        
        <h3><?php esc_html_e('Callback URL', 'engageplus'); ?></h3>
        <p><?php esc_html_e('Add this URL as a redirect URI in your EngagePlus dashboard:', 'engageplus'); ?></p>
        <code class="engageplus-shortcode"><?php echo esc_url($plugin->get_callback_url()); ?></code>
    </div>
</div>
