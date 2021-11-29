<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Parsely;

/**
 * Registers Parse.ly fields in WPGraphQL.
 *
 * @return void
 */
function register_parsely_fields() {
	if ( ! isset( $GLOBALS['parsely'] ) ) {
		return;
	}

	register_graphql_field(
		'NodeWithContentEditor',
		'schemaOrgMeta',
		[
			'type'        => 'String',
			'description' => 'Document schema for post',
			'resolve'     => function ( $post_model ) {
				$post    = get_post( $post_model->ID );
				$options = $GLOBALS['parsely']->get_options();
				$schema  = $GLOBALS['parsely']->construct_parsely_metadata( $options, $post );

				// A JSON representation of the schema that can be embedded in a script
				// tag by the decoupled frontend -- or optionally parsed and used to
				// populate <meta> tags.
				$json = wp_json_encode( $schema );

				return empty( $json ) ? null : $json;
			},
		]
	);
}
add_filter( 'init', __NAMESPACE__ . '\\register_parsely_fields', 10, 0 );
