<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Preview;

require_once __DIR__ . '/token.php';

/**
 * Redirect preview requests to our decoupled frontend. Add a preview token to
 * the URL that will be echoed back and verified. (See validate_preview_request.)
 *
 * @return void
 */
function redirect_to_preview() {
	// If home and siteurl are the same, that indicates that the decoupled frontend
	// is not available. Do not redirect.
	if ( home_url() === site_url() ) {
		return;
	}

	if ( is_preview() || ( is_singular() && get_query_var( 'preview' ) ) ) {
		$post          = get_queried_object();
		$preview_token = generate_token( $post->ID, 'preview', 'edit_posts' );

		// If the user does not have permission to generate a preview token for this
		// post, the token will be false.
		if ( empty( $preview_token ) ) {
			wp_safe_redirect( home_url(), 302 );
			exit;
		}

		$preview_url = home_url( sprintf( '/preview/%s/%d', $preview_token, $post->ID ) );
		wp_safe_redirect( $preview_url, 302 );
		exit;
	}
}
add_action( 'template_redirect', __NAMESPACE__ . '\\redirect_to_preview', 10, 0 );

/**
 * Validate the preview token sent in X-Preview-Token request header. If valid.
 * allow the post to be viewed.
 *
 * @param  bool   $is_private Whether the data is considered private for the current user.
 * @param  string $model_name Name of the model (unused).
 * @param  mixed  $data       The raw data passed to the model.
 * @return bool
 */
function validate_preview_request( $is_private, $model_name, $data ) {
	// Already allowed?
	if ( false === $is_private ) {
		return $is_private;
	}

	// Not a post?
	if ( ! $data instanceof \WP_Post ) {
		return $is_private;
	}

	$header_name = 'HTTP_X_PREVIEW_TOKEN';
	$token       = null;
	$post_id     = $data->ID;

	// If this post is a revision, we need to get the parent post.
	$parent_post_id = wp_is_post_revision( $post_id );
	if ( ! empty( $parent_post_id ) ) {
		$post_id = $parent_post_id;
	}

	// Get preview token from request header.
	if ( isset( $_SERVER[ $header_name ] ) ) {
		$token = sanitize_text_field( wp_unslash( $_SERVER[ $header_name ] ) );
	}

	// No preview token sent?
	if ( empty( $token ) ) {
		return $is_private;
	}

	$token_is_valid = validate_token( $token, $post_id, 'preview' );

	return ! $token_is_valid;
}
add_filter( 'graphql_data_is_private', __NAMESPACE__ . '\\validate_preview_request', 10, 3 );
