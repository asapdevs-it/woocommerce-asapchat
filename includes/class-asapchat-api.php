<?php
/**
 * HTTP client for the AsapChat plugin API.
 *
 * @package asapchat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Talks to AsapChat: code exchange for a token, config, look settings and disconnect.
 *
 * Contract: docs/CONNECT-API.md, points 2 and 4. Every method returns
 * array( 'status' => int, 'data' => array|null ). Status 0 means no HTTP response
 * (network error, timeout). The caller decides what a status means (spec point 10).
 */
class Asapchat_Api {

	const PATH = '/api/plugin/v1';

	const TIMEOUT = 5;

	const NO_RESPONSE = 0;

	/**
	 * POST /token: exchanges the one-time code for the connection token.
	 *
	 * Sent without Authorization and X-Site-Url, there is no token yet.
	 *
	 * @param string $code          Code from the return address.
	 * @param string $code_verifier PKCE secret saved before the redirect.
	 * @return array{status: int, data: array<string, mixed>|null}
	 */
	public static function exchange_code( $code, $code_verifier ) {
		return self::request(
			'POST',
			'/token',
			null,
			array(
				'code'          => $code,
				'code_verifier' => $code_verifier,
			)
		);
	}

	/**
	 * GET /config.
	 *
	 * @param string $token Connection token or install key.
	 * @return array{status: int, data: array<string, mixed>|null}
	 */
	public static function get_config( $token ) {
		return self::request( 'GET', '/config', $token );
	}

	/**
	 * PATCH /settings with the changed look fields only. Answers with the full config.
	 *
	 * @param string               $token   Connection token.
	 * @param array<string, mixed> $changes Changed fields (bot_name, main_color, position, greeting_1, greeting_2).
	 * @return array{status: int, data: array<string, mixed>|null}
	 */
	public static function update_settings( $token, $changes ) {
		return self::request( 'PATCH', '/settings', $token, $changes );
	}

	/**
	 * DELETE /connection: revokes the token in AsapChat.
	 *
	 * @param string $token Connection token.
	 * @return array{status: int, data: array<string, mixed>|null}
	 */
	public static function disconnect( $token ) {
		return self::request( 'DELETE', '/connection', $token );
	}

	/**
	 * Sends one request.
	 *
	 * @param string                    $method HTTP method.
	 * @param string                    $path   Path after /api/plugin/v1.
	 * @param string|null               $token  Connection token, null for POST /token.
	 * @param array<string, mixed>|null $body   JSON body.
	 * @return array{status: int, data: array<string, mixed>|null}
	 */
	private static function request( $method, $path, $token = null, $body = null ) {
		$headers = array( 'Accept' => 'application/json' );

		if ( null !== $token ) {
			$headers['Authorization'] = 'Bearer ' . $token;
			$headers['X-Site-Url']    = home_url();
		}

		$args = array(
			'method'  => $method,
			'timeout' => self::TIMEOUT,
			'headers' => $headers,
		);

		if ( null !== $body ) {
			$args['headers']['Content-Type'] = 'application/json';
			$args['body']                    = wp_json_encode( $body );
		}

		$response = wp_remote_request( ASAPCHAT_BASE . self::PATH . $path, $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'status' => self::NO_RESPONSE,
				'data'   => null,
			);
		}

		$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );

		return array(
			'status' => (int) wp_remote_retrieve_response_code( $response ),
			'data'   => is_array( $data ) ? $data : null,
		);
	}
}
