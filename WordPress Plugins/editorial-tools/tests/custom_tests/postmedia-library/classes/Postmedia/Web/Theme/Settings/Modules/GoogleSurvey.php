<?php

namespace Postmedia\Web\Theme\Settings\Modules;

use Postmedia\Web\Theme\Settings\Module;

class GoogleSurvey extends Module {

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'google-survey';

		register_setting( $this->option_group, 'google_survey_enabled', array( $this, 'sanitize_input_checkbox' ) );

		add_settings_section( $section, 'Google Survey', '__return_false', $this->option_group );
		add_settings_field( 'google_survey_enabled', 'Enable', array( $this, 'render_input_checkbox' ), $this->option_group, $section, array( 'key' => 'google_survey_enabled' ) );
	}



	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'action_enqueue_scripts' ) );
	}

	/**
	 * Enqueue JS
	 * @return  void
	 */
	public function action_enqueue_scripts() {
		if ( is_admin() ) {
			return;
		}

		// Only include survey on post pages that are not a gallery and do not have a featured video
		if ( is_single() && ! get_post_gallery() && ! get_post_meta( get_the_ID(), 'pn_featured_video_id', true ) ) {
			wp_register_script( 'google-survey', plugins_url( '', __FILE__ ) . 'GoogleSurvey/js/google-survey.js', array(), '1.0', false );
			wp_register_script( 'google-survey-caller', plugins_url( '', __FILE__ ) . 'GoogleSurvey/js/google-survey-caller.js', array(), '1.0', true );

			$google_survey_enabled = ( boolean ) get_option( 'google_survey_enabled' );

			if ( $google_survey_enabled ) {
				wp_enqueue_script( 'google-survey' );
				wp_enqueue_script( 'google-survey-caller' );
			}
		}
	}
}
