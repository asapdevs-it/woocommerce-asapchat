<?php
/**
 * Tests for the chat script on the storefront (spec point 8).
 *
 * @package asapchat
 */

namespace Asapchat\Tests\Unit;

use Asapchat\Tests\TestCase;
use Asapchat_Frontend;
use Brain\Monkey\Functions;

final class FrontendTest extends TestCase {

	/**
	 * Saved options returned by get_option().
	 *
	 * @var array<string, mixed>
	 */
	private $options;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::load_classes();
	}

	protected function set_up() {
		parent::set_up();

		Functions\stubEscapeFunctions();
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'wp_parse_args' )->alias(
			function ( $args, $defaults ) {
				return array_merge( $defaults, $args );
			}
		);
		Functions\when( 'is_admin' )->justReturn( false );
		Functions\when( 'wp_doing_ajax' )->justReturn( false );
		Functions\when( 'is_customize_preview' )->justReturn( false );

		// The storefront never talks to AsapChat.
		foreach ( array( 'wp_remote_request', 'wp_remote_get', 'wp_remote_post', 'wp_safe_remote_request', 'wp_safe_remote_get', 'wp_safe_remote_post' ) as $function ) {
			Functions\expect( $function )->never();
		}

		$this->options = array(
			'asapchat_connection' => array(
				'token'  => 'ack_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUV',
				'status' => 'ok',
			),
			'asapchat_config'     => self::fixture( 'config.json' ),
			'asapchat_settings'   => array( 'chat_enabled' => true ),
		);

		Functions\when( 'get_option' )->alias(
			function ( $name, $default = false ) {
				return array_key_exists( $name, $this->options ) ? $this->options[ $name ] : $default;
			}
		);
	}

	public function test_chat_script_is_enqueued_in_the_footer_from_the_config() {
		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with( 'asapchat-chat', 'https://asapchat.io/chat/embedded-widget.js', array(), null, true );

		Asapchat_Frontend::enqueue();

		$this->addToAssertionCount( 1 );
	}

	public function test_tag_has_every_attribute_from_the_config_and_the_cache_markers() {
		$tag = Asapchat_Frontend::filter_tag( '<script src="x"></script>', 'asapchat-chat' );

		$this->assertSame(
			'<script src="https://asapchat.io/chat/embedded-widget.js" data-no-optimize="1" data-cfasync="false" main_color="#112233" name="Flora" shop_id="17" token="abc123"></script>' . "\n",
			$tag
		);
	}

	public function test_true_gives_an_attribute_without_value_and_unknown_names_are_kept() {
		$tag = Asapchat_Frontend::build_tag(
			'https://asapchat.io/chat/embedded-widget.js',
			array(
				'defer'          => true,
				'data-new-thing' => 'yes',
				'allow_add-to-cart' => 'true',
			)
		);

		$this->assertStringContainsString( ' defer', $tag );
		$this->assertStringNotContainsString( 'defer=', $tag );
		$this->assertStringContainsString( ' data-new-thing="yes"', $tag );
		$this->assertStringContainsString( ' allow_add-to-cart="true"', $tag );
	}

	public function test_values_are_escaped_and_bad_names_skipped() {
		$tag = Asapchat_Frontend::build_tag(
			'https://asapchat.io/chat/embedded-widget.js',
			array(
				'name'               => 'Flora" onload="alert(1)',
				'bad name'           => 'x',
				'onerror=alert(1) x' => 'y',
				'1starts-with-digit' => 'z',
				'src'                => 'https://evil.example/x.js',
				'empty'              => null,
				'off'                => false,
				'nested'             => array( 'a' => 'b' ),
				'number'             => 17,
			)
		);

		$this->assertStringContainsString( 'name="Flora&quot; onload=&quot;alert(1)"', $tag );
		$this->assertStringNotContainsString( 'bad name', $tag );
		$this->assertStringNotContainsString( 'onerror', $tag );
		$this->assertStringNotContainsString( '1starts', $tag );
		$this->assertStringNotContainsString( 'evil.example', $tag );
		$this->assertStringNotContainsString( 'empty', $tag );
		$this->assertStringNotContainsString( ' off', $tag );
		$this->assertStringNotContainsString( 'nested', $tag );
		$this->assertStringContainsString( ' number="17"', $tag );
	}

	public function test_other_scripts_are_not_touched() {
		$this->assertSame( '<script src="a.js"></script>', Asapchat_Frontend::filter_tag( '<script src="a.js"></script>', 'jquery' ) );
	}

	/**
	 * @dataProvider data_no_chat
	 *
	 * @param string $case What is different from the connected default.
	 */
	public function test_chat_script_is_not_added( $case ) {
		switch ( $case ) {
			case 'chat switch off':
				$this->options['asapchat_settings']['chat_enabled'] = false;
				break;
			case 'chat disabled in AsapChat':
				$this->options['asapchat_config']['chat']['enabled'] = false;
				break;
			case 'src on another host':
				$this->options['asapchat_config']['chat']['src'] = 'https://cdn.example/embedded-widget.js';
				break;
			case 'src over http':
				$this->options['asapchat_config']['chat']['src'] = 'http://asapchat.io/chat/embedded-widget.js';
				break;
			case 'src on a look-alike host':
				$this->options['asapchat_config']['chat']['src'] = 'https://asapchat.io.example/chat.js';
				break;
			case 'connection lost':
				$this->options['asapchat_connection'] = array(
					'status'     => 'error',
					'error_code' => 401,
				);
				break;
			case 'not connected':
				unset( $this->options['asapchat_connection'], $this->options['asapchat_config'] );
				break;
			case 'admin':
				Functions\when( 'is_admin' )->justReturn( true );
				break;
			case 'ajax':
				Functions\when( 'wp_doing_ajax' )->justReturn( true );
				break;
			case 'customizer preview':
				Functions\when( 'is_customize_preview' )->justReturn( true );
				break;
		}

		Functions\expect( 'wp_enqueue_script' )->never();

		Asapchat_Frontend::enqueue();

		$this->addToAssertionCount( 1 );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public function data_no_chat() {
		$cases = array( 'chat switch off', 'chat disabled in AsapChat', 'src on another host', 'src over http', 'src on a look-alike host', 'connection lost', 'not connected', 'admin', 'ajax', 'customizer preview' );

		return array_combine(
			$cases,
			array_map(
				function ( $c ) {
					return array( $c );
				},
				$cases
			)
		);
	}

	public function test_tag_is_dropped_when_the_config_no_longer_allows_it() {
		$this->options['asapchat_config']['chat']['src'] = 'https://cdn.example/embedded-widget.js';

		$this->assertSame( '', Asapchat_Frontend::filter_tag( '<script src="x"></script>', 'asapchat-chat' ) );
	}
}
