<?php

namespace Postmedia\Library\Settings;

use Postmedia\Web\Theme\Settings\Module;

class WCM extends Module {

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'wcm';

		register_setting( $this->option_group, 'wcm_push_enabled', array( $this, 'sanitize_input_checkbox' ) );
		register_setting( $this->option_group, 'wcm_api_url', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'wcm_read_key', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'wcm_write_key', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'wcm_client_id', array( $this, 'sanitize_input_text' ) );

		add_settings_section( $section, 'WCM API Settings', '__return_false', $this->option_group );
		add_settings_field( 'wcm_push_enabled', 'Enable Push to WCM', array( $this, 'render_input_checkbox' ), $this->option_group, $section, array( 'key' => 'wcm_push_enabled' ) );
		add_settings_field( 'wcm_api_url', 'API Url', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'wcm_api_url' ) );
		add_settings_field( 'wcm_read_key', 'Read Key', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'wcm_read_key' ) );
		add_settings_field( 'wcm_write_key', 'Write Key', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'wcm_write_key' ) );
		add_settings_field( 'wcm_client_id', 'Client ID', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'wcm_client_id' ) );

		$log_section = 'external_logging';
		register_setting( $this->option_group, 'external_logging_enabled', array( $this, 'sanitize_input_checkbox' ) );
		register_setting( $this->option_group, 'external_logging_url', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'external_logging_key', array( $this, 'sanitize_input_text' ) );

		add_settings_section( $log_section, 'External Logging Settings', '__return_false', $this->option_group );
		add_settings_field( 'external_logging_enabled', 'Enable External Logging', array( $this, 'render_input_checkbox' ), $this->option_group, $log_section, array( 'key' => 'external_logging_enabled' ) );
		add_settings_field( 'external_logging_url', 'External Logging Url', array( $this, 'render_input_text' ), $this->option_group, $log_section, array( 'key' => 'external_logging_url' ) );
		add_settings_field( 'external_logging_key', 'Account Key', array( $this, 'render_input_text' ), $this->option_group, $log_section, array( 'key' => 'external_logging_key' ) );

	}
}
