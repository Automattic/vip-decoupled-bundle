<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Settings;

/**
 * Get a config to support settings registration.
 *
 * @return array
 */
function get_settings_config() {
	return [
		'cap'         => 'manage_options',
		'defaults'    => [
			'enabled_plugins' => [ 'wpgraphql', 'blocks', 'preview', 'registration' ],
			'allowed_origins' => [],
		],
		'group'       => 'VIP Decoupled',
		'menu_slug'   => 'vip_decoupled',
		'option_name' => 'vip_decoupled_settings',
	];
}

/**
 * Get a VIP decoupled setting by key.
 *
 * @param  string $key The key to get in the saved option array.
 * @return mixed
 */
function get_setting_by_key( $key ) {
	static $option = null;

	$config = get_settings_config();

	if ( empty( $option ) ) {
		// Merge option with defaults to ensure that every key is defined.
		$option = get_option( $config['option_name'], $config['defaults'] );
	}

	if ( isset( $option[ $key ] ) ) {
		return $option[ $key ];
	}

	return null;
}

/**
 * Load the saved options and return the allowed origins specified by the user.
 *
 * @return string[]
 */
function get_allowed_origins() {
	$origins = get_setting_by_key( 'allowed_origins' );
	if ( is_array( $origins ) ) {
		return $origins;
	}

	return [];
}

/**
 * Load the saved options and determine whether a plugin is enabled.
 *
 * @param  string $plugin The plugin slug.
 * @return bool
 */
function is_plugin_enabled( $plugin ) {
	$setting = get_setting_by_key( 'enabled_plugins' );

	// Handle null value.
	if ( empty( $setting ) ) {
		return false;
	}

	return in_array( $plugin, get_setting_by_key( 'enabled_plugins' ), true );
}

/**
 * Render the current GraphQL endpoint and allow copy-to-clipboard.
 *
 * @return void
 */
function render_graphql_endpoint() {
	// Match logic in \WPGraphQL\Router:
	// https://github.com/wp-graphql/wp-graphql/blob/df2c5b2556fcbd69983d96d4e906572d835c0832/src/Router.php
	$endpoint = apply_filters( 'graphql_endpoint', null );
	if ( empty( $endpoint ) && function_exists( '\\get_graphql_setting' ) ) {
		$endpoint = get_graphql_setting( 'graphql_endpoint', 'graphql' );
	}

	if ( ! empty( $endpoint ) ) {
		?>
		<fieldset style="display: flex; flex-direction: row; margin-bottom: 3em;">
			<input
				disabled="disabled"
				id="graphql_endpoint"
				style="color: black; flex-grow: 1; max-width: 800px;"
				type="text"
				value="<?php echo esc_url( site_url( $endpoint ) ); ?>"
			/>
			<button
				class="button button-secondary hide-if-no-js"
				onclick="const el = document.querySelector('#graphql_endpoint'); el.setSelectionRange(0, el.value.length); document.execCommand('Copy'); el.setSelectionRange(0, 0); return false;"
				style="margin-left: 10px;"
			>
				Copy to clipboard
			</button>
		</fieldset>
		<?php
	}
}

/**
 * Render the settings form.
 *
 * @return void
 */
function render_form() {
	$config = get_settings_config();

	if ( ! current_user_can( $config['cap'] ) ) {
		wp_die( esc_html( __( 'You do not have sufficient permissions to access this page.' ) ) );
	}

	?>
	<div class="wrap">
		<h1>VIP Decoupled Settings</h1>
		<form action='options.php' method='post'>
		<?php
			settings_fields( $config['group'] );
			do_settings_sections( $config['menu_slug'] );
			submit_button();
		?>
		</form>
	</div>
	<?php
}

/**
 * Render a settings checkbox field.
 *
 * @param  string      $name        The input name (e.g., "option_name[setting_key][]").
 * @param  string      $value       The input value.
 * @param  bool        $checked     Whether the input should be checked.
 * @param  bool        $disabled    Whether the input should be disabled.
 * @param  string      $label       The input label.
 * @param  string|null $description An optional description to render under the input.
 * @return void
 */
function render_checkbox_field( $name, $value, $checked, $disabled, $label, $description = null ) {
	?>
	<label>
		<input
			name="<?php echo esc_attr( $name ); ?>"
			type="checkbox"
			value="<?php echo esc_attr( $value ); ?>"
			<?php checked( true, $checked ); ?>
			<?php disabled( true, $disabled ); ?>
		/>
		<strong><?php echo esc_html( $label ); ?></strong> <?php echo esc_html( $description ); ?>
	</label>
	<br>
	<?php
}

/**
 * Register the VIP Decoupled settings sections using the WordPress Settings API.
 *
 * @return void
 */
function register_decoupled_settings() {
	$config = get_settings_config();

	register_setting( $config['group'], $config['option_name'] );

	// Register plugin settings section.
	add_settings_section(
		'endpoint',
		'Your WPGraphQL endpoint',
		__NAMESPACE__ . '\\render_graphql_endpoint',
		$config['menu_slug']
	);

	// Register plugin settings section.
	add_settings_section(
		'plugins',
		'Plugins',
		function() {
			?>
			<p>These plugins provide core decoupled functionality. Additional WPGraphQL settings, including useful debugging settings, are located <a href="<?php echo esc_url( admin_url( 'admin.php?page=graphql' ) ); ?>">on their own page</a>.</p>
			<?php
		},
		$config['menu_slug']
	);

	// Register plugin settings section.
	add_settings_section(
		'cors',
		'CORS',
		null,
		$config['menu_slug']
	);
}
add_action( 'admin_init', __NAMESPACE__ . '\\register_decoupled_settings' );

/**
 * Register the VIP Decoupled settings plugin fields.
 *
 * @return void
 */
function register_decoupled_settings_plugin_fields() {
	$config = get_settings_config();

	$option_key = 'enabled_plugins';
	$field_name = sprintf( '%s[%s]', $config['option_name'], $option_key );
	$args       = [
		'field_name' => $field_name,
		'option_key' => $option_key,
	];

	// Register plugin settings fields.
	add_settings_field(
		$option_key,
		'Enabled plugins',
		function () use ( $field_name ) {
			$name              = sprintf( '%s[]', $field_name );
			$wpgraphql_enabled = is_plugin_enabled( 'wpgraphql' );

			?>
			<fieldset><legend class="screen-reader-text"><span>Enabled plugins</span></legend>
			<?php

			render_checkbox_field(
				$name,
				'wpgraphql',
				$wpgraphql_enabled,
				false,
				'WPGraphQL',
				'is the API used by your decoupled frontend to query data from WordPress.'
			);
			render_checkbox_field(
				$name,
				'blocks',
				is_plugin_enabled( 'blocks' ),
				! $wpgraphql_enabled,
				'WPGraphQL Content Blocks',
				'exposes Gutenberg blocks as structured data, allowing you to map blocks to frontend components.'
			);
			render_checkbox_field(
				$name,
				'preview',
				is_plugin_enabled( 'preview' ),
				! $wpgraphql_enabled,
				'WPGraphQL Preview',
				'changes the behavior of the Preview button to work with VIP’s Next.js boilerplate.'
			);
			render_checkbox_field(
				$name,
				'registration',
				is_plugin_enabled( 'registration' ),
				! $wpgraphql_enabled,
				'WPGraphQL Type Registration',
				'automatically registers all public custom post types in WPGraphQL.'
			);

			?>
			</fieldset>
			<?php
		},
		$config['menu_slug'],
		'plugins',
		$args
	);
}
add_action( 'admin_init', __NAMESPACE__ . '\\register_decoupled_settings_plugin_fields' );

/**
 * Register the VIP Decoupled settings plugin fields.
 *
 * @return void
 */
function register_decoupled_settings_cors_fields() {
	$config = get_settings_config();

	$option_key = 'allowed_origins';
	$field_name = sprintf( '%s[%s]', $config['option_name'], $option_key );
	$args       = [
		'field_name' => $field_name,
		'option_key' => $option_key,
	];

	// Register plugin settings fields.
	add_settings_field(
		$option_key,
		'Allowed origins',
		function () use ( $field_name ) {
			$name  = sprintf( '%s[]', $field_name );
			$value = get_allowed_origins();
			if ( is_array( $value ) ) {
				$value = implode( "\n", $value );
			}

			?>
			<fieldset><legend class="screen-reader-text"><span>Allowed origins</span></legend>
				<textarea
					class="large-text code"
					cols="50"
					id="allowed-origins"
					name="<?php echo esc_attr( $field_name ); ?>"
					rows="10"
				><?php echo esc_textarea( $value ); ?></textarea>
			</fieldset>
			<p>Provide the <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Origin">origins</a> of non-production environments that need to make client-side requests to WPGraphQL. Only provide origins that you control. Your frontend (<code>HOME</code>) and <code>localhost</code> (any port) are always allowed. One per line. Always include the protocol, e.g., <code>https://example.com</code>. Include the port if it is non-standard, e.g., <code>http://example.com:8888</code>.</em></p>
			<?php
		},
		$config['menu_slug'],
		'cors',
		$args
	);
}
add_action( 'admin_init', __NAMESPACE__ . '\\register_decoupled_settings_cors_fields' );

function sanitize_decoupled_settings( $value ) {
	if ( ! empty( $value['allowed_origins'] ) ) {
		$split_text = explode( "\n", $value['allowed_origins'] );
		$split_text = array_map(
			function( $origin ) {
				$parts = wp_parse_url( trim( $origin ) );

				if ( ! is_array( $parts ) ) {
					return null;
				}

				$port = '';
				if ( is_int( $parts['port'] ) ) {
					$port = sprintf( ':%d', $parts['port'] );
				}

				return sprintf( '%s://%s%s', $parts['scheme'], $parts['host'], $port );
			},
			$split_text
		);

		$value['allowed_origins'] = array_filter( $split_text );
	}

	return $value;
}
add_filter( 'pre_update_option_vip_decoupled_settings', __NAMESPACE__ . '\\sanitize_decoupled_settings', 10, 1 );

/**
 * Add the VIP Decoupled page to the settings menu.
 *
 * @return void
 */
function add_decoupled_page() {
	$config = get_settings_config();

	add_options_page(
		$config['group'],
		$config['group'],
		$config['cap'],
		$config['menu_slug'],
		__NAMESPACE__ . '\\render_form'
	);
}
add_action( 'admin_menu', __NAMESPACE__ . '\\add_decoupled_page' );
