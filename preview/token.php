<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Preview;

/**
 * Get token post meta key.
 *
 * @return string
 */
function get_meta_key() {
	return 'vip_decoupled_token';
}

/**
 * Get token lifetime (expiration period) in seconds.
 *
 * @param  string $action Action that will be performed with this token.
 * @return int
 */
function get_token_lifetime_in_seconds( $action ) {
	$one_hour_in_seconds = 60 * 60;
	$default_lifetime    = $one_hour_in_seconds;
	$max_lifetime        = $one_hour_in_seconds * 3;

	/**
	 * Filter the allowed token lifetime.
	 *
	 * @param int    $default_lifetime Token lifetime in seconds.
   * @param string $action           Action that will be performed with this token.
	 */
	$token_lifetime = apply_filters( 'vip_decoupled_token_lifetime', $default_lifetime, $action );

	// Enforce a maximum token lifetime.
	if ( $token_lifetime > $max_lifetime ) {
		return $max_lifetime;
	}

	return $token_lifetime;
}

/**
 * Generate a one-time-use authentication token that can be returned with a
 * subequent request and validated. Upon verification, both the token and the
 * expiration should be validated. (See validate_token.)
 *
 * @param  int    $post_id Post ID.
 * @param  string $action  Action that will be performed with this token.
 * @param  string $cap     Capability required to generate this token.
 * @return array
 */
function generate_token( $post_id, $action, $cap ) {
	if ( ! current_user_can( $cap, $post_id ) ) {
		wp_die( esc_html( __( 'You do not have sufficient permissions to access this page.' ) ) );
	}

	$token_lifetime = get_token_lifetime_in_seconds( $action );
	$expiration     = time() + $token_lifetime;
	$secret         = wp_generate_password( 64, true, true );
	$token          = hash_hmac( 'sha256', $action, $secret );

	$meta_value = [
		'action'     => $action,
		'expiration' => $expiration,
		'user'       => get_current_user(), // not validated, just stored in case it's interesting.
		'token'      => $token,
		'version'    => 1,                  // not validated, but might be useful in future.
	];

	add_post_meta( $post_id, get_meta_key(), $meta_value );

	return $token;
}

/**
 * Validate a token that has been sent with a request.
 *
 * @param  string $token   Token.
 * @param  int    $post_id Post ID.
 * @param  string $action  Token action.
 * @return bool
 */
function validate_token( $token, $post_id, $action ) {
	$meta_key     = get_meta_key();
	$saved_tokens = get_post_meta( $post_id, $meta_key, false );
	$current_time = time();
	$is_valid     = false;

	foreach ( $saved_tokens as $saved ) {
		$is_expired = false;

		// Check for token expiration.
		if ( ! isset( $saved['expiration'] ) || $current_time > $saved['expiration'] ) {
			$is_expired = true;
		}

		// Check if token matches. If it does, mark as expired.
		if ( ! $is_expired && $token === $saved['token'] && $action === $saved['action'] ) {
			$is_expired = true;
			$is_valid   = true;
		}

		// Delete expired tokens.
		if ( $is_expired ) {
			delete_post_meta( $post_id, $meta_key, $saved );
		}
	}

	if ( $is_valid ) {
		return true;
	}

	graphql_debug(
		__( 'Invalid token.' ),
		[
			'type' => 'INVALID_TOKEN',
		]
	);

	return false;
}
