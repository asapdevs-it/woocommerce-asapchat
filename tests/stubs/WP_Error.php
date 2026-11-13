<?php
/**
 * Minimal stand-in for WP_Error, enough for is_wp_error() checks.
 *
 * @package asapchat
 */

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {

		/**
		 * Error code.
		 *
		 * @var string
		 */
		public $code;

		public function __construct( $code = '' ) {
			$this->code = $code;
		}
	}
}
