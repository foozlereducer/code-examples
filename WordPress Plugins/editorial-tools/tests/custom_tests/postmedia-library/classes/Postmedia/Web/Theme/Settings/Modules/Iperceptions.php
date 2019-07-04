<?php

namespace Postmedia\Web\Theme\Settings\Modules;

use Postmedia\Web\Theme\Settings\Module;

class Iperceptions extends Module {

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'iperceptions';

		register_setting( $this->option_group, 'iperception_enabled', array( $this, 'sanitize_input_checkbox' ) );
		register_setting( $this->option_group, 'iperception_api_key', array( $this, 'sanitize_input_text' ) );

		add_settings_section( $section, 'iPerceptions', '__return_false', $this->option_group );
		add_settings_field( 'iperception_enabled', 'Enable', array( $this, 'render_input_checkbox' ), $this->option_group, $section, array( 'key' => 'iperception_enabled' ) );
		add_settings_field( 'iperception_api_key', 'API Key', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'iperception_api_key' ) );
	}



	public function __construct() {
		add_action( 'wp_footer', array( $this, 'action_render_in_footer' ), 1000 );
	}

	/**
	 * Render JS in footer
	 * @return  void
	 */
	public function action_render_in_footer() {
		if ( is_admin() ) {
			return;
		}

		global $post;

		$iperception_enabled = ( boolean ) get_option( 'iperception_enabled' );
		$iperception_api_key = get_option( 'iperception_api_key' );

		if ( $iperception_enabled ) {
			?>
			<script>/* 2011-2015 iPerceptions, Inc. All rights reserved. Do not distribute.iPerceptions provides this code 'as is' without warranty of any kind, either express or implied.*/window.iperceptionskey = <?php echo '"' . esc_attr( $iperception_api_key ) . '"'; ?>;(function () { var a = document.createElement('script'),b = document.getElementsByTagName('body')[0]; a.type = 'text/javascript'; a.async = true;a.src = '//universal.iperceptions.com/wrapper.js';b.appendChild(a);})();</script>
			<?php
		}
	}
}
