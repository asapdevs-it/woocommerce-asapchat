<?php
/**
 * Tests for "Connect with key" (spec point 7).
 *
 * @package asapchat
 */

namespace Asapchat\Tests\Unit;

use Asapchat\Tests\TestCase;
use Asapchat_Connect;
use Brain\Monkey\Functions;

final class InstallKeyTest extends TestCase {

	const KEY = 'ack_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUV';

	/**
	 * Options written during a test: name => value.
	 *
	 * @var array<string, mixed>
	 */
	private $written;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::load_classes();
	}

	protected function set_up() {
		parent::set_up();
		$this->stub_http_helpers();

		$this->written = array();

		Functions\when( 'update_option' )->alias(
			function ( $name, $value ) {
				$this->written[ $name ] = $value;
				return true;
			}
		);
		Functions\when( 'add_option' )->alias(
			function ( $name, $value ) {
				$this->written[ $name ] = $value;
				return true;
			}
		);
	}

	/**
	 * @dataProvider data_bad_formats
	 *
	 * @param string $key Pasted text.
	 */
	public function test_text_that_is_not_a_key_is_rejected_without_a_request( $key ) {
		Functions\expect( 'wp_remote_request' )->never();

		$result = Asapchat_Connect::connect_with_key( $key );

		$this->assertSame( 'key_format', $result['notice'] );
		$this->assertSame( array(), $this->written );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public function data_bad_formats() {
		return array(
			'empty'                => array( '' ),
			'too short'            => array( 'ack_abc' ),
			'too long'             => array( self::KEY . 'x' ),
			'other prefix'         => array( 'key_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUV' ),
			'character outside AZ' => array( 'ack_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRST-V' ),
			'widget token'         => array( '9f2c41d7a0b35e6688c1d4f7a2b90e13' ),
		);
	}

	public function test_key_with_spaces_around_is_accepted() {
		Functions\expect( 'wp_remote_request' )->once()->andReturn( self::http_response( 200, self::fixture( 'config.json' ) ) );

		$result = Asapchat_Connect::connect_with_key( '  ' . self::KEY . ' ' );

		$this->assertSame( 'connected', $result['notice'] );
	}

	public function test_valid_key_is_checked_with_config_and_saved_like_the_button() {
		$sent = null;
		Functions\expect( 'wp_remote_request' )
			->once()
			->andReturnUsing(
				function ( $url, $args ) use ( &$sent ) {
					$sent = compact( 'url', 'args' );
					return self::http_response( 200, self::fixture( 'config.json' ) );
				}
			);

		$result = Asapchat_Connect::connect_with_key( self::KEY );

		$this->assertSame(
			array(
				'notice' => 'connected',
				'value'  => 'Kwiatowy',
			),
			$result
		);
		$this->assertSame( 'https://panel.asapchat.io/api/plugin/v1/config', $sent['url'] );
		$this->assertSame( 'GET', $sent['args']['method'] );
		$this->assertSame( 'Bearer ' . self::KEY, $sent['args']['headers']['Authorization'] );
		$this->assertSame( self::HOME_URL, $sent['args']['headers']['X-Site-Url'] );

		$this->assertSame( self::KEY, $this->written['asapchat_connection']['token'] );
		$this->assertSame( 17, $this->written['asapchat_connection']['shop_id'] );
		$this->assertSame( 'ok', $this->written['asapchat_connection']['status'] );
		$this->assertArrayHasKey( 'asapchat_config', $this->written );
		$this->assertArrayHasKey( 'asapchat_settings', $this->written );
	}

	/**
	 * @dataProvider data_errors
	 *
	 * @param mixed  $response Response of wp_remote_request().
	 * @param string $notice   Expected notice code.
	 */
	public function test_errors_give_the_messages_from_the_spec_and_save_nothing( $response, $notice ) {
		Functions\expect( 'wp_remote_request' )->once()->andReturn( $response );

		$result = Asapchat_Connect::connect_with_key( self::KEY );

		$this->assertSame( $notice, $result['notice'] );
		$this->assertSame( '', $result['value'] );
		$this->assertSame( array(), $this->written );
	}

	/**
	 * @return array<string, array{0: mixed, 1: string}>
	 */
	public function data_errors() {
		$errors = self::fixture( 'errors.json' );

		return array(
			'401 invalid, revoked or expired' => array( self::http_response( 401, $errors['unauthorized'] ), 'key_invalid' ),
			'409 bound to another domain'     => array( self::http_response( 409, $errors['host_mismatch'] ), 'key_other_domain' ),
			'410 shop deleted'                => array( self::http_response( 410, $errors['shop_deleted'] ), 'key_failed' ),
			'429 rate limit'                  => array( self::http_response( 429 ), 'key_failed' ),
			'500 server error'                => array( self::http_response( 500, '<html></html>' ), 'key_failed' ),
			'no network'                      => array( new \WP_Error( 'http_request_failed' ), 'key_failed' ),
		);
	}

	public function test_config_in_a_newer_version_asks_for_a_plugin_update() {
		$config      = self::fixture( 'config.json' );
		$config['v'] = 2;
		Functions\expect( 'wp_remote_request' )->once()->andReturn( self::http_response( 200, $config ) );

		$result = Asapchat_Connect::connect_with_key( self::KEY );

		$this->assertSame( 'update_plugin', $result['notice'] );
		$this->assertSame( array(), $this->written );
	}

	public function test_texts_of_the_key_messages_match_the_spec() {
		Functions\stubTranslationFunctions();

		$this->assertSame( 'This does not look like an install key.', \Asapchat_Admin::notice_message( 'key_format' )['text'] );
		$this->assertSame( 'The key is invalid, revoked or expired. The key must be used within 24 hours of being generated.', \Asapchat_Admin::notice_message( 'key_invalid' )['text'] );
		$this->assertSame( 'This key is already assigned to another domain.', \Asapchat_Admin::notice_message( 'key_other_domain' )['text'] );
		$this->assertSame( 'Could not connect. Try again in a moment.', \Asapchat_Admin::notice_message( 'key_failed' )['text'] );
	}
}
