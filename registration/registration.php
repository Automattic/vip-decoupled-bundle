<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Registration;

/**
 * Filter custom post type args and auto-register post types that are public, not
 * built-in, and don't already have a "show_in_graphql" value set.
 *
 * @param  array  $args      Custom post type registration args.
 * @param  string $post_type Post type slug.
 * @return array
 */
function register_custom_post_types( $args, $post_type ) {
	if (
		isset( $args['show_in_graphql'] ) ||
		true === $args['_builtin'] ||
		false === $args['public']
	) {
		return $args;
	}

	$slug = str_replace(
		[ ' ', '-', '_' ],
		'',
		ucwords( trim( $post_type ), ' _-' )
	);

	if ( 's' === substr( $slug, -1 ) && strlen( $slug ) > 1 ) {
		$slug = substr( $slug, 0, -1 );
	}

	$args['show_in_graphql']     = true;
	$args['graphql_single_name'] = $slug;
	$args['graphql_plural_name'] = sprintf( '%s%s', $slug, 's' );

	return $args;
}
add_filter( 'register_post_type_args', __NAMESPACE__ . '\\register_custom_post_types', 10, 2 );
