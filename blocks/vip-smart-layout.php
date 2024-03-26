<?php

/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package vip-bundle-decoupled
 */

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function vip_smart_layout_block_init()
{
	// Skip block registration if Gutenberg is not enabled/merged.
	if (!function_exists('register_block_type')) {
		return;
	}
	$dir = dirname(__FILE__);

	$index_js = 'vip-smart-layout/index.js';
	wp_register_script(
		'vip-smart-layout-block-editor',
		plugins_url($index_js, __FILE__),
		[
			'wp-blocks',
			'wp-i18n',
			'wp-element',
		],
		filemtime("{$dir}/{$index_js}")
	);

	$editor_css = 'vip-smart-layout/editor.css';
	wp_register_style(
		'vip-smart-layout-block-editor',
		plugins_url($editor_css, __FILE__),
		[],
		filemtime("{$dir}/{$editor_css}")
	);

	$style_css = 'vip-smart-layout/style.css';
	wp_register_style(
		'vip-smart-layout-block',
		plugins_url($style_css, __FILE__),
		[],
		filemtime("{$dir}/{$style_css}")
	);

	register_block_type( 'vip-bundle-decoupled/vip-smart-layout', [
		'editor_script' => 'vip-smart-layout-block-editor',
		'editor_style'  => 'vip-smart-layout-block-editor',
		'style'         => 'vip-smart-layout-block',
	]);
}

add_action('init', 'vip_smart_layout_block_init');
