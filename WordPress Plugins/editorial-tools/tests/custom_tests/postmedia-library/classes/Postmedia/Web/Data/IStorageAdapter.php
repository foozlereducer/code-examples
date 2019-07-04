<?php

namespace Postmedia\Web\Data;

/**
 * Storage adapter interface declarations
 */
interface IStorageAdapter {

	/**
	 * Returns content data in a standardized format.
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content.json
	 * @param  string $id
	 * @return array
	 */
	public static function get_content( $id );

	/**
	 * Add content
	 * @param string $json
	 * @return ???
	 */
	public static function add_content( $wcm_post );

	/**
	 * Update content
	 * @param  string $id
	 * @param  string $json
	 * @return ???
	 */
	public static function update_content( $wcm_post );

	/**
	 * Get content list
	 * @param  string $id
	 * @param  string $source
	 * @return array
	 */
	public static function get_content_list( $id );

	/**
	 * Add content list
	 * @param string $json
	 */
	public static function add_content_list( $wcm_zone );

	/**
	 * Update content list
	 * @param  string $json
	 * @return ???
	 */
	public static function update_content_list( $wcm_zone );


	/**
	 * TODO - Move to use 'get_content_list'
	 * @param  [type] $qs       [description]
	 * @param  [type] $expand   [description]
	 * @param  [type] $maxposts [description]
	 * @return [type]           [description]
	 */
	public static function get_list_data( $qs, $expand, $maxposts );

	/**
	 * Get License
	 * @param  string $id
	 * @param  string $source
	 * @return array
	 */
	public static function get_license( $id );

	/**
	 * Get get_licenses
	 * @return array
	 */
	public static function get_licenses();

}
