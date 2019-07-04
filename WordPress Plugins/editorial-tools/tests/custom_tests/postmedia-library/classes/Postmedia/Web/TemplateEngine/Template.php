<?php

namespace Postmedia\Web\TemplateEngine;

use Postmedia\Web\Utilities;

class Template {

	/**
	 * MD5 of path & params to create id
	 * @var string
	 */
	public $id;

	/**
	 * Full path to template file. If $allow_override = true then it will look in the
	 * child theme folder '../templates/' for a matching file.
	 * 	Example Template Path w/ $allow_override = true
	 * 		'/www/html/path/to/plugin/templates/theprovince/newsletter-widget.php'
	 *
	 * 	The template will first look in the child theme under the following folder:
	 * 		'../templates/theprovince/newsletter-widget.php'
	 *
	 *	Then finally fall back to the parent theme if one exists.
	 *
	 *	If the file is not found in the child or parent it will be expected in the calling functions project
	 *
	 * @var string
	 */
	public $path;

	/**
	 * An array of parameters to use within template
	 * NOTE: Parameters maintain references and parameter changes within the template can
	 * be reflected in the passed variable. If this is desired add '&' to the passed in param
	 * eg. array( 'my_param_by_reference' => &$my_param)
	 * @var array
	 */
	public $parameters;

	/**
	 * Contexts to make available to template.
	 * NOTE: Contexts technically could be put into parameters... but we build our cache key
	 * based on the path and parameters. We don't want to include any passed contexts in this
	 * cache key. Unless you have a valid reason to do so...
	 * @var array
	 */
	public $contexts;

	/**
	 * Cache rendered template?
	 * @var bool
	 */
	public $cache;

	/**
	 * Length of time to cache
	 * @var int
	 */
	public $cache_time;

	/**
	 * Allow the provided template path to be overriden by child theme templates
	 * @var bool
	 */
	public $allow_override;



	public function __construct( $path, $parameters = array(), $contexts = array() ) {
		$this->path = $path;
		$this->parameters =& $parameters;
		$this->contexts =& $contexts;
		$this->cache = true;
		$this->cache_time = 300;
		$this->allow_override = false;

		// Add device type to key... a lot of caching issues are resolved by doing this here
		$device = 'desktop';
		$device = ( Utilities::is_mobile() ) ? 'mobile' : $device;
		$device = ( Utilities::is_tablet() ) ? 'tablet' : $device;

		// Create unique identifier for this template and it's params
		// Yes.. json_encode is faster than serialize to stringify an array
		// I use get_template_path to cache the proper path if it has been overridden
		$this->id = md5( $this->get_template_path() . wp_json_encode( $parameters ) . $device );
	}

	/**
	 * Return the path of the template to use. If allow_override is true
	 * we need to look to the child theme to see if there is an override provided.
	 * @return string Absolute path to template file
	 */
	public function get_template_path() {
		if ( $this->allow_override ) {
			$template_path = null;

			$transient_key = 'template_path-' . md5( $this->path );

			if ( ! $this->should_cache() || false === ( $template_path = get_transient( $transient_key ) ) ) {

				// Get file name from provided path
				$path_parts = explode( 'templates/', $this->path );

				if ( isset( $path_parts[1] ) ) {
					// Look to child theme for template override file
					$child_template_path = get_stylesheet_directory() . '/templates/' . $path_parts[1];

					if ( file_exists( $child_template_path ) ) {
						$template_path = $child_template_path;
					} else {
						// Look to parent theme for template override file
						$parent_template_path = get_template_directory() . '/templates/' . $path_parts[1];

						if ( file_exists( $parent_template_path ) ) {
							$template_path = $parent_template_path;
						}
					}
				}

				if ( ! $template_path ) {
					$template_path = $this->path;
				}

				set_transient( $transient_key, $template_path, $this->cache_time );
			}

			return $template_path;
		}

		return $this->path;
	}

	/**
	 * Render the template by requiring the template from path and localizing parameters
	 * @param  boolean $return_output
	 * @return mixed
	 */
	public function render( $return_output = false ) {
		$output = '';

		if ( ! $this->should_cache() || false === ( $output = get_transient( $this->id ) ) ) {
			$path = $this->get_template_path();

			// Issues using validate_file on PC vs. MAC. Using directory traversal check for '..' instead.
			if ( file_exists( $path ) && ! strstr( $path, '..' ) ) {
				// Localize parameters
				foreach ( $this->parameters as $key => &$value ) {
					$$key =& $value;
				}

				// Localize contexts
				foreach ( $this->contexts as $key => &$value ) {
					$$key =& $value;
				}

				// Added as another way to access the template class context from within template
				$template_context =& $this;

				// Buffer output
				ob_start();

				// Require template
				require( $path );

				// Dump output
				$output = ob_get_contents();

				// Clean up
				ob_end_clean();

				// Cache output
				set_transient( $this->id, $output, $this->cache_time );
			} else {
				return false;
			}
		}

		// All templates have the required escaping - We're safe to output
		if ( $return_output ) {
			return $output;
		} else {
			echo $output; // @codingStandardsIgnoreLine - The TemplateEngine stores a cached version of the output buffer generated by the escaped content of the Template. In order to implement caching we must echo. 
		}

		return true;
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
