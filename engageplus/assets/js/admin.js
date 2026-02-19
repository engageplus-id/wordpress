/**
 * EngagePlus Admin JavaScript
 *
 * @package EngagePlus
 * @since 1.2.0
 */

(function($) {
    'use strict';

    var EngagePlusAdmin = {
        config: window.engageplusAdmin || {},

        init: function() {
            this.bindProviderEvents();
            this.bindWebhookEvents();
            this.bindWidgetEvents();
            this.bindEmailProviderEvents();
            this.bindIntegrationEvents();
            this.bindModalEvents();
        },

        // API Request Helper
        apiRequest: function(action, data, callback) {
            var self = this;
            
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'engageplus_api_request',
                    nonce: this.config.nonce,
                    api_action: action,
                    data: JSON.stringify(data || {})
                },
                success: function(response) {
                    if (response.success) {
                        callback(null, response.data);
                    } else {
                        callback(response.data?.message || self.config.strings.error);
                    }
                },
                error: function() {
                    callback(self.config.strings.error);
                }
            });
        },

        // Provider Events
        bindProviderEvents: function() {
            var self = this;

            $('#engageplus-add-provider').on('click', function() {
                self.openProviderModal();
            });

            $(document).on('click', '.engageplus-edit-provider', function() {
                var provider = $(this).data('provider');
                self.openProviderModal(provider);
            });

            $(document).on('click', '.engageplus-delete-provider', function() {
                var id = $(this).data('id');
                if (confirm(self.config.strings.confirmDelete)) {
                    self.deleteProvider(id);
                }
            });

            $(document).on('click', '.engageplus-test-provider', function() {
                var $btn = $(this);
                var id = $btn.data('id');
                $btn.prop('disabled', true);
                
                self.apiRequest('test_provider', { id: id }, function(err, data) {
                    $btn.prop('disabled', false);
                    if (err) {
                        alert(self.config.strings.testFailed + '\n' + err);
                    } else {
                        alert(self.config.strings.testSuccess);
                    }
                });
            });

            $('#engageplus-provider-form').on('submit', function(e) {
                e.preventDefault();
                self.saveProvider();
            });

            $('#provider-type').on('change', function() {
                var issuer = $(this).find(':selected').data('issuer');
                $('#provider-issuer').val(issuer);
                $('#provider-name').val($(this).find(':selected').text());
                $('#issuer-row').toggle($(this).val() === 'custom');
            });
        },

        openProviderModal: function(provider) {
            var $modal = $('#engageplus-provider-modal');
            var $form = $('#engageplus-provider-form');
            
            $form[0].reset();
            
            if (provider) {
                $('#engageplus-provider-modal-title').text('Edit Provider');
                $('#provider-id').val(provider.id);
                $('#provider-type').val(provider.type).trigger('change');
                $('#provider-name').val(provider.name);
                $('#provider-issuer').val(provider.issuerUrl);
                $('#provider-client-id').val(provider.clientId || '');
                $('#provider-scopes').val((provider.scopes || []).join(' '));
                $('#provider-enabled').prop('checked', provider.enabled !== false);
            } else {
                $('#engageplus-provider-modal-title').text('Add Provider');
                $('#provider-id').val('');
                $('#provider-type').trigger('change');
            }
            
            $modal.show();
        },

        saveProvider: function() {
            var self = this;
            var $form = $('#engageplus-provider-form');
            var id = $('#provider-id').val();
            
            var data = {
                name: $('#provider-name').val(),
                type: $('#provider-type').val(),
                issuerUrl: $('#provider-issuer').val(),
                scopes: $('#provider-scopes').val().split(/\s+/).filter(Boolean),
                enabled: $('#provider-enabled').is(':checked')
            };
            
            var clientId = $('#provider-client-id').val();
            var clientSecret = $('#provider-client-secret').val();
            
            if (clientId || clientSecret) {
                data.credentials = {};
                if (clientId) data.credentials.clientId = clientId;
                if (clientSecret) data.credentials.clientSecret = clientSecret;
            }
            
            if (id) {
                data.id = id;
            }
            
            var action = id ? 'update_provider' : 'create_provider';
            
            this.apiRequest(action, data, function(err, result) {
                if (err) {
                    alert(err);
                } else {
                    $('#engageplus-provider-modal').hide();
                    location.reload();
                }
            });
        },

        deleteProvider: function(id) {
            this.apiRequest('delete_provider', { id: id }, function(err) {
                if (err) {
                    alert(err);
                } else {
                    location.reload();
                }
            });
        },

        // Webhook Events
        bindWebhookEvents: function() {
            var self = this;

            $('#engageplus-add-webhook').on('click', function() {
                self.openWebhookModal();
            });

            $(document).on('click', '.engageplus-edit-webhook', function() {
                var webhook = $(this).data('webhook');
                self.openWebhookModal(webhook);
            });

            $(document).on('click', '.engageplus-delete-webhook', function() {
                var id = $(this).data('id');
                if (confirm(self.config.strings.confirmDelete)) {
                    self.deleteWebhook(id);
                }
            });

            $('#engageplus-webhook-form').on('submit', function(e) {
                e.preventDefault();
                self.saveWebhook();
            });
        },

        openWebhookModal: function(webhook) {
            var $modal = $('#engageplus-webhook-modal');
            var $form = $('#engageplus-webhook-form');
            
            $form[0].reset();
            $form.find('input[name="events[]"]').prop('checked', false);
            
            if (webhook) {
                $('#engageplus-webhook-modal-title').text('Edit Webhook');
                $('#webhook-id').val(webhook.id);
                $('#webhook-name').val(webhook.name);
                $('#webhook-url').val(webhook.webhookUrl);
                $('#webhook-enabled').prop('checked', webhook.enabled !== false);
                
                (webhook.events || []).forEach(function(event) {
                    $form.find('input[name="events[]"][value="' + event + '"]').prop('checked', true);
                });
            } else {
                $('#engageplus-webhook-modal-title').text('Add Webhook');
                $('#webhook-id').val('');
            }
            
            $modal.show();
        },

        saveWebhook: function() {
            var self = this;
            var id = $('#webhook-id').val();
            
            var events = [];
            $('#engageplus-webhook-form input[name="events[]"]:checked').each(function() {
                events.push($(this).val());
            });
            
            var data = {
                name: $('#webhook-name').val(),
                webhookUrl: $('#webhook-url').val(),
                events: events,
                enabled: $('#webhook-enabled').is(':checked')
            };
            
            var secret = $('#webhook-secret').val();
            if (secret) {
                data.authentication = { type: 'hmac', secret: secret };
            }
            
            if (id) {
                data.id = id;
            }
            
            var action = id ? 'update_webhook' : 'create_webhook';
            
            this.apiRequest(action, data, function(err, result) {
                if (err) {
                    alert(err);
                } else {
                    $('#engageplus-webhook-modal').hide();
                    location.reload();
                }
            });
        },

        deleteWebhook: function(id) {
            this.apiRequest('delete_webhook', { id: id }, function(err) {
                if (err) {
                    alert(err);
                } else {
                    location.reload();
                }
            });
        },

        // Widget Events
        bindWidgetEvents: function() {
            var self = this;

            $('#engageplus-widget-form').on('submit', function(e) {
                e.preventDefault();
                self.saveWidgetConfig();
            });
        },

        saveWidgetConfig: function() {
            var self = this;
            var $form = $('#engageplus-widget-form');
            var $status = $form.find('.engageplus-save-status');
            var $spinner = $form.find('.spinner');
            
            $spinner.addClass('is-active');
            $status.text(this.config.strings.saving);
            
            var data = {
                authFlow: $('#widget-auth-flow').val(),
                layout: $('#widget-layout').val(),
                branding: {
                    primaryColor: $('#widget-primary-color').val(),
                    backgroundColor: $('#widget-bg-color').val(),
                    textColor: $('#widget-text-color').val(),
                    borderRadius: parseInt($('#widget-border-radius').val()) || 8
                },
                authMethods: {}
            };
            
            $('.engageplus-auth-method').each(function() {
                var method = $(this).data('method');
                var $checkbox = $(this).find('input[type="checkbox"]');
                var $position = $(this).find('input[type="number"]');
                
                data.authMethods[method] = {
                    enabled: $checkbox.is(':checked'),
                    position: parseInt($position.val()) || 1
                };
            });
            
            this.apiRequest('update_widget', data, function(err, result) {
                $spinner.removeClass('is-active');
                
                if (err) {
                    $status.text(err).css('color', '#dc3232');
                } else {
                    $status.text(self.config.strings.saved).css('color', '#46b450');
                    setTimeout(function() { $status.text(''); }, 2000);
                }
            });
        },

        // Email Provider Events
        bindEmailProviderEvents: function() {
            var self = this;

            $('#engageplus-add-email-provider').on('click', function() {
                self.openEmailProviderModal();
            });

            $(document).on('click', '.engageplus-edit-email-provider', function() {
                var provider = $(this).data('provider');
                self.openEmailProviderModal(provider);
            });

            $(document).on('click', '.engageplus-delete-email-provider', function() {
                var id = $(this).data('id');
                if (confirm(self.config.strings.confirmDelete)) {
                    self.deleteEmailProvider(id);
                }
            });

            $(document).on('click', '.engageplus-test-email-provider', function() {
                var $btn = $(this);
                var id = $btn.data('id');
                $btn.prop('disabled', true);
                
                self.apiRequest('test_email_provider', { id: id }, function(err, data) {
                    $btn.prop('disabled', false);
                    if (err) {
                        alert(self.config.strings.testFailed + '\n' + err);
                    } else {
                        alert(self.config.strings.testSuccess);
                    }
                });
            });

            $('#engageplus-email-provider-form').on('submit', function(e) {
                e.preventDefault();
                self.saveEmailProvider();
            });

            $('#email-provider-type').on('change', function() {
                var type = $(this).val();
                $('.engageplus-provider-field').each(function() {
                    var providers = $(this).data('providers').split(',');
                    $(this).toggle(providers.indexOf(type) !== -1);
                });
            }).trigger('change');
        },

        openEmailProviderModal: function(provider) {
            var $modal = $('#engageplus-email-provider-modal');
            var $form = $('#engageplus-email-provider-form');
            
            $form[0].reset();
            
            if (provider) {
                $('#engageplus-email-provider-modal-title').text('Edit Email Provider');
                $('#email-provider-id').val(provider.id);
                $('#email-provider-type').val(provider.type).trigger('change');
                $('#email-provider-name').val(provider.name);
                $('#email-provider-from-address').val(provider.config?.fromAddress || '');
                $('#email-provider-from-name').val(provider.config?.fromName || '');
                $('#email-provider-default').prop('checked', provider.isDefault === true);
            } else {
                $('#engageplus-email-provider-modal-title').text('Add Email Provider');
                $('#email-provider-id').val('');
                $('#email-provider-type').trigger('change');
            }
            
            $modal.show();
        },

        saveEmailProvider: function() {
            var self = this;
            var id = $('#email-provider-id').val();
            var type = $('#email-provider-type').val();
            
            var data = {
                name: $('#email-provider-name').val(),
                type: type,
                config: {
                    fromAddress: $('#email-provider-from-address').val(),
                    fromName: $('#email-provider-from-name').val()
                },
                isDefault: $('#email-provider-default').is(':checked')
            };
            
            // Type-specific config
            if (type === 'sendgrid' || type === 'mailgun' || type === 'postmark') {
                var apiKey = $('#email-provider-api-key').val();
                if (apiKey) data.config.apiKey = apiKey;
            }
            if (type === 'mailgun') {
                data.config.domain = $('#email-provider-domain').val();
            }
            if (type === 'ses') {
                data.config.accessKeyId = $('#email-provider-access-key').val();
                data.config.secretAccessKey = $('#email-provider-secret-key').val();
                data.config.region = $('#email-provider-region').val();
            }
            if (type === 'smtp') {
                data.config.host = $('#email-provider-host').val();
                data.config.port = parseInt($('#email-provider-port').val()) || 587;
                data.config.username = $('#email-provider-username').val();
                data.config.password = $('#email-provider-password').val();
                data.config.secure = $('#email-provider-secure').is(':checked');
            }
            
            if (id) {
                data.id = id;
            }
            
            var action = id ? 'update_email_provider' : 'create_email_provider';
            
            this.apiRequest(action, data, function(err, result) {
                if (err) {
                    alert(err);
                } else {
                    $('#engageplus-email-provider-modal').hide();
                    location.reload();
                }
            });
        },

        deleteEmailProvider: function(id) {
            this.apiRequest('delete_email_provider', { id: id }, function(err) {
                if (err) {
                    alert(err);
                } else {
                    location.reload();
                }
            });
        },

        // Integration Events
        bindIntegrationEvents: function() {
            var self = this;

            $('#engageplus-add-integration').on('click', function() {
                self.openIntegrationModal();
            });

            $(document).on('click', '.engageplus-edit-integration', function() {
                var integration = $(this).data('integration');
                self.openIntegrationModal(integration);
            });

            $(document).on('click', '.engageplus-delete-integration', function() {
                var id = $(this).data('id');
                if (confirm(self.config.strings.confirmDelete)) {
                    self.deleteIntegration(id);
                }
            });

            $('#engageplus-integration-form').on('submit', function(e) {
                e.preventDefault();
                self.saveIntegration();
            });

            $('#integration-type').on('change', function() {
                var type = $(this).val();
                $('.engageplus-integration-field').each(function() {
                    var types = $(this).data('types').split(',');
                    $(this).toggle(types.indexOf(type) !== -1);
                });
            }).trigger('change');
        },

        openIntegrationModal: function(integration) {
            var $modal = $('#engageplus-integration-modal');
            var $form = $('#engageplus-integration-form');
            
            $form[0].reset();
            
            if (integration) {
                $('#engageplus-integration-modal-title').text('Edit Integration');
                $('#integration-id').val(integration.id);
                $('#integration-type').val(integration.type).trigger('change');
                $('#integration-name').val(integration.name);
                $('#integration-store-users').prop('checked', integration.storeUserProfiles !== false);
                $('#integration-handle-events').prop('checked', integration.handleEvents !== false);
                $('#integration-include-pii').prop('checked', integration.includePii === true);
                
                if (integration.settings) {
                    $('#integration-project-url').val(integration.settings.projectUrl || '');
                    $('#integration-users-table').val(integration.settings.usersTable || 'oidc_users');
                    $('#integration-events-table').val(integration.settings.eventsTable || 'oidc_auth_events');
                    $('#integration-base-id').val(integration.settings.baseId || '');
                    $('#integration-table-name').val(integration.settings.tableName || '');
                }
            } else {
                $('#engageplus-integration-modal-title').text('Add Integration');
                $('#integration-id').val('');
                $('#integration-type').trigger('change');
            }
            
            $modal.show();
        },

        saveIntegration: function() {
            var self = this;
            var id = $('#integration-id').val();
            var type = $('#integration-type').val();
            
            var data = {
                name: $('#integration-name').val(),
                type: type,
                storeUserProfiles: $('#integration-store-users').is(':checked'),
                handleEvents: $('#integration-handle-events').is(':checked'),
                includePii: $('#integration-include-pii').is(':checked'),
                credentials: {},
                settings: {}
            };
            
            if (type === 'supabase') {
                data.settings.projectUrl = $('#integration-project-url').val();
                data.settings.usersTable = $('#integration-users-table').val();
                data.settings.eventsTable = $('#integration-events-table').val();
                
                var apiKey = $('#integration-anon-key').val();
                var serviceKey = $('#integration-service-key').val();
                if (apiKey) data.credentials.apiKey = apiKey;
                if (serviceKey) data.credentials.serviceRoleKey = serviceKey;
            }
            
            if (type === 'airtable') {
                data.settings.baseId = $('#integration-base-id').val();
                data.settings.tableName = $('#integration-table-name').val();
                
                var airtableKey = $('#integration-airtable-key').val();
                if (airtableKey) data.credentials.apiKey = airtableKey;
            }
            
            if (id) {
                data.id = id;
            }
            
            var action = id ? 'update_integration' : 'create_integration';
            
            this.apiRequest(action, data, function(err, result) {
                if (err) {
                    alert(err);
                } else {
                    $('#engageplus-integration-modal').hide();
                    location.reload();
                }
            });
        },

        deleteIntegration: function(id) {
            this.apiRequest('delete_integration', { id: id }, function(err) {
                if (err) {
                    alert(err);
                } else {
                    location.reload();
                }
            });
        },

        // Modal Events
        bindModalEvents: function() {
            $(document).on('click', '.engageplus-modal-close, .engageplus-modal-cancel', function() {
                $(this).closest('.engageplus-modal').hide();
            });

            $(document).on('click', '.engageplus-modal', function(e) {
                if ($(e.target).hasClass('engageplus-modal')) {
                    $(this).hide();
                }
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $('.engageplus-modal:visible').hide();
                }
            });
        }
    };

    $(document).ready(function() {
        EngagePlusAdmin.init();
    });

})(jQuery);
