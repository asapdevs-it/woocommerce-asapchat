<?php
/**
 * Tests for the AsapChat HTTP client.
 *
 * @package asapchat
 */

namespace Asapchat\Tests\Unit;

use Asapchat\Tests\TestCase;
use Asapchat_Api;
use Brain\Monkey\Functions;

final class ApiTest extends TestCase {

	const TOKEN = 'ack_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUV';

	/**
	 * Arguments of the last wp_remote_request() call.
	 *
	 * @var array{url: string, args: array<string, mixed>}|null
	 */
	private $sent;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::load_classes();
	}

	protected function set_up() {
		parent::set_up();
		$this->stub_http_helpers();
		$this->sent = null;
	}

	/**
	 * Makes wp_remote_request() record the call and return the given response.
	 *
	 * @param mixed $response Response or WP_Error.
	 * @return void
	 */
	private function answer( $response ) {
		Functions\expect( 'wp_remote_request' )
			->once()
			->andReturnUsing(
				function ( $url, $args ) use ( $response ) {
					$this->sent = array(
						'url'  => $url,
						'args' => $args,
					);
					return $response;
				}
			);
	}

	public function test_token_exchange_posts_json_without_authorization_and_site_url() {
		$this->answer( self::http_response( 200, array( 'token' => self::TOKEN ) ) );

		Asapchat_Api::exchange_code( 'code123', 'verifier456' );

		$this->assertSame( 'https://panel.asapchat.io/api/plugin/v1/token', $this->sent['url'] );
		$this->assertSame( 'POST', $this->sent['args']['method'] );
		$this->assertSame( 5, $this->sent['args']['timeout'] );
		$this->assertSame( 'application/json', $this->sent['args']['headers']['Accept'] );
		$this->assertSame( 'application/json', $this->sent['args']['headers']['Content-Type'] );
		$this->assertArrayNotHasKey( 'Authorization', $this->sent['args']['headers'] );
		$this->assertArrayNotHasKey( 'X-Site-Url', $this->sent['args']['headers'] );
		$this->assertSame(
			array(
				'code'          => 'code123',
				'code_verifier' => 'verifier456',
			),
			json_decode( $this->sent['args']['body'], true )
		);
	}

	public function test_config_request_sends_token_accept_and_site_url() {
		$this->answer( self::http_response( 200, self::fixture( 'config.json' ) ) );

		Asapchat_Api::get_config( self::TOKEN );

		$this->assertSame( 'https://panel.asapchat.io/api/plugin/v1/config', $this->sent['url'] );
		$this->assertSame( 'GET', $this->sent['args']['method'] );
		$this->assertSame( 5, $this->sent['args']['timeout'] );
		$this->assertSame( 'Bearer ' . self::TOKEN, $this->sent['args']['headers']['Authorization'] );
		$this->assertSame( 'application/json', $this->sent['args']['headers']['Accept'] );
		$this->assertSame( self::HOME_URL, $this->sent['args']['headers']['X-Site-Url'] );
		$this->assertArrayNotHasKey( 'body', $this->sent['args'] );
	}

	public function test_settings_update_is_a_json_patch_with_changed_fields_only() {
		$this->answer( self::http_response( 200, self::fixture( 'config.json' ) ) );

		Asapchat_Api::update_settings( self::TOKEN, array( 'main_color' => '#6543f0' ) );

		$this->assertSame( 'https://panel.asapchat.io/api/plugin/v1/settings', $this->sent['url'] );
		$this->assertSame( 'PATCH', $this->sent['args']['method'] );
		$this->assertSame( 'application/json', $this->sent['args']['headers']['Content-Type'] );
		$this->assertSame( 'Bearer ' . self::TOKEN, $this->sent['args']['headers']['Authorization'] );
		$this->assertSame( self::HOME_URL, $this->sent['args']['headers']['X-Site-Url'] );
		$this->assertSame( '{"main_color":"#6543f0"}', $this->sent['args']['body'] );
	}

	public function test_disconnect_sends_delete_with_token_headers() {
		$this->answer( self::http_response( 204 ) );

		$result = Asapchat_Api::disconnect( self::TOKEN );

		$this->assertSame( 'https://panel.asapchat.io/api/plugin/v1/connection', $this->sent['url'] );
		$this->assertSame( 'DELETE', $this->sent['args']['method'] );
		$this->assertSame( 'Bearer ' . self::TOKEN, $this->sent['args']['headers']['Authorization'] );
		$this->assertSame( self::HOME_URL, $this->sent['args']['headers']['X-Site-Url'] );
		$this->assertSame( 204, $result['status'] );
		$this->assertNull( $result['data'] );
	}

	/**
	 * @dataProvider data_responses
	 *
	 * @param int          $status        HTTP status.
	 * @param array|string $body          Response body.
	 * @param array|null   $expected_data Decoded data.
	 */
	public function test_returns_status_and_decoded_body( $status, $body, $expected_data ) {
		$this->answer( self::http_response( $status, $body ) );

		$result = Asapchat_Api::get_config( self::TOKEN );

		$this->assertSame( $status, $result['status'] );
		$this->assertSame( $expected_data, $result['data'] );
	}

	/**
	 * Status codes from the contract table (point 4) with their sample bodies.
	 *
	 * @return array<string, array{0: int, 1: array|string, 2: array|null}>
	 */
	public function data_responses() {
		$errors = self::fixture( 'errors.json' );
		$config = self::fixture( 'config.json' );

		return array(
			'200 config'                 => array( 200, $config, $config ),
			'200 config with search, 7a' => array( 200, self::fixture( 'config-search.json' ), self::fixture( 'config-search.json' ) ),
			'400 site_url_required'      => array( 400, $errors['site_url_required'], $errors['site_url_required'] ),
			'401 unauthorized'           => array( 401, $errors['unauthorized'], $errors['unauthorized'] ),
			'409 host_mismatch'          => array( 409, $errors['host_mismatch'], $errors['host_mismatch'] ),
			'410 shop_deleted'           => array( 410, $errors['shop_deleted'], $errors['shop_deleted'] ),
			'422 validation'             => array( 422, $errors['validation'], $errors['validation'] ),
			'429 without a body'         => array( 429, '', null ),
			'500 with an HTML page'      => array( 500, '<html>Server Error</html>', null ),
		);
	}

	public function test_network_error_has_status_zero() {
		$this->answer( new \WP_Error( 'http_request_failed' ) );

		$result = Asapchat_Api::get_config( self::TOKEN );

		$this->assertSame( Asapchat_Api::NO_RESPONSE, $result['status'] );
		$this->assertNull( $result['data'] );
	}

	public function test_base_address_comes_from_the_constant() {
		$this->answer( self::http_response( 200, array() ) );

		Asapchat_Api::get_config( self::TOKEN );

		$this->assertStringStartsWith( ASAPCHAT_BASE . '/api/plugin/v1/', $this->sent['url'] );
	}
}
