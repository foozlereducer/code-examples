<?php

namespace Wholesite\Exception;

/**
 * Class property not found exception handler
 *
 * @license See license in source root directory
 */
class ClassPropertyNotFoundException extends \Exception {

	public function __construct( $class, $property, $code = 0, Exception $previous = null ) {
		parent::__construct( $class . '::' . $property, $code, $previous );
	}
}
