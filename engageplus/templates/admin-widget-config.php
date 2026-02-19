<?php
/**
 * EngagePlus Widget Configuration Template
 *
 * @package EngagePlus
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$plugin = engageplus();
$api = $plugin->get_api_client();
$widget_config = $api->get_widget_config();
$error = is_wp_error($widget_config) ? $widget_config->get_error_message() : null;

$auth_flows = array(
    'redirect' => __('Redirect', 'engageplus'),
    'popup' => __('Popup', 'engageplus'),
);

$layouts = array(
    'single' => __('Single Column', 'engageplus'),
    'grid' => __('Grid', 'engageplus'),
);
?>

<div class="wrap engageplus-admin-wrap">
    <div class="engageplus-admin-header">
        <div class="engageplus-header-content">
            <h1><?php esc_html_e('Widget Configuration', 'engageplus'); ?></h1>
            <p><?php esc_html_e('Customize the appearance and behavior of your login widget.', 'engageplus'); ?></p>
        </div>
    </div>
    
    <?php if ($error) : ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="engageplus-widget-config-layout">
        <div class="engageplus-config-form">
            <form id="engageplus-widget-form">
                <div class="engageplus-card">
                    <h2><?php esc_html_e('Auth Flow', 'engageplus'); ?></h2>
                    
                    <div class="engageplus-form-row">
                        <label for="widget-auth-flow"><?php esc_html_e('Authentication Flow', 'engageplus'); ?></label>
                        <select name="authFlow" id="widget-auth-flow">
                            <?php foreach ($auth_flows as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($widget_config['authFlow'] ?? 'redirect', $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e('How users are redirected for authentication.', 'engageplus'); ?></p>
                    </div>
                    
                    <div class="engageplus-form-row">
                        <label for="widget-layout"><?php esc_html_e('Layout', 'engageplus'); ?></label>
                        <select name="layout" id="widget-layout">
                            <?php foreach ($layouts as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($widget_config['layout'] ?? 'single', $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="engageplus-card">
                    <h2><?php esc_html_e('Branding', 'engageplus'); ?></h2>
                    
                    <div class="engageplus-form-row">
                        <label for="widget-primary-color"><?php esc_html_e('Primary Color', 'engageplus'); ?></label>
                        <input type="color" name="branding[primaryColor]" id="widget-primary-color" 
                               value="<?php echo esc_attr($widget_config['branding']['primaryColor'] ?? '#4F46E5'); ?>">
                    </div>
                    
                    <div class="engageplus-form-row">
                        <label for="widget-bg-color"><?php esc_html_e('Background Color', 'engageplus'); ?></label>
                        <input type="color" name="branding[backgroundColor]" id="widget-bg-color" 
                               value="<?php echo esc_attr($widget_config['branding']['backgroundColor'] ?? '#FFFFFF'); ?>">
                    </div>
                    
                    <div class="engageplus-form-row">
                        <label for="widget-text-color"><?php esc_html_e('Text Color', 'engageplus'); ?></label>
                        <input type="color" name="branding[textColor]" id="widget-text-color" 
                               value="<?php echo esc_attr($widget_config['branding']['textColor'] ?? '#1F2937'); ?>">
                    </div>
                    
                    <div class="engageplus-form-row">
                        <label for="widget-border-radius"><?php esc_html_e('Border Radius', 'engageplus'); ?></label>
                        <input type="number" name="branding[borderRadius]" id="widget-border-radius" 
                               value="<?php echo esc_attr($widget_config['branding']['borderRadius'] ?? 8); ?>" min="0" max="24" step="1">
                        <span class="description">px</span>
                    </div>
                </div>
                
                <div class="engageplus-card">
                    <h2><?php esc_html_e('Authentication Methods', 'engageplus'); ?></h2>
                    
                    <div class="engageplus-auth-methods">
                        <?php 
                        $auth_methods = $widget_config['authMethods'] ?? array(
                            'providers' => array('enabled' => true, 'position' => 1),
                            'emailOtp' => array('enabled' => false, 'position' => 2),
                            'passkey' => array('enabled' => false, 'position' => 3),
                        );
                        ?>
                        
                        <div class="engageplus-auth-method" data-method="providers">
                            <label>
                                <input type="checkbox" name="authMethods[providers][enabled]" value="1" 
                                       <?php checked(!empty($auth_methods['providers']['enabled'])); ?>>
                                <?php esc_html_e('Social/OIDC Providers', 'engageplus'); ?>
                            </label>
                            <input type="number" name="authMethods[providers][position]" 
                                   value="<?php echo esc_attr($auth_methods['providers']['position'] ?? 1); ?>" 
                                   min="1" max="5" class="small-text">
                        </div>
                        
                        <div class="engageplus-auth-method" data-method="emailOtp">
                            <label>
                                <input type="checkbox" name="authMethods[emailOtp][enabled]" value="1" 
                                       <?php checked(!empty($auth_methods['emailOtp']['enabled'])); ?>>
                                <?php esc_html_e('Email OTP (Magic Link)', 'engageplus'); ?>
                            </label>
                            <input type="number" name="authMethods[emailOtp][position]" 
                                   value="<?php echo esc_attr($auth_methods['emailOtp']['position'] ?? 2); ?>" 
                                   min="1" max="5" class="small-text">
                        </div>
                        
                        <div class="engageplus-auth-method" data-method="passkey">
                            <label>
                                <input type="checkbox" name="authMethods[passkey][enabled]" value="1" 
                                       <?php checked(!empty($auth_methods['passkey']['enabled'])); ?>>
                                <?php esc_html_e('Passkey / WebAuthn', 'engageplus'); ?>
                            </label>
                            <input type="number" name="authMethods[passkey][position]" 
                                   value="<?php echo esc_attr($auth_methods['passkey']['position'] ?? 3); ?>" 
                                   min="1" max="5" class="small-text">
                        </div>
                    </div>
                    <p class="description"><?php esc_html_e('Enable methods and set their display order (1 = first).', 'engageplus'); ?></p>
                </div>
                
                <div class="engageplus-form-actions">
                    <button type="submit" class="button button-primary button-hero">
                        <?php esc_html_e('Save Widget Configuration', 'engageplus'); ?>
                    </button>
                    <span class="spinner"></span>
                    <span class="engageplus-save-status"></span>
                </div>
            </form>
        </div>
        
        <div class="engageplus-widget-preview">
            <div class="engageplus-card">
                <h2><?php esc_html_e('Preview', 'engageplus'); ?></h2>
                <p class="description"><?php esc_html_e('Live preview is available in the EngagePlus dashboard.', 'engageplus'); ?></p>
                <div class="engageplus-preview-placeholder">
                    <span class="dashicons dashicons-visibility"></span>
                    <p><?php esc_html_e('Widget Preview', 'engageplus'); ?></p>
                    <a href="https://engageplus.id" target="_blank" rel="noopener" class="button">
                        <?php esc_html_e('Open Dashboard', 'engageplus'); ?>
                    </a>
                </div>
            </div>
            
            <div class="engageplus-card">
                <h2><?php esc_html_e('Shortcode', 'engageplus'); ?></h2>
                <p><?php esc_html_e('Add the widget to any page:', 'engageplus'); ?></p>
                <code class="engageplus-shortcode">[engageplus]</code>
            </div>
        </div>
    </div>
</div>
