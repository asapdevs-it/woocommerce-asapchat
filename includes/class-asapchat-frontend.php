<?php
/**
 * Widget scripts on the storefront.
 *
 * @package asapchat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds the chat and search scripts from the saved config, applying exclusions and the chat delay.
 *
 * The storefront reads only the saved options. It never sends a request to AsapChat (spec point 8).
 */
class Asapchat_Frontend {

	const CHAT_HANDLE = 'asapchat-chat';

	/**
	 * Scripts are added only from this address, also when ASAPCHAT_BASE is overridden.
	 */
	const SCRIPT_ORIGIN = 'https://asapchat.io/';

	/**
	 * HTML attribute name syntax. Not a list of allowed names: AsapChat may add new attributes.
	 */
	const ATTRIBUTE_NAME = '/^[a-zA-Z_][a-zA-Z0-9_\-]*$/';

	/**
	 * Hooks the storefront.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'script_loader_tag', array( __CLASS__, 'filter_tag' ), 10, 2 );
	}

	/**
	 * Enqueues the chat script when every condition from spec point 8.1 is met.
	 *
	 * @return void
	 */
	public static function enqueue() {
		$chat = self::script_block( 'chat' );

		if ( null === $chat || ! self::is_storefront_request() || ! self::is_connected() ) {
			return;
		}

		$settings = self::settings();

		if ( empty( $settings['chat_enabled'] ) ) {
			return;
		}

		// The address comes from AsapChat as it is, a version parameter would change it.
		wp_enqueue_script( self::CHAT_HANDLE, $chat['src'], array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	/**
	 * Rebuilds the whole tag of the chat script with the attributes from the config (spec point 8.2).
	 *
	 * @param string $tag    Tag printed by WordPress.
	 * @param string $handle Script handle.
	 * @return string
	 */
	public static function filter_tag( $tag, $handle ) {
		if ( self::CHAT_HANDLE !== $handle ) {
			return $tag;
		}

		$chat = self::script_block( 'chat' );

		if ( null === $chat ) {
			return '';
		}

		return self::build_tag( $chat['src'], isset( $chat['attributes'] ) && is_array( $chat['attributes'] ) ? $chat['attributes'] : array() );
	}

	/**
	 * Builds a script tag: src, the cache plugin markers and every attribute from the config.
	 *
	 * The value true gives an attribute without a value (e.g. defer). Names that are not valid
	 * HTML attribute names, empty values (null, false) and nested values are skipped.
	 *
	 * @param string               $src        Script address.
	 * @param array<string, mixed> $attributes Attributes from the config.
	 * @return string
	 */
	public static function build_tag( $src, $attributes ) {
		// Cache and optimisation plugins skip scripts marked like this, the widget reads its own tag.
		// The tag is rebuilt inside script_loader_tag for a script that is enqueued with wp_enqueue_script().
		$html = '<script src="' . esc_url( $src ) . '" data-no-optimize="1" data-cfasync="false"'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

		foreach ( $attributes as $name => $value ) {
			$name = (string) $name;

			if ( ! preg_match( self::ATTRIBUTE_NAME, $name ) || in_array( strtolower( $name ), array( 'src', 'data-no-optimize', 'data-cfasync' ), true ) ) {
				continue;
			}

			if ( true === $value ) {
				$html .= ' ' . $name;
			} elseif ( is_scalar( $value ) && false !== $value ) {
				$html .= ' ' . $name . '="' . esc_attr( (string) $value ) . '"';
			}
		}

		return $html . '></script>' . "\n";
	}

	/**
	 * A script block of the config (chat or search) when it can be added to the page.
	 *
	 * @param string $name Block name.
	 * @return array<string, mixed>|null Null when disabled or when src is not on https://asapchat.io/.
	 */
	public static function script_block( $name ) {
		$config = Asapchat_Connect::config();
		$block  = isset( $config[ $name ] ) && is_array( $config[ $name ] ) ? $config[ $name ] : null;

		if ( null === $block || ! isset( $block['enabled'] ) || true !== $block['enabled'] ) {
			return null;
		}

		if ( ! isset( $block['src'] ) || ! is_string( $block['src'] ) || 0 !== strpos( $block['src'], self::SCRIPT_ORIGIN ) ) {
			return null;
		}

		return $block;
	}

	/**
	 * Whether this is a regular storefront page: not the admin, AJAX, REST or the Customizer preview.
	 *
	 * @return bool
	 */
	private static function is_storefront_request() {
		if ( is_admin() || wp_doing_ajax() || is_customize_preview() ) {
			return false;
		}

		return ! ( defined( 'REST_REQUEST' ) && REST_REQUEST );
	}

	/**
	 * Whether the saved connection works (no 401, 409 or 410 since the last check).
	 *
	 * @return bool
	 */
	private static function is_connected() {
		$connection = Asapchat_Connect::connection();

		return isset( $connection['status'] ) && 'ok' === $connection['status'];
	}

	/**
	 * Local settings with defaults.
	 *
	 * @return array<string, mixed>
	 */
	private static function settings() {
		$settings = get_option( Asapchat_Connect::OPTION_SETTINGS, array() );

		return wp_parse_args( is_array( $settings ) ? $settings : array(), Asapchat_Admin::default_settings() );
	}
}
