<?php
/**
 * Plugin Name:       AsapChat: AI Chat and Product Search
 * Plugin URI:        https://asapchat.io
 * Description:       Connect your site to AsapChat in one click: AI chat that answers customers and a fast product search for your store.
 * Version:           1.0.0
 * Requires at least: 6.9
 * Requires PHP:      7.4
 * Author:            AsapCommerce
 * Author URI:        https://asapchat.io
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       asapchat
 * Domain Path:       /languages
 * WC tested up to:   11.3
 *
 * @package asapchat
 */

defined( 'ABSPATH' ) || exit;

define( 'ASAPCHAT_VERSION', '1.0.0' );
define( 'ASAPCHAT_FILE', __FILE__ );
define( 'ASAPCHAT_DIR', plugin_dir_path( __FILE__ ) );
define( 'ASAPCHAT_URL', plugin_dir_url( __FILE__ ) );

// AsapChat address. Override it in wp-config.php, e.g. to point automated tests at a mock server.
if ( ! defined( 'ASAPCHAT_BASE' ) ) {
	define( 'ASAPCHAT_BASE', 'https://panel.asapchat.io' );
}

require_once ASAPCHAT_DIR . 'includes/class-asapchat-api.php';
require_once ASAPCHAT_DIR . 'includes/class-asapchat-connect.php';
require_once ASAPCHAT_DIR . 'includes/class-asapchat-admin.php';
require_once ASAPCHAT_DIR . 'includes/class-asapchat-frontend.php';

register_activation_hook( __FILE__, 'asapchat_activate' );
add_action( 'before_woocommerce_init', 'asapchat_declare_wc_compatibility' );

if ( is_admin() ) {
	Asapchat_Admin::init();
}

/**
 * Runs on plugin activation.
 *
 * Activation never contacts AsapChat: nothing is sent before the user clicks
 * "Connect to AsapChat" or submits an install key (WordPress.org guideline).
 *
 * @return void
 */
function asapchat_activate() {
}

/**
 * Declares compatibility with WooCommerce High-Performance Order Storage.
 *
 * The plugin never reads or writes orders. WooCommerce is optional, so the
 * declaration is skipped when it is not active.
 *
 * @return void
 */
function asapchat_declare_wc_compatibility() {
	if ( class_exists( Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', ASAPCHAT_FILE, true );
	}
}
