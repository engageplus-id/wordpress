# Changelog

All notable changes to the EngagePlus WordPress Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-02-19

### Added
- **Management API Integration** - Full integration with EngagePlus Management API
- **Provider Management Page** - Add, edit, test, and delete OIDC identity providers
- **Widget Configuration Page** - Customize widget branding, auth flow, layout, and auth methods
- **Webhooks Management Page** - Create and manage webhook subscriptions for auth events
- **Data Integrations Page** - Configure Supabase and Airtable integrations
- **Email Providers Page** - Configure email delivery (SendGrid, Mailgun, Postmark, SES, SMTP)
- **Metrics Dashboard** - View authentication analytics with Chart.js visualizations
- **API Client Class** - New `EngagePlus_API_Client` class for all API interactions
- **Admin JavaScript** - Full admin interface with modals, forms, and AJAX handling
- API Key setting for Management API authentication
- Organization info display on dashboard
- Service success rate metrics

### Changed
- Reorganized admin menu to top-level with submenus
- Settings page now at EngagePlus > Settings
- Modernized admin interface with card-based layout
- Updated admin styles with new components (modals, badges, metrics cards)
- Plugin link on plugins page now points to new settings location

### Security
- API key stored securely in WordPress options
- AJAX requests protected with nonces
- Capability checks on all admin pages

## [1.1.0] - 2026-02-19

### Changed
- Updated to new OPWidget PKCE-based authentication flow
- Changed configuration from Client ID to Organization ID
- Widget styling is now configured in EngagePlus dashboard (removed local theme/button_text settings)
- Updated widget script URL from `engageplus.id/widget.js` to `auth.engageplus.id/public/pkce.js`
- Simplified shortcode attributes (removed styling options)
- Updated redirect URI to use REST API callback endpoint

### Added
- JWT token decoding for extracting user data from ID tokens
- `get_callback_url()` method for generating redirect URI

### Removed
- Local widget appearance settings (theme, button_text, show_labels)
- API Base URL setting (no longer needed)

## [1.0.0] - 2024-12-18

### Added
- Initial release of EngagePlus WordPress Plugin
- Core authentication functionality with EngagePlus widget
- Admin settings page for configuration
- Shortcode `[engageplus]` for embedding login widget
- WordPress widget for sidebar placement
- Automatic user creation for new OAuth users
- WordPress login page integration
- User meta storage for OAuth provider information
- Light and dark theme support
- Debug mode for troubleshooting
- REST API endpoint for authentication callback
- AJAX-based authentication handling
- Hooks for custom integrations (`engageplus_user_created`, `engageplus_user_login`)
- Redirect configuration after login
- Username pattern configuration (email or name)
- Skip email verification option
- Multiple widget instances support

### Security
- CSRF protection with WordPress nonces
- Input sanitization and validation
- Secure password generation for new users
- HTTPS enforcement recommendation
