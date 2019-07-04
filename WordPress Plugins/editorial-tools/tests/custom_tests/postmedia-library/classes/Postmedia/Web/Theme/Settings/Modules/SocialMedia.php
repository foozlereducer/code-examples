<?php

namespace Postmedia\Web\Theme\Settings\Modules;

use Postmedia\Web\Theme\Settings\Module;

class SocialMedia extends Module {

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'social-media';

		register_setting( $this->option_group, 'pn_socialmedia_connect_text', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_socialmedia_site', array( $this, 'sanitize_url_list' ) );

		add_settings_section( $section, 'Connect Social Media Settings', '__return_false', $this->option_group );
		add_settings_field( 'pn_socialmedia_connect_text', 'Connect More Menu Text', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_socialmedia_connect_text' ) );
		add_settings_field( 'pn_socialmedia_site[facebook]', 'Facebook Url', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_socialmedia_site[facebook]' ) );
		add_settings_field( 'pn_socialmedia_site[twitter]', 'Twitter Url', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_socialmedia_site[twitter]' ) );
		add_settings_field( 'pn_socialmedia_site[instagram]', 'Instagram Url', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_socialmedia_site[instagram]' ) );
		add_settings_field( 'pn_socialmedia_site[tumblr]', 'Tumblr Url', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_socialmedia_site[tumblr]' ) );
		add_settings_field( 'pn_socialmedia_site[pinterest]', 'Pinterest Url', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_socialmedia_site[pinterest]' ) );
		add_settings_field( 'pn_socialmedia_site[youtube]', 'YouTube Url', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_socialmedia_site[youtube]' ) );
		add_settings_field( 'pn_socialmedia_site[reddit]', 'Reddit Url', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_socialmedia_site[reddit]' ) );
		add_settings_field( 'pn_socialmedia_site[rss]', 'RSS Url', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_socialmedia_site[rss]' ) );
		add_settings_field( 'pn_socialmedia_site[email]', 'Email Alerts Url', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_socialmedia_site[email]' ) );
	}
}
