<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Blocks;

/**
 * The semver of the ContentBlock data type.
 *
 * @return string Version number.
 */
function get_block_version() {
	return '0.2.0';
}

/**
 * Extract the content blocks and associated meta for a post.
 *
 * @param  WPGraphQL\Model\Post $post_model Post model for post.
 * @return array
 */
function get_content_blocks( $post_model ) {
	$version = get_block_version();

	if ( ! function_exists( 'parse_blocks' ) || ! function_exists( 'has_blocks' ) ) {
		return [
			'blocks'      => [],
			'isGutenberg' => false,
			'version'     => $version,
		];
	}

	// WPGraphQL's Post model restricts access to the raw post_content (contentRaw)
	// based on "edit_posts" cap. Since we want to serve blocks even to logged-out
	// users -- and because we are parsing this content before returning it --
	// we'll bypass the model and access the raw content directly.
	$post = \get_post( $post_model->ID );

	$is_gutenberg = \has_blocks( $post->post_content );
	$raw_blocks   = \parse_blocks( $post->post_content );

	return [
		'blocks'      => process_content_blocks( $raw_blocks ),
		'isGutenberg' => $is_gutenberg,
		'version'     => $version,
	];
}

/**
 * Provide additional data for image blocks.
 *
 * @param  array $block Content block.
 * @return array
 */
function transform_block_attributes( $block ) {
	if ( 'core/image' === $block['name'] ) {
		$attachment_metadata = \wp_get_attachment_metadata( $block['attributes']['id'] );

		$block['attributes']['src']            = \wp_get_attachment_url( $block['attributes']['id'] );
		$block['attributes']['originalHeight'] = $attachment_metadata['height'];
		$block['attributes']['originalWidth']  = $attachment_metadata['width'];
		$block['attributes']['srcset']         = \wp_get_attachment_image_srcset( $block['attributes']['id'] );
		$block['attributes']['alt']            = trim( strip_tags( \get_post_meta( $block['attributes']['id'], '_wp_attachment_image_alt', true ) ) );

		// If width and height attributes aren't exposed, add the default ones
		if ( ! isset( $block['attributes']['height'] ) ) {
			$block['attributes']['height'] = $attachment_metadata['height'];
		}

		if ( ! isset( $block['attributes']['width'] ) ) {
			$block['attributes']['width'] = $attachment_metadata['width'];
		}
	}

	return $block;
}
add_filter( 'vip_decoupled_graphql_content_block', __NAMESPACE__ . '\\transform_block_attributes', 10, 1 );

/**
 * Strip wrapping tags from the content and set as a property on the block. This
 * allows the front-end implementor to delegate tag creation to a component.
 *
 * @param  string $html Inner HTML of block.
 * @return array
 */
function get_content_block_html( $html ) {
	$html = trim( $html );

	preg_match( '#^<([A-z][A-z0-9]*)\b([^>])*>(.*?)</\1>$#', $html, $matches );

	if ( isset( $matches[1] ) ) {
		return [
			'innerHTML' => $matches[3],
			'outerHTML' => $html,
			'tagName'   => $matches[1],
		];
	}

	// Self closing HTML block
	preg_match( '#^<([A-z][A-z0-9]*)+?\b(.*?)\/>$#', $html, $self_closing_matches );

	if ( isset( $self_closing_matches[1] ) ) {
		return [
			'innerHTML' => null,
			'outerHTML' => sprintf( '<%s />', $self_closing_matches[1] ),
			'tagName'   => $self_closing_matches[1],
		];
	}

	return [];
}

/**
 * Process content blocks to match expected shape of ContentBlock type and
 * remove unwanted data.
 *
 * @param  array $raw_blocks The raw blocks returned by parse_blocks.
 * @return array
 */
function process_content_blocks( $raw_blocks ) {
	$blocks = array_map(
		function ( $raw_block ) {
			$block = $raw_block;

			// Classic editor blocks get a blockName of null with the raw post content
			// shoved inside. Set a usable block name and allow the client to use the
			// HTML as they see fit.
			if ( null === $block['blockName'] ) {
				$block['blockName'] = 'core/classic-editor';
			}

			// However, Gutenberg can sometimes produce spurious empty blocks. Return
			// null and filter them out below.
			if ( 'core/classic-editor' === $block['blockName'] && empty( trim( $block['innerHTML'] ) ) ) {
				return null;
			}

			$block = array_merge(
				get_content_block_html( $block['innerHTML'] ),
				[
					'attributes'   => $block['attrs'],
					'innerBlocks'  => process_content_blocks( $block['innerBlocks'] ),
					'innerContent' => $block['innerContent'],
					'name'         => $block['blockName'],
				]
			);

			/**
			 * Filter content block before it is provided to the GraphQL resolver.
			 *
			 * @param  $block     ContentBlock data.
			 * @param  $raw_block "Raw" block data from parse_blocks().
			 * @param  $version   Block version.
			 */
			$block = \apply_filters( 'vip_decoupled_graphql_content_block', $block, $raw_block, get_block_version() );

			// Attributes are much easier to work with as an associative array, but
			// must be provided to the GraphQL resolver as an array of key/value pairs.
			$block['attributes'] = array_map(
				function ( $key ) use ( $block ) {
					return [
						'name'  => $key,
						'value' => $block['attributes'][ $key ],
					];
				},
				array_keys( $block['attributes'] )
			);

			return $block;
		},
		$raw_blocks
	);

	return array_filter( $blocks );
}

/**
 * Register types and fields for content blocks.
 *
 * @return void
 */
function register_types() {
	\register_graphql_object_type(
		'ContentBlockAttribute',
		[
			'description' => 'Content block attribute',
			'fields'      => [
				'name'  => [
					'type'        => 'String',
					'description' => 'Content block attribute name',
				],
				'value' => [
					'type'        => 'String',
					'description' => 'Content block attribute value',
				],
			],
		],
	);

	\register_graphql_object_type(
		'ContentBlock',
		[
			'description' => 'Content block',
			'fields'      => [
				'attributes'  => [
					'type'        => [ 'list_of' => 'ContentBlockAttribute' ],
					'description' => 'Content block attributes',
				],
				'innerBlocks' => [
					'type'        => [ 'list_of' => 'ContentBlock' ],
					'description' => 'Inner blocks of this block',
				],
				'innerContent' => [
					'type'        => [ 'list_of' => 'String' ],
					'description' => 'List of string fragments and null markers where inner blocks were found',
				],
				'innerHTML'   => [
					'type'        => 'String',
					'description' => 'Content block inner HTML (without wrapping tag)',
				],
				'name'        => [
					'type'        => 'String',
					'description' => 'Content block name',
				],
				'outerHTML'   => [
					'type'        => 'String',
					'description' => 'Content block HTML (with wrapping tag)',
				],
				'tagName'     => [
					'type'        => 'String',
					'description' => 'Content block HTML wrapping tag name',
				],
			],
		],
	);

	\register_graphql_object_type(
		'ContentBlocks',
		[
			'description' => 'Content block',
			'fields'      => [
				'blocks'      => [
					'type'        => [ 'list_of' => 'ContentBlock' ],
					'description' => 'Content block attributes',
				],
				'isGutenberg' => [
					'type'        => 'Boolean',
					'description' => 'Whether the post was created with the Gutenberg editor',
				],
				'version'     => [
					'type'        => 'String',
					'description' => 'Content block version',
				],
			],
		],
	);

	// Register the field on every post type that supports 'editor'.
	\register_graphql_field(
		'NodeWithContentEditor',
		'contentBlocks',
		[
			'type'        => 'ContentBlocks',
			'description' => 'A block representation of post content',
			'resolve'     => __NAMESPACE__ . '\\get_content_blocks',
		]
	);
}
\add_action( 'graphql_register_types', __NAMESPACE__ . '\\register_types', 10, 0 );
