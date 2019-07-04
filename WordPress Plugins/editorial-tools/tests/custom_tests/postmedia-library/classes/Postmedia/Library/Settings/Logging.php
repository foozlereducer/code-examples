<?php

namespace Postmedia\Library\Settings;

use Postmedia\Web\Theme\Settings\Module;

class Logging extends Module {

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'external_logging';

		register_setting( $this->option_group, 'external_logging_enabled', array( $this, 'sanitize_input_checkbox' ) );
		register_setting( $this->option_group, 'external_logging_url', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'external_logging_hostname', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'external_logging_key', array( $this, 'sanitize_input_text' ) );

		add_settings_section( $section, 'External Logging Settings', '__return_false', $this->option_group );

		add_settings_field( 'external_logging_enabled', 'Enable External Logging', array( $this, 'render_input_checkbox' ), $this->option_group, $section, array( 'key' => 'external_logging_enabled' ) );
		add_settings_field( 'external_logging_url', 'External Logging Url', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'external_logging_url' ) );
		add_settings_field( 'external_logging_key', 'Account Key', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'external_logging_key' ) );
		add_settings_field( 'external_logging_hostname', 'Hostname', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'external_logging_hostname' ) );
	}
}
