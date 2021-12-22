<?php
/**
 * Blocks
 *
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Blocks\Tests;

use WP_UnitTestCase;
use function WPCOMVIP\Decoupled\Blocks\parse_inner_html;

/**
 * Blocks
 */
class BlocksTest extends WP_UnitTestCase {
	public function contentBlockHtmlDataProvider() {
		return [
			// Null
			[
				null,
				[
					'innerHTMLUnwrapped' => null,
					'tagName'            => null,
				],
			],
			// No wrapping tag
			[
				'Hello, world!',
				[
					'innerHTMLUnwrapped' => 'Hello, world!',
					'tagName'            => null,
				],
			],
			// Simple wrapping tag
			[
				'<div>Test</div>',
				[
					'innerHTMLUnwrapped' => 'Test',
					'tagName'            => 'div',
				],
			],
			// Simple self-closing tag
			[
				'<div />',
				[
					'innerHTMLUnwrapped' => null,
					'tagName'            => 'div',
				],
			],
			// Wrapping tag with attributes and nested HTML
			[
				'<aside id="aside"><p>Hello, world!</p></aside>',
				[
					'innerHTMLUnwrapped' => '<p>Hello, world!</p>',
					'tagName'            => 'aside',
				],
			],
			// Wrapping tag with nested HTML of the same kind
			[
				'<div><div>A little bit me</div><div>A little bit you</div></div>',
				[
					'innerHTMLUnwrapped' => '<div>A little bit me</div><div>A little bit you</div>',
					'tagName'            => 'div',
				],
			],
			// Sibling elements (no wrapping tag) should be left alone
			[
				'<div>A little bit me</div><div>A little bit you</div>',
				[
					'innerHTMLUnwrapped' => null,
					'tagName'            => null,
				],
			],
			// Malformed HTML should be left alone
			[
				'<div>Hello, world!',
				[
					'innerHTMLUnwrapped' => null,
					'tagName'            => null,
				],
			],
		];
	}

	/**
	 * parse_inner_html() extracts inner HTML from block HTML.
	 *
	 * @dataProvider contentBlockHtmlDataProvider
	 */
	public function test_parse_inner_html( $input_html, $expected_output ) {
		$output = parse_inner_html( $input_html );
		$this->assertEquals( $expected_output, $output );

		// Make the same assertion with white space surrounding the input.
		$wrapped_input_html = sprintf( "\n\n   %s   \n\n", $input_html );
		$output = parse_inner_html( $wrapped_input_html );
		$this->assertEquals( $expected_output, $output );
	}
}

