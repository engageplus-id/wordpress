<?php
/**
 * EngagePlus Admin Settings Page Template
 *
 * @package EngagePlus
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin = engageplus();
$client_id = $plugin->get_setting('client_id');
?>

<div class="wrap engageplus-settings-wrap">
    
    <!-- Header -->
    <div class="engageplus-admin-header">
        <svg width="48" height="48" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="45" fill="white" fill-opacity="0.2"/>
            <path d="M30 35h40v6H30v-6zm0 12h40v6H30v-6zm0 12h25v6H30v-6z" fill="white"/>
        </svg>
        <div>
            <h1><?php esc_html_e('EngagePlus Settings', 'engageplus'); ?></h1>
            <p><?php esc_html_e('Configure social login for your WordPress site using any OIDC provider.', 'engageplus'); ?></p>
        </div>
    </div>
    
    <!-- Status -->
    <?php if ($client_id) : ?>
        <div class="engageplus-status engageplus-status-success">
            <span class="dashicons dashicons-yes-alt"></span>
            <?php esc_html_e('Widget configured and ready', 'engageplus'); ?>
        </div>
    <?php else : ?>
        <div class="engageplus-status engageplus-status-warning">
            <span class="dashicons dashicons-warning"></span>
            <?php esc_html_e('Client ID required - Get yours from the EngagePlus dashboard', 'engageplus'); ?>
        </div>
    <?php endif; ?>
    
    <!-- Quick Links -->
    <div class="engageplus-quick-links">
        <a href="https://engageplus.id" target="_blank" rel="noopener" class="engageplus-quick-link">
            <span class="dashicons dashicons-external"></span>
            <?php esc_html_e('EngagePlus Dashboard', 'engageplus'); ?>
        </a>
        <a href="https://engageplus.id/docs" target="_blank" rel="noopener" class="engageplus-quick-link">
            <span class="dashicons dashicons-book"></span>
            <?php esc_html_e('Documentation', 'engageplus'); ?>
        </a>
        <a href="https://github.com/engageplus-id/wordpress" target="_blank" rel="noopener" class="engageplus-quick-link">
            <span class="dashicons dashicons-editor-code"></span>
            <?php esc_html_e('GitHub', 'engageplus'); ?>
        </a>
    </div>
    
    <!-- Settings Form -->
    <form method="post" action="options.php" class="engageplus-settings-form">
        <?php
        settings_fields('engageplus_settings_group');
        do_settings_sections('engageplus');
        submit_button(__('Save Settings', 'engageplus'));
        ?>
    </form>
    
    <!-- Usage Info -->
    <div class="engageplus-info-box">
        <h3><?php esc_html_e('How to Use', 'engageplus'); ?></h3>
        <p>
            <?php
            printf(
                esc_html__('Use the shortcode %1$s anywhere in your content, or add the "EngagePlus Login" widget to any widget area. The login button will also appear on the WordPress login page.', 'engageplus'),
                '<code>[engageplus]</code>'
            );
            ?>
        </p>
    </div>
    
    <div class="engageplus-info-box">
        <h3><?php esc_html_e('Shortcode Options', 'engageplus'); ?></h3>
        <p>
            <code>[engageplus]</code> - <?php esc_html_e('Basic widget with global settings', 'engageplus'); ?><br>
            <code>[engageplus button_text="Login"]</code> - <?php esc_html_e('Custom button text', 'engageplus'); ?><br>
            <code>[engageplus theme="dark"]</code> - <?php esc_html_e('Dark theme', 'engageplus'); ?><br>
            <code>[engageplus hide_logged_in="false"]</code> - <?php esc_html_e('Show for logged-in users', 'engageplus'); ?><br>
            <code>[engageplus show_logout="true"]</code> - <?php esc_html_e('Show logout button when logged in', 'engageplus'); ?>
        </p>
    </div>
    
    <div class="engageplus-info-box">
        <h3><?php esc_html_e('Redirect URI for EngagePlus Dashboard', 'engageplus'); ?></h3>
        <p>
            <?php esc_html_e('Add this URL as a redirect URI in your EngagePlus dashboard:', 'engageplus'); ?><br>
            <code><?php echo esc_url(home_url()); ?></code>
        </p>
    </div>
    
    <!-- Footer -->
    <div class="engageplus-admin-footer">
        <p>
            <?php
            printf(
                esc_html__('EngagePlus WordPress Plugin v%s | %s | %s', 'engageplus'),
                ENGAGEPLUS_VERSION,
                '<a href="https://engageplus.id" target="_blank" rel="noopener">engageplus.id</a>',
                '<a href="mailto:support@engageplus.id">support@engageplus.id</a>'
            );
            ?>
        </p>
    </div>
    
</div>

