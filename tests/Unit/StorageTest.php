<?php
/**
 * Tests for the options kept by the plugin and the reaction to config answers (spec points 5.1 and 10).
 *
 * @package asapchat
 */

namespace Asapchat\Tests\Unit;

use Asapchat\Tests\TestCase;
use Asapchat_Admin;
use Asapchat_Connect;
use Brain\Monkey\Functions;

final class StorageTest extends TestCase {

	const TOKEN = 'ack_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUV';

	/**
	 * Options written during a test: name => array( value, autoload ).
	 *
	 * @var array<string, array{0: mixed, 1: mixed}>
	 */
	private $written;

	/**
	 * Options deleted during a test.
	 *
	 * @var array<int, string>
	 */
	private $deleted;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::load_classes();
	}

	protected function set_up() {
		parent::set_up();

		$this->written = array();
		$this->deleted = array();

		Functions\when( 'update_option' )->alias(
			function ( $name, $value, $autoload = null ) {
				$this->written[ $name ] = array( $value, $autoload );
				return true;
			}
		);
		Functions\when( 'add_option' )->alias(
			function ( $name, $value, $unused = '', $autoload = 'yes' ) {
				$this->written[ $name ] = array( $value, $autoload );
				return true;
			}
		);
		Functions\when( 'delete_option' )->alias(
			function ( $name ) {
				$this->deleted[] = $name;
				return true;
			}
		);
	}

	public function test_new_connection_keeps_the_token_only_in_the_connection_option() {
		$config = self::fixture( 'config.json' );

		Asapchat_Connect::save_connection( self::TOKEN, $config );

		list( $connection, $autoload ) = $this->written['asapchat_connection'];
		$this->assertSame( self::TOKEN, $connection['token'] );
		$this->assertSame( 17, $connection['shop_id'] );
		$this->assertSame( 'Kwiatowy', $connection['shop_name'] );
		$this->assertSame( 'ok', $connection['status'] );
		$this->assertFalse( $autoload, 'The token must not be autoloaded.' );

		list( $saved_config, $config_autoload ) = $this->written['asapchat_config'];
		$this->assertTrue( $config_autoload, 'The config is read on every storefront page.' );
		$this->assertStringNotContainsString( self::TOKEN, json_encode( $saved_config ) );
		$this->assertArrayHasKey( 'fetched_at', $saved_config );
		$this->assertSame( $config['chat'], $saved_config['chat'] );
	}

	public function test_new_connection_adds_default_settings_without_overwriting() {
		Asapchat_Connect::save_connection( self::TOKEN, self::fixture( 'config.json' ) );

		list( $settings, $autoload ) = $this->written['asapchat_settings'];
		$this->assertSame( Asapchat_Admin::default_settings(), $settings );
		$this->assertTrue( $autoload );
	}

	public function test_config_200_is_saved() {
		$result = Asapchat_Connect::apply_config_response(
			array(
				'status' => 200,
				'data'   => self::fixture( 'config-search.json' ),
			)
		);

		$this->assertSame( Asapchat_Connect::RESULT_SAVED, $result );
		$this->assertTrue( $this->written['asapchat_config'][0]['search']['enabled'] );
		$this->assertTrue( $this->written['asapchat_config'][1] );
	}

	public function test_config_with_another_version_does_not_replace_the_saved_copy() {
		$config      = self::fixture( 'config.json' );
		$config['v'] = 2;

		$result = Asapchat_Connect::apply_config_response(
			array(
				'status' => 200,
				'data'   => $config,
			)
		);

		$this->assertSame( Asapchat_Connect::RESULT_UPDATE_PLUGIN, $result );
		$this->assertSame( array(), $this->written );
		$this->assertSame( array(), $this->deleted );
	}

	/**
	 * @dataProvider data_lost_codes
	 *
	 * @param int    $status HTTP status.
	 * @param string $error  Sample body key in errors.json.
	 */
	public function test_401_409_410_drop_token_and_config_but_keep_settings( $status, $error ) {
		Functions\when( 'get_option' )->justReturn(
			array(
				'token'     => self::TOKEN,
				'shop_name' => 'Kwiatowy',
				'status'    => 'ok',
			)
		);

		$result = Asapchat_Connect::apply_config_response(
			array(
				'status' => $status,
				'data'   => self::fixture( 'errors.json' )[ $error ],
			)
		);

		$this->assertSame( Asapchat_Connect::RESULT_LOST, $result );

		list( $connection, $autoload ) = $this->written['asapchat_connection'];
		$this->assertArrayNotHasKey( 'token', $connection );
		$this->assertSame( 'error', $connection['status'] );
		$this->assertSame( $status, $connection['error_code'] );
		$this->assertSame( 'Kwiatowy', $connection['shop_name'] );
		$this->assertFalse( $autoload );
		$this->assertSame( array( 'asapchat_config' ), $this->deleted );
		$this->assertArrayNotHasKey( 'asapchat_settings', $this->written );
	}

	/**
	 * @return array<string, array{0: int, 1: string}>
	 */
	public function data_lost_codes() {
		return array(
			'401 unauthorized'  => array( 401, 'unauthorized' ),
			'409 host_mismatch' => array( 409, 'host_mismatch' ),
			'410 shop_deleted'  => array( 410, 'shop_deleted' ),
		);
	}

	/**
	 * @dataProvider data_temporary_problems
	 *
	 * @param int        $status HTTP status, 0 for no network.
	 * @param array|null $data   Decoded body.
	 */
	public function test_temporary_problems_keep_everything( $status, $data ) {
		$result = Asapchat_Connect::apply_config_response(
			array(
				'status' => $status,
				'data'   => $data,
			)
		);

		$this->assertSame( Asapchat_Connect::RESULT_UNAVAILABLE, $result );
		$this->assertSame( array(), $this->written );
		$this->assertSame( array(), $this->deleted );
	}

	/**
	 * @return array<string, array{0: int, 1: array|null}>
	 */
	public function data_temporary_problems() {
		$errors = self::fixture( 'errors.json' );

		return array(
			'400 site_url_required' => array( 400, $errors['site_url_required'] ),
			'422 validation'        => array( 422, $errors['validation'] ),
			'429 rate limit'        => array( 429, null ),
			'500 server error'      => array( 500, null ),
			'no network'            => array( 0, null ),
			'200 without JSON'      => array( 200, null ),
		);
	}

	public function test_token_is_empty_without_a_connection() {
		Functions\when( 'get_option' )->justReturn( array() );

		$this->assertSame( '', Asapchat_Connect::token() );
	}

	public function test_token_is_read_from_the_connection() {
		Functions\when( 'get_option' )->justReturn( array( 'token' => self::TOKEN ) );

		$this->assertSame( self::TOKEN, Asapchat_Connect::token() );
	}
}
