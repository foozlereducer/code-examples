<?php

namespace Postmedia\Web\Theme\Settings\Modules;

use Postmedia\Web\Theme\Settings\Module;

class StorylineTemplate extends Module {

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'storyline-template';

		register_setting( $this->option_group, 'pn_theme_storyline_app_button_colour', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_storyline_app_button_colour_hover', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_storyline_header_text', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_storyline_app_link_android', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_storyline_app_link_iphone', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_storyline_app_link_desktop', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_storyline_footer_text', array( $this, 'sanitize_input_text' ) );

		add_settings_section( $section, 'Storyline Template Settings', '__return_false', $this->option_group );
		add_settings_field( 'pn_theme_storyline_app_button_colour', 'Get the App Button Colour', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_theme_storyline_app_button_colour' ) );
		add_settings_field( 'pn_theme_storyline_app_button_colour_hover', 'Get the App Button Hover Colour', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_theme_storyline_app_button_colour_hover' ) );
		add_settings_field( 'pn_theme_storyline_header_text', 'Header Text', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_theme_storyline_header_text' ) );
		add_settings_field( 'pn_theme_storyline_app_link_android', 'App Android URL', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_theme_storyline_app_link_android' ) );
		add_settings_field( 'pn_theme_storyline_app_link_iphone', 'App iPhone URL', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_theme_storyline_app_link_iphone' ) );
		add_settings_field( 'pn_theme_storyline_app_link_desktop', 'App Desktop Page URL', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_theme_storyline_app_link_desktop' ) );
		add_settings_field( 'pn_theme_storyline_footer_text', 'Footer Read More Text', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_theme_storyline_footer_text' ) );
	}
}
