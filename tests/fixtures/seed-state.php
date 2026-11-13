<?php
/**
 * Development helper: puts the plugin screen into a given state with the sample data from
 * docs/CONNECT-API.md (points 4 and 7a). Writes the plugin options only. Not part of the package.
 *
 * Usage:
 *   npx @wordpress/env run cli wp eval-file wp-content/plugins/asapchat/tests/fixtures/seed-state.php <state>
 *
 * States:
 *   disconnected       not connected
 *   disconnected-http  not connected, site address over http outside localhost (button disabled);
 *                      sets the WP_HOME constant in wp-config.php (wp-env defines it), any other
 *                      state restores it. The storefront does not work until then.
 *   connected          connected, contract point 4: search unavailable, widgets not seen yet
 *   connected-search   connected, contract point 7a: search enabled, chat seen, search not seen yet
 *   error-401          connection revoked in the AsapChat panel
 *   error-409          connection assigned to another domain
 *   error-410          shop deleted in AsapChat
 *
 * @package asapchat
 */

defined( 'ABSPATH' ) || exit;

$asapchat_seed_state  = isset( $args[0] ) ? (string) $args[0] : '';
$asapchat_seed_states = array( 'disconnected', 'disconnected-http', 'connected', 'connected-search', 'error-401', 'error-409', 'error-410' );

if ( ! in_array( $asapchat_seed_state, $asapchat_seed_states, true ) ) {
	WP_CLI::error( 'Pass one of: ' . implode( ', ', $asapchat_seed_states ) );
}

// Restore the site address changed by "disconnected-http".
$asapchat_seed_home = get_option( 'asapchat_seed_home' );
if ( false !== $asapchat_seed_home ) {
	WP_CLI::runcommand( 'config set WP_HOME ' . escapeshellarg( $asapchat_seed_home ) . ' --type=constant --quiet' );
	delete_option( 'asapchat_seed_home' );
}

$asapchat_seed_config = function ( $file ) {
	$json = file_get_contents( __DIR__ . '/' . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	return json_decode( $json, true );
};

$asapchat_seed_notice = function ( $code, $value ) {
	foreach ( get_users( array( 'role' => 'administrator', 'fields' => 'ID' ) ) as $user_id ) {
		set_transient(
			'asapchat_notice_' . $user_id,
			array(
				'code'  => $code,
				'value' => $value,
			),
			5 * MINUTE_IN_SECONDS
		);
	}
};

if ( false === get_option( 'asapchat_settings' ) ) {
	update_option( 'asapchat_settings', Asapchat_Admin::default_settings(), true );
}

switch ( $asapchat_seed_state ) {
	case 'disconnected':
	case 'disconnected-http':
		delete_option( 'asapchat_connection' );
		delete_option( 'asapchat_config' );

		if ( 'disconnected-http' === $asapchat_seed_state ) {
			update_option( 'asapchat_seed_home', home_url(), false );
			WP_CLI::runcommand( 'config set WP_HOME http://shop.example --type=constant --quiet' );
		}
		break;

	case 'connected':
	case 'connected-search':
		$config = $asapchat_seed_config( 'connected' === $asapchat_seed_state ? 'config.json' : 'config-search.json' );

		update_option(
			'asapchat_connection',
			array(
				'token'        => 'ack_' . str_repeat( '0', 48 ),
				'shop_id'      => $config['shop']['id'],
				'shop_name'    => $config['shop']['name'],
				'connected_at' => time(),
				'status'       => 'ok',
			),
			false
		);
		update_option( 'asapchat_config', array_merge( $config, array( 'fetched_at' => time() ) ), true );
		$asapchat_seed_notice( 'connected', $config['shop']['name'] );
		break;

	default:
		update_option(
			'asapchat_connection',
			array(
				'shop_name'  => 'Kwiatowy',
				'status'     => 'error',
				'error_code' => (int) substr( $asapchat_seed_state, -3 ),
			),
			false
		);
		delete_option( 'asapchat_config' );
}

WP_CLI::success( 'AsapChat screen state: ' . $asapchat_seed_state );
