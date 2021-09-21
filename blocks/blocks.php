<?php
/**
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Blocks;

use WPGraphQL;

function preg_match_html_block( $block ) {
	// Strip wrapping tags from the content and set as a property on the block.
	// This allows the front-end implementor to delegate tag creation to a
	// component.

	preg_match( '#^<([A-z][A-z0-9]*)\b([^>])*>(.*?)</\1>$#', $block, $matches );
	
	if ( isset( $matches[1] ) ) {
		return [
			'inner_html' => $matches[3],
			'tag_name'   => $matches[1],
		];
	}

	// Self closing HTML block
	preg_match( '#^<([A-z][A-z0-9]*)+?\b(.*?)\/>$#', $block, $self_closing_matches );

	if ( isset( $self_closing_matches[1] ) ) {
		return [
			'inner_html' => null,
			'tag_name'   => $self_closing_matches[ 1 ],
		];
	}
}	

function parse_blocks( $post_model ) {
	$version = '0.1.0';

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
	$post = get_post( $post_model->ID );

	$is_gutenberg = \has_blocks( $post->post_content );
	$blocks       = \parse_blocks( $post->post_content );

	$blocks = array_map(
		function ( $block ) {
			// Classic editor blocks get a blockName of null with the raw post content
			// shoved inside. Set a usable block name and allow the client to use the
			// HTML as they see fit.
			if ( null === $block['blockName'] ) {
				$block['blockName'] = 'core/classic-editor';
			}

			// Map the block attributes to the shape of BlockAttribute.
			$attributes = array_map(
				function ( $key ) use ( $block ) {
					return [
						'name'  => $key,
						'value' => $block['attrs'][ $key ],
					];
				},
				array_keys( $block['attrs'] ) 
			);

			if ( $block['blockName'] === 'core/image' ) {
				$attachment_metadata = wp_get_attachment_metadata( $block['attrs'][ 'id' ] );

				array_push( $attributes, [
					'name'	=> 'src',
					'value'	=> wp_get_attachment_url( $block['attrs'][ 'id' ] )
				] );

				array_push( $attributes, [
					'name'	=> 'originalHeight',
					'value'	=> $attachment_metadata['height']
				] );

				array_push( $attributes, [
					'name'	=> 'originalWidth',
					'value'	=> $attachment_metadata['width']
				] );

				// If width and height attributes aren't exposed, add the default ones
				if ( isset( $block['attrs']['width'] ) ) {
					array_push( $attributes, [
						'name'	=> 'height',
						'value'	=> $attachment_metadata['height']
					] );
				}

				if ( isset( $block['attrs']['height'] ) ) {
					array_push( $attributes, [
						'name'	=> 'width',
						'value'	=> $attachment_metadata['width']
					] );
				}
			}

			$tag_name   = null;
			$inner_html = trim( $block['innerHTML'] );
			$outer_html = $inner_html;
			$block_matches = preg_match_html_block( $inner_html );

			return [
				'attributes' => $attributes,
				'innerHTML'  => $block_matches['inner_html'],
				'name'       => $block['blockName'],
				'outerHTML'  => $outer_html,
				'tagName'    => $block_matches['tag_name'],
			];
		},
		$blocks 
	);

	$blocks = array_filter(
		$blocks,
		function( $block ) {
			return $block['name'] !== 'core/classic-editor' || $block['innerHTML'] !== null;
		} 
	);

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
				'attributes' => [
					'type'        => [ 'list_of' => 'ContentBlockAttribute' ],
					'description' => 'Content block attributes',
				],
				'innerHTML'  => [
					'type'        => 'String',
					'description' => 'Content block inner HTML (without wrapping tag)',
				],
				'name'       => [
					'type'        => 'String',
					'description' => 'Content block name',
				],
				'outerHTML'  => [
					'type'        => 'String',
					'description' => 'Content block HTML (with wrapping tag)',
				],
				'tagName'    => [
					'type'        => 'String',
					'description' => 'Content block HTML wrapping tag name',
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
				'version'     => [
					'type'        => 'String',
					'description' => 'Content block version',
				],
			],
		],
	);

	// Register the field on every post type that supports 'editor'.
	register_graphql_field(
		'NodeWithContentEditor',
		'contentBlocks',
		[
			'type'        => 'ContentBlocks',
			'description' => 'A block representation of post content',
			'resolve'     => __NAMESPACE__ . '\\parse_blocks',
		]
	);
}
add_action( 'graphql_register_types', __NAMESPACE__ . '\\register_types', 10, 0 );
