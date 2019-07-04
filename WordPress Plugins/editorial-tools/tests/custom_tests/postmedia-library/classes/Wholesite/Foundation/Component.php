<?php

namespace Wholesite\Foundation;

use Wholesite\Exception;

/**
 * Component is a base class that provides the foundation for all objects.
 *
 * It enables get/set functionality for private/protected class properties.
 *
 * @license See license in source root directory
 */
class Component {

	/**
	 * Setter functions defined in Class
	 * Speeds performance of magic __set function
	 * @var array
	 */
	private $setters;

	/**
	 * Getter functions defined in Class
	 * Speeds performance of magic __get function
	 * @var array
	 */
	private $getters;



	/**
	 * Magic get function
	 *
	 * NOTE: Only if the property requested is marked as 'private' or 'protected'
	 * will this method be used. If no 'get_' method has been defined you will get
	 * the expected fatal error.
	 *
	 * @param  string  $property
	 * @return mixed
	 */
	public function __get( $property ) {
		if ( ! isset( $this->getters ) ) {
			$this->getters = array();

			foreach ( get_class_methods( $this ) as $method ) {
				if ( 'get_' == substr( $method, 0, 4 ) ) {
					$this->getters[] = $method;
				}
			}
		}

		if ( in_array( "get_$property", $this->getters ) ) {
			return $this->{"get_$property"}();
		}

		if ( ! property_exists( $this, $property ) ) {
			throw new Exception\ClassPropertyNotFoundException( get_class( $this ), $property );
		} else {
			throw new Exception\ClassPropertyAccessibilityException( get_class( $this ), $property );
		}
	}

	/**
	 * Magic set function
	 *
	 * NOTE: Only if the property requested is marked as 'private' or 'protected'
	 * will this method be used. If no 'set_' method has been defined you will get
	 * the expected fatal error.
	 *
	 * @param  string  $property
	 * @param  mixed  $value
	 * @return void
	 */
	public function __set( $property, $value ) {
		if ( ! isset( $this->setters ) ) {
			$this->setters = array();

			foreach ( get_class_methods( $this ) as $method ) {
				if ( 'set_' == substr( $method, 0, 4 ) ) {
					$this->setters[] = $method;
				}
			}
		}

		if ( in_array( "set_$property", $this->setters ) ) {
			$this->{"set_$property"}( $value );

			return;
		}

		if ( ! property_exists( $this, $property ) ) {
			throw new Exception\ClassPropertyNotFoundException( get_class( $this ), $property );
		} else {
			throw new Exception\ClassPropertyAccessibilityException( get_class( $this ), $property );
		}
	}

	/**
	 * Magic isset function
	 *
	 * NOTE: Only if the property requested is marked as 'private' or 'protected'
	 * will this method be used. If no 'get_' method has been defined you will get
	 * a 'false' return.
	 *
	 * @param  string  $property
	 * @return boolean
	 */
	public function __isset( $property ) {
		if ( method_exists( $this, "get_$property" ) ) {
			$value = $this->{"get_$property"}();

			return isset( $value );
		}

		return false;
	}
}
