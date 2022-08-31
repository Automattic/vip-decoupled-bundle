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

namespace WPCOMVIP\Decoupled;

use function WPCOMVIP\Decoupled\Settings\is_plugin_enabled;

/**
 * Admin UI and helpers
 */
require_once __DIR__ . '/admin/admin.php';

/**
 * Enable settings
 */
require_once __DIR__ . '/settings/settings.php';

/**
 * Load plugin.php to ensure we can access `is_plugin_active` function.
 */
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * WPGraphQL 1.6.12
 *
 * Load only if the site or network does not have an active GraphQL plugin.
 */
if ( is_plugin_enabled( 'wpgraphql' ) && ! is_plugin_active( 'wp-graphql/wp-graphql.php' ) ) {
	require_once __DIR__ . '/lib/wp-graphql-1.6.12/wp-graphql.php';
}

/**
 * Make Gutenberg blocks available in WPGraphQL
 */
if ( is_plugin_enabled( 'blocks' ) ) {
	require_once __DIR__ . '/blocks/blocks.php';
}

/**
 * Adjust CORS headers
 */
require_once __DIR__ . '/cors/cors.php';

/**
 * Enable decoupled previews
 */
if ( is_plugin_enabled( 'preview' ) ) {
	require_once __DIR__ . '/preview/preview.php';
}

/**
 * Automatic type registration
 */
if ( is_plugin_enabled( 'registration' ) ) {
	require_once __DIR__ . '/registration/registration.php';
}

/**
 * Adjust resource URLs
 */
require_once __DIR__ . '/urls/urls.php';

/**
 * Force-enable schema introspection. If schema introspection is disabled, code
 * generation and, therefore, the Next.js build will fail.
 *
 * @param  string $value       The current value of the setting.
 * @param  string $default     The default value of the setting.
 * @param  string $option_name The setting name.
 * @return string
 */
function force_enable_schema_introspection( $value, $default, $option_name ) {
	if ( 'public_introspection_enabled' === $option_name ) {
		return 'on';
	}

	return $value;
}
add_filter( 'graphql_get_setting_section_field_value', __NAMESPACE__ . '\\force_enable_schema_introspection', 10, 3 );
