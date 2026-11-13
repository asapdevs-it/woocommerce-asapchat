<?php
/**
 * Connecting the site to AsapChat.
 *
 * @package asapchat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Starts the connection, handles the return from AsapChat, the install key and disconnecting,
 * and keeps the connection, the config and the local settings in wp_options (spec point 5.1).
 */
class Asapchat_Connect {

	const OPTION_CONNECTION = 'asapchat_connection';

	const OPTION_CONFIG = 'asapchat_config';

	const OPTION_SETTINGS = 'asapchat_settings';

	const TOKEN_PATTERN = '/^ack_[A-Za-z0-9]{48}$/';

	const CONFIG_VERSION = 1;

	const LOST_CODES = array( 401, 409, 410 );

	const RESULT_SAVED = 'saved';

	const RESULT_UPDATE_PLUGIN = 'update_plugin';

	const RESULT_LOST = 'lost';

	const RESULT_UNAVAILABLE = 'unavailable';

	const TRANSIENT_PREFIX = 'asapchat_connect_';

	/**
	 * Hooks the admin-post handlers.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_post_asapchat_connect_start', array( __CLASS__, 'handle_start' ) );
		add_action( 'admin_post_asapchat_connect_callback', array( __CLASS__, 'handle_callback' ) );
		add_action( 'admin_post_asapchat_connect_key', array( __CLASS__, 'handle_key' ) );
	}

	/**
	 * "Connect with key": the fallback when the redirect cannot work (spec point 7).
	 *
	 * @return void
	 */
	public static function handle_key() {
		check_admin_referer( 'asapchat_connect_key' );
		self::require_admin();

		$key    = isset( $_POST['asapchat_key'] ) ? sanitize_text_field( wp_unslash( $_POST['asapchat_key'] ) ) : '';
		$result = self::connect_with_key( $key );

		// The key never goes into the notice or the address.
		Asapchat_Admin::add_notice( $result['notice'], $result['value'] );
		self::back_to_screen();
	}

	/**
	 * Checks an install key with GET /config and saves the connection like the button does.
	 *
	 * The first call binds the key to this site (X-Site-Url). The key is already a connection token.
	 *
	 * @param string $key Install key pasted by the user.
	 * @return array{notice: string, value: string} Notice code for the screen.
	 */
	public static function connect_with_key( $key ) {
		$key = trim( $key );

		if ( ! preg_match( self::TOKEN_PATTERN, $key ) ) {
			return self::result( 'key_format' );
		}

		$response = Asapchat_Api::get_config( $key );

		if ( 401 === $response['status'] ) {
			return self::result( 'key_invalid' );
		}

		if ( 409 === $response['status'] ) {
			return self::result( 'key_other_domain' );
		}

		if ( 200 !== $response['status'] || ! is_array( $response['data'] ) ) {
			return self::result( 'key_failed' );
		}

		if ( ! self::is_supported_config( $response['data'] ) ) {
			return self::result( 'update_plugin' );
		}

		self::save_connection( $key, $response['data'] );

		$shop_name = isset( $response['data']['shop']['name'] ) ? (string) $response['data']['shop']['name'] : '';

		return self::result( 'connected', $shop_name );
	}

	/**
	 * "Connect to AsapChat": sends the browser to the AsapChat panel (spec point 6, steps 1 to 5).
	 *
	 * @return void
	 */
	public static function handle_start() {
		check_admin_referer( 'asapchat_connect' );
		self::require_admin();

		if ( ! Asapchat_Admin::connect_button_available( home_url(), admin_url() ) ) {
			self::back_to_screen();
		}

		// The AsapChat panel is on another host, so wp_safe_redirect() would refuse it.
		wp_redirect( self::start_url( get_current_user_id() ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit;
	}

	/**
	 * Return from the AsapChat panel (spec point 6, step 7).
	 *
	 * There is no nonce: the request comes back from AsapChat. It is protected by the
	 * one-time state, the logged-in administrator session and PKCE (spec point 6, step 8).
	 *
	 * @return void
	 */
	public static function handle_callback() {
		self::require_admin();

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- see the method description.
		$params = array(
			'state' => isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '',
			'code'  => isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '',
			'error' => isset( $_GET['error'] ) ? sanitize_text_field( wp_unslash( $_GET['error'] ) ) : '',
		);
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$result = self::finish_connect( get_current_user_id(), $params );

		Asapchat_Admin::add_notice( $result['notice'], $result['value'] );
		self::back_to_screen();
	}

	/**
	 * Prepares a connection attempt and builds the address of the AsapChat consent screen.
	 *
	 * A new click overwrites the previous attempt of the same user.
	 *
	 * @param int $user_id Current user.
	 * @return string
	 */
	public static function start_url( $user_id ) {
		$state    = wp_generate_password( 32, false );
		$verifier = self::random_verifier();

		// Two hours: a new user goes through the sign-up and the whole AsapChat wizard on the way.
		set_transient(
			self::TRANSIENT_PREFIX . $user_id,
			array(
				'state'    => $state,
				'verifier' => $verifier,
			),
			2 * HOUR_IN_SECONDS
		);

		$query = array(
			'platform'       => 'wordpress',
			'site_url'       => home_url(),
			'return_url'     => admin_url( 'admin-post.php?action=asapchat_connect_callback' ),
			'state'          => $state,
			'code_challenge' => self::challenge( $verifier ),
		);

		return ASAPCHAT_BASE . '/connect?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );
	}

	/**
	 * Checks the return from AsapChat and exchanges the code for the token.
	 *
	 * @param int                                               $user_id Current user.
	 * @param array{state: string, code: string, error: string} $params  Query parameters of the return address.
	 * @return array{notice: string, value: string} Notice code for the screen (spec point 4.5).
	 */
	public static function finish_connect( $user_id, $params ) {
		$key     = self::TRANSIENT_PREFIX . $user_id;
		$attempt = get_transient( $key );

		if ( ! is_array( $attempt ) || empty( $attempt['state'] ) || empty( $attempt['verifier'] )
			|| '' === $params['state'] || ! hash_equals( (string) $attempt['state'], $params['state'] ) ) {
			return self::result( 'state_expired' );
		}

		// The state works only once, also when the user cancelled.
		delete_transient( $key );

		if ( '' !== $params['error'] ) {
			return self::result( 'cancelled' );
		}

		if ( '' === $params['code'] ) {
			return self::result( 'exchange_failed' );
		}

		// The code is single use and expires after 120 seconds, so a failed exchange is never retried.
		$response = Asapchat_Api::exchange_code( $params['code'], (string) $attempt['verifier'] );
		$data     = $response['data'];

		if ( 200 !== $response['status'] || ! is_array( $data )
			|| ! isset( $data['token'] ) || ! is_string( $data['token'] ) || ! preg_match( self::TOKEN_PATTERN, $data['token'] )
			|| ! isset( $data['config'] ) || ! is_array( $data['config'] ) ) {
			return self::result( 'exchange_failed' );
		}

		if ( ! self::is_supported_config( $data['config'] ) ) {
			return self::result( 'update_plugin' );
		}

		self::save_connection( $data['token'], $data['config'] );

		$shop_name = isset( $data['config']['shop']['name'] ) ? (string) $data['config']['shop']['name'] : '';

		return self::result( 'connected', $shop_name );
	}

	/**
	 * PKCE code challenge: base64url( sha256( verifier ) ) without padding, 43 characters.
	 *
	 * @param string $verifier Code verifier.
	 * @return string
	 */
	public static function challenge( $verifier ) {
		return self::base64url( hash( 'sha256', $verifier, true ) );
	}

	/**
	 * Random PKCE code verifier, 64 characters from [A-Za-z0-9_-].
	 *
	 * @return string
	 */
	private static function random_verifier() {
		return self::base64url( random_bytes( 48 ) );
	}

	/**
	 * Base64url without padding (RFC 7636).
	 *
	 * @param string $bytes Raw bytes.
	 * @return string
	 */
	private static function base64url( $bytes ) {
		return rtrim( strtr( base64_encode( $bytes ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- PKCE encoding, not obfuscation.
	}

	/**
	 * Builds a result for the screen notice.
	 *
	 * @param string $notice Notice code.
	 * @param string $value  Value for the notice text.
	 * @return array{notice: string, value: string}
	 */
	private static function result( $notice, $value = '' ) {
		return array(
			'notice' => $notice,
			'value'  => $value,
		);
	}

	/**
	 * Stops unless the user can manage the plugin.
	 *
	 * @return void
	 */
	private static function require_admin() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( '', '', array( 'response' => 403 ) );
		}
	}

	/**
	 * Goes back to Settings > AsapChat.
	 *
	 * @return void
	 */
	private static function back_to_screen() {
		wp_safe_redirect( admin_url( 'options-general.php?page=' . Asapchat_Admin::PAGE_SLUG ) );
		exit;
	}

	/**
	 * The saved connection.
	 *
	 * @return array<string, mixed>
	 */
	public static function connection() {
		$connection = get_option( self::OPTION_CONNECTION, array() );

		return is_array( $connection ) ? $connection : array();
	}

	/**
	 * The connection token, or an empty string when the site is not connected.
	 *
	 * @return string
	 */
	public static function token() {
		$connection = self::connection();

		return isset( $connection['token'] ) && is_string( $connection['token'] ) ? $connection['token'] : '';
	}

	/**
	 * The saved config.
	 *
	 * @return array<string, mixed>
	 */
	public static function config() {
		$config = get_option( self::OPTION_CONFIG, array() );

		return is_array( $config ) ? $config : array();
	}

	/**
	 * Whether a config has the response shape this plugin understands.
	 *
	 * @param mixed $config Config from AsapChat.
	 * @return bool
	 */
	public static function is_supported_config( $config ) {
		return is_array( $config ) && isset( $config['v'] ) && self::CONFIG_VERSION === $config['v'];
	}

	/**
	 * Saves a new connection: token, shop, config and default local settings when there are none yet.
	 *
	 * The token is kept only in asapchat_connection (autoload off), never in asapchat_config.
	 *
	 * @param string               $token  Connection token.
	 * @param array<string, mixed> $config Config from AsapChat.
	 * @return void
	 */
	public static function save_connection( $token, $config ) {
		$shop = isset( $config['shop'] ) && is_array( $config['shop'] ) ? $config['shop'] : array();

		update_option(
			self::OPTION_CONNECTION,
			array(
				'token'        => $token,
				'shop_id'      => isset( $shop['id'] ) ? (int) $shop['id'] : 0,
				'shop_name'    => isset( $shop['name'] ) ? (string) $shop['name'] : '',
				'connected_at' => time(),
				'status'       => 'ok',
			),
			false
		);

		self::save_config( $config );

		// Local settings survive reconnecting, so they are created only once.
		add_option( self::OPTION_SETTINGS, Asapchat_Admin::default_settings(), '', true );
	}

	/**
	 * Saves the whole config with the time it was fetched. Read on every storefront page, so autoloaded.
	 *
	 * @param array<string, mixed> $config Config from AsapChat.
	 * @return void
	 */
	public static function save_config( $config ) {
		$config['fetched_at'] = time();

		update_option( self::OPTION_CONFIG, $config, true );
	}

	/**
	 * Applies an answer that carries a config (GET /config, PATCH /settings), spec point 10.
	 *
	 * @param array{status: int, data: array<string, mixed>|null} $response Result of Asapchat_Api.
	 * @return string One of the RESULT_* constants.
	 */
	public static function apply_config_response( $response ) {
		$status = $response['status'];

		if ( 200 === $status && is_array( $response['data'] ) ) {
			if ( ! self::is_supported_config( $response['data'] ) ) {
				// A newer response shape: keep the saved copy, ask for a plugin update.
				return self::RESULT_UPDATE_PLUGIN;
			}

			self::save_config( $response['data'] );

			return self::RESULT_SAVED;
		}

		if ( in_array( $status, self::LOST_CODES, true ) ) {
			self::mark_lost( $status );

			return self::RESULT_LOST;
		}

		// 400 (a bug in the plugin), 429, 5xx, no network: keep everything, try again later.
		return self::RESULT_UNAVAILABLE;
	}

	/**
	 * Drops the token and the config after 401, 409 or 410. Local settings stay.
	 *
	 * @param int $code HTTP status from AsapChat.
	 * @return void
	 */
	public static function mark_lost( $code ) {
		$connection = self::connection();

		update_option(
			self::OPTION_CONNECTION,
			array(
				'shop_name'  => isset( $connection['shop_name'] ) ? (string) $connection['shop_name'] : '',
				'status'     => 'error',
				'error_code' => (int) $code,
			),
			false
		);

		delete_option( self::OPTION_CONFIG );
	}
}
