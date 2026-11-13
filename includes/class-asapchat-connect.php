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
