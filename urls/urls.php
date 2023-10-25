<?php
/**
 * The URLs module.
 * 
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\URLs;

use function WPCOMVIP\Decoupled\Admin\is_decoupled;

/**
 * Setting `home` and `siteurl` options to different values helps us set
 * permalinks correctly, but it causes some problems for resouces that we still
 * want to serve from WordPress. Filter those resources to use siteurl.
 *
 * @param  string $resource_url URL of a WordPress resource.
 * @return string
 */
function update_resource_url( $resource_url ) {
	if ( ! is_decoupled() ) {
		return $resource_url;
	}

	$home_path     = wp_make_link_relative( home_url() );
	$resource_path = wp_make_link_relative( $resource_url );

	if ( ! empty( $home_path ) ) {
		$resource_path = preg_replace( sprintf( '#^%s/*#', $home_path ), '/', $resource_path );
	}

	return site_url( $resource_path );
}

/**
 * Update the feed, rest API and media library resource urls to use siteurl.
 *
 * @return void
 */
function add_filters() {
	$filters = [
		// Feed links.
		'author_feed_link',
		'category_feed_link',
		'feed_link',
		'post_comments_feed_link',
		'tag_feed_link',
		'taxonomy_feed_link',

		// WP REST API.
		'rest_url',

		// Media library.
		'wp_get_attachment_url',
	];

	foreach ( $filters as $filter ) {
		add_filter( $filter, __NAMESPACE__ . '\\update_resource_url', 10, 1 );
	}
}
add_filters();
