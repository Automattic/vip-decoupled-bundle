<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Settings;

function get_settings_config() {
	return [
		'cap'         => 'manage_options',
		'group'       => 'VIP Decoupled',
		'menu_slug'   => 'vip_decoupled',
		'option_name' => 'vip_decoupled_settings',
		'sections'    => [
			'vip_decoupled_plugins' => [
				'callback' => function () {
					echo '<p>Turn on/off the plugins needed for your decoupled application:</p>';
				},
				'label'    => 'VIP Decoupled Plugins',
			],
		],
		'settings'    => [
			// Order determines render order.
			'plugin_wpgraphql' => [
				'default' => '1',
				'label'   => 'WPGraphQL',
				'section' => 'vip_decoupled_plugins',
			],
			'plugin_blocks'    => [
				'default' => '1',
				'label'   => 'WPGraphQL Blocks',
				'section' => 'vip_decoupled_plugins',
			],
			'plugin_preview'   => [
				'default' => '1',
				'label'   => 'WPGraphQL Preview',
				'section' => 'vip_decoupled_plugins',
			],
		],
	];
}

function get_setting_by_key( $key ) {
	static $option = null;

	$config = get_settings_config();

	if ( empty( $option ) ) {
		// Build defaults.
		$defaults = [];
		foreach ( $config['settings'] as $setting_key => $setting ) {
			$defaults[ $setting_key ] = $setting['default'];
		}

		// Merge option with defaults to ensure that every key is defined.
		$option = get_option( $config['option_name'], $defaults );
	}

	if ( isset( $option[ $key ] ) ) {
		return $option[ $key ];
	}

	return null;
}

function is_plugin_enabled( $key ) {
	return '1' === get_setting_by_key( $key );
}

function render_form() {
	$config = get_settings_config();

	if ( ! current_user_can( $config['cap'] ) ) {
		wp_die( esc_html( __( 'You do not have sufficient permissions to access this page.' ) ) );
	}

	?>
	<form action='options.php' method='post'>
	<?php
	settings_fields( $config['group'] );
	do_settings_sections( $config['menu_slug'] );
	submit_button();
	?>

	</form>
	<?php
}

function render_plugin_field( $args ) {
	?>
		<input
			type="checkbox"
			name="<?php echo esc_attr( $args['field_name'] ); ?>"
			value="1"
			<?php checked( true, is_plugin_enabled( $args['option_key'] ) ); ?>
		/>
	<?php
}

function add_decoupled_menu() {
	$config = get_settings_config();

	add_options_page(
		$config['group'],
		$config['group'],
		$config['cap'],
		$config['menu_slug'],
		__NAMESPACE__ . '\\render_form'
	);
}

function register_decoupled_settings() {
	$config = get_settings_config();

	register_setting( $config['group'], $config['option_name'] );

	foreach ( $config['sections'] as $key => $section ) {
		add_settings_section(
			$key,
			$section['label'],
			$section['callback'],
			$config['menu_slug']
		);
	}

	foreach ( $config['settings'] as $option_key => $setting ) {
		$args = [
			'field_name' => sprintf( '%s[%s]', $config['option_name'], $option_key ),
			'option_key' => $option_key,
		];

		add_settings_field(
			$option_key,
			$setting['label'],
			__NAMESPACE__ . '\\render_plugin_field',
			$config['menu_slug'],
			$setting['section'],
			$args
		);
	}
}

add_action( 'admin_menu', __NAMESPACE__ . '\\add_decoupled_menu' );
add_action( 'admin_init', __NAMESPACE__ . '\\register_decoupled_settings' );
