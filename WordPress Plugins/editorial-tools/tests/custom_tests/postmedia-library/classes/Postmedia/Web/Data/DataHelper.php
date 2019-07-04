<?php

/**
 * Contains functions used in multiple classes for formatting data.
 */

namespace Postmedia\Web\Data;

use Postmedia\Web\Data\ContentElement\Audio;
use Postmedia\Web\Data\ContentElement\Blockquote;
use Postmedia\Web\Data\ContentElement\Gallery;
use Postmedia\Web\Data\ContentElement\Image;
use Postmedia\Web\Data\ContentElement\OEmbed;
use Postmedia\Web\Data\ContentElement\Pointer;
use Postmedia\Web\Data\ContentElement\RawHtml;
use Postmedia\Web\Data\ContentElement\Text;
use Postmedia\Web\Data\ContentElement\Video;

class DataHelper {
	/**
	 * Normalizes date strings.
	 *
	 * @param  string $date
	 * @return string
	 */
	public static function format_date( $date ) {
		return gmdate( 'Y-m-d\TH:i:s.v\Z', strtotime( $date ) );
	}

	/**
	 * Converts JSON to HTML or HTML to JSON.
	 *
	 * @param  string|array      $input
	 * @param  string            $output_format 'json' or 'html'
	 * @param  string            $type          eg. 'audio', 'blockquote' (Anything in Postmedia/Web/Data/ContentElement)
	 * @return string|array|null
	 */
	public static function get_content_element( $input, $output_format = 'json', $type = '' ) {
		// Ensure this is a valid conversion (ie. JSON to HTML or HTML to JSON; not something like JSON to int)
		$function_name = self::get_conversion_function_name( $input, $output_format );
		if ( empty( $function_name ) ) {
			return null;
		}

		// If the input is a shortcode, convert it using the shortcode's JSON in/out attributes.
		$output = self::convert_shortcode( $input, $output_format );
		if ( ! empty( $output ) ) {
			return $output;
		}

		// Otherwise, the input must be a regular content element.
		return self::convert_content_element( $function_name, $input, $type );
	}

	/**
	 * Determines whether to convert from JSON to HTML or HTML to JSON.
	 *
	 * @param  string|array $input
	 * @param  string       $output_format 'json' or 'html'
	 * @return string|null
	 */
	private static function get_conversion_function_name( $input, $output_format ) {
		// Ensure the output format is valid.
		$valid_output_formats = array( 'json', 'html' );
		if ( ! in_array( $output_format, $valid_output_formats ) ) {
			return null;
		}

		// Determine the input format.
		$input_format = is_array( $input ) ? 'json' : 'html';

		// Ensure this is a valid conversion. (Prevent html_to_html or json_to_json.)
		$function_name = $input_format . '_to_' . $output_format;
		$valid_function_names = array(
			'html_to_json',
			'json_to_html',
		);
		if ( ! in_array( $function_name, $valid_function_names ) ) {
			return null;
		}

		return $function_name;
	}

	/**
	 * Returns a list of all supported shortcodes.
	 *
	 * @return array
	 */
	public static function get_supported_shortcodes() {
		$shortcodes = array();
		return apply_filters( 'wcm_shortcodes_json_output', $shortcodes );
	}

	/**
	 * Converts shortcode from JSON to HTML or HTML to JSON.
	 *
	 * @param  string|array      $input
	 * @param  string            $output_format 'json' or 'html'
	 * @return string|array|null
	 */
	private static function convert_shortcode( $input, $output_format ) {
		// Find shortcodes that provide JSON input/output.
		$shortcodes = self::get_supported_shortcodes();
		if ( empty( $shortcodes ) ) {
			// There are no supported shortcodes, so the input won't be handled as a shortcode.
			return null;
		}

		// If the input matches one of the supported shortcodes, use that shortcode for the conversion.
		foreach ( $shortcodes as $shortcode => $enabled ) {
			if ( $enabled ) {
				if ( 'json' == $output_format && strpos( $input, '[' . $shortcode ) !== false ) {
					// The input is a shortcode; add jsonout to the existing shortcode.
					$shortcode = preg_replace( '/(\s*\/\s*)?\]$/', ' jsonout="true"$1]', $input );
				} else if ( 'html' == $output_format && $shortcode == $input['shortcode_tag'] ) {
					// The input is JSON; create a shortcode string and pass the JSON to the shortcode using the shortcode's json attribute.
					$shortcode = self::json_to_shortcode( $input );
				} else {
					continue;
				}

				// Call the shortcode; we have jsonout set to true, so we should be getting JSON back regardless of the input.
				$json = do_shortcode( $shortcode );
				$json = json_decode( $json, true );

				if ( 'json' == $output_format ) {
					// We want JSON output; return the entire JSON array.
					return $json;
				} else {
					// We want HTML output; just return shortcode attribute from the JSON array.
					return $json['shortcode'];
				}
			}
		}

		// The input didn't match a supported shortcode.
		return null;
	}

	/**
	 * Creates a shortcode string that will pass in JSON and will output JSON so we can get a nicely formatted shortcode string.
	 *
	 * @param  array  $input
	 * @return string
	 */
	private static function json_to_shortcode( $input ) {
		// Remove the shortcode_tag attribute, since it isn't actually used by any shortcodes.
		$shortcode_tag = $input['shortcode_tag'];
		unset( $input['shortcode_tag'] );

		// Set the jsonout attribute.
		$input['jsonout'] = true;

		// Convert special characters (namely "'<>) that will break shortcode_parse_atts().
		array_walk_recursive( $input, function( &$value ) {
			if ( is_string( $value ) ) {
				$value = htmlspecialchars( $value, ENT_QUOTES );
			}
		} );

		// Encode the values as JSON.
		$json = wp_json_encode( $input );

		// Convert [ to &#091; and ] to &#093; in the JSON so they don't break shortcode_parse_atts().
		$json = str_replace( '[', '&#091;', $json );
		$json = str_replace( ']', '&#093;', $json );

		// Use both jsonin and jsonout to get the properly formatted shortcode.
		return '[' . $shortcode_tag . ' jsonin="true" jsonout="true" json=\'' . $json . '\']';
	}

	/**
	 * Returns a list of all supported content element types.
	 *
	 * @return array
	 */
	public static function get_supported_content_element_types() {
		$types = array(
			'audio'      => new Audio(),
			'blockquote' => new Blockquote(),
			'gallery'    => new Gallery(),
			'image'      => new Image(),
			'oembed'     => new OEmbed(),
			'pointer'    => new Pointer(),
			'raw_html'   => new RawHtml(),
			'text'       => new Text(),
			'video'      => new Video(),
		);
		return apply_filters( 'postmedia_library_content_element_types', $types );
	}

	/**
	 * Converts basic content element (ie. not a shortcode) from JSON to HTML or HTML to JSON.
	 *
	 * @param  string       $function_name 'json_to_html' or 'html_to_json'
	 * @param  string|array $input
	 * @param  string       $type          eg. 'audio', 'blockquote' (Anything in Postmedia/Web/Data/ContentElement)
	 * @return string|array
	 */
	private static function convert_content_element( $function_name, $input, $type = '' ) {
		// Get all available content element types.
		$types = self::get_supported_content_element_types();

		// The type is known; convert the data using the corresponding class.
		if ( ! empty( $type ) && isset( $types[ $type ] ) ) {
			return $types[ $type ]::$function_name( $input );
		}

		// The type is not known; determine the type, then convert the data using the corresponding class.
		foreach ( $types as $type => $obj ) {
			if ( $obj::is( $input ) ) {
				return $obj::$function_name( $input );
			}
		}

		// Anything else must be plain text.
		return Text::$function_name( $input );
	}

	/**
	 * Combines required and optional field values in the specified order.
	 *
	 * @param  array $required
	 * @param  array $optional
	 * @param  array $order
	 * @return array
	 */
	public static function merge_fields( $required, $optional, $order = array() ) {
		// Strip empty optional fields.
		$optional = array_filter( $optional );

		// Combine all values.
		$values = array_merge( $required, $optional );

		// Order the values.
		if ( ! empty( $order ) ) {
			$output = array();
			foreach ( $order as $key ) {
				if ( ! array_key_exists( $key, $values ) ) {
					continue;
				}
				$output[ $key ] = $values[ $key ];
			}
			return $output;
		}

		return $values;
	}

	/**
	 * Returns a list of WordPress taxonomy names and their corresponding WCM keys.
	 *
	 * @return array
	 */
	public static function get_taxonomy_labels() {
		return array(
			'bodystyle'      => 'bodystyles',
			'category'       => 'categories',
			'classification' => 'classifications',
			'make'           => 'makes',
			'model_year'     => 'model_years',
			'post_tag'       => 'tags',
			'specialsection' => 'specialsections',
		);
	}

	/**
	 * Converts a WordPress taxonomy name to its corresponding WCM key.
	 *
	 * @param  string $taxonomy
	 * @return string
	 */
	public static function taxonomy_to_label( $taxonomy ) {
		$data = self::get_taxonomy_labels();
		if ( isset( $data[ $taxonomy ] ) ) {
			return $data[ $taxonomy ];
		}
		return $taxonomy;
	}

	/**
	 * Converts WordPress status to WCM status.
	 *
	 * @param  string      $status
	 * @return string|null
	 */
	public static function get_wcm_status( $status ) {
		$data = array(
			'inherit'    => 'published',
			'private'    => 'published',
			'publish'    => 'published',
			'auto-draft' => 'draft',
			'draft'      => 'draft',
			'future'     => 'draft',
			'pending'    => 'draft',
			'trash'      => 'deleted',
		);
		if ( isset( $data[ $status ] ) ) {
			return $data[ $status ];
		}
		return null;
	}

	/**
	 * Converts WCM status to WordPress status.
	 *
	 * @param  string      $status
	 * @return string|null
	 */
	public static function get_wp_status( $status ) {
		$data = array(
			'published' => 'publish',
			'draft'     => 'draft',
			'deleted'   => 'trash',
		);
		if ( isset( $data[ $status ] ) ) {
			return $data[ $status ];
		}
		return null;
	}

	/**
	 * Converts WordPress post type to WCM type.
	 *
	 * @param  string      $type
	 * @return string|null
	 */
	public static function get_wcm_type( $type ) {
		$data = self::get_types();
		$data = array_combine( array_values( $data ), array_keys( $data ) );
		if ( isset( $data[ $type ] ) ) {
			return $data[ $type ];
		}
		return null;
	}

	/**
	 * Converts WCM type to WordPress post type.
	 *
	 * @param  string      $type
	 * @return string|null
	 */
	public static function get_wp_type( $type ) {
		$data = self::get_types();
		if ( isset( $data[ $type ] ) ) {
			return $data[ $type ];
		}
		return null;
	}

	/**
	 * Returns array of WCM types => WordPress post types.
	 *
	 * @return array
	 */
	private static function get_types() {
		return array(
			'story'        => 'post',
			'gallery'      => 'gallery',
			'versus'       => 'versus',
			'sunshinegirl' => 'sunshinegirl',
			'pointer'      => 'pn_pointer',
			'feature'      => 'feature',
		);
	}

	/**
	 * Converts JSON to raw text / unrendered shortcodes.
	 *
	 * @param  array  $content_elements
	 * @return string
	 */
	public static function build_post_content( $content_elements ) {
		$content = array();
		foreach ( $content_elements as $element ) {
			$content[] = self::get_content_element( $element, 'html', $element['type'] );
		}
		return implode( "\n\n", $content );
	}

	/**
	 * Returns a list of HTML elements that should not be wrapped in paragraph tags.
	 *
	 * @return array
	 */
	public static function get_block_level_elements() {
		return array(
			'address',
			'article',
			'aside',
			'blockquote',
			'canvas',
			'div',
			'dl',
			'fieldset',
			'figcaption',
			'figure',
			'footer',
			'form',
			'h([1-6])',
			'header',
			'hgroup',
			'hr',
			'main',
			'nav',
			'noscript',
			'ol',
			'output',
			'p',
			'pre',
			'section',
			'table',
			'tfoot',
			'ul',
			'video',
		);
	}
}
