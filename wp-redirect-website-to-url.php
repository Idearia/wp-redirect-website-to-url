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
define( __NAMESPACE__ . "\\DEBUG", false );

/**
 * Redirection URL
 */
define( __NAMESPACE__ . "\\DESTINATION_URL", "https://www.omai.it/omai-punti-vendita/" );

/**
 * If you want to redirect to a WordPress page or post, you must specify here
 * its WordPress ID; it will be used to prevent infinite redirection.
 */
define( __NAMESPACE__ . "\\DESTINATION_URL_ID", "32965" );

/**
 * WordPress IDs of Pages, where no redirection should occur; f.e. imprint or tos
 */
define( __NAMESPACE__ . "\\REDIRECTION_ID_BLOCKER", [101, 3, 96, 58] );

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
 * Any users with one of these IPs won't be redirected to the lockout URL,
 * independently from the login status or capabilities.
 */
define( __NAMESPACE__ . "\\WHITELIST_IPS", ["93.42.97.22"] );

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
	DEBUG && error_log( "current_user_can(USER_CAPABILITY) = " . var_export( current_user_can( USER_CAPABILITY ), true ) );
	DEBUG && error_log( "user_email = " . ( $user->user_email ?? "not set" ) );
	if ( DESTINATION_URL_ID ) {
		DEBUG && error_log( "get_post_status(DESTINATION_URL_ID) = " . var_export( get_post_status( DESTINATION_URL_ID ), true ) );
		DEBUG && error_log( "is_single(DESTINATION_URL_ID) = " . var_export( is_single( DESTINATION_URL_ID ), true ) );
		DEBUG && error_log( "is_page(DESTINATION_URL_ID) = " . var_export( is_page( DESTINATION_URL_ID ), true ) );
	}
	DEBUG && error_log( "WHITELIST_IPS = " . var_export( WHITELIST_IPS, true ) );
	DEBUG && error_log( "user_ip = " . getClientIp() );

	/* By default, redirect everybody and every page */
	$redirect = true;

	/* Do not redirect users with certain capabilities */
	if ( ! empty( USER_CAPABILITY ) ) {
		$redirect = $redirect && ( ! current_user_can( USER_CAPABILITY ) && ! is_wp_login() );
	}

	/* Do not redirect users with a certain email */
	if ( ! empty( $user->user_email ) && ( ! empty( USER_EMAILS ) || ! empty( PREVIEW_EMAILS ) ) ) {
		DEBUG && error_log( "email in list = " . ( in_array( $user->user_email, array_merge( USER_EMAILS, PREVIEW_EMAILS ) ) ? "yes" : "no" ) );
		$redirect = $redirect && ( ! in_array( $user->user_email, array_merge( USER_EMAILS, PREVIEW_EMAILS ) ) );
	}

	/* Prevent infinite redirection if we are already on the redirection URL */
	if ( DESTINATION_URL_ID && ( is_single( DESTINATION_URL_ID ) || is_page( DESTINATION_URL_ID ) ) ) {
		return; //return w.o. redirect
	}

	/* Prevent redirection if we are on wp-post that is mandatory */
	if ( is_array(REDIRECTION_ID_BLOCKER) && sizeof(REDIRECTION_ID_BLOCKER) > 0 ) {
		foreach(REDIRECTION_ID_BLOCKER as $blocker_id)
			if( is_single( $blocker_id ) || is_page( $blocker_id ) )
				return; //return w.o. redirect
		
	}

	/* Do not redirect users with these IPs */
	if ( ! empty( WHITELIST_IPS ) && in_array( getClientIp(), WHITELIST_IPS ) ) {
		return; //return w.o. redirect
	}

	/* Redirect the user */
	if ( $redirect ) {
		wp_redirect( esc_url_raw( DESTINATION_URL ), REDIRECT_STATUS_CODE );
		exit;
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

/**
 * Return the client's IP address.
 *
 * The algorithm uses the HTTP_CLIENT_IP and HTTP_X_FORWARDED_FOR
 * globals to infer the IP address; if they are not available (as
 * it is the case in most cases), it suse the REMOTE_ADDR global.
 *
 * Do not use this function to grant access or privileges to
 * IP addresses, because the HTTP_CLIENT_IP and
 * HTTP_X_FORWARDED_FOR globals can be easily spoofed.
 *
 * On the other hand, the REMOTE_ADDR global is very difficult
 * to spoof. However, when the client is beyond a proxy, it isn't
 * necessarily the correct IP.
 *
 * Returns the IP address if found, false if not. The output is
 * santized via FILTER_VALIDATE_IP.
 *
 * See here for more details: http://stackoverflow.com/questions/
 * 3003145/how-to-get-the-client-ip-address-in-php
 *
 * Created by Guido W. Pettinari on 05.09.2016.
 * Latest version here:
 * https://gist.github.com/coccoinomane/4c420776dc16d80ea772aff06d3e1ef4
 */
function getClientIp() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return filter_var($ip, FILTER_VALIDATE_IP);
}
