<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Blocks;

use WPGraphQL;

function parse_blocks( $post ) {
	$version = '0.1.0';

	if ( ! function_exists( 'parse_blocks' ) || ! function_exists( 'has_blocks' ) ) {
		return [
			'blocks' => [],
			'isGutenberg' => false,
			'version' => $version,
		];
	}

	$is_gutenberg = \has_blocks( $post->contentRaw );
	$blocks = \parse_blocks( $post->contentRaw );

	// Classic editor blocks get a blockName of null with the raw post content
	// shoved inside. Set a usable block name and allow the client to use the HTML
	// as they see fit (including parsing with @wordpress/blocks#rawHandler).
	//
	// Additionally, map the block attributes to the shape of BlockAttribute.
	$blocks = array_map( function ( $block ) {
		if ( null === $block['blockName'] ) {
			$block['blockName'] = 'core/classic-editor';
		}

		$attributes = array_map( function ( $key ) use ( $block ) {
			return [
				'name'  => $key,
				'value' => $block['attrs'][ $key ],
			];
		}, array_keys( $block['attrs'] ) );

		$content = preg_replace( '#^<([A-z][A-z0-9]*)\b[^>]*>(.*?)</\1>$#', '$2', trim($block['innerHTML']) );

		return [
			'attributes' => $attributes,
			'innerHTML'  => $content,
			'name'       => $block['blockName'],
		];
	}, $blocks );

	$blocks = array_filter( $blocks, function( $block ) {
		return strlen( $block['innerHTML'] ) != 0;
	} );

	return [
		'blocks'      => $blocks,
		'isGutenberg' => $is_gutenberg,
		'version'     => $version,
	];
}

function register_types() {
	register_graphql_object_type(
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

	register_graphql_object_type(
		'ContentBlock',
		[
			'description' => 'Content block',
			'fields'      => [
				'attributes'    => [
					'type'        => [ 'list_of' => 'ContentBlockAttribute' ],
					'description' => 'Content block attributes',
				],
				'innerHTML' => [
					'type'        => 'String',
					'description' => 'Content block inner HTML',
				],
				'name' => [
					'type'        => 'String',
					'description' => 'Content block name',
				],
			],
		],
	);

	register_graphql_object_type(
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
				'version'      => [
					'type'        => 'String',
					'description' => 'Content block version',
				],
			],
		],
	);

	$post_types = array_filter( WPGraphQL::get_allowed_post_types(), function ( $post_type ) {
		return post_type_supports( $post_type, 'editor' );
	} );

	foreach ( $post_types as $post_type ) {
		register_graphql_field(
			get_post_type_object( $post_type )->graphql_single_name,
			'contentBlocks',
			[
				'type'        => 'ContentBlocks',
				'description' => 'A block representation of post content',
				'resolve'     => __NAMESPACE__ . '\\parse_blocks',
			]
		);
	}
}
add_action( 'graphql_register_types', __NAMESPACE__ . '\\register_types', 10, 0 );
