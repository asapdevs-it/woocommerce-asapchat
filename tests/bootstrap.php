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
