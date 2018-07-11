<?php
namespace Idearia\WP_Redirect_Website_To_Url;
/*
 * Plugin Name: Redirect Website to URL
 * Version: 1
 * Description: Redirect all WordPress pages/posts in the website frontend to a hard-coded URL; uses template_redirect as suggested in https://wordpress.stackexchange.com/a/76807/86662
 * Author: Idearia Srl
 * Author URI: http://www.idearia.it
 * Requires at least: 3.0.0
 *
 * Text Domain: wp-redirect-website-to-url
 * Domain Path: /languages/
 *
 * @author Idearia Srl
 */

/**
 * Print debug information to debug.log?
 */
define( __NAMESPACE__ . "\\DEBUG", false );

/**
 * Redirection URL
 */
define( __NAMESPACE__ . "\\DESTINATION_URL", "https://www.omai.it/omai-punti-vendita/" );

/**
 * If the redirection URL is a WordPress page or post, specify here its WordPress ID
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


// =========
// = HOOKS =
// =========

add_action( 'template_redirect', __NAMESPACE__ . '\\wp_redirect_website_to_url' );


// ==================
// = HOOK FUNCTIONS =
// ==================

/**
 * Redirect all pages/posts to a URL
 */
function wp_redirect_website_to_url() {

	/* Debug */
	DEBUG && error_log( "DENTRO wp_redirect_website_to_url" );
	DEBUG && error_log( "USER_CAPABILITY = " . USER_CAPABILITY );
	DEBUG && error_log( "DESTINATION_URL_ID = " . DESTINATION_URL_ID );
	DEBUG && error_log( "DESTINATION_URL = " . DESTINATION_URL );
	DEBUG && error_log( "get_post_status(DESTINATION_URL_ID) = " . var_export( get_post_status( DESTINATION_URL_ID ), true ) );
	DEBUG && error_log( "current_user_can(USER_CAPABILITY) = " . var_export( current_user_can( USER_CAPABILITY ), true ) );
	DEBUG && error_log( "is_single(DESTINATION_URL_ID) = " . var_export( is_single( DESTINATION_URL_ID ), true ) );
	DEBUG && error_log( "is_page(DESTINATION_URL_ID) = " . var_export( is_page( DESTINATION_URL_ID ), true ) );

	if ( empty( USER_CAPABILITY ) || ! current_user_can( USER_CAPABILITY ) ) {
		if ( ! is_single( DESTINATION_URL_ID ) && ! is_page( DESTINATION_URL_ID ) ) {
			wp_redirect( esc_url_raw( DESTINATION_URL ), REDIRECT_STATUS_CODE );
			exit;
		}
	}

}



