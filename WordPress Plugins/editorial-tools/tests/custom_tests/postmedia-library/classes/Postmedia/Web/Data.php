<?php

/**
 * Determines which data source to use to fetch the data.
 */

namespace Postmedia\Web;

use Postmedia\Web\Data\DataHelper;
use Postmedia\Web\Data\StorageAdapter\WCM;
use Postmedia\Web\Data\StorageAdapter\WordPress;

class Data {

	/**
	 * @param  int    $id
	 * @param  string $source 'wordpress' or 'wcm'
	 * @return array
	 */
	public static function get_content( $id = null, $source = '' ) {
		// If ID hasn't been explicitly provided, figure out the ID.
		$id = empty( $id ) ? self::get_id() : $id;

		// Determine which data source to use.
		$source = self::get_source_object( $id, $source );

		// Get the data from the appropriate data source.
		return $source::get_content( $id );
	}

	public static function add_content( $wcm_post, $source = 'wcm' ) {

		// Determine which data source to use.
		$source = self::get_source_object( $wcm_post->origin_id, $source );

		// Get the data from the appropriate data source.
		return $source::add_content( $wcm_post );
	}

	public static function update_content( $wcm_post, $source = 'wcm' ) {
		// Determine which data source to use.
		$source = self::get_source_object( $wcm_post->origin_id, $source );

		return $source::update_content( $wcm_post );
	}

	public static function get_content_list( $id, $source = 'wcm' ) {
		if ( 'wcm' == $source ) {
			return ( new WCM() )->get_content_list( $id );
		}

		return ( new WordPress() )->get_content_list( $id );
	}

	public static function add_content_list( $wcm_zone, $source = 'wcm' ) {
		return ( new WCM() )->add_content_list( $wcm_zone );
	}

	public static function update_content_list( $wcm_zone, $source = 'wcm' ) {
		return ( new WCM() )->update_content_list( $wcm_zone );
	}

	public static function get_license( $id, $source = 'wcm' ) {
		return ( new WCM() )->get_license( $id );
	}

	public static function get_licenses( $source = 'wcm' ) {
		return ( new WCM() )->get_licenses();
	}

	/**
	 * Determines the ID.
	 *
	 * @return int|string|false
	 */
	private static function get_id() {
		// If the WCM ID is set in the URL, use the WCM ID.
		$wcm_id = get_query_var( 'wcm_id' );
		if ( ! empty( $wcm_id ) ) {
			return $wcm_id;
		}

		// Otherwise, use the WordPress ID.
		return get_the_ID();
	}

	/**
	 * Determines which data source to use.
	 *
	 * @param  int|string $id
	 * @param  string     $source 'wordpress' or 'wcm'
	 * @return StorageAdapter
	 */
	private static function get_source_object( $id, $source = '' ) {
		// The data source is known; get the requested data source class.
		if ( 'wordpress' == $source ) {
			return new WordPress();
		} else if ( 'wcm' == $source ) {
			return new WCM();
		}

		// The data source is not known; automatically determine the data source class.
		if ( get_post_status( $id ) ) {
			// There is a WordPress post with this ID; get the data from WordPress.
			return new WordPress();
		}

		// Otherwise, this must be a WCM ID; get the data from WCM.
		return new WCM();
	}
}
