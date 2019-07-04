<?php

namespace Postmedia\Web\Theme\Settings\Modules;

use Postmedia\Web\Theme\Settings\Module;

class Pixels extends Module {

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'pixels';

		register_setting( $this->option_group, 'retargeting_pixel_cat', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'retargeting_pixel_tag', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'retargeting_pixel_sub_cat', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'retargeting_pixel_id', array( $this, 'sanitize_input_text' ) );

		add_settings_section( $section, 'Retargeting Pixel Settings', '__return_false', $this->option_group );
		add_settings_field( 'retargeting_pixel_cat', 'Retargeting Pixel Category', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'retargeting_pixel_cat' ) );
		add_settings_field( 'retargeting_pixel_tag', 'Retargeting Pixel Tag', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'retargeting_pixel_tag' ) );
		add_settings_field( 'retargeting_pixel_id', 'Secondary Pixel ID', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'retargeting_pixel_id' ) );
		add_settings_field( 'retargeting_pixel_sub_cat', 'Secondary Pixel Subscription Page Category', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'retargeting_pixel_sub_cat' ) );
	}



	public function __construct() {
		add_action( 'wp_footer', array( $this, 'action_render_retargeting_pixel_js' ), 1000 );
	}

	/**
	 * Set image quality settings for VIP delivered assets
	 * @return  void
	 */
	public function action_render_retargeting_pixel_js() {
		if ( is_admin() ) {
			return;
		}

		?>
		<!-- Google Code for Remarketing Tag -->	
		<script type="text/javascript">
			/* <![CDATA[ */
			var google_conversion_id = 990309138;
			var google_custom_params = window.google_tag_params;
			var google_remarketing_only = true;
			/* ]]> */
		</script>
		<?php

		function pn_googleadservices_js() {
			wp_enqueue_script( 'pn_googleadservices', '//www.googleadservices.com/pagead/conversion.js' );
		}
		add_action( 'wp_enqueue_scripts', 'pn_googleadservices_js' );

		?>
		<noscript>
			<div style="display:inline;">
			<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/990309138/?value=0&amp;guid=ON&amp;script=0"/>
			</div>
		</noscript>		
		<?php
	}
}
