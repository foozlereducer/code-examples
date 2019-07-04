<?php

namespace Postmedia\Web\Theme\Settings\Modules;

use Postmedia\Web\Theme\Settings\Module;

class SEO extends Module {

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'seo';

		register_setting( $this->option_group, 'google_site_verification', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'google_plus', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'seo_logo', array( $this, 'sanitize_input_text' ) );

		add_settings_section( $section, 'SEO Settings', '__return_false', $this->option_group );
		add_settings_field( 'google_site_verification', 'Google Site Verifiation Content ID', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'google_site_verification' ) );
		add_settings_field( 'google_plus', 'Google Plus URL Only', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'google_plus' ) );
		add_settings_field( 'seo_logo', 'SEO Publisher Logo', array( $this, 'render_file_upload' ), $this->option_group, $section, array( 'key' => 'seo_logo' ) );
	}

	/**
	 * Render file upload input
	 * @param  array $args
	 * @return void
	 */
	public function render_file_upload( $args ) {
		if ( '' != esc_attr( get_option( $args['key'] ) ) ) { ?>
			<img src="<?php echo esc_attr( get_option( $args['key'] ) ); ?>" style="max-width:25em; display:block; margin-bottom:10px;">
		<?php } ?>
		<input type="text" class="regular-text upload_image" name="<?php echo esc_attr( $args['key'] ); ?>" value="<?php echo esc_attr( get_option( $args['key'] ) ); ?>" /><br>
		<input style="margin-top:10px;" class="upload_image_button" class="button" type="button" value="Upload Image" />
		<?php
	}
}
