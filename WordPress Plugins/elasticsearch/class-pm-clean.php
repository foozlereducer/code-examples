<?php
/*
 * Sanatizing Class
 *
 * @uses:	pm_Clean::get_clean_val()
 * @uses:	gettype()
 * @uses:	abs()
 * @uses:	sanitize_text_field()
 * @uses:	strip_tags()
 * @uses:	absint()
 * @uses:	ceil()
 */
class pm_Clean
{
	static function get_clean_val( $val, $strip_tags = 1, $enforce_type = null ) {
		$valid_value = null;
		$val_type    = gettype( $val ); // get value type
		
		if ( $val_type == 'array' ) {
			
			$valid_values = array();
			
			foreach ( $val as $vl ) {
				
				switch ( gettype( $vl ) ) {
					
					case 'integer':
						if ( $val < 0 ) {
							$valid_value = abs( $vl ); // convert negative integers to be positive
						} else {
							$valid_value = $vl;
						}
						break;
					case 'string':
						if ( $strip_tags == 1 ) {
							if ( function_exists( 'sanitize_text_field' ) ) { // Check if we're in Wordpress
								$valid_value = sanitize_text_field( $vl ); // Checks for invalid UTF-8, Convert single < characters to entity, strip all tags, remove line breaks, tabs and extra white space, strip octets.
							} else {
								$valid_value = strip_tags( $vl ); //  Strip HTML and PHP tags from the value
							}
						}
						break;
					case 'float':
					case 'double':
						if ( function_exists( 'absint' ) ) {
							$valid_value = absint( ceil( $vl ) ); // convert positive and negative floats to positive integers the Wordpress Way
						} else {
							$valid_value = abs( ceil( $vl ) ); // convert positive and negative floats to positive integers
						}
						break;
					case 'object':
					case 'resource':
					case "NULL":
					case "unknown type":
						$valid_value = false;
						break;
				}
				
				$valid_values[] = $valid_value;
				
			}
			
			return $valid_values;
		}
		
		if ( $enforce_type == null ) { // a enforced type is not set so process based on type
			
			switch ( $val_type ) {
				
				case 'integer':
					if ( $val < 0 ) {
						$valid_value = abs( $val ); // convert negative integers to be positive
					} else {
						$valid_value = $val;
					}
					break;
				
				case 'string':
					if ( $strip_tags == 1 ) {
						if ( function_exists( 'sanitize_text_field' ) ) { // Check if we're in Wordpress
							$valid_value = sanitize_text_field( $val ); // Checks for invalid UTF-8, Convert single < characters to entity, strip all tags, remove line breaks, tabs and extra white space, strip octets.
						} else {
							$valid_value = strip_tags( $val ); //  Strip HTML and PHP tags from the value
						}
					}
					break;
				
				case 'float':
				case 'double':
					if ( function_exists( 'absint' ) ) {
						$valid_value = absint( ceil( $val ) ); // convert positive and negative floats to positive integers the Wordpress Way
					} else {
						$valid_value = abs( ceil( $val ) ); // convert positive and negative floats to positive integers
					}
					break;
				
				case 'object':
				case 'resource':
				case "NULL":
				case "unknown type":
					$valid_value = false;
					break;
					
			}
			
			return $valid_value;
			
		} else {
			
			if ( 'object ' == $val_type || 'resource' == $val_type || 'NULL' == $val_type || 'unknown type' == $val_type ) {
				
				return false;
				
			} else {
				
				switch ( $enforce_type ) {
					
					case 'integer':
						if ( empty( $val ) ) {
							
							return 0;
							
						}
						$val = (int) $val;
						
						// if a float or double it will be rounded up to 
						// the nearest positive integer, if an integer then no 
						// need to round up. If a string then also converts to an int before processing
						$valid_value = abs( ceil( $val ) );
						break;
					
					case 'bool':
						if ( empty( $val ) ) {
							
							return 0;
						}
						
						if ( 'integer' != gettype( $val ) || 'bool' != gettype( $val ) ) {
							
							(int) $val;
						}
						
						if ( $val > 1 ) {
							
							return 1;
						}
						
						$valid_value = (bool) $val;
						break;
					
					case 'host':
						if ( $no_strip_tags == 0 ) {
							
							if ( function_exists( 'sanitize_text_field' ) ) {
								
								$val = sanitize_text_field( $val );
								
							} else {
								
								$val = strip_tags( $val );
								
							}
							
						}
						
						$valid_value = $val;
						break;
				}
				return $valid_value;
			}
		}
	}
}
