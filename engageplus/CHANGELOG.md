# Changelog

All notable changes to the EngagePlus WordPress Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
