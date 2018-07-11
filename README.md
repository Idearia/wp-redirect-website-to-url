# WordPress Redirect Website to URL

Redirect all WordPress pages & posts to a hard-coded URL; doesn't affect the backend.

It uses the `template_redirect` action as suggested in https://wordpress.stackexchange.com/a/76807/86662.

# Usage

1. Download the PHP file and configure the hard-coded options to suit your needs.
2. Copy the PHP file to either the plugins directory or the mu-plugins directory; in the latter case, there's no need to activate the plugin.

# Options

The plugin has a bunch of hard-coded options, that can be changed right in the PHP file:

```php
/**
 * Print debug information to debug.log?
 */
define( __NAMESPACE__ . "\\DEBUG", false );

/**
 * Redirection URL
 */
define( __NAMESPACE__ . "\\DESTINATION_URL", "https://www.google.com" );

/**
 * If the redirection URL is a WordPress page or post, specify here its WordPress ID;
 * it will be used to prevent an infinite redirect loop.
 */
define( __NAMESPACE__ . "\\DESTINATION_URL_ID", "" );

/**
 * Users with this capability won't be redirected; leave blank to redirect everybody.
 */
define( __NAMESPACE__ . "\\USER_CAPABILITY", "manage_options" );

/**
 * Redirection status: 302 for temporary redirect, 301 for permanent redirect.
 */
define( __NAMESPACE__ . "\\REDIRECT_STATUS_CODE", "302" );
```
