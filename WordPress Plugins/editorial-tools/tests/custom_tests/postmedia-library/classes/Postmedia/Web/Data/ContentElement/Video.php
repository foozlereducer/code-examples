<?php

namespace Postmedia\Web\Data\ContentElement;

use Postmedia\Web\Data\IContentElement;
use Postmedia\Web\Data\DataHelper;

class Video implements IContentElement {
	/**
	 * Determines whether the given HTML or JSON is a video.
	 *
	 * @param  string|array $input
	 * @return boolean
	 */
	public static function is( $input ) {
		if ( is_array( $input ) ) {
			return 'video' == $input['type'];
		}
		return self::is_video_url( $input ) || self::get_video_shortcode_url( $input );
	}

	/**
	 * @param  string  $html
	 * @return boolean
	 */
	private static function is_video_url( $html ) {
		return preg_match( '/^http.+\.(flv|m4v|mp4|ogv|webm|wmv)$/', $html ) ? true : false;
	}

	/**
	 * @param  string      $html
	 * @return string|null
	 */
	private static function get_video_shortcode_url( $html ) {
		$has_shortcode = preg_match( '/^\[video.* (?:src|flv|m4v|mp4|ogv|webm|wmv)="([^"]+)".*\]$/', $html, $url );
		if ( $has_shortcode ) {
			return $url[1];
		}
		return null;
	}

	/**
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content_elements/video.json
	 *
	 * @param  string $html
	 * @return array
	 */
	public static function html_to_json( $html ) {
		if ( self::is_video_url( $html ) ) {
			$url = $html;
		} else {
			$url = self::get_video_shortcode_url( $html );
		}
		$thumbnail = self::get_thumbnail( $html );

		$required = array(
			'origin_id'  => null, // TODO
			'origin_cms' => null, // TODO
		);

		$optional = array(
			'_id'            => null,
			'type'           => 'video',
			'publication_id' => null, // TODO
			'title'          => null, // TODO
			'description'    => null, // TODO
			'thumbnail'      => $thumbnail,
			'url'            => $url,
			'inline'         => null, // TODO
			'shortcode_tag'  => 'video',
		);

		$order = array(
			'_id',
			'type',
			'origin_id',
			'origin_cms',
			'publication_id',
			'title',
			'description',
			'thumbnail',
			'url',
			'inline',
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
	private static function get_thumbnail( $html ) {
		$has_thumbnail = preg_match( '/^\[video.* poster="([^"]+)".*\]$/', $html, $thumbnail );
		if ( $has_thumbnail ) {
			return $thumbnail[1];
		}
		return null;
	}

	/**
	 * @param  array  $json
	 * @return string
	 */
	public static function json_to_html( $json ) {
		$output = '[video';
		if ( isset( $json['thumbnail'] ) && ! empty( $json['thumbnail'] ) ) {
			$output .= ' poster="' . $json['thumbnail'] . '"';
		}
		if ( isset( $json['url'] ) && ! empty( $json['url'] ) ) {
			$output .= ' src="' . $json['url'] . '"';
		}
		$output .= ']';

		return $output;
	}
}
