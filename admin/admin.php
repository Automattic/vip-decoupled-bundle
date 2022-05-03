<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Admin;

/**
 * Determine whether the site has been decoupled. Currently, this is only a
 * function of whether a distinct home URL has been set.
 *
 * @return bool
 */
function is_decoupled() {
	$frontend = wp_parse_url( home_url(), PHP_URL_HOST );
	$backend  = wp_parse_url( site_url(), PHP_URL_HOST );

	return $frontend !== $backend;
}
