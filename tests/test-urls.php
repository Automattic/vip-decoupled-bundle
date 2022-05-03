<?php
/**
 * Blocks
 *
 * @package vip-bundle-decoupled
 */

namespace WPCOMVIP\Decoupled\Urls\Tests;

use WP_UnitTestCase;
use function WPCOMVIP\Decoupled\Urls\update_resource_url;

/**
 * Blocks
 */
class UrlsTest extends WP_UnitTestCase {
	public function setUp(): void {
		remove_all_filters( 'home_url' );
		remove_all_filters( 'site_url' );
		remove_all_filters( 'option_home' );
		remove_all_filters( 'option_siteurl' );
	}

	public function updateResourceUrlDataProvider() {
		return [
			[
				[
					'description' => 'Not decoupled, no base paths',
					'home'        => 'http://example1.com',
					'siteurl'     => 'http://example1.com',
					'test_urls'   => [
						[
							'input'    => 'http://example1.com',
							'expected' => 'http://example1.com',
						],
						[
							'input'    => 'http://example1.com/',
							'expected' => 'http://example1.com/',
						],
						[
							'input'    => 'http://example1.com/wp-json/v2/posts',
							'expected' => 'http://example1.com/wp-json/v2/posts',
						],
					],
				],
			],
			[
				[
					'description' => 'Not decoupled, same base path of "/"',
					'home'        => 'http://example1.com/',
					'siteurl'     => 'http://example1.com/',
					'test_urls'   => [
						[
							'input'    => 'http://example1.com',
							'expected' => 'http://example1.com',
						],
						[
							'input'    => 'http://example1.com/',
							'expected' => 'http://example1.com/',
						],
						[
							'input'    => 'http://example1.com/wp-json/v2/posts',
							'expected' => 'http://example1.com/wp-json/v2/posts',
						],
					],
				],
			],
			[
				[
					'description' => 'Not decoupled, same base path of "/test/123"',
					'home'        => 'http://example1.com/test/123',
					'siteurl'     => 'http://example1.com/test/123',
					'test_urls'   => [
						[
							'input'    => 'http://example1.com/test/123',
							'expected' => 'http://example1.com/test/123',
						],
						[
							'input'    => 'http://example1.com/test/123/',
							'expected' => 'http://example1.com/test/123/',
						],
						[
							'input'    => 'http://example1.com/wp-json/v2/posts',
							'expected' => 'http://example1.com/wp-json/v2/posts',
						],
					],
				],
			],
			[
				[
					'description' => 'Not decoupled, `home` has base path',
					'home'        => 'http://example1.com/test/123',
					'siteurl'     => 'http://example1.com',
					'test_urls'   => [
						[
							'input'    => 'http://example1.com/test/123',
							'expected' => 'http://example1.com/test/123',
						],
						[
							'input'    => 'http://example1.com/test/123/',
							'expected' => 'http://example1.com/test/123/',
						],
						[
							'input'    => 'http://example1.com/test/123/wp-json/v2/posts',
							'expected' => 'http://example1.com/test/123/wp-json/v2/posts',
						],
					],
				],
			],
			[
				[
					'description' => 'Not decoupled, `siteurl` has base path',
					'home'        => 'http://example1.com',
					'siteurl'     => 'http://example1.com/test/123',
					'test_urls'   => [
						[
							'input'    => 'http://example1.com/test/123',
							'expected' => 'http://example1.com/test/123',
						],
						[
							'input'    => 'http://example1.com/test/123/',
							'expected' => 'http://example1.com/test/123/',
						],
						[
							'input'    => 'http://example1.com/wp-json/v2/posts',
							'expected' => 'http://example1.com/wp-json/v2/posts',
						],
					],
				],
			],
			[
				[
					'description' => 'Decoupled, no base paths',
					'home'        => 'http://example1.com',
					'siteurl'     => 'http://example2.com',
					'test_urls'   => [
						[
							'input'    => 'http://example1.com',
							'expected' => 'http://example2.com',
						],
						[
							'input'    => 'http://example1.com/',
							'expected' => 'http://example2.com/',
						],
						[
							'input'    => 'http://example1.com/wp-json/v2/posts',
							'expected' => 'http://example2.com/wp-json/v2/posts',
						],
					],
				],
			],
			[
				[
					'description' => 'Decoupled, same base path of "/"',
					'home'        => 'http://example1.com/',
					'siteurl'     => 'http://example2.com/',
					'test_urls'   => [
						[
							'input'    => 'http://example1.com',
							'expected' => 'http://example2.com',
						],
						[
							'input'    => 'http://example1.com/',
							'expected' => 'http://example2.com/',
						],
						[
							'input'    => 'http://example1.com/wp-json/v2/posts',
							'expected' => 'http://example2.com/wp-json/v2/posts',
						],
					],
				],
			],
			[
				[
					'description' => 'Decoupled, same base path of "/test/123"',
					'home'        => 'http://example1.com/test/123',
					'siteurl'     => 'http://example2.com/test/123',
					'test_urls'   => [
						[
							'input'    => 'http://example1.com',
							'expected' => 'http://example2.com/test/123',
						],
						[
							'input'    => 'http://example1.com/test/123',
							'expected' => 'http://example2.com/test/123/',
						],
						[
							'input'    => 'http://example1.com/test/123/',
							'expected' => 'http://example2.com/test/123/',
						],
						[
							'input'    => 'http://example1.com/test/456/',
							'expected' => 'http://example2.com/test/123/test/456/',
						],
						[
							'input'    => 'http://example1.com/test/123/wp-json/v2/posts',
							'expected' => 'http://example2.com/test/123/wp-json/v2/posts',
						],
					],
				],
			],
			[
				[
					'description' => 'Decoupled, `home` has base path',
					'home'        => 'http://example1.com/test/123',
					'siteurl'     => 'http://example2.com',
					'test_urls'   => [
						[
							'input'    => 'http://example1.com',
							'expected' => 'http://example2.com',
						],
						[
							'input'    => 'http://example1.com/',
							'expected' => 'http://example2.com/',
						],
						[
							'input'    => 'http://example1.com/test/123',
							'expected' => 'http://example2.com/',
						],
						[
							'input'    => 'http://example1.com/test/123/',
							'expected' => 'http://example2.com/',
						],
						[
							'input'    => 'http://example1.com/test/123/wp-json/v2/posts',
							'expected' => 'http://example2.com/wp-json/v2/posts',
						],
						[
							'input'    => 'http://example1.com/test/456',
							'expected' => 'http://example2.com/test/456',
						],
					],
				],
			],
			[
				[
					'description' => 'Decoupled, `siteurl` has base path',
					'home'        => 'http://example1.com',
					'siteurl'     => 'http://example2.com/test/123',
					'test_urls'   => [
						[
							'input'    => 'http://example1.com',
							'expected' => 'http://example2.com/test/123',
						],
						[
							'input'    => 'http://example1.com/test/123/',
							'expected' => 'http://example2.com/test/123/test/123/',
						],
						[
							'input'    => 'http://example1.com/test/456',
							'expected' => 'http://example2.com/test/123/test/456',
						],
						[
							'input'    => 'http://example1.com/wp-json/v2/posts',
							'expected' => 'http://example2.com/test/123/wp-json/v2/posts',
						],
					],
				],
			],
		];
	}

	/**
	 * update_resource_url() correctly updates URLs when `home` and `siteurl`
	 * point to the same hostnames and no base paths.
	 *
	 * @dataProvider updateResourceUrlDataProvider
	 */
	public function test_update_resource_url( $test_data ): void {
		update_option( 'home', $test_data['home'] );
		update_option( 'siteurl', $test_data['siteurl'] );

		foreach ( $test_data['test_urls'] as $test_url ) {
			$output = update_resource_url( $test_url['input'] );
			$this->assertEquals( $test_url['expected'], $output, $test_data['description'] );
		}
	}
}
