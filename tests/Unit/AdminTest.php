<?php
/**
 * Tests for the settings screen logic.
 *
 * @package asapchat
 */

namespace Asapchat\Tests\Unit;

use Asapchat\Tests\TestCase;
use Asapchat_Admin;
use Brain\Monkey\Functions;

final class AdminTest extends TestCase {

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::load_classes();
	}

	protected function set_up() {
		parent::set_up();

		Functions\stubTranslationFunctions();
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
	}

	public function test_screen_state_without_connection_is_disconnected() {
		$this->assertSame( Asapchat_Admin::STATE_DISCONNECTED, Asapchat_Admin::screen_state( array(), array() ) );
	}

	public function test_screen_state_with_token_and_config_is_connected() {
		$connection = array(
			'token'  => 'ack_x',
			'status' => 'ok',
		);

		$this->assertSame( Asapchat_Admin::STATE_CONNECTED, Asapchat_Admin::screen_state( $connection, array( 'v' => 1 ) ) );
	}

	public function test_screen_state_with_token_but_no_config_is_disconnected() {
		$connection = array(
			'token'  => 'ack_x',
			'status' => 'ok',
		);

		$this->assertSame( Asapchat_Admin::STATE_DISCONNECTED, Asapchat_Admin::screen_state( $connection, array() ) );
	}

	public function test_screen_state_after_401_409_410_is_error() {
		$connection = array(
			'status'     => 'error',
			'error_code' => 401,
		);

		$this->assertSame( Asapchat_Admin::STATE_ERROR, Asapchat_Admin::screen_state( $connection, array() ) );
	}

	/**
	 * @dataProvider data_connect_button
	 *
	 * @param string $home     home_url().
	 * @param string $admin    admin_url().
	 * @param bool   $expected Whether the button works.
	 */
	public function test_connect_button_availability( $home, $admin, $expected ) {
		$this->assertSame( $expected, Asapchat_Admin::connect_button_available( $home, $admin ) );
	}

	/**
	 * @return array<string, array{0: string, 1: string, 2: bool}>
	 */
	public function data_connect_button() {
		return array(
			'https, same host'                => array( 'https://shop.pl', 'https://shop.pl/wp-admin/', true ),
			'http on localhost with port'     => array( 'http://localhost:8888', 'http://localhost:8888/wp-admin/', true ),
			'http on 127.0.0.1'               => array( 'http://127.0.0.1', 'http://127.0.0.1/wp-admin/', true ),
			'http outside localhost'          => array( 'http://shop.pl', 'http://shop.pl/wp-admin/', false ),
			'https, admin on another host'    => array( 'https://shop.pl', 'https://admin.shop.pl/wp-admin/', false ),
			'https, www only on the site'     => array( 'https://www.shop.pl', 'https://shop.pl/wp-admin/', true ),
			'https, www only on the admin'    => array( 'https://shop.pl', 'https://www.shop.pl/wp-admin/', true ),
			'https, admin on another port'    => array( 'https://shop.pl', 'https://shop.pl:8443/wp-admin/', false ),
			'https, explicit default port'    => array( 'https://shop.pl:443', 'https://shop.pl/wp-admin/', true ),
			'localhost, admin on other port'  => array( 'http://localhost:8888', 'http://localhost:8889/wp-admin/', false ),
			'site under a subdirectory'       => array( 'https://shop.pl/store', 'https://shop.pl/store/wp-admin/', true ),
			'not a URL'                       => array( '', 'https://shop.pl/wp-admin/', false ),
		);
	}

	public function test_plan_label_with_limit() {
		$shop = array(
			'plan'                   => 'free',
			'ai_conversations_limit' => 50,
		);

		$this->assertSame( 'Free plan, 50 AI conversations per month', Asapchat_Admin::plan_label( $shop ) );
	}

	public function test_plan_label_without_limit() {
		$shop = array(
			'plan'                   => 'pro',
			'ai_conversations_limit' => null,
		);

		$this->assertSame( 'Pro plan', Asapchat_Admin::plan_label( $shop ) );
	}

	public function test_plan_label_for_an_unknown_plan_is_capitalised() {
		$shop = array(
			'plan'                   => 'business',
			'ai_conversations_limit' => 1000,
		);

		$this->assertSame( 'Business plan, 1000 AI conversations per month', Asapchat_Admin::plan_label( $shop ) );
	}

	public function test_plan_label_is_empty_for_accounts_without_a_plan() {
		$shop = array(
			'plan'                   => null,
			'ai_conversations_limit' => 50,
		);

		$this->assertSame( '', Asapchat_Admin::plan_label( $shop ) );
	}

	public function test_format_date_uses_site_format_and_time_zone() {
		Functions\when( 'get_option' )->alias(
			function ( $name ) {
				return 'date_format' === $name ? 'Y-m-d' : 'H:i';
			}
		);
		Functions\expect( 'wp_date' )
			->once()
			->with( 'Y-m-d H:i', strtotime( '2026-11-11T10:42:00+01:00' ) )
			->andReturn( '2026-11-11 10:42' );

		$this->assertSame( '2026-11-11 10:42', Asapchat_Admin::format_date( '2026-11-11T10:42:00+01:00' ) );
	}

	public function test_format_date_without_a_date_is_empty() {
		Functions\expect( 'wp_date' )->never();

		$this->assertSame( '', Asapchat_Admin::format_date( null ) );
		$this->assertSame( '', Asapchat_Admin::format_date( 'not a date' ) );
	}

	public function test_known_notice_has_text_and_type() {
		$notice = Asapchat_Admin::notice_message( 'connected', 'Kwiatowy' );

		$this->assertSame( 'success', $notice['type'] );
		$this->assertSame( 'Connected to the shop Kwiatowy. The chat is already on your site.', $notice['text'] );
	}

	public function test_unknown_notice_is_ignored() {
		$this->assertNull( Asapchat_Admin::notice_message( 'something-else' ) );
	}

	public function test_assets_load_only_on_the_plugin_screen() {
		Functions\expect( 'wp_enqueue_style' )->never();

		Asapchat_Admin::enqueue_assets( 'options-general.php' );
		Asapchat_Admin::enqueue_assets( 'toplevel_page_woocommerce' );

		$this->addToAssertionCount( 1 );
	}

	public function test_assets_load_on_the_plugin_screen() {
		Functions\expect( 'wp_enqueue_style' )
			->once()
			->with( 'asapchat-admin', ASAPCHAT_URL . 'admin/admin.css', array(), ASAPCHAT_VERSION );

		Asapchat_Admin::enqueue_assets( Asapchat_Admin::HOOK_SUFFIX );

		$this->addToAssertionCount( 1 );
	}

	public function test_default_settings_turn_all_widgets_on() {
		$defaults = Asapchat_Admin::default_settings();

		$this->assertTrue( $defaults['chat_enabled'] );
		$this->assertTrue( $defaults['search_enabled'] );
		$this->assertTrue( $defaults['search_add_to_cart'] );
		$this->assertSame( 0, $defaults['delay_seconds'] );
	}
}
