<?php
/**
 * Stand-in for the WooCommerce FeaturesUtil class that records compatibility declarations.
 *
 * @package asapchat
 */

namespace Automattic\WooCommerce\Utilities;

class FeaturesUtil {

	/**
	 * Arguments of every declare_compatibility() call.
	 *
	 * @var array<int, array{0: string, 1: string, 2: bool}>
	 */
	public static $declarations = array();

	public static function declare_compatibility( $feature_id, $plugin_file, $positive_compatibility = true ) {
		self::$declarations[] = array( $feature_id, $plugin_file, $positive_compatibility );
		return true;
	}
}
