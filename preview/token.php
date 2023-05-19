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
 * @param string $action  Action that will be performed with this token.
 * @param int    $post_id Post ID.
 * @return int
 */
function get_token_lifetime_in_seconds( $action, $post_id ) {
	$default_lifetime = HOUR_IN_SECONDS;
	$max_lifetime     = 3 * HOUR_IN_SECONDS;

	/**
	 * Filter the allowed token lifetime.
	 *
	 * @param int    $default_lifetime Token lifetime in seconds.
	 * @param string $action           Action that will be performed with this token.
	 * @param int    $post_id          Post ID.
	 */
	$token_lifetime = (int) apply_filters( 'vip_decoupled_token_lifetime', $default_lifetime, $action, $post_id );

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

	$token_lifetime = get_token_lifetime_in_seconds( $action, $post_id );
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
			/**
			 * Filter whether to expire the token on use. By default, tokens are
			 * "one-time use" and we mark them as expired as soon as they are used.
			 * If you want to allow tokens to be used more than once, filter this
			 * value to `false`. Understand the security implications of this change:
			 * Within the expiration window, tokens / preview URLs become bearer
			 * tokens for viewing the associated draft post preview. Anyone who
			 * possesses them will be able to view and share the preview, even if they
			 * are not an authorized WordPress user, and could share them with anyone
			 * else.
			 *
			 * @param bool   $expire_on_use Whether the token should expire on use.
			 * @param string $action        Action that will be performed with this token.
			 * @param int    $post_id       Post ID.
			 */
			$expire_on_use = (bool) apply_filters( 'vip_decoupled_token_expire_on_use', true, $action, $post_id );

			if ( $expire_on_use ) {
				$is_expired = true;
			}

			$is_valid = true;
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
