<?php

namespace Postmedia\Web\Data\ContentElement;

use Postmedia\Web\Data\IContentElement;
use Postmedia\Web\Data\DataHelper;

class Audio implements IContentElement {
	/**
	 * Determines whether the given HTML or JSON is audio.
	 *
	 * @param  string|array $input
	 * @return boolean
	 */
	public static function is( $input ) {
		if ( is_array( $input ) ) {
			return 'audio' == $input['type'];
		}
		return self::is_audio_url( $input ) || self::get_audio_shortcode_url( $input );
	}

	/**
	 * @param  string  $html
	 * @return boolean
	 */
	private static function is_audio_url( $html ) {
		return preg_match( '/^http.+\.(mp3|ogg|wav)$/', $html ) ? true : false;
	}

	/**
	 * @param  string      $html
	 * @return string|null
	 */
	private static function get_audio_shortcode_url( $html ) {
		$has_shortcode = preg_match( '/^\[audio.* (?:mp3|src|ogg|wav)="([^"]+)".*\]$/', $html, $url );
		if ( $has_shortcode ) {
			return $url[1];
		}
		return null;
	}

	/**
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content_elements/audio.json
	 *
	 * @param  string $html
	 * @return array
	 */
	public static function html_to_json( $html ) {
		if ( self::is_audio_url( $html ) ) {
			$url = $html;
		} else {
			$url = self::get_audio_shortcode_url( $html );
		}

		$required = array(
			'type' => 'audio',
			'url'  => $url,
		);

		$optional = array(
			'_id'           => null,
			'mime_type'     => null, // TODO
			'title'         => null, // TODO
			'description'   => null, // TODO
			'shortcode_tag' => 'audio',
		);

		$order = array(
			'_id',
			'type',
			'mime_type',
			'url',
			'title',
			'description',
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
	 * @param  array  $json
	 * @return string
	 */
	public static function json_to_html( $json ) {
		return '[audio src="' . $json['url'] . '"]';
	}
}
