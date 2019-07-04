<?php

namespace Postmedia\Web\Data\ContentElement;

use Postmedia\Web\Data\IContentElement;
use Postmedia\Web\Data\DataHelper;

class Pointer implements IContentElement {
	/**
	 * Determines whether the given HTML or JSON is a pointer.
	 *
	 * TODO
	 *
	 * @param  string|array $input
	 * @return boolean
	 */
	public static function is( $input ) {
		if ( is_array( $input ) ) {
			return 'pointer' == $input['type'];
		}
		return false;
	}

	/**
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content_elements/pointer.json
	 *
	 * @param  string $html
	 * @return array
	 */
	public static function html_to_json( $html ) {
		$required = array(
			'url' => null, // TODO
		);

		$optional = array(
			'_id'  => null,
			'type' => 'pointer',
		);

		$order = array(
			'_id',
			'type',
			'url',
		);

		return DataHelper::merge_fields( $required, $optional, $order );
	}

	/**
	 * TODO
	 *
	 * @param  array  $json
	 * @return string
	 */
	public static function json_to_html( $json ) {
		return 'pointer goes here';
	}
}
