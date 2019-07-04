<?php
namespace Postmedia\Plugins\Edash;

/**
 * Taxonomy Class
 */
class Taxonomy {
	private $taxonomy;

	/**
	 * Set the Taxonomy String
	 * @param {string} $taxonomy - a name string; typically a plugin, theme or widget name
	 * @return {string} ~ return the taxonomy that was just set.
	 */
	public function set_taxonomy( $taxonomy ) {
		return $this->taxonomy = $this->filter_to_string( $taxonomy );
	}

	/**
	 * Get the Taxonomy String
	 * @return {string} - a name string; typically a plugin, theme or widget name
	 */
	public function get_taxonomy() {
		return $this->taxonomy;
	}

	/**
	 * Convert non-strings to random string value; otherwise return string value set but
	 * trim at 100 characters length
	 */
	private function filter_to_string( $value ) {
		if ( ! is_string( $value ) ) {
			return substr( md5( rand() ), 0, 7 );
		}

		return substr( $value, 0, 100 );
	}
}
