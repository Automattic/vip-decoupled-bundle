<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Registration;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Connection\PostObjects;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Type\WPEnumType;

/**
 * Our DisplayNode interface is a composition of other interfaces. Each of those
 * interfaces registers fields that correspond to whether a post type supports
 * core WordPress features, e.g., `post_type_supports( 'title' )`.
 *
 * Composing these interfaces into one interface allows us to define a single
 * type that we can target with our GraphQL queries, making initial
 * implementation easier.
 *
 * A post type must implement all of these interfaces returned from this function
 * to be included in our interface. Therefore, we need to target a reasonable
 * "least-common-denominator" set of interfaces that cover the general use case
 * of "displayable" content.
 *
 * The plugin user might have different use cases and may want to target a
 * different set of interfaces. For that reason, the list is filterable.
 *
 * See all of the available core interfaces in wp-graphql/src/InterfaceType/.
 *
 * @return string[]
 */
function get_common_interfaces() {
	static $interfaces = null;

	if ( null === $interfaces ) {
		$default_interfaces = [
			'NodeWithAuthor',
			'NodeWithContentEditor',
			'NodeWithFeaturedImage',
			'NodeWithTitle',
		];

		$interfaces = apply_filters( 'vip_decoupled_display_node_interfaces', $default_interfaces );

		// The common interfaces must include Node and ContentNode.
		$interfaces = array_unique( array_merge( $interfaces, [ 'Node', 'ContentNode' ] ) );
	}

	return $interfaces;
}

/**
 * Add the DisplayNode interface to types that implement it and return a list
 * of those types, which functions as the list of "possible types" that this
 * interface can resolve to.
 *
 * @param  TypeRegistry $type_registry  WPGraphQL type registry.
 * @param  string       $interface_name Interface name
 * @return string[]
 */
function add_interface_and_get_possible_types( TypeRegistry $type_registry, $interface_name ) {
	$common_interfaces = get_common_interfaces();
	$existing_types    = $type_registry->get_types();
	$possible_types    = [];

	/**
	 * Inspect the interfaces for each type and conditionally add our interface. If
	 * the type implements all of our common interfaces, then it also implements
	 * the DisplayNode interface.
	 */
	foreach ( $existing_types as $type ) {
		if ( isset( $type->config['interfaces'] ) ) {
			if ( 0 === count( array_diff( $common_interfaces, $type->config['interfaces'] ) ) ) {
				array_push( $type->config['interfaces'], $interface_name );
				array_push( $possible_types, $type->config['name'] );
			}
		}
	}

	return $possible_types;
}

/**
 * Get the post type slug for each possible GraphQL type.
 *
 * @param  string[] $possible_types Possible GraphQL types.
 * @return string[]
 */
function get_possible_post_types( $possible_types ) {
	$post_type_objects   = get_post_types( [ 'show_in_graphql' => true ], 'objects' );
	$possible_post_types = [];

	foreach ( $post_type_objects as $post_type ) {
		if ( in_array( ucfirst( $post_type->graphql_single_name ), $possible_types, true ) ) {
			array_push( $possible_post_types, $post_type->name );
		}
	}

	return $possible_post_types;
}

/**
 * Adds the DisplayNode interface type to the WPGraphQL type registry.
 *
 * @param  TypeRegistry $type_registry
 * @return void
 */
function register_types( TypeRegistry $type_registry ) {
	$interface_name = 'DisplayNode';
	$enum_type_name = 'DisplayContentTypeEnum';

	$content_node_type = $type_registry->get_type( 'ContentNode' );
	$root_query_type   = $type_registry->get_type( 'RootQuery' );

	$possible_types      = add_interface_and_get_possible_types( $type_registry, $interface_name );
	$possible_post_types = get_possible_post_types( $possible_types );

	$enum_values = [];
	foreach ( $possible_post_types as $post_type_slug ) {
		$enum_values[ WPEnumType::get_safe_name( $post_type_slug ) ] = [
			'value'       => $post_type_slug,
			'description' => 'Displayable content type',
		];
	}

	// Register an enum representing displayable content types for query input.
	register_graphql_enum_type(
		$enum_type_name,
		[
			'description' => 'Displayable content types',
			'values'      => $enum_values,
		]
	);

	// Register our DisplayNode interface.
	register_graphql_interface_type(
		$interface_name,
		[
			'description' => 'Nodes used to display content to users',
			'fields'      => [],
			'interfaces'  => get_common_interfaces(),
			'resolveType' => $content_node_type->config['resolveType'],
		]
	);

	// Create a root connection to query DisplayNodes and provide an argument to
	// restrict the query to specific content types (post types).
	register_graphql_connection(
		[
			'fromType'       => 'RootQuery',
			'toType'         => $interface_name,
			'queryClass'     => 'WP_Query',
			'fromFieldName'  => sprintf( '%ss', lcfirst( $interface_name ) ),
			'connectionArgs' => PostObjects::get_connection_args(
				[
					'contentTypes' => [
						'type'        => [ 'list_of' => $enum_type_name ],
						'description' => 'The Types of content to filter',
					],
				],
				null
			),
			'resolve'        => function( $source, $args, $context, $info ) use ( $possible_post_types ) {
				$post_types = isset( $args['where']['contentTypes'] ) && is_array( $args['where']['contentTypes'] ) ? $args['where']['contentTypes'] : $possible_post_types;

				return DataSource::resolve_post_objects_connection( $source, $args, $context, $info, $post_types );
			},
		]
	);
}
// Action priority of 5 fires after all types have been registered with
// "graphql_register_types" action, but before other core actions run on
// "graphql_register_types_late" with default priority (10).
add_action( 'graphql_register_types_late', __NAMESPACE__ . '\\register_types', 5, 1 );

/**
 * Rather than register a new field on RootQuery, which would involve a lot of
 * code duplication, we make a copy of the `contentNode` field and change only
 * what we need.
 *
 * We must do this via the RootQuery fields filter before it is acted on by
 * WPObjectType#prepare_fields. Afterwards, it is locked inside a closure.
 *
 * @param  array $fields RootQuery fields.
 * @return array
 */
function register_root_query_field( $fields ) {
	$interface_name = 'DisplayNode';
	$enum_type_name = 'DisplayContentTypeEnum';

	$field                                = $fields['contentNode'];
	$field['type']                        = $interface_name;
	$field['args']['contentType']['type'] = $enum_type_name;
	$fields[ lcfirst( $interface_name ) ] = $field;

	return $fields;
}
add_filter( 'graphql_RootQuery_fields', __NAMESPACE__ . '\\register_root_query_field', 10, 1 );

/**
 * Filter custom post type args and auto-register post types that are public, not
 * built-in, and don't already have a "show_in_graphql" value set.
 *
 * @param  array  $args      Custom post type registration args.
 * @param  string $post_type Post type slug.
 * @return array
 */
function register_custom_post_types( $args, $post_type ) {
	if (
		isset( $args['show_in_graphql'] ) ||
		true === $args['_builtin'] ||
		false === $args['public']
	) {
		return $args;
	}

	$slug = str_replace(
		[ ' ', '-', '_' ],
		'',
		ucwords( trim( $post_type ), ' _-' )
	);

	if ( 's' === substr( $slug, -1 ) && strlen( $slug ) > 1 ) {
		$slug = substr( $slug, 0, -1 );
	}

	$args['show_in_graphql']     = true;
	$args['graphql_single_name'] = $slug;
	$args['graphql_plural_name'] = sprintf( '%s%s', $slug, 's' );

	return $args;
}
add_filter( 'register_post_type_args', __NAMESPACE__ . '\\register_custom_post_types', 10, 2 );
