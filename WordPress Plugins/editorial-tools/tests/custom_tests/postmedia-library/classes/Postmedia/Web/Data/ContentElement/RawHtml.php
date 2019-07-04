<?php

namespace Postmedia\Web\Data\ContentElement;

use Postmedia\Web\Data\IContentElement;
use Postmedia\Web\Data\DataHelper;

class RawHtml implements IContentElement {
	/**
	 * Determines whether the given HTML or JSON is a raw HTML.
	 *
	 * @param  string|array $input
	 * @return boolean
	 */
	public static function is( $input ) {
		if ( is_array( $input ) ) {
			return 'raw_html' == $input['type'];
		}
		return preg_match( '/^<[^>]*(><[^>]*)*>$/', $input ) ? true : false;
	}

	/**
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content_elements/raw_html.json
	 *
	 * @param  string $html
	 * @return array
	 */
	public static function html_to_json( $html ) {
		$required = array(
			'type'    => 'raw_html',
			'content' => $html,
		);

		$optional = array(
			'_id' => null,
		);

		$order = array(
			'_id',
			'type',
			'content',
		);

		return DataHelper::merge_fields( $required, $optional, $order );
	}

	/**
	 * @param  array  $json
	 * @return string
	 */
	public static function json_to_html( $json ) {
		return $json['content'];
	}
}
