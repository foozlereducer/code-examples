<?php

namespace Postmedia\Web\Data\ContentElement;

use Postmedia\Web\Data\IContentElement;
use Postmedia\Web\Data\DataHelper;

class Blockquote implements IContentElement {
	/**
	 * Determines whether the given HTML or JSON is a blockquote.
	 *
	 * @param  string|array $input
	 * @return boolean
	 */
	public static function is( $input ) {
		if ( is_array( $input ) ) {
			return 'blockquote' == $input['type'];
		}
		return preg_match( '/^<blockquote.*<\/blockquote>$/', $input ) ? true : false;
	}

	/**
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content_elements/blockquote.json
	 *
	 * @param  string $html
	 * @return array
	 */
	public static function html_to_json( $html ) {
		$content = self::get_blockquote_content( $html );

		$required = array(
			'type'    => 'blockquote',
			'content' => $content,
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
	 * @param  string      $html
	 * @return string|null
	 */
	private static function get_blockquote_content( $html ) {
		$has_content = preg_match( '/^<blockquote[^>]*>(.*)<\/blockquote>$/', $html, $content );
		if ( $has_content ) {
			return $content[1];
		}
		return null;
	}

	/**
	 * @param  array  $json
	 * @return string
	 */
	public static function json_to_html( $json ) {
		return '<blockquote>' . esc_html( $json['content'] ) . '</blockquote>';
	}
}
