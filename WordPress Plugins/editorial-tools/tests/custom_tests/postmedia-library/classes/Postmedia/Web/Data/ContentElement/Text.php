<?php

namespace Postmedia\Web\Data\ContentElement;

use Postmedia\Web\Data\IContentElement;
use Postmedia\Web\Data\DataHelper;

class Text implements IContentElement {
	/**
	 * Determines whether the given HTML or JSON is text.
	 *
	 * @param  string|array $input
	 * @return boolean
	 */
	public static function is( $input ) {
		if ( is_array( $input ) ) {
			return 'text' == $input['type'];
		}

		$regex_pattern = get_shortcode_regex();
		if ( preg_match( '/' . $regex_pattern . '/s', $input ) ) {
			// This is a shortcode.
			return false;
		}

		if ( preg_match( '/^<[^>]*(><[^>]*)*>$/', $input ) ) {
			// This contains only HTML.
			return false;
		}

		return true;
	}

	/**
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content_elements/text.json
	 *
	 * @param  string $html
	 * @return array
	 */
	public static function html_to_json( $html ) {
		$required = array(
			'type'    => 'text',
			'content' => $html,
		);

		$optional = array(
			'_id'       => null,
			'paragraph' => self::get_paragraph( $html ),
		);

		$order = array(
			'_id',
			'type',
			'content',
			'paragraph',
		);

		return DataHelper::merge_fields( $required, $optional, $order );
	}

	/**
	 * @param  string $html
	 * @return string
	 */
	private static function get_paragraph( $html ) {
		$block_level_elements = DataHelper::get_block_level_elements();
		$block_level_elements = implode( '|', $block_level_elements );
		if ( preg_match( '/<(' . $block_level_elements . ')(\s|>)/', $html ) ) {
			return 'none';
		}
		return 'wrap';
	}

	/**
	 * @param  array  $json
	 * @return string
	 */
	public static function json_to_html( $json ) {
		return $json['content'];
	}
}
