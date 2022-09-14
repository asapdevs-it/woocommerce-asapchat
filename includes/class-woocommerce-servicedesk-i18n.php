<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       servicedesk20.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Servicedesk
 * @subpackage Woocommerce_Servicedesk/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woocommerce_Servicedesk
 * @subpackage Woocommerce_Servicedesk/includes
 * @author     Service Desk 2.0 <kontakt@servicedesk20.com>
 */
class Woocommerce_Servicedesk_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woocommerce-servicedesk',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
