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

function vip_decoupled_menu_content() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    ?>
    <form action='options.php' method='post'>
        <?php
        settings_fields( 'vip_decoupled' );
        do_settings_sections( 'vip_decoupled' );
        submit_button();
        ?>

    </form>
    <?php
}

function vip_decoupled_plugin_blocks_render($args) {
    $options = get_option( 'vip_decoupled_settings' );

    ?>
        <input type='checkbox' name='vip_decoupled_settings[vip_decoupled_plugin_blocks]' value="1" <?php checked( '1', $options["vip_decoupled_plugin_blocks"] ); ?> />
    <?php
     
}

function vip_decoupled_plugin_wpgraphql_render($args) {
    $options = get_option( 'vip_decoupled_settings' );

    ?>
        <input type='checkbox' name='vip_decoupled_settings[vip_decoupled_plugin_wpgraphql]' value="1" <?php checked( '1', $options["vip_decoupled_plugin_wpgraphql"] ); ?> />
    <?php
}

function vip_decoupled_settings_section_callback() {
    echo '<p>Turn on/off the plugins needed for your decoupled application:</p>';
} //

function vip_decoupled_menu() {
    add_options_page( "VIP Decoupled", "VIP Decoupled", "manage_options", "vip_decoupled", 'vip_decoupled_menu_content' );  
}

function vip_decoupled_settings() {
    register_setting(
        'vip_decoupled',
        'vip_decoupled_settings'
    );

    add_settings_section(
        'vip_decoupled_settings_section',
        'VIP Decoupled Plugins Settings',
        'vip_decoupled_settings_section_callback',
        'vip_decoupled'
    );


    add_settings_field( 
        'vip_decoupled_plugin_wpgraphql',
        'WP GraphQL',
        'vip_decoupled_plugin_wpgraphql_render',
        'vip_decoupled',
        'vip_decoupled_settings_section'
    );

    add_settings_field( 
        'vip_decoupled_plugin_blocks',
        'VIP Blocks',
        'vip_decoupled_plugin_blocks_render',
        'vip_decoupled',
        'vip_decoupled_settings_section'
    );
}

add_action( 'admin_menu', 'vip_decoupled_menu' );
add_action( 'admin_init', 'vip_decoupled_settings' );
