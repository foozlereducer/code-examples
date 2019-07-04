<?php

/**
 * Fetches data from WCM and returns it in WCM format.
 */

namespace Postmedia\Web\Data\StorageAdapter;

use Postmedia\Web\Data\IStorageAdapter;
use Postmedia\Web\License;

class WCM implements IStorageAdapter {
	/**
	 * Parses WCM content data and returns it in WCM format.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content.json
	 *
	 * @param  int        $id
	 * @return array|null
	 */
	public static function get_content( $id ) {
		$url = get_option( 'wcm_api_url' );
		$url = sprintf( '%s/%s/%s', untrailingslashit( $url ), 'content', $id );
		$args = array(
			'method'      => 'GET',
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
				'x-api-key' => get_option( 'wcm_read_key' ),
			),
		);
		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			error_log( print_r( $response, 1 ) );
			return null;
		}

		$code = isset( $response['response']['code'] ) ? $response['response']['code'] : null;
		if ( '200' != $code ) {
			error_log( print_r( $response, 1 ) );
			return null;
		}

		$data = json_decode( $response['body'], true );

		return self::format_content( $data );
	}

	private static function format_content( $data ) {
		// Ensure authors have slugs so get_coauthors() get_coauthor_by( 'user_nicename' ) works.
		if ( isset( $data['credits']['authors'] ) ) {
			foreach ( $data['credits']['authors'] as $key => $author ) {
				if ( isset( $data['credits']['authors'][ $key ]['slug'] ) && ! empty( $data['credits']['authors'][ $key ]['slug'] ) ) {
					continue;
				}
				if ( ! isset( $author['name'] ) ) {
					continue;
				}
				$data['credits']['authors'][ $key ]['slug'] = sanitize_title( $author['name'] );
			}
		}

		// TODO
		$data['commenting_enabled'] = true;

		return $data;
	}

	public static function add_content( $wcm_post ) {
		$api_url = sprintf( '%s/%s/', get_option( 'wcm_api_url' ), 'content' );
		$write_key = get_option( 'wcm_write_key' );
		$args = array(
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(
				'Content-Type' => 'application/json',
				'x-api-key' => $write_key,
			),
			'body' => wp_json_encode( $wcm_post, JSON_UNESCAPED_SLASHES ),
		);
		$response = wp_remote_post( $api_url, $args );
		return $response;
	}

	public static function update_content( $wcm_post ) {
		$api_url = sprintf( '%s/%s/%s', get_option( 'wcm_api_url' ), 'content', $wcm_post->_id );
		unset( $wcm_post->_id );
		$write_key = get_option( 'wcm_write_key' );
		$args = array(
			'method' => 'PUT',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(
				'Content-Type' => 'application/json',
				'x-api-key' => $write_key,
			),
			'body' => wp_json_encode( $wcm_post, JSON_UNESCAPED_SLASHES ),
		);
		$response = wp_remote_request( $api_url, $args );
		return $response;
	}

	public static function get_content_list( $id ) {
		return null;
	}

	public static function add_content_list( $wcm_zone ) {
		$api_url = sprintf( '%s/%s/', get_option( 'wcm_api_url' ), 'lists' );
		$write_key = get_option( 'wcm_write_key' );
		$args = array(
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(
				'Content-Type' => 'application/json',
				'x-api-key' => $write_key,
			),
			'body' => wp_json_encode( $wcm_zone, JSON_UNESCAPED_SLASHES ),
		);
		$response = wp_remote_post( $api_url, $args );
		return $response;
	}

	public static function update_content_list( $wcm_zone ) {
		$api_url = sprintf( '%s/%s/%s', get_option( 'wcm_api_url' ), 'lists', $wcm_zone->_id );
		unset( $wcm_zone->_id );
		$write_key = get_option( 'wcm_write_key' );
		$args = array(
			'method' => 'PUT',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(
				'Content-Type' => 'application/json',
				'x-api-key' => $write_key,
			),
			'body' => wp_json_encode( $wcm_zone, JSON_UNESCAPED_SLASHES ),
		);
		$response = wp_remote_request( $api_url, $args );
		return $response;
	}

	/*
	* Get a list of posts from the WCM
	* @param $qs (string) Additional filter parameters preformatted into a valid query string fragment
	* @param $expand (boolean) Whether to request full (expanded) posts or partial ones
	* @param $maxposts (integer) The number of posts to request
	* @param $from (integer) The post to start at
	*
	* @return (array) List of elements
	*/
	public static function get_list_data( $qs = '', $expand = false, $maxposts = 20, $from = 0 ) {
		$api_url = get_option( 'wcm_api_url' ) . '/content/';
		$api_url .= '?expand_content=' . ( true === $expand ) ? 'true' : 'false';
		if ( 0 < intval( $maxposts ) ) {
			$api_url .= '&size=' . intval( $maxposts );
		}
		if ( 0 < intval( $from ) ) {
			$api_url .= '&from=' . intval( $from );
		}
		$api_url .= $qs;
		$read_key = get_option( 'wcm_read_key' );
		$args = array(
			'method' => 'GET',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(
				'x-api-key' => $read_key,
			),
			'body' => $json,
		);
		$response = wp_remote_get( $api_url, $args );
		$data = json_decode( $response, false );
		return $data;
	}

	public static function get_license( $id ) {
		$cache_key = '_license_' . $id;
		$expiration = 300;
		$data = get_transient( $cache_key );

		if ( false === $data ) {
			$api_url = get_option( 'wcm_api_url' );
			$url = sprintf( '%s/licenses/%s', $api_url, $id );
			$read_key = get_option( 'wcm_read_key' );
			$args = array(
				'method' => 'GET',
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(
					'x-api-key' => $read_key,
				),
			);
			$response = wp_remote_get( $url, $args );
			$data = $response['body'];
			set_transient( $cache_key, wp_json_encode( $data ), $expiration );
		}
		return json_decode( $data, true );
	}

	public static function get_licenses() {
		$api_url = get_option( 'wcm_api_url' );
		$url = sprintf( '%s/licenses/', $api_url );
		$read_key = get_option( 'wcm_read_key' );

		$expiration = 300;
		$cache_key_list = '_licenses_' . md5( $url . '_' . $read_key );

		$license_list = get_transient( $cache_key_list );

		if( false === $license_list ) {
			$args = array(
				'method' => 'GET',
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(
					'x-api-key' => $read_key,
				),
			);
			$response = wp_remote_get( $url, $args );
			set_transient( $cache_key_list, $response['body'], $expiration );
			$data = json_decode( $response['body'], true );
			foreach( $data as $item ) {
				$cache_key_single = '_license_' . $item['_id'];
				set_transient( $cache_key_single, wp_json_encode( $item ), $expiration );
			}
		} else {
			$data = json_decode( $license_list, true );
		}
		$licenses = array();
		foreach( $data as $item ) {
			$licenses[] = new License( $item['_id'], 'wcm' );
		}
		return $licenses;
	}
}
