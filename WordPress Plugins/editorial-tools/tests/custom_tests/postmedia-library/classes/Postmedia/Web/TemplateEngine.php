<?php

namespace Postmedia\Web;

use Postmedia\Web\TemplateEngine\Template;

class TemplateEngine {

	/**
	 * Enable caching?
	 * @var bool
	 */
	public $cache;

	/**
	 * Length of time to cache
	 * @var int
	 */
	public $cache_time;

	/**
	 * Base path for templates
	 * 	eg. /www/html/path/to/plugin/templates/theprovince/
	 * @var string
	 */
	public $template_root;


	public function __construct( $template_root = '', $cache = true, $cache_time = 300 ) {
		$this->template_root = $template_root;
		$this->cache = $cache;
		$this->cache_time = $cache_time;
	}

	/**
	 * Run initialization tasks
	 * @return bool
	 */
	public function initialize() {
		return $this->load_init_script();
	}

	/**
	 * Load template
	 * @param  string $template_path The path/name of the file relative to $template_root
	 * @param  array  $parameters
	 * @param  array  $contexts
	 * @param  boolean $allow_override
	 * @return object
	 */
	public function load_template( $template_path, $parameters = array(), $contexts = array(), $allow_override = false ) {
		// Clean path & assemble
		$template_location = trailingslashit( $this->template_root ) . ltrim( $template_path, '/' );

		$template = new Template( $template_location, $parameters, $contexts );

		// Set additional attributes
		$template->allow_override = $allow_override;
		$template->cache = $this->cache;
		$template->cache_time = $this->cache_time;

		return $template;
	}

	/**
	 * Include 'initialize.php' scripts if available  ( require this only once )
	 * This will look to the child theme first then fall back to local
	 * These files are intended to enqueue scripts or load functions related to templates before the template
	 * has been rendered. Sometimes at the time of template render it may be too late to load resources.
	 * @return bool
	 */
	private function load_init_script() {
		$init_location = null;

		$local_init_location = trailingslashit( $this->template_root ) . 'initialize.php';

		$transient_key = 'template_init_location-' . md5( $local_init_location );

		if ( ! $this->should_cache() || false === ( $init_location = get_transient( $transient_key ) ) ) {
			// Get file name from provided path
			$path_parts = explode( 'templates/', $local_init_location );

			if ( isset( $path_parts[1] ) ) {
				// Look to child theme for template override file
				$child_init_location = get_stylesheet_directory() . '/templates/' . $path_parts[1];

				if ( file_exists( $child_init_location ) ) {
					$init_location = $child_init_location;
				} else {
					// Look to parent theme for file
					$parent_init_location = get_template_directory() . '/templates/' . $path_parts[1];

					if ( $parent_init_location != $child_init_location && file_exists( $parent_init_location ) ) {
						$init_location = $parent_init_location;
					}
				}
			}

			if ( ! $init_location ) {
				if ( file_exists( $local_init_location ) ) {
					$init_location = $local_init_location;
				}
			}

			set_transient( $transient_key, $init_location, $this->cache_time );
		}

		// Issues using validate_file on PC vs. MAC. Using directory traversal check for '..' instead.
		if ( ! empty( $init_location ) && file_exists( $init_location ) && ! strstr( $init_location, '..' ) ) {
			return ( require_once( $init_location ) );
		}

		return false;
	}

	/**
	 * Check if we should cache
	 * Always force true for PROD
	 * @return bool
	 */
	public function should_cache() {
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV ) {
			return true;
		}

		return $this->cache;
	}
}
