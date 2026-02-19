/**
 * EngagePlus WordPress Plugin JavaScript
 *
 * Handles widget initialization and authentication callbacks
 * Uses the OPWidget PKCE-based authentication flow
 *
 * @package EngagePlus
 * @since 1.1.0
 */

(function($) {
    'use strict';

    // EngagePlus WordPress Handler
    var EngagePlusWP = {
        
        /**
         * Configuration from WordPress
         */
        config: window.engageplusConfig || {},
        
        /**
         * Widget instances
         */
        widgets: [],
        
        /**
         * Initialize the handler
         */
        init: function() {
            var self = this;
            
            // Wait for OPWidget to be ready
            if (typeof window.OPWidget === 'undefined') {
                $(document).ready(function() {
                    self.waitForWidget();
                });
            } else {
                self.initWidgets();
            }
            
            // Bind events
            this.bindEvents();
        },
        
        /**
         * Wait for OPWidget to load
         */
        waitForWidget: function() {
            var self = this;
            var attempts = 0;
            var maxAttempts = 50; // 5 seconds max
            
            var checkWidget = setInterval(function() {
                attempts++;
                
                if (typeof window.OPWidget !== 'undefined') {
                    clearInterval(checkWidget);
                    self.initWidgets();
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkWidget);
                    self.log('OPWidget failed to load');
                }
            }, 100);
        },
        
        /**
         * Initialize all widget containers
         */
        initWidgets: function() {
            var self = this;
            
            // Check if already logged in
            if (this.config.isLoggedIn) {
                this.log('User already logged in to WordPress');
                return;
            }
            
            // Check for Organization ID
            if (!this.config.orgId) {
                this.log('Organization ID not configured');
                return;
            }
            
            // Find all widget containers
            var $containers = $('.engageplus-widget');
            
            if ($containers.length === 0) {
                this.log('No widget containers found');
                return;
            }
            
            $containers.each(function() {
                self.initWidget($(this));
            });
        },
        
        /**
         * Initialize a single widget
         */
        initWidget: function($container) {
            var self = this;
            var containerId = $container.attr('id');
            
            if (!containerId) {
                containerId = 'engageplus-widget-' + Date.now();
                $container.attr('id', containerId);
            }
            
            this.log('Initializing widget: ' + containerId);
            
            try {
                // Create new OPWidget instance with PKCE flow
                var widget = new OPWidget({
                    orgId: this.config.orgId,
                    redirectUri: this.config.redirectUri,
                    onSuccess: function(tokens) {
                        self.handleSuccess(tokens);
                    },
                    onError: function(error) {
                        self.handleError(error);
                    }
                });
                
                // Mount the widget to the container
                widget.mount('#' + containerId);
                
                this.widgets.push({
                    id: containerId,
                    instance: widget
                });
                
                this.log('Widget mounted successfully');
                
            } catch (error) {
                this.log('Failed to initialize widget: ' + error.message);
            }
        },
        
        /**
         * Handle successful authentication
         */
        handleSuccess: function(tokens) {
            var self = this;
            
            this.log('Authentication successful, received tokens');
            
            // Show loading state
            this.showLoading();
            
            // Send tokens to WordPress to create/login user
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'engageplus_auth',
                    nonce: this.config.nonce,
                    tokens: JSON.stringify(tokens)
                },
                success: function(response) {
                    self.hideLoading();
                    
                    if (response.success) {
                        self.log('WordPress authentication successful');
                        
                        // Show success message
                        self.showMessage(response.data.message, 'success');
                        
                        // Redirect after short delay
                        setTimeout(function() {
                            var redirectUrl = response.data.redirect || self.config.redirectAfterLogin || window.location.href;
                            window.location.href = redirectUrl;
                        }, 500);
                    } else {
                        self.log('WordPress authentication failed', response);
                        self.showMessage(response.data.message || 'Authentication failed', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    self.hideLoading();
                    self.log('AJAX error', { status: status, error: error });
                    self.showMessage('Connection error. Please try again.', 'error');
                }
            });
        },
        
        /**
         * Handle authentication error
         */
        handleError: function(error) {
            this.log('Authentication error', error);
            this.showMessage(error.message || error || 'Authentication failed', 'error');
        },
        
        /**
         * Bind DOM events
         */
        bindEvents: function() {
            var self = this;
            
            // Listen for custom events
            $(document).on('engageplus:success', function(e, tokens) {
                self.handleSuccess(tokens);
            });
            
            $(document).on('engageplus:error', function(e, error) {
                self.handleError(error);
            });
        },
        
        /**
         * Show loading state
         */
        showLoading: function() {
            $('.engageplus-widget').addClass('engageplus-loading');
        },
        
        /**
         * Hide loading state
         */
        hideLoading: function() {
            $('.engageplus-widget').removeClass('engageplus-loading');
        },
        
        /**
         * Show message to user
         */
        showMessage: function(message, type) {
            var $container = $('.engageplus-widget').first();
            var $message = $('<div class="engageplus-message engageplus-message-' + type + '">' + message + '</div>');
            
            // Remove existing messages
            $('.engageplus-message').remove();
            
            // Insert message
            $container.before($message);
            
            // Auto-remove success messages
            if (type === 'success') {
                setTimeout(function() {
                    $message.fadeOut(function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        },
        
        /**
         * Log message (debug mode only)
         */
        log: function(message, data) {
            if (!this.config.debugMode) {
                return;
            }
            
            var logMessage = '[EngagePlus WP] ' + message;
            
            if (data) {
                console.log(logMessage, data);
            } else {
                console.log(logMessage);
            }
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        EngagePlusWP.init();
    });
    
    // Expose for external access
    window.EngagePlusWP = EngagePlusWP;
    
})(jQuery);
