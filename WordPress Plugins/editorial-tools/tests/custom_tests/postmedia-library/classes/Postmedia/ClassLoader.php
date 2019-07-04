<?php

namespace Postmedia;

require_once( 'ClassLoader/Prefix.php' );

use Postmedia\ClassLoader\Prefix;

/**
 * The ClassLoader will require class files as they are used or instantiated
 * by registering the namespace prefix with PHP's spl autoloader
 */
class ClassLoader {

	/**
	 * Array of Prefix Objects to register
	 * @var Array Prefix
	 */
	private $prefixes;

	/**
	 * Create the ClassLoader with an option to specify a set of prefixes
	 * @param array $prefixes Prefix Objects
	 */
	public function __construct( $prefixes = array() ) {
		$this->prefixes = $prefixes;
	}

	/**
	 * Add a prefix to register
	 * @param string  $prefix
	 * @param string  $base_path
	 * @param bool $absolute
	 */
	public function add_prefix( $prefix, $base_path, $absolute = false ) {
		$this->prefixes[] = new Prefix( $prefix, $base_path, $absolute );
	}

	/**
	 * Locate and require file based on class name
	 * @param  string $class Requested class name w/ namespace
	 * @return bool
	 */
	public function load_class_file( $class ) {
		$class_path = null;

		$class = ltrim( $class, '\\' );

		foreach ( $this->prefixes as $p ) {
			if ( 0 === strpos( $class, $p->prefix ) ) {
				$class_no_prefix = substr( $class, strlen( $p->prefix ) );

				$file = $p->base_path . str_replace( '\\', DIRECTORY_SEPARATOR, $class_no_prefix ) . '.php';

				if ( $p->absolute ) {
					// If path is absolute check path
					if ( file_exists( $file ) ) {
						$class_path = $file;
						break;
					}
				} else {
					// Look for file in child theme first then fallback to parent
					if ( file_exists( get_stylesheet_directory() . $file ) ) {
						// return child path
						$class_path = get_stylesheet_directory() . $file;
						break;
					} else if ( file_exists( get_template_directory() . $file ) ) {
						// return parent path
						$class_path = get_template_directory() . $file;
						break;
					}
				}
			}
		}

		// Issues using validate_file on PC vs. MAC. Using directory traversal check for '..' instead.
		if ( file_exists( $class_path ) && ! strstr( $class_path, '..' ) ) {
			require $class_path;

			return true;
		}

		return false;
	}

	/**
	 * Register prefixes with PHP autoloader
	 * @return void
	 */
	public function register() {
		spl_autoload_register( array( $this, 'load_class_file' ) );
	}
}
