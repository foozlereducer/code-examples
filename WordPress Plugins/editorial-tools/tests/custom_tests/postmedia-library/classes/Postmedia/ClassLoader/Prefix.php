<?php

namespace Postmedia\ClassLoader;

class Prefix {

	/**
	 * Prefix
	 * eg. 'Postmedia'
	 * @var string
	 */
	public $prefix;

	/**
	 * Path where to look for classes starting with the prefix
	 * eg. '/classes/Postmedia'
	 * @var string
	 */
	public $base_path;

	/**
	 * Is the path provided an absolute path
	 * @var bool
	 */
	public $absolute;

	/**
	 * Create the prefix
	 * @param string  $prefix
	 * @param string  $base_path
	 * @param bool $absolute
	 */
	public function __construct( $prefix, $base_path, $absolute = false ) {
		// clean input
		$this->prefix = trim( $prefix, '\\' ) . '\\';

		$this->base_path = str_replace( '\\', DIRECTORY_SEPARATOR, $base_path );

		$this->base_path = rtrim( $this->base_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

		$this->absolute = $absolute;
	}
}
