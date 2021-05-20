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
 * @package vip-decoupled-bundle
 */

/**
 * WPGraphQL 1.3.8
 */
require_once __DIR__ . '/wp-graphql-1.3.8/wp-graphql.php';

/**
 * Make Gutenberg blocks available in WPGraphQL
 */
require_once __DIR__ . '/blocks/blocks.php';
