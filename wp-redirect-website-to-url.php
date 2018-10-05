<?php
namespace Idearia\WP_Redirect_Website_To_Url;
/*
 * Plugin Name: Redirect Website to URL
 * Plugin URI: https://github.com/Idearia/wp-redirect-website-to-url
 * Version: 0.1
 * Description: Redirect all WordPress pages/posts in the website frontend to a hard-coded URL; uses template_redirect as suggested in https://wordpress.stackexchange.com/a/76807/86662
 * Author: Idearia Srl
 * Author URI: https://www.idearia.it
 * Requires at least: 3.0.0
 *
 * Text Domain: wp-redirect-website-to-url
 * Domain Path: /languages/
 * License: GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @author Idearia Srl
 */


/**
 * TODO: In own subfolder installations, why does the plugin always allow to log-in
 * using the full wp-login.php URL (ex. https://www.omai.it/cms/wp-login.php), regardless
 * of the result of is_wp_login()?
 * TODO: Verify if is_wp_login() works as intended.
 */

/**
 * Print debug information to debug.log?
 */
define( __NAMESPACE__ . "\\DEBUG", true );

/**
 * Redirection URL
 */
define( __NAMESPACE__ . "\\DESTINATION_URL", "https://www.omai.it/omai-punti-vendita/" );

/**
 * If the redirection URL is a WordPress page or post, specify here its WordPress ID
 */
define( __NAMESPACE__ . "\\DESTINATION_URL_ID", "32965" );

/**
 * Logged-in users with this capability won't be redirected; leave blank to redirect
 * everybody.
 */
define( __NAMESPACE__ . "\\USER_CAPABILITY", "manage_options" );

/**
 * Logged-in users with one of these emails won't be redirected to the lockout URL,
 * independently from their capabilities; leave blank to redirect everybody.
 */
define( __NAMESPACE__ . "\\USER_EMAILS", [] );

/**
 * Logged-in users with one of these emails won't be redirected to the lockout URL;
 * after they log-in however they will be redirected to the home page.
 */
define( __NAMESPACE__ . "\\PREVIEW_EMAILS", ["site-preview@idearia.it"] );

/**
 * Redirection status: 302 for temporary redirect, 301 for permanent redirect.
 */
define( __NAMESPACE__ . "\\REDIRECT_STATUS_CODE", "302" );


// =========
// = HOOKS =
// =========

add_action( 'template_redirect', __NAMESPACE__ . '\\wp_redirect_website_to_url' );
add_filter( 'login_redirect', __NAMESPACE__ . '\\login_redirect', 10, 3 );


// ==================
// = HOOK FUNCTIONS =
// ==================

/**
 * Redirect all pages/posts to a URL
 */
function wp_redirect_website_to_url() {

	$user = wp_get_current_user();

	/* Debug */
	DEBUG && error_log( "DENTRO wp_redirect_website_to_url" );
	DEBUG && error_log( "USER_CAPABILITY = " . USER_CAPABILITY );
	DEBUG && error_log( "DESTINATION_URL_ID = " . DESTINATION_URL_ID );
	DEBUG && error_log( "DESTINATION_URL = " . DESTINATION_URL );
	DEBUG && error_log( "get_post_status(DESTINATION_URL_ID) = " . var_export( get_post_status( DESTINATION_URL_ID ), true ) );
	DEBUG && error_log( "current_user_can(USER_CAPABILITY) = " . var_export( current_user_can( USER_CAPABILITY ), true ) );
	DEBUG && error_log( "is_single(DESTINATION_URL_ID) = " . var_export( is_single( DESTINATION_URL_ID ), true ) );
	DEBUG && error_log( "is_page(DESTINATION_URL_ID) = " . var_export( is_page( DESTINATION_URL_ID ), true ) );
	DEBUG && error_log( "user_email = " . ( $user->user_email ?? "not set" ) );

	/* By default, redirect everybody and every page */
	$redirect = true;

	/* Do not redirect users with certain capabilities */
	if ( ! empty( USER_CAPABILITY ) ) {
		$redirect = $redirect && ( ! current_user_can( USER_CAPABILITY ) && ! is_wp_login() );
	}

	/* Do not redirect users with a certain email */
	if ( ! empty( $user->user_email ) && ( ! empty( USER_EMAILS ) || ! empty( PREVIEW_EMAILS ) ) ) {
		error_log( "email in list = " . ( in_array( $user->user_email, array_merge( USER_EMAILS, PREVIEW_EMAILS ) ) ? "yes" : "no" ) );
		$redirect = $redirect && ( ! in_array( $user->user_email, array_merge( USER_EMAILS, PREVIEW_EMAILS ) ) );
	}

	/* Redirect the user */
	if ( $redirect ) {
		if ( ! is_single( DESTINATION_URL_ID ) && ! is_page( DESTINATION_URL_ID ) ) {
			wp_redirect( esc_url_raw( DESTINATION_URL ), REDIRECT_STATUS_CODE );
			exit;
		}
	}

}


/**
 * Check if the user is attempting to login
 *
 * Should works also if the login URL is custom (i.e. different from
 * wp-login.php).
 *
 * Source: https://wordpress.stackexchange.com/a/237285/86662
 */
function is_wp_login() {

	/* Get a properly separated path */
	$ABSPATH = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, ABSPATH );
	
	/* Files included or required by the current script */
	$included_files  = get_included_files();
	
	/* Conditions that imply the user is on the login page */
	$conditions = [
		in_array( $ABSPATH . 'wp-login.php', $included_files ),
		in_array( $ABSPATH . 'wp-register.php', $included_files ),
		$GLOBALS['pagenow'] === 'wp-login.php',
		$_SERVER['PHP_SELF'] === '/wp-login.php'
	];

	/* Debug */
	// error_log( $ABSPATH );
	// error_log( print_r( get_included_files(), true ) );
	// error_log( print_r( $conditions, true ) );
	// error_log( print_r( $GLOBALS['pagenow'], true ) );
	// error_log( print_r( $_SERVER['PHP_SELF'], true ) );

	/* Return true if any of these conditions are true */
	return in_array( true, $conditions, true );

}


/**
 * Redirect preview users to the homepage after login. 
 */
function login_redirect( $redirect_to, $request, $user ){
	if ( ! empty( $user->user_email ) && in_array( $user->user_email, PREVIEW_EMAILS ) ) {
		return home_url();
	}
	else {
		return $redirect_to;
	}
}

