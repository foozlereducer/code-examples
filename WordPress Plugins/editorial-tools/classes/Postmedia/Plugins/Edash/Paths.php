<?php
namespace Postmedia\Plugins\Edash;

/**
 * Path class used to provide the current plugin, widget or themes root directory
 *
 * $p->url()	- returns the absolute url of the plugin
 * $p->path()	- return the absolute path to the root directory
 *
 * If using the postmedia class library and having the default plugin/classes/Postmedia a simple intantiation is needed
 * $p = new Paths();
 *
 * You can wire a custom directory, so if not using the postmedia library class loader with a path like plugin/inclues/:
 * $p = new Paths( 'includes' );
 *
 * Get the absolute directory path:
 * $p->path();
 *
 * Get the absolute url:
 * $p->url();
 *
 * You can use Paths will a custom delimiter like:
 * $p = new Paths( $top_library_dir = false, $custom_delimiter = ':' );
 *
 * @param string $top_library_dir optional - custom top level library like dir/includes/classes/, 'includes' === $top_library_dir
 * @param string $path_delimiter optional  - custom path delimiter; normally '/' but could be specified as ':'
 */
class Paths {
	private $path;
	private $url;
	private $process_path;
	private $top_library_dir;
	private $path_delimiter;
	/**
	 * Paths constructor - wires the default or overriden and sets the $url and $path properties
	 * @param string $top_library_dir optional custom top level library like dir/includes/classes/, 'includes' === $top_library_dir
	 * @param string  $path_delimiter optional custom path delimiter; normally '/' but could be specified as ':'
	 */
	public function __construct( $top_library_dir = false, $path_delimiter = '/' ) {
		// Force default top_library_dir when bad data is passed in
		if ( ! empty( $top_library_dir ) && 'string' !== gettype( $top_library_dir ) ) {
			$top_library_dir = false;
		}
		// Force default path_delimiter when bad data is passed in
		if ( '/' != $path_delimiter && 'string' !== gettype( $path_delimiter ) ) {
			$path_delimiter = '/';
		}
		$this->top_library_dir = $top_library_dir;
		$this->path_delimiter = $path_delimiter;
		$this->set_path();
		$this->set_url();
	}
	/**
	 * Gets current plugin root path
	 * @return string - like 'plugin/classes/Postmedia'
	 */
	public function path() {
		return $this->path;
	}
	/**
	 * Gets current plugin root url
	 * @return string - like 'http://domain.ca/path/to/plugin'
	 */
	public function url() {
		return $this->url;
	}
	/**
	 * Private method that sets the private path property
	 * @see  plugin_dir_path() https://developer.wordpress.org/reference/functions/plugin_dir_path/
	 */
	public function set_path() {
		$this->path = $this->get_base_path( explode( $this->path_delimiter , plugin_dir_path( __FILE__ ) ) );
	}
	/**
	 * Private method that sets the private url property
	 * @see  plugin_dir_url() https://developer.wordpress.org/reference/functions/plugin_dir_url/
	 */
	public function set_url() {
		$this->url = $this->get_base_path( explode( '/' , plugins_url( '', __FILE__ ) ) );
	}
	/**
	 * Searches and array for keywords
	 * @return integer position in the $this->process_path array
	 */
	public function search_array() {
		// Does the postmedia library classloader path exist then get the default
		// path otherwise get custom path array keys
		if ( false != array_search( 'classes', $this->process_path, true ) &&
			false != array_search( 'Postmedia',$this->process_path, true )
		) {
			return array_search( 'classes', $this->process_path, true );
		}
		// Search path targeting the top library directory
		if ( $this->top_library_dir ) {
			return array_search( $this->top_library_dir, $this->process_path, true );
		}
		// Path top library directory index not found so return null
		return null;
	}
	/**
	 * Gets the private process_path property value
	 * @return array of path
	 */
	public function get_processed_path() {
		return $this->process_path;
	}
	/**
	 * Processes the default postmedia library class path; trims the array to the root directory
	 * @return array of path
	 */
	private function process_class_loader_path() {
		$i = $this->search_array();
		if ( false !== $i ) {
			if ( 'Postmedia' === $this->process_path[ $i + 1 ] ) {
				$i++;
				$cnt = count( $this->process_path );
				for ( $i; $i <= $cnt; $i++ ) {
					// trim off each library directory, end trimming at the base directory
					array_pop( $this->process_path );
				}
			}
		}
	}
	/**
	 * Processes a custom library path; trims the array to the root directory
	 * @param  string $override_path unit testing path
	 * @return array of path
	 */
	public function proccess_custom_path( $override_path = false ) {
		if ( false !== $override_path ) {
			 $this->process_path = $override_path;
		}
		$i = $this->search_array();
		if ( false !== $i ) {
			$cnt = count( $this->process_path );
			$i++;
			for ( $i; $i <= $cnt; $i++ ) {
				// trim off each library directory, end trimming at the base directory
				array_pop( $this->process_path );
			}
		}
		return;
	}
	/**
	 * Determines the base path of the plugin or theme
	 * @param  array   $path  - the absolute path exploded as an array
	 * @return string         - the base path of plugin or theme
	 */
	private function get_base_path( $path, $path_delimiter = '/' ) {
		$this->process_path = $path;
		if ( false === $this->top_library_dir ) {
			$this->process_class_loader_path();
		} else {
			$this->proccess_custom_path();
		}
		return implode( $this->path_delimiter, $this->process_path );
	}
	/**
	 * Unit testing method to test the private get_base_path() object
	 * @param  array   $path  - the absolute path exploded as an array
	 * @return string  the root plugin, widget or themes absolute path
	 */
	public function unit_test_get_base_path_private_method( $path ) {
		return $this->get_base_path( $path );
	}
	/**
	 * get_base_dir		- returns the base directory of the path
	 * @return string
	 */
	public function get_base_dir() {
		if ( ! empty( $this->path_delimiter ) && ! empty( $this->path ) ) {
			return array_pop( explode( $this->path_delimiter, $this->path ) );
		}
		return;
	}
}
