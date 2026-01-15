/**
 * EngagePlus WordPress Plugin JavaScript
 *
 * Handles widget initialization and authentication callbacks
 *
 * @package EngagePlus
 * @since 1.0.0
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
         * Widgets initialized
         */
        widgets: [],
        
        /**
         * Initialize the handler
         */
        init: function() {
            var self = this;
            
            // Wait for EngagePlus widget to be ready
            if (typeof window.EngagePlus === 'undefined') {
                // Wait for the script to load
                $(document).ready(function() {
                    self.waitForWidget();
                });
            } else {
                self.initWidgets();
            }
            
            // Listen for EngagePlus events
            this.bindEvents();
        },
        
        /**
         * Wait for EngagePlus widget to load
         */
        waitForWidget: function() {
            var self = this;
            var attempts = 0;
            var maxAttempts = 50; // 5 seconds max
            
            var checkWidget = setInterval(function() {
                attempts++;
                
                if (typeof window.EngagePlus !== 'undefined') {
                    clearInterval(checkWidget);
                    self.initWidgets();
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkWidget);
                    self.log('EngagePlus widget failed to load');
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
            
            // Check for client ID
            if (!this.config.clientId) {
                this.log('Client ID not configured');
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
            
            // Get widget-specific settings
            var buttonText = $container.data('button-text') || this.config.buttonText || 'Sign In';
            var theme = $container.data('theme') || this.config.theme || 'light';
            var showLabels = $container.data('show-labels');
            if (showLabels === undefined) {
                showLabels = this.config.showLabels;
            }
            
            this.log('Initializing widget: ' + containerId);
            
            // Initialize EngagePlus widget
            try {
                EngagePlus.init({
                    clientId: this.config.clientId,
                    issuer: this.config.apiBaseUrl,
                    container: '#' + containerId,
                    buttonText: buttonText,
                    theme: theme,
                    showLabels: showLabels,
                    
                    // Authentication callbacks
                    onLogin: function(user) {
                        self.handleLogin(user);
                    },
                    onLogout: function() {
                        self.handleLogout();
                    },
                    onError: function(error) {
                        self.handleError(error);
                    }
                });
                
                this.widgets.push(containerId);
                this.log('Widget initialized successfully');
                
            } catch (error) {
                this.log('Failed to initialize widget: ' + error.message);
            }
        },
        
        /**
         * Handle successful login
         */
        handleLogin: function(user) {
            var self = this;
            
            this.log('Login callback received', user);
            
            // Show loading state
            this.showLoading();
            
            // Send user data to WordPress
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'engageplus_auth',
                    nonce: this.config.nonce,
                    user_data: JSON.stringify(user)
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
         * Handle logout
         */
        handleLogout: function() {
            var self = this;
            
            this.log('Logout callback received');
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'engageplus_logout',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.log('WordPress logout successful');
                        window.location.href = response.data.redirect || window.location.href;
                    }
                },
                error: function(xhr, status, error) {
                    self.log('Logout error', { status: status, error: error });
                }
            });
        },
        
        /**
         * Handle authentication error
         */
        handleError: function(error) {
            this.log('Authentication error', error);
            this.showMessage(error.message || 'Authentication failed', 'error');
        },
        
        /**
         * Bind DOM events
         */
        bindEvents: function() {
            var self = this;
            
            // Listen for EngagePlus DOM events
            $(document).on('engageplus:login', function(e, user) {
                self.handleLogin(user);
            });
            
            $(document).on('engageplus:logout', function() {
                self.handleLogout();
            });
            
            $(document).on('engageplus:error', function(e, error) {
                self.handleError(error);
            });
            
            // Handle logout button clicks
            $(document).on('click', '.engageplus-logout-btn', function(e) {
                // Allow default logout URL to work
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

