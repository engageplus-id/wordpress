# EngagePlus WordPress Plugin

Add social login to your WordPress site using any OIDC provider with EngagePlus - a lightweight, data-agnostic authentication platform.

![WordPress Plugin Version](https://img.shields.io/badge/version-1.0.0-blue)
![WordPress Minimum Version](https://img.shields.io/badge/wordpress-%3E%3D5.0-green)
![PHP Minimum Version](https://img.shields.io/badge/php-%3E%3D7.4-purple)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-orange)

## Description

EngagePlus is a lightweight JavaScript widget that enables social login via any OIDC provider. This WordPress plugin provides seamless integration with EngagePlus, allowing your users to sign in with Google, GitHub, Microsoft, LinkedIn, and many more providers.

### Features

- 🔐 **Social Login** - Support for Google, GitHub, Microsoft, LinkedIn, X, Facebook, and custom OIDC providers
- ⚡ **Lightweight** - Minimal footprint with efficient JavaScript widget
- 🎨 **Customizable** - Light/dark themes, custom button text, flexible placement
- 🔄 **Auto User Creation** - Automatically create WordPress accounts for new OAuth users
- 📱 **Responsive** - Works seamlessly on all devices
- 🔒 **Secure** - HTTPS required, PKCE flow, no user data stored by EngagePlus
- 🧩 **Multiple Widgets** - Place multiple widgets with different configurations
- 📊 **Debug Mode** - Built-in logging for troubleshooting

## Installation

### From WordPress Admin

1. Download the plugin zip file
2. Go to **Plugins** > **Add New** > **Upload Plugin**
3. Upload the zip file and click **Install Now**
4. Activate the plugin

### Manual Installation

1. Download and extract the plugin files
2. Upload the `engageplus` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress

### From GitHub

```bash
cd wp-content/plugins
git clone https://github.com/engageplus-id/wordpress.git engageplus
```

## Configuration

### Step 1: Create an EngagePlus Account

1. Visit [engageplus.id](https://engageplus.id) and create a free account
2. Configure your OAuth providers (Google, GitHub, Microsoft, etc.) in the dashboard
3. Customize your widget appearance
4. Copy your **Client ID**

### Step 2: Configure the Plugin

1. Go to **Settings** > **EngagePlus** in your WordPress admin
2. Enter your **Client ID**
3. Configure user settings:
   - **Auto Create Users**: Enable to create WordPress accounts for new OAuth users
   - **Default Role**: Choose which role to assign to new users
   - **Username Pattern**: Use email or display name for usernames
   - **Skip Email Verification**: Trust OAuth provider verification (recommended)
4. Customize widget appearance:
   - **Button Text**: Text displayed on the login button
   - **Theme**: Light or dark theme
   - **Show Provider Labels**: Display provider names next to icons
5. Save settings

### Step 3: Configure Redirect URIs

In your EngagePlus dashboard, add your WordPress site URL as a redirect URI:

```
https://yoursite.com
```

## Usage

### Shortcode

Add the login widget anywhere using the shortcode:

```
[engageplus]
```

#### Shortcode Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| `id` | Auto-generated | Unique container ID |
| `button_text` | Global setting | Custom button text |
| `theme` | Global setting | `light` or `dark` |
| `show_labels` | Global setting | Show provider names |
| `hide_logged_in` | `true` | Hide widget for logged-in users |
| `show_logout` | `false` | Show logout button when logged in |

#### Examples

```
[engageplus button_text="Sign In with Social"]
[engageplus theme="dark"]
[engageplus hide_logged_in="false" show_logout="true"]
[engageplus button_text="Get Started" theme="dark"]
```

### Widget

1. Go to **Appearance** > **Widgets**
2. Add the **EngagePlus Login** widget to any widget area
3. Configure widget settings:
   - Title
   - Container ID
   - Button text
   - Theme
   - Visibility options

### Login Page Integration

The widget automatically appears on the WordPress login and registration pages, providing an alternative login method.

## User Management

### Automatic User Creation

When enabled, new users are created with:
- Email from OAuth provider
- Username based on your pattern setting
- Default role as configured
- Email marked as verified (if skip verification enabled)

### Existing Users

If a user with the same email already exists:
- They are logged in automatically
- No duplicate account is created
- OAuth provider info is stored in user meta

### User Meta

The plugin stores the following user meta:
- `engageplus_provider` - OAuth provider used
- `engageplus_provider_id` - Provider's user ID
- `engageplus_picture` - Profile picture URL
- `engageplus_created` - Account creation timestamp
- `engageplus_last_login` - Last login timestamp

## Hooks & Filters

### Actions

```php
// Fired after a new user is created via EngagePlus
do_action('engageplus_user_created', $user_id, $user_data);

// Fired after a user logs in via EngagePlus
do_action('engageplus_user_login', $user, $user_data);
```

### Example: Custom Logic on Login

```php
add_action('engageplus_user_login', function($user, $user_data) {
    // Log the login
    error_log('User logged in via EngagePlus: ' . $user->user_email);
    
    // Send notification
    wp_mail(
        get_option('admin_email'),
        'New EngagePlus Login',
        'User ' . $user->display_name . ' logged in via ' . $user_data['provider']
    );
}, 10, 2);
```

### Example: Modify User Creation

```php
add_action('engageplus_user_created', function($user_id, $user_data) {
    // Add custom user meta
    update_user_meta($user_id, 'registration_source', 'engageplus');
    update_user_meta($user_id, 'oauth_provider', $user_data['provider']);
    
    // Subscribe to newsletter
    if (function_exists('newsletter_subscribe')) {
        newsletter_subscribe($user_data['email']);
    }
}, 10, 2);
```

## Customization

### CSS Customization

Override default styles in your theme:

```css
/* Widget container */
.engageplus-widget {
    margin: 20px 0;
}

/* Messages */
.engageplus-message-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

/* Logout button */
.engageplus-logout-btn {
    background-color: #dc3545;
    border-radius: 4px;
}

/* Login form separator */
.engageplus-separator {
    color: #666;
}
```

### JavaScript Events

Listen for authentication events:

```javascript
// Using jQuery
jQuery(document).on('engageplus:login', function(e, user) {
    console.log('User logged in:', user);
});

jQuery(document).on('engageplus:logout', function() {
    console.log('User logged out');
});

jQuery(document).on('engageplus:error', function(e, error) {
    console.error('Auth error:', error);
});

// Access the handler
if (window.EngagePlusWP) {
    console.log('Widgets:', EngagePlusWP.widgets);
}
```

## Troubleshooting

### Widget Not Appearing

1. **Check Client ID**: Ensure Client ID is configured in settings
2. **Check Block Placement**: Verify shortcode/widget is in visible area
3. **Clear Cache**: Clear any caching plugins and browser cache
4. **Check JavaScript Console**: Look for errors in browser developer tools

### Authentication Fails

1. **Check Redirect URI**: Verify your site URL is added in EngagePlus dashboard
2. **Check Browser Console**: Look for JavaScript errors
3. **Enable Debug Mode**: Enable in settings and check PHP error log
4. **Verify OAuth Config**: Ensure providers are configured in EngagePlus

### Users Not Being Created

1. **Check Auto-Create Setting**: Ensure "Auto Create Users" is enabled
2. **Check Default Role**: Verify the default role exists
3. **Check PHP Logs**: Look for errors in WordPress debug log
4. **Email Conflicts**: User may already exist with that email

### Widget Shows But Doesn't Work

1. **Check HTTPS**: EngagePlus requires HTTPS in production
2. **Check Script Loading**: Verify widget.js loads in Network tab
3. **Check Conflicts**: Disable other plugins to test for conflicts
4. **Check Ad Blockers**: Some ad blockers may block OAuth popups

## Security

### Best Practices

1. **Use HTTPS** - Always use HTTPS in production
2. **Keep Updated** - Keep the plugin updated to latest version
3. **Limit Roles** - Assign minimal default role to new users
4. **Monitor Logs** - Regularly check authentication logs
5. **Review Access** - Periodically review OAuth user accounts

### Data Privacy

- User data is stored ONLY in your WordPress database
- No user data is stored by EngagePlus
- OAuth tokens are short-lived (1 hour expiry)
- PKCE flow used for enhanced security

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- HTTPS recommended (required for production OAuth)
- JavaScript enabled in browser

## Support

- **Documentation**: [engageplus.id/docs](https://engageplus.id/docs)
- **GitHub Issues**: [github.com/engageplus-id/wordpress/issues](https://github.com/engageplus-id/wordpress/issues)
- **Email Support**: [support@engageplus.id](mailto:support@engageplus.id)

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This plugin is licensed under the GPL-2.0+ license. See [LICENSE](LICENSE) for details.

## Changelog

### 1.0.0 (2024-12-18)
- Initial release
- Basic widget integration
- User creation and login
- WordPress login page integration
- Admin settings page
- Shortcode and widget support
- Debug mode for troubleshooting

---

Made with ❤️ by the [EngagePlus Team](https://engageplus.id)

