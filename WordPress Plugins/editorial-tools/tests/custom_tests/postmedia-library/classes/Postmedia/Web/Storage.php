<?php

namespace Postmedia\Web;

class Storage {

	/**
	 * Unique Storage Property - the storage hook that will be writted to the database
	 * and will house all your options. This must be set; otherwise Storage will not manage CRUD
	 * operations
	 * @var unique string typically the name of the plugin, widget or theme
	 */
	private $unique_storage_key;



	/**
	 * Initialize Storage - check if exists and if not then initialize the storage hook to the storage db
	 * @param  string $storage_hook name used to write data to in the storage db
	 * @return boolean
	 */
	public function initialize_storage( $storage_hook = false ) {
		if ( is_string( $storage_hook ) && function_exists( 'wlo_get_option' ) ) {
			$this->unique_storage_key = $storage_hook;

			// Initialize this option in regular options
			if ( false === wlo_get_option( $this->unique_storage_key ) ) {
				// passing no data value sets the option name to the hook as an array
				return wlo_update_option( $this->unique_storage_key, '{ "status" : "initialized" }' );
			}

			return true;
		}

		return false;
	}

	/**
	 * Is JSON validates a string is in JSON format
	 * @param  	mixed  $value
	 * @see		<a href='https://developer.wordpress.org/reference/functions/wp_json_encode/'>wp_json_encode()</a>
	 * @return 	boolean
	 */
	public function is_json( $value ) {
		// json encode non string value
		if ( ! is_string( $value ) ) {
			$value = wp_json_encode( $value );
		}

		// json_decode will return and error if the string $value can't be decoded.
		// when decoding is successful then return true otherwise return false
		if ( is_string( $value ) ) {
			json_decode( $value );

			return ( json_last_error() == JSON_ERROR_NONE );
		}

		return false;
	}

	/**
	 * Process Data		gets, merges and updates existing key and values with new or updated key and values
	 * @param  array 	$new_options new options to be added
	 * @return array 	processed now full options
	 */
	private function process_data( $new_options ) {
		return array_replace( $this->get_options(), $new_options );
	}

	/**
	 * Add Option 			will convert key and value into JSON and store in the database
	 * @param string 		$key - single string used as name for the value(s)
	 * @param mixed			$value - Any type asided from <a href='http://php.net/manual/en/language.types.resource.php'>resource</a>
	 * @return boolean
	 */
	public function add_option( $key, $value ) {
		if ( ! function_exists( 'wlo_update_option' ) ) {
			return false;
		}

		$data = array();

		if ( is_string( $key ) ) {
			if ( $this->is_json( $value ) ) {
				// ensure $value is in array format
				if ( is_string( $value ) ) {
					$value = json_decode( $value, true );
				}

				$data[ $key ] = $value;
			} else {
				$data = array( $key => $value );
			}

			return wlo_update_option( $this->unique_storage_key, wp_json_encode( $this->process_data( $data ) ) );
		}

		return false;
	}

	/**
	 * Update Option adapter function for add_option() for usage context;
	 * if updating use update option, if adding use add_option
	 * @param  string $key   optional key
	 * @param  string $value optional value(s)
	 * @return boolean
	 */
	public function update_option( $key, $value ) {
		return $this->add_option( $key, $value );
	}

	/**
	 * Get Options from Wordpress stored under the base directory name in JSON format
	 * @param  	boolean $get_json 	default array will be returned. True json will returned.
	 * @see							<a href='http://codex.wordpress.org/Function_Reference/get_option'>get_option()</a>
	 * @see							<a href='http://codex.wordpress.org/Function_Reference/update_option'>update_option()</a>
	 * @return 	mixed values 		string if $get_json is true. Default returns an array
	 */
	public function get_options( $get_json = false ) {
		if ( ! function_exists( 'wlo_get_option' ) ) {
			return null;
		}

		if ( $get_json ) {
			// return JSON
			return wlo_get_option( $this->unique_storage_key );
		}

		// return array
		return json_decode( wlo_get_option( $this->unique_storage_key ), true );
	}

	/**
	 * Get Option returns single options
	 * @param  string  $key 		$key - single string used as name for the value(s)
	 * @param  boolean $get_json 	default array will be returned. True json will returned.
	 * @return mixed values 		string if $get_json is true. Default returns an array
	 */
	public function get_option( $key, $get_json = false ) {
		if ( is_string( $key ) ) {
			// get options as an array
			$options = $this->get_options();

			if ( array_key_exists( $key, $options ) ) {
				// don't JSON encode single return values, they always should be returned as strings
				if ( ! is_array( $options[ $key ] ) ) {
					// return string
					return $options[ $key ];
				}

				if ( $get_json ) {
					// return JSON
					return wp_json_encode( $options[ $key ] );
				}

				// return array
				return  $options[ $key ];
			}
		}
	}

	/**
	 * Delete Option 	specify a key and delete it
	 * @param  string 	$key is deleted along with its' values
	 * @return boolean
	 */
	public function delete_option( $key ) {
		if ( ! function_exists( 'wlo_update_option' ) ) {
			return false;
		}

		// get options as an array
		$options = $this->get_options();

		if ( isset( $options ) ) {
			if ( array_key_exists( $key, $options ) ) {
				unset( $options[ $key ] );

				return wlo_update_option( $this->unique_storage_key, wp_json_encode( $options ) );
			}
		}

		return false;
	}

	/**
	 * Expunge Settings 							completely destroy plugin settings
	 * @param  string $matching_unique_storage_key 	match string of main storage hook / main option name
	 * @return boolean								true if delete is successful otherwise false
	 */
	public function expunge_settings( $matching_unique_storage_key ) {
		if ( ! function_exists( 'wlo_delete_option' ) ) {
			return false;
		}

		if ( $matching_unique_storage_key == $this->unique_storage_key ) {
			return wlo_delete_option( $this->unique_storage_key );
		}
	}
}
