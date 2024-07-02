<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              asapchat.io
 * @since             1.0.0
 * @package           Woocommerce_asapchat
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce asapchat
 * Plugin URI:        asapchat.io
 * Description:       asapchat
 * Version:           1.0.0
 * Author:            asapchat
 * Author URI:        asapchat.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-asapchat
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOOCOMMERCE_asapchat_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-asapchat-activator.php
 */
function activate_woocommerce_asapchat() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-asapchat-activator.php';
	Woocommerce_asapchat_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-asapchat-deactivator.php
 */
function deactivate_woocommerce_asapchat() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-asapchat-deactivator.php';
	Woocommerce_asapchat_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woocommerce_asapchat' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_asapchat' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-asapchat.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woocommerce_asapchat() {

	$plugin = new Woocommerce_asapchat();
	$plugin->run();

}
run_woocommerce_asapchat();