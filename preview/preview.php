<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Preview;

/**
 * Filter preview URL to point to our decoupled frontend. Add a nonce to the URL
 * that will be echoed back and verified by WPGraphQL\Request
 *
 * @TODO Add UI to make this configurable
 *
 * @param  string  $preview_link Post preview link
 * @param  WP_Post $post         Post
 * @return string
 */
function decoupled_preview_link( $preview_link, $post ) {
	// WPGraphQL reuses the REST nonce action.
	$nonce = wp_create_nonce( 'wp_rest' ); // hardcoded to Next.js boilerplate

	// @TODO Accommodate custom post types.
	return home_url( "/posts/preview/{$nonce}/{$post->ID}" );
}
add_filter( 'preview_post_link', __NAMESPACE__ . '\\decoupled_preview_link', 10, 2 );
