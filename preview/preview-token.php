<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Preview;

/**
 * Retrieve or generate a secret that can be used to generate a time-based
 * authentication token.
 *
 * @return string
 */
function get_preview_token_secret() {
	$option_name = 'vip_decoupled_preview_secret';

	$secret = get_option( $option_name );

	if ( empty( $secret ) ) {
		$secret = wp_generate_password( 64, true, true );
		update_option( $option_name, $secret );
	}

	return $secret;
}

/**
 * Get preview token lifetime (expiration period) in seconds.
 *
 * @return int
 */
function get_preview_token_lifetime_in_seconds() {
	$ten_minutes_in_seconds = 60 * 10;
	$one_hour_in_seconds    = 60 * 60;
	$default_lifetime       = $ten_minutes_in_seconds;
	$max_lifetime           = $one_hour_in_seconds;

	/**
	 * Filter the allowed token lifetime.
	 *
	 * @param int $default_lifetime Preview token lifetime in seconds.
	 */
	$token_lifetime = apply_filters( 'vip_decoupled_preview_token_lifetime', $default_lifetime );

	// Enforce a maximum token lifetime.
	if ( $token_lifetime > $max_lifetime ) {
		return $max_lifetime;
	}

	return $token_lifetime;
}

/**
 * Generate a hash of a post ID and expiration date that can be used to lock a
 * preview token to a specific post and time window.
 *
 * @param  int    $post_id    Post ID.
 * @param  int    $expiration UNIX timestamp of message expiration in seconds.
 * @return string
 */
function generate_preview_hash( $post_id, $expiration ) {
	$message_data = sprintf( 'preview:%d:%d', $post_id, $expiration );

	// Generate a secure hash of the message data. Since the message data encodes
	// information that restricts the token to a single post within a specific
	// time window, we can validate it later to ensure it is not being reused
	// outside its intended use.
	return hash_hmac( 'sha256', $message_data, get_preview_token_secret() );
}

/**
 * Generate a time-based authentication token that can be returned with a
 * subequent request and validated. The token is locked to a specific post ID and
 * a timestamp in the future. Upon verification, both the post ID and the
 * timestamp should be verified. (See validate_preview_token.)
 *
 * @param  int    $post_id Post ID.
 * @return string
 */
function generate_preview_token( $post_id ) {
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html( __( 'You do not have sufficient permissions to access this page.' ) ) );
	}

	$token_lifetime = get_preview_token_lifetime_in_seconds();
	$expiration     = time() + $token_lifetime;

	$hash = generate_preview_hash( $post_id, $expiration );

	// We must additionally encode the expiration timestamp in the token so that it
	// can be validated when we receive the token. Use a simple delimiter.
	return sprintf( '%s_%d', $hash, $expiration );
}

/**
 * Validate a preview token that has been sent with a request.
 *
 * @param  string $token   Preview token.
 * @param  int    $post_id ID of the post being previewed.
 * @return bool
 */
function validate_preview_token( $token, $post_id ) {
	$token_parts = explode( '_', $token );

	if ( 2 !== count( $token_parts ) ) {
		graphql_debug(
			__( 'Malformed preview token.' ),
			[
				'type' => 'MALFORMED_PREVIEW_TOKEN',
			]
		);

		return false;
	}

	$expiration = intval( $token_parts[1] );
	$sent_hash  = $token_parts[0];

	// Has the sent token expired?
	if ( $expiration < time() ) {
		graphql_debug(
			__( 'Preview token has expired.' ),
			[
				'type' => 'EXPIRED_PREVIEW_TOKEN',
			]
		);
		return false;
	}

	// Does the sent hash match the expected hash?
	$expected_hash = generate_preview_hash( $post_id, $expiration );
	if ( $sent_hash !== $expected_hash ) {
		graphql_debug(
			__( 'Invalid preview token.' ),
			[
				'type' => 'INVALID_PREVIEW_TOKEN',
			]
		);
		return false;
	}

	return true;
}
