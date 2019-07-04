<?php

/**
 * Converts data for a single content element to and from various formats.
 */

namespace Postmedia\Web\Data;

interface IContentElement {
	/**
	 * Determines whether the given HTML or JSON is this content element.
	 *
	 * @param  string|array $input
	 * @return boolean
	 */
	public static function is( $input );

	/**
	 * Converts HTML content element to JSON.
	 *
	 * @param  string $html
	 * @return array
	 */
	public static function html_to_json( $html );

	/**
	 * Converts JSON content element to HTML.
	 *
	 * @param  array  $json
	 * @return string
	 */
	public static function json_to_html( $json );
}
