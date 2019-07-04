<?php

namespace Wholesite\Exception;

/**
 * Class property access error exception handler
 *
 * @license See license in source root directory
 */
class ClassPropertyAccessibilityException extends \Exception {

	public function __construct( $class, $property, $code = 0, Exception $previous = null ) {
		parent::__construct( 'Cannot access private or protected property. ' . $class . '::' . $property, $code, $previous );
	}
}
