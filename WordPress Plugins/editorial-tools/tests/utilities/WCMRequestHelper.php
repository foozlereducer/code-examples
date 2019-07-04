<?php

class WCMRequestHelper {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'pre_http_request', array( $this, 'filter_pre_http_request' ), 10, 3 );
	}

	public function filter_pre_http_request( $preempt, $r, $url ) {
		// Ensure this is a request to the WCM API.
		$api_url = get_option( 'wcm_api_url' );
		if ( ! empty( $api_url ) && strpos( $url, $api_url ) === false ) {
			return $preempt;
		}

		// Send errors based on API key.
		$wcm_read_key = get_option( 'wcm_read_key' );
		if ( strpos( $wcm_read_key, '500' ) !== false ) {
			// Server error.
			return $this->return_response( 500, array( 'message' => 'Forbidden' ) );
		} elseif ( strpos( $wcm_read_key, '404' ) !== false ) {
			// Expired content.
			return $this->return_response( 404, array( 'statusCode' => 404, 'error' => 'Not Found' ) );
		} elseif ( strpos( $wcm_read_key, '403' ) !== false ) {
			// Bad/missing API key.
			return $this->return_response( 403, array( 'message' => 'Forbidden' ) );
		}

		// Build the URL.
		$url = str_replace( untrailingslashit( $api_url ), self::get_api_url(), $url );
		$url = wp_parse_url( $url );

		// Ensure the local file exists.
		$path = isset( $url['path'] ) ? $url['path'] : '';
		if ( empty( $path ) || ! file_exists( $path ) ) {
			return $this->return_response( 404 );
		}

		// Handle list.
		if ( is_dir( $path ) ) {
			if ( strpos( $wcm_read_key, '401' ) !== false ) {
				$data = array(
					'statusCode' => 401,
					'error' => 'Unauthorized',
					'message' => null,
				);
				return $this->return_response( 401, $data );
			}

			return $this->handle_index( $path );
		}

		// Create a fake response using the local data.
		$data = file_get_contents( $path );
		if ( empty( $data ) ) {
			return $this->return_response( 500 );
		} elseif ( strpos( $wcm_read_key, '401' ) !== false ) {
			$data = json_decode( $data, true );
			$data = array(
				'statusCode' => 401,
				'error' => 'Unauthorized',
				'message' => $data['origin_url'],
			);
			return $this->return_response( 401, $data );
		}

		return $this->return_response( 200, $data );
	}

	public static function get_api_url() {
		$dir = dirname( __FILE__ );
		return substr( $dir, 0, strrpos( $dir, '/' ) ) . '/data';
	}

	private function return_response( $code, $body = null ) {
		if ( is_array( $body ) ) {
			$body = wp_json_encode( $body );
		}

		return array(
			'response' => array(
				'code' => $code,
			),
			'body' => $body,
		);
	}

	private function handle_index( $path ) {
		$data = array();
		$filenames = scandir( $path );

		foreach ( $filenames as $filename ) {
			if ( ! in_array( $filename, array( '.', '..', '.DS_Store' ) ) && ! is_dir( $path . '/' . $filename ) ) {
				$data[] = file_get_contents( $path . '/' . $filename );
			}
		}

		return $this->return_response( 200, '[' . implode( ',', $data ) . ']' );
	}
}
