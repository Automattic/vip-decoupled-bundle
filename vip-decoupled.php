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

$vip_decoupled_default_options = array(
    'vip_decoupled_plugin_wpgraphql' => '1',
    'vip_decoupled_plugin_blocks' => '1',
);

$vip_decoupled_options = get_option( 'vip_decoupled_settings', $vip_decoupled_default_options );

/**
 * WPGraphQL 1.3.8
 */
if( $vip_decoupled_options["vip_decoupled_plugin_wpgraphql"] == '1' ) {
    require_once __DIR__ . '/wp-graphql-1.3.8/wp-graphql.php';
}

/**
 * Make Gutenberg blocks available in WPGraphQL
 */
if( $vip_decoupled_options["vip_decoupled_plugin_blocks"] == '1' ) {
    require_once __DIR__ . '/blocks/blocks.php';
}

/**
 * Adjust CORS headers
 */
require_once __DIR__ . '/cors/cors.php';

/**
 * Enable decoupled previews
 */
require_once __DIR__ . '/preview/preview.php';

/**
 * Enable settings
 */
require_once __DIR__ . '/settings/settings.php';
