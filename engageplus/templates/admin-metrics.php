<?php
/**
 * EngagePlus Metrics Dashboard Template
 *
 * @package EngagePlus
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$plugin = engageplus();
$api = $plugin->get_api_client();

$days = isset($_GET['days']) ? intval($_GET['days']) : 30;
$days = in_array($days, array(7, 14, 30, 90)) ? $days : 30;

$metrics = $api->get_metrics($days);
$error = is_wp_error($metrics) ? $metrics->get_error_message() : null;
?>

<div class="wrap engageplus-admin-wrap">
    <div class="engageplus-admin-header">
        <div class="engageplus-header-content">
            <h1><?php esc_html_e('Metrics & Analytics', 'engageplus'); ?></h1>
            <p><?php esc_html_e('View authentication metrics and service performance.', 'engageplus'); ?></p>
        </div>
        <div class="engageplus-header-actions">
            <select id="engageplus-metrics-days" class="engageplus-period-select">
                <option value="7" <?php selected($days, 7); ?>><?php esc_html_e('Last 7 days', 'engageplus'); ?></option>
                <option value="14" <?php selected($days, 14); ?>><?php esc_html_e('Last 14 days', 'engageplus'); ?></option>
                <option value="30" <?php selected($days, 30); ?>><?php esc_html_e('Last 30 days', 'engageplus'); ?></option>
                <option value="90" <?php selected($days, 90); ?>><?php esc_html_e('Last 90 days', 'engageplus'); ?></option>
            </select>
            <button type="button" class="button" id="engageplus-refresh-metrics">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e('Refresh', 'engageplus'); ?>
            </button>
        </div>
    </div>
    
    <?php if ($error) : ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error); ?></p>
        </div>
    <?php elseif ($metrics) : ?>
        
        <!-- Summary Cards -->
        <div class="engageplus-metrics-grid">
            <div class="engageplus-metric-card engageplus-metric-primary">
                <div class="engageplus-metric-icon">
                    <span class="dashicons dashicons-admin-users"></span>
                </div>
                <div class="engageplus-metric-content">
                    <div class="engageplus-metric-value"><?php echo esc_html(number_format($metrics['totalLogins'] ?? 0)); ?></div>
                    <div class="engageplus-metric-label"><?php esc_html_e('Total Logins', 'engageplus'); ?></div>
                </div>
            </div>
            
            <div class="engageplus-metric-card">
                <div class="engageplus-metric-icon">
                    <span class="dashicons dashicons-chart-line"></span>
                </div>
                <div class="engageplus-metric-content">
                    <div class="engageplus-metric-value"><?php echo esc_html(number_format($metrics['dailyAverage'] ?? 0, 1)); ?></div>
                    <div class="engageplus-metric-label"><?php esc_html_e('Daily Average', 'engageplus'); ?></div>
                </div>
            </div>
            
            <?php if (isset($metrics['services']['webhooks'])) : ?>
            <div class="engageplus-metric-card">
                <div class="engageplus-metric-icon">
                    <span class="dashicons dashicons-rss"></span>
                </div>
                <div class="engageplus-metric-content">
                    <div class="engageplus-metric-value"><?php echo esc_html($metrics['services']['webhooks']['successRate'] ?? 0); ?>%</div>
                    <div class="engageplus-metric-label"><?php esc_html_e('Webhook Success', 'engageplus'); ?></div>
                    <div class="engageplus-metric-detail">
                        <?php printf(
                            esc_html__('%d / %d delivered', 'engageplus'),
                            $metrics['services']['webhooks']['successful'] ?? 0,
                            $metrics['services']['webhooks']['total'] ?? 0
                        ); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($metrics['services']['emails'])) : ?>
            <div class="engageplus-metric-card">
                <div class="engageplus-metric-icon">
                    <span class="dashicons dashicons-email"></span>
                </div>
                <div class="engageplus-metric-content">
                    <div class="engageplus-metric-value"><?php echo esc_html($metrics['services']['emails']['successRate'] ?? 0); ?>%</div>
                    <div class="engageplus-metric-label"><?php esc_html_e('Email Delivery', 'engageplus'); ?></div>
                    <div class="engageplus-metric-detail">
                        <?php printf(
                            esc_html__('%d / %d sent', 'engageplus'),
                            $metrics['services']['emails']['successful'] ?? 0,
                            $metrics['services']['emails']['total'] ?? 0
                        ); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($metrics['services']['integrations'])) : ?>
            <div class="engageplus-metric-card">
                <div class="engageplus-metric-icon">
                    <span class="dashicons dashicons-database"></span>
                </div>
                <div class="engageplus-metric-content">
                    <div class="engageplus-metric-value"><?php echo esc_html($metrics['services']['integrations']['successRate'] ?? 0); ?>%</div>
                    <div class="engageplus-metric-label"><?php esc_html_e('Integration Syncs', 'engageplus'); ?></div>
                    <div class="engageplus-metric-detail">
                        <?php printf(
                            esc_html__('%d / %d synced', 'engageplus'),
                            $metrics['services']['integrations']['successful'] ?? 0,
                            $metrics['services']['integrations']['total'] ?? 0
                        ); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Charts Row -->
        <div class="engageplus-charts-row">
            <!-- Logins Over Time -->
            <?php if (!empty($metrics['byDate'])) : ?>
            <div class="engageplus-card engageplus-chart-card">
                <h2><?php esc_html_e('Logins Over Time', 'engageplus'); ?></h2>
                <canvas id="engageplus-logins-chart" height="200"></canvas>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Chart === 'undefined') return;
                    
                    var ctx = document.getElementById('engageplus-logins-chart').getContext('2d');
                    var data = <?php echo wp_json_encode($metrics['byDate']); ?>;
                    var labels = Object.keys(data).sort();
                    var values = labels.map(function(date) { return data[date]; });
                    
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels.map(function(d) { return d.substring(5); }),
                            datasets: [{
                                label: '<?php esc_attr_e('Logins', 'engageplus'); ?>',
                                data: values,
                                borderColor: '#4F46E5',
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                });
                </script>
            </div>
            <?php endif; ?>
            
            <!-- Logins by Provider -->
            <?php if (!empty($metrics['byProvider'])) : ?>
            <div class="engageplus-card engageplus-chart-card">
                <h2><?php esc_html_e('Logins by Provider', 'engageplus'); ?></h2>
                <canvas id="engageplus-providers-chart" height="200"></canvas>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Chart === 'undefined') return;
                    
                    var ctx = document.getElementById('engageplus-providers-chart').getContext('2d');
                    var data = <?php echo wp_json_encode($metrics['byProvider']); ?>;
                    var labels = Object.keys(data);
                    var values = Object.values(data);
                    
                    var colors = [
                        '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                        '#06B6D4', '#EC4899', '#14B8A6', '#F97316', '#6366F1'
                    ];
                    
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: labels.map(function(l) { return l.charAt(0).toUpperCase() + l.slice(1); }),
                            datasets: [{
                                data: values,
                                backgroundColor: colors.slice(0, labels.length)
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'right' }
                            }
                        }
                    });
                });
                </script>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Provider Details Table -->
        <?php if (!empty($metrics['byProvider'])) : ?>
        <div class="engageplus-card">
            <h2><?php esc_html_e('Provider Breakdown', 'engageplus'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Provider', 'engageplus'); ?></th>
                        <th><?php esc_html_e('Logins', 'engageplus'); ?></th>
                        <th><?php esc_html_e('Percentage', 'engageplus'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = array_sum($metrics['byProvider']);
                    foreach ($metrics['byProvider'] as $provider => $count) : 
                        $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html(ucfirst($provider)); ?></strong></td>
                        <td><?php echo esc_html(number_format($count)); ?></td>
                        <td>
                            <div class="engageplus-progress-bar">
                                <div class="engageplus-progress" style="width: <?php echo esc_attr($percentage); ?>%"></div>
                                <span><?php echo esc_html($percentage); ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
    <?php else : ?>
        <div class="engageplus-card">
            <p><?php esc_html_e('No metrics data available. Start using EngagePlus to see authentication analytics.', 'engageplus'); ?></p>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var select = document.getElementById('engageplus-metrics-days');
    if (select) {
        select.addEventListener('change', function() {
            window.location.href = '<?php echo esc_url(admin_url('admin.php?page=engageplus-metrics&days=')); ?>' + this.value;
        });
    }
    
    var refreshBtn = document.getElementById('engageplus-refresh-metrics');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            window.location.reload();
        });
    }
});
</script>
