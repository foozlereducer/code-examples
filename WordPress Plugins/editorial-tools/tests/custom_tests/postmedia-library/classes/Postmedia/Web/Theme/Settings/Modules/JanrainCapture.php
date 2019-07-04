<?php

namespace Postmedia\Web\Theme\Settings\Modules;

use Postmedia\Web\Theme\Settings\Module;
use Postmedia\Web\Utilities;

class JanrainCapture extends Module {

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'janrain-capture';

		register_setting( $this->option_group, 'pn_theme_janrain_enable', array( $this, 'sanitize_input_checkbox' ) );
		register_setting( $this->option_group, 'pn_theme_janrain_debug', array( $this, 'sanitize_input_checkbox' ) );

		add_settings_section( $section, 'Janrain Capture', '__return_false', $this->option_group );
		add_settings_field( 'pn_theme_janrain_enable', 'Enable Janrain Capture', array( $this, 'render_input_checkbox' ), $this->option_group, $section, array( 'key' => 'pn_theme_janrain_enable', 'help' => 'Used by theme to register proper click events. eg. Janrain vs. Press+' ) );
		add_settings_field( 'pn_theme_janrain_debug', 'Enable Janrain Debug', array( $this, 'render_input_checkbox' ), $this->option_group, $section, array( 'key' => 'pn_theme_janrain_debug', 'help' => 'Turns on Janrain event logging' ) );
	}

	public function __construct() {
		$janrain_debug = ( boolean ) get_option( 'pn_theme_janrain_debug' );
		// Render on desktop only
		if ( $janrain_debug && ! Utilities::is_mobile() && ! is_admin() ) {
			wp_enqueue_script( 'janrain-utils', plugins_url( '', __FILE__ ) . 'JanrainCapture/js/janrain-utils.js', array( 'jquery' ), '1.0.0', true );
		}
	}
}
