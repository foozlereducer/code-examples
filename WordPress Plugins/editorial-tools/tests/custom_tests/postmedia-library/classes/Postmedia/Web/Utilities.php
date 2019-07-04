<?php

namespace Postmedia\Web;

use Postmedia\Web\Utilities\Device;

/**
 * Postmedia Theme Utilities
 */
class Utilities {

	/**
	 * Device utility object
	 * @var Device
	 */
	public static $device;

	/**
	 * Check if user is on a mobile device
	 * @deprecated 2.0.2
	 *             Please use alternate:
	 *             $device = new Postmedia\Web\Utilities\Device();
	 *             $device->is_mobile();
	 * @return bool
	 */
	public static function is_mobile() {
		if ( ! self::$device ) {
			self::$device = new Device();
		}

		return self::$device->is_mobile();
	}

	/**
	 * Check if user is on a tablet
	 * @deprecated 2.0.2
	 *             Please use alternate:
	 *             $device = new Postmedia\Web\Utilities\Device();
	 *             $device->is_tablet();
	 * @return bool
	 */
	public static function is_tablet() {
		if ( ! self::$device ) {
			self::$device = new Device();
		}

		return self::$device->is_tablet();
	}

	/**
	 * Validate Current User role; adminstrators can only enter areas of this plugin wrapped by this method.
	 * @return boolean
	 */
	public function validate_role( $role_type = '' ) {
		if ( empty( $role_type ) ) {
			return current_user_can( 'manage_options' );
		}

		return current_user_can( $role_type );
	}

	/**
	 * Return the parent theme URI
	 * @return string
	 */
	public static function parent_uri() {
		return get_template_directory_uri();
	}

	/**
	 * Return the child theme URI
	 * @return string
	 */
	public static function child_uri() {
		return get_stylesheet_directory_uri();
	}

	/**
	 * Return the parent theme absolute path
	 * @return string
	 */
	public static function parent_dir() {
		return get_template_directory();
	}

	/**
	 * Return the child theme absolute path
	 * @return string
	 */
	public static function child_dir() {
		return get_stylesheet_directory();
	}

	/**
	 * Get Schema attributes for the HTML element
	 * @return string
	 */
	public static function get_html_schema_type() {
		$schema = 'http://schema.org/';

		// Author page
		if ( is_author() ) {
			$type = 'ProfilePage';
		} else if ( is_search() ) {
			$type = 'SearchResultsPage';
		} else if ( is_single() ) {
			$type = 'NewsArticle';
		} else {
			$type = 'WebPage';
		}

		return $schema . $type;
	}

	/**
	 * Get page ID by slug
	 * @param  string $slug
	 * @return string
	 */
	public static function page_id_by_slug( $slug ) {
		$page = wpcom_vip_get_page_by_path( $slug );

		if ( $page ) {
			return $page->ID;
		}

		return null;
	}

	/**
	 * Check if the page is child of a page.
	 * @param  mixed $page_id ID of the page we're looking for pages underneath
	 * @return bool
	 */
	public static function page_related_to_post( $page_id ) {
		global $post;

		// At the page or sub page
		if ( is_page( $page_id ) ) {
			return true;
		}

		$anc = get_post_ancestors( $post->ID );

		foreach ( $anc as $ancestor ) {
			if ( is_page() && $ancestor == $page_id ) {
				return true;
			}
		}

		// Page is not an ancestor
		return false;
	}

	/**
	 * Output the timing on a specific task / function by taking the average time of n* tests on function
	 *
	 *	Usage:
	 *	$time = Postmedia\Web\Utilities::loopinator_time_combobulator( function() use ( $variables, $to_localize, &$or_modify ) {
	 *		// do stuff
	 *	}, 100, true );
	 *
	 * @param  function  $func    Anonymous function to time
	 * @param  integer $loops     Number of times to iterate over function
	 * @param  boolean $echo_html Echo html with timing?
	 * @param  string $friendly_name Extra string to output with html to help identify
	 * @return float              Time in seconds
	 */
	public static function loopinator_time_combobulator( $func, $loops = 100, $echo_html = true, $friendly_name = '' ) {
		$loops_orig = $loops;

		$t = microtime( true );

		$result = '';

		while ( true ) {
			if ( 0 == $loops ) {
				break;
			}

			$result = $func();

			$loops--;
		}

		$elapsed = number_format( ( microtime( true ) - $t ) / $loops_orig, 3 );

		if ( $echo_html ) {
			echo sprintf( '%sTime: %ss avg. (%sx): %s<br>', esc_html( '[' . $friendly_name.'] ' ), esc_html( $elapsed ), esc_html( $loops_orig ), esc_html( $result ) );
		}

		return $elapsed;
	}

	/**
	 * Postmedia Layouts pre-escapes content/HTML. This function allows us to
	 * create an exclusion in our CI standards checks for this specific HTML.
	 * Do no use this for anything other than wrapping Layout HTML to be echoed
	 * @param  string $html Escaped Layout HTML
	 * @return string       Escaped Layout HTML
	 */
	public static function escaped_layouts( $escaped_layout_html ) {
		return $escaped_layout_html;
	}

	/**
	 * Get current category or current posts's main category
	 * Replaces: Postmedia\Web\Theme\Breadcrumbs::get_main_category()
	 * @return object
	 */
	public static function main_category() {
		$main_category = null;

		if ( is_single() ) {
			$the_post = get_queried_object();

			$categories = get_the_category( $the_post->ID );

			if ( ! empty( $categories ) ) {
				$main_category = array_shift( $categories );
			}
		} else if ( is_category() || is_page() ) {
			$main_category = get_queried_object();
		}

		return $main_category;
	}
}
