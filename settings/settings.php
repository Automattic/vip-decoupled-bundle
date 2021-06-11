<?php
/**
 * @package vip-bundle-decoupled
 */

function vip_decoupled_menu_content() {
	if ( ! current_user_can( 'manage_options' ) ) {
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

function vip_decoupled_plugin_blocks_render( $args ) {
	global $vip_decoupled_options;

	?>
		<input type='checkbox' name='vip_decoupled_settings[vip_decoupled_plugin_blocks]' value="1" <?php checked( '1', $vip_decoupled_options['vip_decoupled_plugin_blocks'] ); ?> />
	<?php
	 
}

function vip_decoupled_plugin_wpgraphql_render( $args ) {
	global $vip_decoupled_options;

	?>
		<input type='checkbox' name='vip_decoupled_settings[vip_decoupled_plugin_wpgraphql]' value="1" <?php checked( '1', $vip_decoupled_options['vip_decoupled_plugin_wpgraphql'] ); ?> />
	<?php
}

function vip_decoupled_settings_section_callback() {
	echo '<p>Turn on/off the plugins needed for your decoupled application:</p>';
} //

function vip_decoupled_menu() {
	add_options_page( 'VIP Decoupled', 'VIP Decoupled', 'manage_options', 'vip_decoupled', 'vip_decoupled_menu_content' );  
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
