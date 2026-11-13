<?php
/**
 * Tests for the main plugin file.
 *
 * @package asapchat
 */

namespace Asapchat\Tests\Unit;

use Asapchat\Tests\TestCase;
use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Brain\Monkey\Functions;

/**
 * The main file defines constants and functions, so every test loads it in a fresh process.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class MainFileTest extends TestCase {

	/**
	 * Every WordPress function that sends an HTTP request.
	 */
	const HTTP_FUNCTIONS = array(
		'wp_remote_request',
		'wp_remote_get',
		'wp_remote_post',
		'wp_remote_head',
		'wp_safe_remote_request',
		'wp_safe_remote_get',
		'wp_safe_remote_post',
		'wp_safe_remote_head',
	);

	/**
	 * Callback passed to register_activation_hook().
	 *
	 * @var callable|null
	 */
	private $activation_callback;

	protected function set_up() {
		parent::set_up();

		Functions\when( 'plugin_dir_path' )->justReturn( ASAPCHAT_TEST_ROOT . '/' );
		Functions\when( 'plugin_dir_url' )->justReturn( 'https://example.test/wp-content/plugins/asapchat/' );
		Functions\when( 'is_admin' )->justReturn( false );
		Functions\when( 'register_activation_hook' )->alias(
			function ( $file, $callback ) {
				$this->activation_callback = $callback;
			}
		);
	}

	public function test_loading_and_activating_the_plugin_sends_no_http_request() {
		foreach ( self::HTTP_FUNCTIONS as $function ) {
			Functions\expect( $function )->never();
		}

		$this->load_plugin();

		$this->assertIsCallable( $this->activation_callback );
		call_user_func( $this->activation_callback );
	}

	public function test_defines_plugin_constants() {
		$this->load_plugin();

		$this->assertSame( '1.0.0', ASAPCHAT_VERSION );
		$this->assertSame( realpath( ASAPCHAT_TEST_ROOT . '/asapchat.php' ), ASAPCHAT_FILE );
		$this->assertSame( ASAPCHAT_TEST_ROOT . '/', ASAPCHAT_DIR );
		$this->assertSame( 'https://example.test/wp-content/plugins/asapchat/', ASAPCHAT_URL );
	}

	public function test_version_matches_the_plugin_header() {
		$this->load_plugin();

		$header = file_get_contents( ASAPCHAT_TEST_ROOT . '/asapchat.php' );

		$this->assertMatchesRegularExpression( '/^ \* Version:\s+' . preg_quote( ASAPCHAT_VERSION, '/' ) . '$/m', $header );
	}

	public function test_base_address_defaults_to_production() {
		$this->load_plugin();

		$this->assertSame( 'https://panel.asapchat.io', ASAPCHAT_BASE );
	}

	public function test_base_address_can_be_overridden_in_wp_config() {
		define( 'ASAPCHAT_BASE', 'http://127.0.0.1:9999' );

		$this->load_plugin();

		$this->assertSame( 'http://127.0.0.1:9999', ASAPCHAT_BASE );
	}

	public function test_settings_screen_is_hooked_only_in_the_admin() {
		$this->load_plugin();

		$this->assertFalse( has_action( 'admin_menu', array( 'Asapchat_Admin', 'register_page' ) ) );
	}

	public function test_settings_screen_is_hooked_in_the_admin() {
		Functions\when( 'is_admin' )->justReturn( true );

		$this->load_plugin();

		$this->assertNotFalse( has_action( 'admin_menu', array( 'Asapchat_Admin', 'register_page' ) ) );
		$this->assertNotFalse( has_action( 'admin_enqueue_scripts', array( 'Asapchat_Admin', 'enqueue_assets' ) ) );
		$this->assertNotFalse( has_action( 'admin_post_asapchat_connect_start', array( 'Asapchat_Connect', 'handle_start' ) ) );
		$this->assertNotFalse( has_action( 'admin_post_asapchat_connect_callback', array( 'Asapchat_Connect', 'handle_callback' ) ) );
		$this->assertFalse( has_action( 'admin_post_nopriv_asapchat_connect_callback' ), 'No handler for logged-out users.' );
	}

	public function test_registers_the_hpos_declaration_before_woocommerce_init() {
		$this->load_plugin();

		$this->assertNotFalse( has_action( 'before_woocommerce_init', 'asapchat_declare_wc_compatibility' ) );
	}

	public function test_declares_hpos_compatibility_when_woocommerce_is_active() {
		require ASAPCHAT_TEST_ROOT . '/tests/stubs/FeaturesUtil.php';
		$this->load_plugin();

		asapchat_declare_wc_compatibility();

		$this->assertSame(
			array( array( 'custom_order_tables', realpath( ASAPCHAT_TEST_ROOT . '/asapchat.php' ), true ) ),
			FeaturesUtil::$declarations
		);
	}

	public function test_skips_the_hpos_declaration_without_woocommerce() {
		$this->load_plugin();

		asapchat_declare_wc_compatibility();

		$this->assertFalse( class_exists( FeaturesUtil::class, false ) );
	}

	/**
	 * Loads the main plugin file the way WordPress does.
	 */
	private function load_plugin() {
		require ASAPCHAT_TEST_ROOT . '/asapchat.php';
	}
}
