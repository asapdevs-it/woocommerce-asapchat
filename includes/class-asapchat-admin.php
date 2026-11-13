<?php
/**
 * The Settings > AsapChat screen.
 *
 * @package asapchat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the settings screen, loads its assets and prepares the data for the view.
 */
class Asapchat_Admin {

	const PAGE_SLUG = 'asapchat';

	const HOOK_SUFFIX = 'settings_page_asapchat';

	const PRIVACY_URL = 'https://asapchat.io/polityka-prywatnosci';

	const TERMS_URL = 'https://asapchat.io/regulamin';

	const STATE_DISCONNECTED = 'disconnected';

	const STATE_CONNECTED = 'connected';

	const STATE_ERROR = 'error';

	const ERROR_CODES = array( 401, 409, 410 );

	/**
	 * Hooks the screen into the admin.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_page' ) );
		// After WooCommerce registers its admin scripts (wc-enhanced-select).
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 20 );
	}

	/**
	 * Adds Settings > AsapChat.
	 *
	 * @return void
	 */
	public static function register_page() {
		add_options_page(
			__( 'AsapChat', 'asapchat' ),
			__( 'AsapChat', 'asapchat' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Loads the screen CSS, only on the plugin screen.
	 *
	 * @param string $hook_suffix Current admin page.
	 * @return void
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( self::HOOK_SUFFIX !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'asapchat-admin', ASAPCHAT_URL . 'admin/admin.css', array(), ASAPCHAT_VERSION );

		// The product picker in "Where to show" reuses the WooCommerce product search.
		if ( self::woocommerce_active() ) {
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
		}
	}

	/**
	 * Prints the screen. WordPress checks manage_options before calling it (add_options_page).
	 *
	 * @return void
	 */
	public static function render_page() {
		$asapchat_view = self::build_view();

		require ASAPCHAT_DIR . 'admin/settings-page.php';
	}

	/**
	 * Collects everything the view needs from the saved options.
	 *
	 * @return array<string, mixed>
	 */
	public static function build_view() {
		$connection = get_option( 'asapchat_connection', array() );
		$config     = get_option( 'asapchat_config', array() );
		$connection = is_array( $connection ) ? $connection : array();
		$config     = is_array( $config ) ? $config : array();
		$state      = self::screen_state( $connection, $config );
		$home_url   = home_url( '/' );

		$view = array(
			'state'          => $state,
			'notice'         => self::take_notice(),
			'home_url'       => $home_url,
			'logo_url'       => ASAPCHAT_URL . 'admin/img/asapchat-logo-white.svg',
			'admin_post_url' => admin_url( 'admin-post.php' ),
			'can_use_button' => self::connect_button_available( home_url(), admin_url() ),
			'privacy_url'    => self::PRIVACY_URL,
			'terms_url'      => self::TERMS_URL,
		);

		if ( self::STATE_ERROR === $state ) {
			$code               = isset( $connection['error_code'] ) ? (int) $connection['error_code'] : 401;
			$view['error_code'] = in_array( $code, self::ERROR_CODES, true ) ? $code : 401;
		}

		if ( self::STATE_CONNECTED === $state ) {
			$view = array_merge( $view, self::connected_view( $connection, $config, $home_url ) );
		}

		return $view;
	}

	/**
	 * Which of the three screens to show.
	 *
	 * @param array<string, mixed> $connection The asapchat_connection option.
	 * @param array<string, mixed> $config     The asapchat_config option.
	 * @return string One of the STATE_* constants.
	 */
	public static function screen_state( $connection, $config ) {
		$status = isset( $connection['status'] ) ? $connection['status'] : '';

		if ( 'error' === $status ) {
			return self::STATE_ERROR;
		}

		if ( 'ok' === $status && ! empty( $connection['token'] ) && ! empty( $config ) ) {
			return self::STATE_CONNECTED;
		}

		return self::STATE_DISCONNECTED;
	}

	/**
	 * Whether the "Connect to AsapChat" button can work on this site.
	 *
	 * AsapChat accepts a return address only over https (except localhost and 127.0.0.1)
	 * and only on the same host and port as the site, ignoring a leading "www.".
	 *
	 * @param string $home_url  Site address (home_url()).
	 * @param string $admin_url Admin address (admin_url()).
	 * @return bool
	 */
	public static function connect_button_available( $home_url, $admin_url ) {
		$home  = wp_parse_url( $home_url );
		$admin = wp_parse_url( $admin_url );

		if ( empty( $home['host'] ) || empty( $admin['host'] ) ) {
			return false;
		}

		$home_scheme = isset( $home['scheme'] ) ? strtolower( $home['scheme'] ) : '';
		$home_host   = strtolower( $home['host'] );
		$is_local    = in_array( $home_host, array( 'localhost', '127.0.0.1' ), true );

		if ( 'https' !== $home_scheme && ! $is_local ) {
			return false;
		}

		return self::strip_www( $home_host ) === self::strip_www( strtolower( $admin['host'] ) )
			&& self::port( $home ) === self::port( $admin );
	}

	/**
	 * Plan line under the shop name, e.g. "Free plan, 50 AI conversations per month".
	 *
	 * @param array<string, mixed> $shop The "shop" block of the config.
	 * @return string Empty when the account has no plan.
	 */
	public static function plan_label( $shop ) {
		$plan = isset( $shop['plan'] ) && is_string( $shop['plan'] ) ? $shop['plan'] : '';

		if ( '' === $plan ) {
			return '';
		}

		$names = array(
			'free' => 'Free',
			'pro'  => 'Pro',
		);
		$name  = isset( $names[ $plan ] ) ? $names[ $plan ] : ucfirst( $plan );

		if ( isset( $shop['ai_conversations_limit'] ) && is_int( $shop['ai_conversations_limit'] ) ) {
			/* translators: 1: plan name, 2: number of AI conversations per month. */
			return sprintf( __( '%1$s plan, %2$d AI conversations per month', 'asapchat' ), $name, $shop['ai_conversations_limit'] );
		}

		/* translators: %s: plan name. */
		return sprintf( __( '%s plan', 'asapchat' ), $name );
	}

	/**
	 * Formats an ISO 8601 date from AsapChat in the site time zone and format.
	 *
	 * @param mixed $value Date from the config, or null.
	 * @return string Empty when there is no valid date.
	 */
	public static function format_date( $value ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return '';
		}

		$timestamp = strtotime( $value );

		if ( false === $timestamp ) {
			return '';
		}

		return (string) wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
	}

	/**
	 * Default local settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function default_settings() {
		return array(
			'chat_enabled'       => true,
			'search_enabled'     => true,
			'search_add_to_cart' => true,
			'delay_seconds'      => 0,
			'excluded_ids'       => array(),
			'excluded_url_parts' => array(),
		);
	}

	/**
	 * Remembers a notice for the next screen view of the current user.
	 *
	 * @param string $code  Notice code, see notice_message().
	 * @param string $value Optional value placed in the text (e.g. the shop name).
	 * @return void
	 */
	public static function add_notice( $code, $value = '' ) {
		set_transient(
			'asapchat_notice_' . get_current_user_id(),
			array(
				'code'  => $code,
				'value' => $value,
			),
			5 * MINUTE_IN_SECONDS
		);
	}

	/**
	 * Text and type of a notice.
	 *
	 * @param string $code  Notice code.
	 * @param string $value Optional value for the text.
	 * @return array{type: string, text: string}|null Null for an unknown code.
	 */
	public static function notice_message( $code, $value = '' ) {
		switch ( $code ) {
			case 'connected':
				/* translators: %s: shop name. */
				return self::notice( 'success', sprintf( __( 'Connected to the shop %s. The chat is already on your site.', 'asapchat' ), $value ) );
			case 'cancelled':
				return self::notice( 'info', __( 'Connection cancelled. Nothing has been changed.', 'asapchat' ) );
			case 'state_expired':
				return self::notice( 'error', __( 'The connection session expired. Click Connect again.', 'asapchat' ) );
			case 'exchange_failed':
				return self::notice( 'error', __( 'Could not connect. Try again or use an install key.', 'asapchat' ) );
			case 'saved':
				return self::notice( 'success', __( 'Changes saved.', 'asapchat' ) );
			case 'look_not_saved':
				return self::notice( 'error', __( 'Settings in WordPress saved. The look could not be saved in AsapChat, try again in a moment.', 'asapchat' ) );
			case 'refresh_failed':
				return self::notice( 'info', __( 'Could not refresh the settings from AsapChat. The chat keeps working on the saved settings.', 'asapchat' ) );
			case 'disconnected':
				return self::notice( 'success', __( 'Disconnected. The chat and the search have disappeared from your site.', 'asapchat' ) );
			case 'update_plugin':
				return self::notice( 'info', __( 'Update the AsapChat plugin to get the new settings.', 'asapchat' ) );
			case 'key_format':
				return self::notice( 'error', __( 'This does not look like an install key.', 'asapchat' ) );
			case 'key_invalid':
				return self::notice( 'error', __( 'The key is invalid, revoked or expired. The key must be used within 24 hours of being generated.', 'asapchat' ) );
			case 'key_other_domain':
				return self::notice( 'error', __( 'This key is already assigned to another domain.', 'asapchat' ) );
			case 'key_failed':
				return self::notice( 'error', __( 'Could not connect. Try again in a moment.', 'asapchat' ) );
		}

		return null;
	}

	/**
	 * Whether WooCommerce is active.
	 *
	 * @return bool
	 */
	public static function woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Data for the "Connected" screen.
	 *
	 * @param array<string, mixed> $connection The asapchat_connection option.
	 * @param array<string, mixed> $config     The asapchat_config option.
	 * @param string               $home_url   Site address.
	 * @return array<string, mixed>
	 */
	private static function connected_view( $connection, $config, $home_url ) {
		$shop     = self::block( $config, 'shop' );
		$search   = self::block( $config, 'search' );
		$sources  = self::block( $config, 'sources' );
		$status   = self::block( $config, 'status' );
		$links    = self::block( $config, 'links' );
		$look     = self::block( $config, 'settings' );
		$settings = get_option( 'asapchat_settings', array() );
		$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), self::default_settings() );
		$excluded = array_map( 'absint', (array) $settings['excluded_ids'] );
		$is_woo   = self::woocommerce_active();

		$shop_name = isset( $shop['name'] ) ? (string) $shop['name'] : '';
		if ( '' === $shop_name && isset( $connection['shop_name'] ) ) {
			$shop_name = (string) $connection['shop_name'];
		}

		return array(
			'shop_name'        => $shop_name,
			'plan_label'       => self::plan_label( $shop ),
			'site_host'        => (string) wp_parse_url( $home_url, PHP_URL_HOST ),
			'panel_url'        => isset( $links['panel'] ) ? (string) $links['panel'] : '',
			'chat_seen'        => self::format_date( isset( $status['widget_seen_at'] ) ? $status['widget_seen_at'] : null ),
			'search_seen'      => self::format_date( isset( $status['search_seen_at'] ) ? $status['search_seen_at'] : null ),
			'search_available' => isset( $search['enabled'] ) && true === $search['enabled'],
			'connect_url'      => isset( $sources['connect_url'] ) ? (string) $sources['connect_url'] : '',
			'products_ok'      => ! empty( $sources['products_connected'] ),
			'orders_ok'        => ! empty( $sources['orders_connected'] ),
			'settings'         => $settings,
			'look'             => array(
				'bot_name'   => isset( $look['bot_name'] ) ? (string) $look['bot_name'] : '',
				'main_color' => isset( $look['main_color'] ) ? (string) $look['main_color'] : '',
				'position'   => isset( $look['position'] ) && 'left-bottom' === $look['position'] ? 'left-bottom' : 'right-bottom',
				'greeting_1' => isset( $look['greeting_1'] ) ? (string) $look['greeting_1'] : '',
				'greeting_2' => isset( $look['greeting_2'] ) ? (string) $look['greeting_2'] : '',
			),
			'pages'            => self::page_choices(),
			'products'         => $is_woo ? self::selected_products( $excluded ) : array(),
			'woocommerce'      => $is_woo,
			'wc_keys_url'      => admin_url( 'admin.php?page=wc-settings&tab=advanced&section=keys' ),
		);
	}

	/**
	 * All pages for the "Pages without widgets" list.
	 *
	 * @return array<int, string> Page ID => title.
	 */
	private static function page_choices() {
		$choices = array();

		foreach ( get_pages( array( 'post_status' => 'publish' ) ) as $page ) {
			$choices[ (int) $page->ID ] = '' !== $page->post_title ? $page->post_title : '#' . $page->ID;
		}

		return $choices;
	}

	/**
	 * Excluded products, for the preselected options of the product search.
	 *
	 * @param array<int, int> $ids Excluded post IDs (pages and products).
	 * @return array<int, string> Product ID => name.
	 */
	private static function selected_products( $ids ) {
		$products = array();

		foreach ( $ids as $id ) {
			if ( 'product' === get_post_type( $id ) ) {
				$products[ $id ] = get_the_title( $id );
			}
		}

		return $products;
	}

	/**
	 * Reads and removes the pending notice of the current user.
	 *
	 * @return array{type: string, text: string}|null
	 */
	private static function take_notice() {
		$key    = 'asapchat_notice_' . get_current_user_id();
		$notice = get_transient( $key );

		if ( false === $notice ) {
			return null;
		}

		delete_transient( $key );

		if ( ! is_array( $notice ) || empty( $notice['code'] ) ) {
			return null;
		}

		return self::notice_message( (string) $notice['code'], isset( $notice['value'] ) ? (string) $notice['value'] : '' );
	}

	/**
	 * Builds a notice array.
	 *
	 * @param string $type success, info or error.
	 * @param string $text Notice text.
	 * @return array{type: string, text: string}
	 */
	private static function notice( $type, $text ) {
		return array(
			'type' => $type,
			'text' => $text,
		);
	}

	/**
	 * One block of the config as an array.
	 *
	 * @param array<string, mixed> $config Config.
	 * @param string               $key    Block name.
	 * @return array<string, mixed>
	 */
	private static function block( $config, $key ) {
		return isset( $config[ $key ] ) && is_array( $config[ $key ] ) ? $config[ $key ] : array();
	}

	/**
	 * Host without a leading "www.".
	 *
	 * @param string $host Host name.
	 * @return string
	 */
	private static function strip_www( $host ) {
		return 0 === strpos( $host, 'www.' ) ? substr( $host, 4 ) : $host;
	}

	/**
	 * Port of a parsed URL, with the scheme default when it is missing.
	 *
	 * @param array<string, mixed> $parts Result of wp_parse_url().
	 * @return int
	 */
	private static function port( $parts ) {
		if ( isset( $parts['port'] ) ) {
			return (int) $parts['port'];
		}

		return isset( $parts['scheme'] ) && 'https' === strtolower( $parts['scheme'] ) ? 443 : 80;
	}
}
