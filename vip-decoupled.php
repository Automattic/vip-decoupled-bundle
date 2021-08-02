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
 * Determine whether the site has been decoupled. Currently, this is only a
 * function of whether a distinct home URL has been set.
 *
 * @return bool
 */
function is_decoupled() {
	static $is = null;

	if ( null === $is ) {
		$frontend = wp_parse_url( home_url(), PHP_URL_HOST );
		$backend  = wp_parse_url( site_url(), PHP_URL_HOST );

		$is = $frontend !== $backend;
	}

	return $is;
}

/**
 * Enable settings
 */
require_once __DIR__ . '/settings/settings.php';

/**
 * WPGraphQL 1.5.4
 */
if ( is_plugin_enabled( 'wpgraphql' ) ) {
	require_once __DIR__ . '/lib/wp-graphql-1.5.4/wp-graphql.php';
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
if ( is_plugin_enabled( 'preview' ) && is_decoupled() ) {
	require_once __DIR__ . '/preview/preview.php';
}

/**
 * Registration helpers
 */
require_once __DIR__ . '/registration/registration.php';

/**
 * Adjust resource URLs
 */
if ( is_decoupled() ) {
	require_once __DIR__ . '/urls/urls.php';
}

/**
 * Render admin notices if there are compatibility issues.
 *
 * @return void
 */
function render_admin_notices() {
	// If the home URL is the same as the site URL, then the site is not decoupled
	// and features like preview will not work.
	if ( ! is_decoupled() ) {
		?>
		<div class="notice notice-error is-dismissible">
			<p><strong>The VIP Decoupled plugin is active but the <code>home</code> option does not point to a decoupled frontend.</strong> Previewing and other features will not work. Please set "Site Address (URL)" in <a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>">Settings &gt; General</a> to point to the base URL of your decoupled frontend.</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', __NAMESPACE__ . '\\render_admin_notices' );

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
