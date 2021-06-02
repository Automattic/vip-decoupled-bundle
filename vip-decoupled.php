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

function vip_decoupled_register_scripts() {
    wp_register_style( 'vip-decoupled', plugins_url( '/assets/style.css', __FILE__ ) );
    wp_register_script( 'vip-decoupled', plugins_url( '/assets/script.js', __FILE__ ) );
}

add_action( 'admin_enqueue_scripts', 'vip_decoupled_register_scripts' );

function vip_decoupled_menu() {
    function vip_decoupled_menu_content() {
        ?>
            <h1>
                <?php esc_html_e( 'Welcome.', 'vip-decoupled-hello' ); ?>
            </h1>
        <?php
    }

    add_menu_page( "VIP Decoupled", "VIP Decoupled", "edit_plugins", "vip-decoupled", 'vip_decoupled_menu_content', '', 3 );
}

add_action( 'admin_menu', 'vip_decoupled_menu' );

function vip_decoupled_load_scripts($hook) {
    // Load only on ?page=mypluginname
    if( $hook != 'toplevel_page_vip-decoupled' ) {
         return;
    }
    
    wp_enqueue_style( 'vip-decoupled' );
    wp_enqueue_script( 'vip-decoupled' );
}

add_action( 'admin_enqueue_scripts', 'vip_decoupled_load_scripts' );
