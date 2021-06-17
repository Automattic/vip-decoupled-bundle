<?php
/**
 * Plugin Name: VIP Decoupled Plugin Bundle
 * Plugin URI: https://wpvip.com
 * Description: Plugin bundle to quickly provide a decoupled WordPress setup.
 * Author: WordPress VIP
 * Text Domain: vip-decoupled-bundle
 * Version: 0.1.0
 * Requires at least: 5.6.0
 * Tested up to: 5.7.1
 * Requires PHP: 7.2
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package vip-bundle-decoupled
 */

use function WPCOMVIP\Decoupled\Settings\is_plugin_enabled;

/**
 * Enable settings
 */
require_once __DIR__ . '/settings/settings.php';

/**
 * WPGraphQL 1.4.3
 */
if ( is_plugin_enabled( 'plugin_wpgraphql' ) ) {
	require_once __DIR__ . '/wp-graphql-1.4.3/wp-graphql.php';
}

/**
 * Make Gutenberg blocks available in WPGraphQL
 */
if ( is_plugin_enabled( 'plugin_blocks' ) ) {
	require_once __DIR__ . '/blocks/blocks.php';
}

/**
 * Adjust CORS headers
 */
require_once __DIR__ . '/cors/cors.php';

/**
 * Enable decoupled previews
 */
if ( is_plugin_enabled( 'plugin_wpgraphql' ) ) {
	require_once __DIR__ . '/preview/preview.php';
}

/**
 * Registration helpers
 */
require_once __DIR__ . '/registration/registration.php';

/**
 * Adjust resource URLs
 */
require_once __DIR__ . '/urls/urls.php';
