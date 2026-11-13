<?php
/**
 * Base test case with Brain Monkey set up.
 *
 * @package asapchat
 */

namespace Asapchat\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Yoast\PHPUnitPolyfills\TestCases\TestCase as PolyfillTestCase;

abstract class TestCase extends PolyfillTestCase {

	use MockeryPHPUnitIntegration;

	const HOME_URL = 'https://shop.example';

	protected function set_up() {
		parent::set_up();
		Monkey\setUp();
	}

	protected function tear_down() {
		Monkey\tearDown();
		parent::tear_down();
	}

	/**
	 * Loads plugin classes for tests that do not load the main file.
	 *
	 * @return void
	 */
	protected static function load_classes() {
		if ( ! defined( 'ASAPCHAT_BASE' ) ) {
			define( 'ASAPCHAT_BASE', 'https://panel.asapchat.io' );
		}
		if ( ! defined( 'ASAPCHAT_URL' ) ) {
			define( 'ASAPCHAT_URL', 'https://example.test/wp-content/plugins/asapchat/' );
		}
		if ( ! defined( 'ASAPCHAT_VERSION' ) ) {
			define( 'ASAPCHAT_VERSION', '1.0.0' );
		}

		require_once ASAPCHAT_TEST_ROOT . '/tests/stubs/WP_Error.php';
		require_once ASAPCHAT_TEST_ROOT . '/includes/class-asapchat-api.php';
		require_once ASAPCHAT_TEST_ROOT . '/includes/class-asapchat-connect.php';
		require_once ASAPCHAT_TEST_ROOT . '/includes/class-asapchat-admin.php';
	}

	/**
	 * Stubs the WordPress HTTP helpers around wp_remote_request().
	 *
	 * @return void
	 */
	protected function stub_http_helpers() {
		Functions\when( 'is_wp_error' )->alias(
			function ( $thing ) {
				return $thing instanceof \WP_Error;
			}
		);
		Functions\when( 'wp_remote_retrieve_response_code' )->alias(
			function ( $response ) {
				return $response['response']['code'];
			}
		);
		Functions\when( 'wp_remote_retrieve_body' )->alias(
			function ( $response ) {
				return $response['body'];
			}
		);
		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );
		Functions\when( 'home_url' )->justReturn( self::HOME_URL );
	}

	/**
	 * A response array as returned by wp_remote_request().
	 *
	 * @param int          $status HTTP status.
	 * @param array|string $body   Decoded JSON body or raw text.
	 * @return array<string, mixed>
	 */
	protected static function http_response( $status, $body = '' ) {
		return array(
			'response' => array( 'code' => $status ),
			'body'     => is_array( $body ) ? json_encode( $body ) : $body,
		);
	}

	/**
	 * A JSON fixture from tests/fixtures (sample answers from docs/CONNECT-API.md).
	 *
	 * @param string $name File name.
	 * @return array<string, mixed>
	 */
	protected static function fixture( $name ) {
		return json_decode( file_get_contents( ASAPCHAT_TEST_ROOT . '/tests/fixtures/' . $name ), true );
	}
}
