<?php

namespace Postmedia\Web\Data\ContentElement;

use Postmedia\Web\Data\IContentElement;
use Postmedia\Web\Data\DataHelper;

class Gallery implements IContentElement {
	/**
	 * Determines whether the given HTML or JSON is a gallery.
	 *
	 * @param  string|array $input
	 * @return boolean
	 */
	public static function is( $input ) {
		if ( is_array( $input ) ) {
			return 'gallery' == $input['type'];
		}
		return preg_match( '/^\[(snapgallery|pngallery).*\]$/', $input ) ? true : false;
	}

	/**
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content_elements/gallery.json
	 *
	 * @param  string $html
	 * @return array
	 */
	public static function html_to_json( $html ) {
		$id = self::get_gallery_id( $html );

		$required = array(
			'gallery_id' => $id,
		);

		$optional = array(
			'_id'           => null,
			'type'          => 'gallery',
			'shortcode_tag' => 'snapgallery',
		);

		$order = array(
			'_id',
			'gallery_id',
			'type',
			'shortcode_tag',
		);

		$output = DataHelper::merge_fields( $required, $optional, $order );

		$shortcode = self::json_to_html( $output );
		if ( ! empty( $shortcode ) ) {
			$output['shortcode'] = $shortcode;
		}

		return $output;
	}

	/**
	 * @param  string   $html
	 * @return int|null
	 */
	private static function get_gallery_id( $html ) {
		$has_id = preg_match( '/^\[(?:snapgallery|pngallery).* id="([^"]+)".*\]$/', $html, $id );
		if ( $has_id ) {
			return $id[1];
		}
		return null;
	}

	/**
	 * @param  array  $json
	 * @return string
	 */
	public static function json_to_html( $json ) {
		return '[snapgallery id="' . $json['gallery_id'] . '"]';
	}
}
