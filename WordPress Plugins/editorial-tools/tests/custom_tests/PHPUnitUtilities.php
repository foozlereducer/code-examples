<?php

class PHPUnitUtilities {
	/**
	 * Helper to call private methods by making them public
	 *
	 * @param  object $obj
	 * @param  string $name
	 * @param  array  $args
	 * @return mixed
	 */
	public static function callPrivateMethod( $obj, $name, array $args = array() ) {
		$class = new \ReflectionClass( $obj );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );
		return $method->invokeArgs( $obj, $args );
	}

	/**
	 * Helper to access private properties
	 *
	 * @param  object $obj
	 * @param  string $name
	 * @return mixed
	 */
	public static function getPrivateProperty( $obj, $name ) {
		$class = new \ReflectionClass( $obj );
		$property = $class->getProperty( $name );
		$property->setAccessible( true );
		return $property->getValue( $obj );
	}
}
