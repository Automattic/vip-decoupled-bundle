<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\CORS;

/**
 * Add development origins. Filter `allowed_http_origins` to provide access to
 * additional development domains.
 *
 * @TODO Add UI to let plugin users specify additional origins.
 *
 * @param  string[] $origins HTTP origins
 * @return string[]
 */
function add_development_origins( $origins ) {
	$origins[] = 'http://localhost:3000'; // hardcoded for now

	return $origins;
}
add_filter( 'allowed_http_origins', __NAMESPACE__ . '\\add_development_origins', 10, 1 );

/**
 * The default WPGraphQL CORS headers are wide-open; instead, restrict to known
 * allowed origins.
 *
 * @param  array $headers Default CORS headers from WPGraphQL.
 * @return array
 */
function enforce_allowed_origins( $headers ) {
	$origin = get_http_origin();

	if ( is_allowed_http_origin( $origin ) ) {
		$headers['Access-Control-Allow-Origin'] = $origin;
	}

	return $headers;
}
add_filter( 'graphql_response_headers_to_send', __NAMESPACE__ . '\\enforce_allowed_origins', 10, 1 );
