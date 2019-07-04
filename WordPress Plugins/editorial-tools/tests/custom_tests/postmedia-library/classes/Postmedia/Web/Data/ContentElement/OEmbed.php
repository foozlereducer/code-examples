<?php

namespace Postmedia\Web\Data\ContentElement;

use Postmedia\Web\Data\IContentElement;
use Postmedia\Web\Data\DataHelper;

class OEmbed implements IContentElement {
	/**
	 * Determines whether the given HTML or JSON is an embed.
	 *
	 * @param  string|array $input
	 * @return boolean
	 */
	public static function is( $input ) {
		if ( is_array( $input ) ) {
			return 'oembed' == $input['type'];
		}
		return preg_match( '/^\[embed\].+\[\/embed\]$/', $input ) ? true : false;
	}

	/**
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content_elements/oembed.json
	 *
	 * @param  string $html
	 * @return array
	 */
	public static function html_to_json( $html ) {
		$object_url = self::get_object_url( $html );
		$provider_url = self::get_provider_url( $object_url );
		$provider_name = self::get_provider_name( $provider_url );

		$required = array(
			'type'         => 'oembed',
			'provider_url' => $provider_url,
			'object_url'   => $object_url,
		);

		$optional = array(
			'_id'           => null,
			'subtype'       => null, // TODO
			'provider_name' => $provider_name,
			'html'          => null, // TODO
			'shortcode_tag' => 'embed',
		);

		$order = array(
			'_id',
			'type',
			'subtype',
			'provider_name',
			'provider_url',
			'object_url',
			'html',
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
	private static function get_object_url( $html ) {
		$has_url = preg_match( '/\[embed\](.+)\[\/embed\]/', $html, $url );
		if ( $has_url ) {
			return $url[1];
		}
		return null;
	}

	/**
	 * @param  string      $object_url
	 * @return string|null
	 */
	private static function get_provider_url( $object_url ) {
		$oembed = _wp_oembed_get_object();
		$provider = $oembed->get_provider( $object_url );
		if ( ! empty( $provider ) ) {
			return $provider;
		}
		return null;
	}

	/**
	 * Converts URLs to the approximate site name.
	 * Note: Does not work for domains with multi-part TLDs (eg. google.co.uk).
	 *
	 * @param  string      $provider_url
	 * @return string|null
	 */
	private static function get_provider_name( $provider_url ) {
		// http://www.youtube.com/oembed => www.youtube.com
		$url = parse_url( $provider_url );
		if ( ! isset( $url['host'] ) ) {
			return null;
		}

		// www.youtube.com => youtube
		$provider = preg_replace( '/^(.*\.)?([^.]+)\.[^.]+$/', '$2', $url['host'] );

		switch ( $provider ) {
			case 'youtube':
				return 'YouTube';
		}

		return ucfirst( $provider );
	}

	/**
	 * @param  array  $json
	 * @return string
	 */
	public static function json_to_html( $json ) {
		return '[embed]' . $json['object_url'] . '[/embed]';
	}
}
