<?php

namespace Postmedia\Web\Utilities;

/**
 * Is Device - Adapter classes for JetPack device handling
 * @author  Steve Browning
 * @version  0.1 - basic wrappers with jetpack exists checks
 */
class Device {

	/**
	 * Jetpack User Agent class, only instatiated for unit test
	 * @var object
	 */
	private $juai;


	/**
	 * Device Constructor
	 */
	public function __construct() {}

	/**
	 * Is Mobile returns true if device is a mobile
	 * @return boolean
	 */
	public function is_mobile() {
		if ( function_exists( 'jetpack_is_mobile' ) ) {
			if ( $this->valid_user_agent( $this->get_user_agent() ) ) {
				return jetpack_is_mobile();
			}
		} else if ( function_exists( 'wp_is_mobile' ) ) {
			return wp_is_mobile();
		}

		return false;
	}

	/**
	 * Is Tablet runs and returns the tablet check
	 * @return boolean
	 */
	public function is_tablet() {
		if ( function_exists( 'jetpack_is_mobile' ) ) {
			if ( $this->valid_user_agent( $this->get_user_agent() ) ) {
				return $this->tablet_check();
			}
		}

		return false;
	}

	/**
	 * Unit Test Setting of the JetPack_User_Agent_Info class
	 * @param object instance of the jetpack user agent class need to be set for unit testing only
	 */
	public function unit_test_set_jetpack_user_agent_info( \Jetpack_User_Agent_Info $juai ) {
		$this->juai = $juai;
	}

	/**
	 * Valid User Agent   check to ensure that user agent is a valid type
	 * @param  string $ua user agent
	 * @return boolean
	 */
	private function valid_user_agent( $ua ) {
		if ( is_string( $ua ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Set User Agent primary used for unti testing, normally a device will provide a user agent string
	 * @param string $ua user agent
	 * @return void
	 */
	public function set_user_agent( $ua ) {
		if ( $this->valid_user_agent( $ua ) ) {
			if ( ! empty( $ua ) ) {
				// Filter out any bad scoobies in the user agent string
				$_SERVER['HTTP_USER_AGENT'] = filter_var( $ua, FILTER_SANITIZE_STRING );
			}
		} else {
			$_SERVER['HTTP_USER_AGENT'] = null;
		}
	}

	/**
	 * Get User Agent will return the current server user agent
	 * @return void
	 */
	public function get_user_agent() {
		if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return  filter_var( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ), FILTER_SANITIZE_STRING );
		} else {
			$_SERVER['HTTP_USER_AGENT'] = null;
		}
	}

	/**
	 * Tablet Check regex logic and Jetpack_User_Agent_Info::is_tablet() check. The $juai property is only set for the integration ( unit ) tests
	 * @return boolean
	 */
	private function tablet_check() {
		// jetpack_is_mobile will return FALSE for tablets; so we first check if it is false, then we run it
		// against the is_tablet() method which calls internal tablet methods; if this returns tablet = true
		// then we return tablet. As a final fail safe there are many generic tablet user_agents that are returned
		// as mobiles or desktops, so the basic preg_match checks for a match of 'tablet' in the user agent string
		// if it finds it then it will return 'tablet'
		if ( ! empty( $this->juai ) ) {
			if (
				true == $this->juai->is_tablet() ||
				preg_match( '/tablet/i', $this->get_user_agent() )
			) {
				return true;
			}
		} else if ( class_exists( 'Jetpack_User_Agent_Info' ) ) {
			if (
				true == \Jetpack_User_Agent_Info::is_tablet() ||
				preg_match( '/tablet/i', $this->get_user_agent() )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Type - will return the device type ( as string ) running the current page.
	 * @return string the device type; desktop, mobile or tablet
	 */
	public function type() {
		if ( function_exists( 'jetpack_is_mobile' ) ) {
			if ( $this->valid_user_agent( $this->get_user_agent() ) ) {
				if ( $this->is_tablet() ) {
					return 'tablet';
				}

				if ( $this->is_mobile() ) {
					return 'mobile';
				}

				// When reaching here, if jetpack_is_mobile returns FALSE and it is not a tablet then it is a desktop
				// So return 'desktop'
				return 'desktop';
			}
		}

		return 'mobile';
	}
}
