<?php
/**
 * Plugin Name: VIP Decoupled Plugin Bundle
 * Plugin URI: https://github.com/Automattic/vip-decoupled-bundle
 * Description: Plugin bundle to quickly provide a decoupled WordPress setup.
 * Author: WordPress VIP
 * Text Domain: vip-decoupled-bundle
 * Version: 1.2.0
 * Requires at least: 5.9.0
 * Tested up to: 6.4.0
 * Requires PHP: 7.4
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled;

use function WPCOMVIP\Decoupled\Settings\is_plugin_enabled;

/**
 * Admin UI and helpers.
 */
require_once __DIR__ . '/admin/admin.php';

/**
 * Enable settings.
 */
require_once __DIR__ . '/settings/settings.php';

/**
 * WPGraphQL 1.19.0.
 */
if ( is_plugin_enabled( 'wpgraphql' ) ) {
	require_once __DIR__ . '/lib/wp-graphql-1.19.0/wp-graphql.php';
}

/**
 * Make Gutenberg blocks available in WPGraphQL.
 */
if ( is_plugin_enabled( 'blocks' ) ) {
	require_once __DIR__ . '/blocks/blocks.php';
}

/**
 * VIP Block Data API.
 */
if ( is_plugin_enabled( 'block-data-api' ) ) {
	require_once __DIR__ . '/lib/vip-block-data-api/vip-block-data-api.php';
}

/**
 * Adjust CORS headers.
 */
require_once __DIR__ . '/cors/cors.php';

/**
 * Enable decoupled previews.
 */
if ( is_plugin_enabled( 'preview' ) ) {
	require_once __DIR__ . '/preview/preview.php';
}

/**
 * Automatic type registration.
 */
if ( is_plugin_enabled( 'registration' ) ) {
	require_once __DIR__ . '/registration/registration.php';
}

/**
 * Adjust resource URLs.
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

require_once __DIR__ . '/blocks/vip-smart-layout.php';
