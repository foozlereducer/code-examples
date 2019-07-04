<?php

namespace Postmedia\Web\Data\ContentElement;

use Postmedia\Web\Data\IContentElement;
use Postmedia\Web\Data\DataHelper;

class Image implements IContentElement {
	/**
	 * Determines whether the given HTML or JSON is an image.
	 *
	 * @param  string|array $input
	 * @return boolean
	 */
	public static function is( $input ) {
		if ( is_array( $input ) ) {
			return 'image' == $input['type'];
		}
		return preg_match( '/^(\[caption.*\])?<img.*>(.*\[\/caption\])?$/', $input ) ? true : false;
	}

	/**
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content_elements/image.json
	 *
	 * @param  string $html
	 * @return array
	 */
	public static function html_to_json( $html ) {
		$url = self::get_url( $html );
		$title = self::get_title( $html );
		$caption = self::get_caption( $html );
		$width = self::get_attribute( $html, 'width' );
		$height = self::get_attribute( $html, 'height' );

		$required = array(
			'url' => $url,
		);

		$optional = array(
			'_id'           => null,
			'type'          => 'image',
			'origin_id'     => null, // TODO
			'mime_type'     => null, // TODO
			'created_on'    => null, // TODO
			'title'         => $title,
			'caption'       => $caption,
			'description'   => null, // TODO
			'credit'        => null, // TODO
			'distributor'   => null, // TODO
			'width'         => ! empty( $width ) ? (int) $width : null,
			'height'        => ! empty( $height ) ? (int) $height : null,
			'shortcode_tag' => 'caption',
		);

		$order = array(
			'_id',
			'type',
			'origin_id',
			'mime_type',
			'created_on',
			'url',
			'title',
			'caption',
			'description',
			'credit',
			'distributor',
			'width',
			'height',
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
	 * @param  string      $html
	 * @return string|null
	 */
	private static function get_url( $html ) {
		$has_url = preg_match( '/<img [^>]*src="([^"]+)"/', $html, $url );
		if ( $has_url ) {
			return $url[1];
		}

		return null;
	}

	/**
	 * @param  string      $html
	 * @return string|null
	 */
	private static function get_title( $html ) {
		$has_title = preg_match( '/<img [^>]*alt="([^"]+)"/', $html, $title );
		if ( $has_title ) {
			return $title[1];
		}

		return null;
	}

	/**
	 * @param  string      $html
	 * @return string|null
	 */
	private static function get_caption( $html ) {
		$has_caption = preg_match( '/> (.*)\[\/caption\]/', $html, $caption );
		if ( $has_caption ) {
			return $caption[1];
		}

		return null;
	}

	/**
	 * @param  string      $html
	 * @param  string      $attribute
	 * @return string|null
	 */
	private static function get_attribute( $html, $attribute ) {
		$has_attribute = preg_match( '/ ' . $attribute . '="([^"]+)"/', $html, $matches );
		if ( $has_attribute ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * @param  array  $json
	 * @return string
	 */
	public static function json_to_html( $json ) {
		$attributes = array(
			'class'  => array(),
			'width'  => isset( $json['width'] ) ? $json['width'] : null,
			'height' => isset( $json['height'] ) ? $json['height'] : null,
			'src'    => $json['url'],
			'alt'    => isset( $json['title'] ) ? $json['title'] : null,
		);
		$attributes['class'] = implode( ' ', $attributes['class'] );
		$attributes = array_filter( $attributes );

		$img = '<img';
		foreach ( $attributes as $key => $value ) {
			$img .= ' ' . $key . '="' . esc_attr( $value ) . '"';
		}
		$img .= '>';

		if ( isset( $json['caption'] ) && ! empty( $json['caption'] ) ) {
			return '[caption]' . $img . ' ' . $json['caption'] . '[/caption]';
		}

		return $img;
	}
}
