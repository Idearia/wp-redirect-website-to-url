# wp-redirect-website-to-page

Redirect all WordPress pages & posts to a hard-coded URL; doesn't affect the backend.

It uses the `template_redirect` action as suggested in https://wordpress.stackexchange.com/a/76807/86662.

It has several hard-coded options right in the PHP file:

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
define( __NAMESPACE__ . "\\DESTINATION_URL_ID", "32965" );

/**
 * Users with this capability won't be redirected; leave blank to redirect everybody.
 */
define( __NAMESPACE__ . "\\USER_CAPABILITY", "manage_options" );

/**
 * Redirection status: 302 for temporary redirect, 301 for permanent redirect.
 */
define( __NAMESPACE__ . "\\REDIRECT_STATUS_CODE", "302" );
```
