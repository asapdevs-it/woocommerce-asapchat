<?php
/**
 * Tests for "Connect to AsapChat" (spec point 6).
 *
 * @package asapchat
 */

namespace Asapchat\Tests\Unit;

use Asapchat\Tests\TestCase;
use Asapchat_Connect;
use Brain\Monkey\Functions;

final class ConnectTest extends TestCase {

	const USER_ID = 7;

	const TOKEN = 'ack_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUV';

	const ATTEMPT = array(
		'state'    => 'Abcdefghijklmnopqrstuvwxyz012345',
		'verifier' => 'verifier-from-the-attempt',
	);

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

		Functions\when( 'admin_url' )->alias(
			function ( $path = '' ) {
				return 'https://shop.example/wp-admin/' . $path;
			}
		);
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

	public function test_start_saves_the_attempt_for_two_hours_and_points_to_the_consent_screen() {
		$saved = null;
		Functions\when( 'wp_generate_password' )->justReturn( self::ATTEMPT['state'] );
		Functions\expect( 'set_transient' )
			->once()
			->andReturnUsing(
				function ( $key, $value, $ttl ) use ( &$saved ) {
					$saved = compact( 'key', 'value', 'ttl' );
					return true;
				}
			);

		$url = Asapchat_Connect::start_url( self::USER_ID );

		$this->assertSame( 'asapchat_connect_7', $saved['key'] );
		$this->assertSame( 2 * HOUR_IN_SECONDS, $saved['ttl'] );
		$this->assertSame( self::ATTEMPT['state'], $saved['value']['state'] );
		$this->assertMatchesRegularExpression( '/^[A-Za-z0-9_-]{64}$/', $saved['value']['verifier'] );

		$this->assertStringStartsWith( 'https://panel.asapchat.io/connect?', $url );
		parse_str( (string) parse_url( $url, PHP_URL_QUERY ), $query );
		$this->assertSame(
			array(
				'platform'       => 'wordpress',
				'site_url'       => 'https://shop.example',
				'return_url'     => 'https://shop.example/wp-admin/admin-post.php?action=asapchat_connect_callback',
				'state'          => self::ATTEMPT['state'],
				'code_challenge' => Asapchat_Connect::challenge( $saved['value']['verifier'] ),
			),
			$query
		);
		$this->assertSame( 43, strlen( $query['code_challenge'] ) );
		$this->assertStringNotContainsString( $saved['value']['verifier'], $url, 'The verifier never goes into the address.' );
	}

	public function test_challenge_matches_the_rfc_7636_example() {
		$this->assertSame(
			'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
			Asapchat_Connect::challenge( 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk' )
		);
	}

	public function test_return_without_a_saved_attempt_is_an_expired_session() {
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\expect( 'delete_transient' )->never();
		Functions\expect( 'wp_remote_request' )->never();

		$result = Asapchat_Connect::finish_connect( self::USER_ID, $this->params( array( 'state' => self::ATTEMPT['state'] ) ) );

		$this->assertSame( 'state_expired', $result['notice'] );
		$this->assertSame( array(), $this->written );
	}

	public function test_return_with_another_state_is_an_expired_session_and_no_exchange() {
		Functions\when( 'get_transient' )->justReturn( self::ATTEMPT );
		Functions\expect( 'delete_transient' )->never();
		Functions\expect( 'wp_remote_request' )->never();

		$result = Asapchat_Connect::finish_connect(
			self::USER_ID,
			$this->params(
				array(
					'state' => 'Xbcdefghijklmnopqrstuvwxyz012345',
					'code'  => 'code123',
				)
			)
		);

		$this->assertSame( 'state_expired', $result['notice'] );
		$this->assertSame( array(), $this->written );
	}

	public function test_return_without_state_is_an_expired_session() {
		Functions\when( 'get_transient' )->justReturn( self::ATTEMPT );
		Functions\expect( 'wp_remote_request' )->never();

		$result = Asapchat_Connect::finish_connect( self::USER_ID, $this->params( array( 'code' => 'code123' ) ) );

		$this->assertSame( 'state_expired', $result['notice'] );
	}

	public function test_cancel_in_the_panel_uses_up_the_state_and_saves_nothing() {
		Functions\when( 'get_transient' )->justReturn( self::ATTEMPT );
		Functions\expect( 'delete_transient' )->once()->with( 'asapchat_connect_7' );
		Functions\expect( 'wp_remote_request' )->never();

		$result = Asapchat_Connect::finish_connect(
			self::USER_ID,
			$this->params(
				array(
					'state' => self::ATTEMPT['state'],
					'error' => 'access_denied',
				)
			)
		);

		$this->assertSame( 'cancelled', $result['notice'] );
		$this->assertSame( array(), $this->written );
	}

	public function test_successful_exchange_saves_the_connection() {
		$config = self::fixture( 'config.json' );
		$sent   = null;

		Functions\when( 'get_transient' )->justReturn( self::ATTEMPT );
		Functions\expect( 'delete_transient' )->once()->with( 'asapchat_connect_7' );
		Functions\expect( 'wp_remote_request' )
			->once()
			->andReturnUsing(
				function ( $url, $args ) use ( &$sent, $config ) {
					$sent = json_decode( $args['body'], true );
					return self::http_response(
						200,
						array(
							'token'  => self::TOKEN,
							'shop'   => array(
								'id'   => 17,
								'name' => 'Kwiatowy',
							),
							'config' => $config,
						)
					);
				}
			);

		$result = Asapchat_Connect::finish_connect(
			self::USER_ID,
			$this->params(
				array(
					'state' => self::ATTEMPT['state'],
					'code'  => 'code123',
				)
			)
		);

		$this->assertSame(
			array(
				'notice' => 'connected',
				'value'  => 'Kwiatowy',
			),
			$result
		);
		$this->assertSame(
			array(
				'code'          => 'code123',
				'code_verifier' => self::ATTEMPT['verifier'],
			),
			$sent
		);
		$this->assertSame( self::TOKEN, $this->written['asapchat_connection']['token'] );
		$this->assertSame( 'ok', $this->written['asapchat_connection']['status'] );
		$this->assertSame( $config['chat'], $this->written['asapchat_config']['chat'] );
		$this->assertArrayHasKey( 'asapchat_settings', $this->written );
	}

	/**
	 * @dataProvider data_failed_exchanges
	 *
	 * @param mixed $response Response of wp_remote_request().
	 */
	public function test_failed_exchange_saves_nothing( $response ) {
		Functions\when( 'get_transient' )->justReturn( self::ATTEMPT );
		Functions\when( 'delete_transient' )->justReturn( true );
		Functions\expect( 'wp_remote_request' )->once()->andReturn( $response );

		$result = Asapchat_Connect::finish_connect(
			self::USER_ID,
			$this->params(
				array(
					'state' => self::ATTEMPT['state'],
					'code'  => 'code123',
				)
			)
		);

		$this->assertSame( 'exchange_failed', $result['notice'] );
		$this->assertSame( array(), $this->written );
	}

	/**
	 * @return array<string, array{0: mixed}>
	 */
	public function data_failed_exchanges() {
		return array(
			'400 invalid_grant'      => array( self::http_response( 400, self::fixture( 'errors.json' )['invalid_grant'] ) ),
			'429 rate limit'         => array( self::http_response( 429 ) ),
			'500 server error'       => array( self::http_response( 500, '<html></html>' ) ),
			'200 with a bad token'   => array(
				self::http_response(
					200,
					array(
						'token'  => 'ack_short',
						'config' => self::fixture( 'config.json' ),
					)
				),
			),
			'200 without the config' => array( self::http_response( 200, array( 'token' => self::TOKEN ) ) ),
		);
	}

	public function test_network_error_saves_nothing() {
		Functions\when( 'get_transient' )->justReturn( self::ATTEMPT );
		Functions\when( 'delete_transient' )->justReturn( true );
		Functions\expect( 'wp_remote_request' )->once()->andReturn( new \WP_Error( 'http_request_failed' ) );

		$result = Asapchat_Connect::finish_connect(
			self::USER_ID,
			$this->params(
				array(
					'state' => self::ATTEMPT['state'],
					'code'  => 'code123',
				)
			)
		);

		$this->assertSame( 'exchange_failed', $result['notice'] );
		$this->assertSame( array(), $this->written );
	}

	public function test_config_in_a_newer_version_asks_for_a_plugin_update() {
		$config      = self::fixture( 'config.json' );
		$config['v'] = 2;

		Functions\when( 'get_transient' )->justReturn( self::ATTEMPT );
		Functions\when( 'delete_transient' )->justReturn( true );
		Functions\expect( 'wp_remote_request' )->once()->andReturn(
			self::http_response(
				200,
				array(
					'token'  => self::TOKEN,
					'config' => $config,
				)
			)
		);

		$result = Asapchat_Connect::finish_connect(
			self::USER_ID,
			$this->params(
				array(
					'state' => self::ATTEMPT['state'],
					'code'  => 'code123',
				)
			)
		);

		$this->assertSame( 'update_plugin', $result['notice'] );
		$this->assertSame( array(), $this->written );
	}

	/**
	 * Query parameters of the return address.
	 *
	 * @param array<string, string> $values Values to set.
	 * @return array{state: string, code: string, error: string}
	 */
	private function params( $values ) {
		return array_merge(
			array(
				'state' => '',
				'code'  => '',
				'error' => '',
			),
			$values
		);
	}
}
