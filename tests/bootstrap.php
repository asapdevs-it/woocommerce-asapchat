<?php
/**
 * PHPUnit bootstrap: unit tests run without WordPress, WordPress functions are mocked with Brain Monkey.
 *
 * @package asapchat
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', sys_get_temp_dir() . '/wordpress/' );
}

if ( ! defined( 'ASAPCHAT_TEST_ROOT' ) ) {
	define( 'ASAPCHAT_TEST_ROOT', dirname( __DIR__ ) );
}

// Data providers run before set_up_before_class(), so the WP_Error stand-in is loaded here.
require_once __DIR__ . '/stubs/WP_Error.php';

// WordPress time constants used by the plugin.
if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
	define( 'HOUR_IN_SECONDS', 3600 );
}
