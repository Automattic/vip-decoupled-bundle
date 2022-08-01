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
	$frontend_url = wp_parse_url( home_url(), PHP_URL_HOST );
	$frontend_port = wp_parse_url( home_url(), PHP_URL_PORT );
	$frontend = $frontend_url . ( $frontend_port ? ':' . $frontend_port : '' );

	$backend_url = wp_parse_url( site_url(), PHP_URL_HOST );
	$backend_port = wp_parse_url( site_url(), PHP_URL_PORT );
	$backend = $backend_url . ( $backend_port ? ':' . $backend_port : '' );

	return $frontend !== $backend;
}

/**
 *
 * @return bool
 */
function is_misconfigured_multisite() {
	if ( ! is_multisite() ) {
		return false;
	}

	$frontend_path = wp_parse_url( home_url(), PHP_URL_PATH );
	$backend_path  = wp_parse_url( site_url(), PHP_URL_PATH );

	$frontend_path = null === $frontend_path ? '/' : $frontend_path;
	$backend_path  = null === $backend_path ? '/' : $backend_path;

	return $frontend_path !== $backend_path;
}

/**
 * Render admin notices if there are compatibility issues.
 *
 * @return void
 */
function render_admin_notices() {
	$administration_name = 'Settings > General > Site Address (URL)';
	$administration_url  = admin_url( 'options-general.php' );

	// Multisite options are a bit different.
	if ( is_multisite() ) {
		$site_id             = get_current_blog_id();
		$administration_name = sprintf( 'Network Admin > Sites > Site %d > Settings > Home', $site_id );
		$administration_url  = network_admin_url( sprintf( 'site-settings.php?id=%d', $site_id ) );
	}

	// If the home URL is the same as the site URL, then the site is not decoupled
	// and features like preview will not work.
	if ( ! is_decoupled() ) {
		?>
		<div class="notice notice-error is-dismissible">
			<h3>Additional configuration is needed to support your decoupled frontend</h3>
			<p><strong>This WordPress site’s <code>home_url()</code> does not point to a decoupled frontend.</strong> Previewing, permalinks, and other features will not work.</p>
			<table class="widefat importers">
				<tbody id="the-list">
					<tr class="source-other">
						<td><code>home_url()</code></td>
						<td><?php echo esc_html( home_url() ); ?></td>
					</tr>
					<tr class="source-other">
						<td><code>site_url()</code></td>
						<td><?php echo esc_html( site_url() ); ?></td>
					</tr>
				</tbody>
			</table>
			<p>Please update <a href="<?php echo esc_url( $administration_url ); ?>"><?php echo esc_html( $administration_name ); ?></a> to point to the base URL of your decoupled frontend.</p>
		</div>
		<?php
	}

	// Multisite options are a bit different.
	if ( is_misconfigured_multisite() ) {
		?>
		<div class="notice notice-error is-dismissible">
			<h3>Decoupled multisite configuration error</h3>
			<p><strong>When using multisite WordPress with a decoupled frontend, the base paths of the backend and frontend must match.</strong> This misconfiguration will lead to serious issues with the operation of WordPress.</p>
			<table class="widefat importers">
				<tbody id="the-list">
					<tr class="source-other">
						<td><code>home_url()</code></td>
						<td><?php echo esc_html( home_url() ); ?></td>
					</tr>
					<tr class="source-other">
						<td><code>site_url()</code></td>
						<td><?php echo esc_html( site_url() ); ?></td>
					</tr>
				</tbody>
			</table>
			<p>Please update <a href="<?php echo esc_url( $administration_url ); ?>"><?php echo esc_html( $administration_name ); ?></a> so that the path of "Home" matches the path of "Siteurl".</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', __NAMESPACE__ . '\\render_admin_notices' );
