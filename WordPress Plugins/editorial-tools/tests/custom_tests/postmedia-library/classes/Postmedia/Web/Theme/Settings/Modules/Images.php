<?php

namespace Postmedia\Web\Theme\Settings\Modules;

use Postmedia\Web\Utilities;
use Postmedia\Web\Theme\Settings\Module;

class Images extends Module {

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'images';

		register_setting( $this->option_group, 'pn_theme_desktop_tablet_image_compression', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_mobile_image_compression', array( $this, 'sanitize_input_text' ) );

		add_settings_section( $section, 'Image Compression Settings', '__return_false', $this->option_group );
		add_settings_field( 'pn_theme_desktop_tablet_image_compression', 'Desktop and Tablet Image Compression', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_theme_desktop_tablet_image_compression' ) );
		add_settings_field( 'pn_theme_mobile_image_compression', 'Mobile Image Compression', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_theme_mobile_image_compression' ) );
	}



	public function __construct() {
		add_action( 'wp_head', array( $this, 'action_set_image_quality' ) );
	}

	/**
	 * Set image quality settings for VIP delivered assets
	 * @return  void
	 */
	public function action_set_image_quality() {
		$desktop_tablet_image_quality = trim( get_option( 'pn_theme_desktop_tablet_image_compression' ) );
		$mobile_image_quality = trim( get_option( 'pn_theme_mobile_image_compression' ) );
		$default_quality = 60;

		if ( Utilities::is_mobile() && ! empty( $mobile_image_quality ) ) {
			wpcom_vip_set_image_quality( $mobile_image_quality, 'all' );
		} else if ( ! empty( $desktop_tablet_image_quality ) ) {
			wpcom_vip_set_image_quality( $desktop_tablet_image_quality, 'all' );
		} else {
			wpcom_vip_set_image_quality( $default_quality, 'all' );
		}
	}
}
