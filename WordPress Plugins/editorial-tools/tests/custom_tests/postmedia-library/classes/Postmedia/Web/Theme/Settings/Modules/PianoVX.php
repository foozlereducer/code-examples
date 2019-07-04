<?php

namespace Postmedia\Web\Theme\Settings\Modules;

use Postmedia\Web\TemplateEngine;
use Postmedia\Web\Theme\Settings\Module;
use Postmedia\Web\Utilities;

class PianoVX extends Module {

	public $template_engine;

	public $environment_endpoints = array();


	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'pianovx';

		register_setting( $this->option_group, 'pn_theme_piano_enable', array( $this, 'sanitize_input_checkbox' ) );
		register_setting( $this->option_group, 'pn_theme_piano_environment', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_piano_app_id', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_paywall_exempt_ie', array( $this, 'sanitize_input_checkbox' ) );

		add_settings_section( $section, 'Piano VX', '__return_false', $this->option_group );
		add_settings_field( 'pn_theme_piano_enable', 'Enable', array( $this, 'render_input_checkbox' ), $this->option_group, $section, array( 'key' => 'pn_theme_piano_enable' ) );
		add_settings_field( 'pn_theme_piano_environment', 'Environment', array( $this, 'render_input_dropdown' ), $this->option_group, $section, array( 'key' => 'pn_theme_piano_environment', 'options' => $this->environment_endpoints ) );
		add_settings_field( 'pn_theme_piano_app_id', 'Piano VX App ID', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_theme_piano_app_id' ) );
		add_settings_field( 'pn_theme_paywall_exempt_ie', 'IE Users Exempt', array( $this, 'render_input_checkbox' ), $this->option_group, $section, array( 'key' => 'pn_theme_paywall_exempt_ie', 'help' => 'This disables the paywall for IE users. Matching user agent with "MSIE" or "Trident".' ) );
	}

	public function __construct() {
		$this->environment_endpoints = array(
			'Production' => '//experience.tinypass.com/xbuilder/experience/load',
			'Sandbox' => '//sandbox.tinypass.com/xbuilder/experience/load',
		);

		// Render on desktop only
		if ( ! Utilities::is_mobile() && ! is_admin() ) {
			$pianovx_enabled = ( boolean ) get_option( 'pn_theme_piano_enable' );

			if ( $pianovx_enabled && ! is_admin() ) {
				add_action( 'wp_head', array( $this, 'action_render_piano_vx_js' ) );

				wp_enqueue_script( 'piano-js', plugins_url( '', __FILE__ ) . 'PianoVX/js/piano-vx.js', array( 'jquery' ), '1.0.0', true );

				$endpoint = get_option( 'pn_theme_piano_environment', '' );

				if ( empty( $endpoint ) && isset( array_values( $this->environment_endpoints )[0] ) ) {
					$endpoint = array_values( $this->environment_endpoints )[0];
				}

				wp_localize_script(
					'piano-js',
					'postmedia_piano',
					array(
						'paywall_exempt_ie' => ( boolean ) get_option( 'pn_theme_paywall_exempt_ie' ),
						'endpoint' => $endpoint,
					)
				);

				$this->template_engine = new TemplateEngine( POSTMEDIA_LIBRARY_DIR . 'templates/pianovx/', false );
				$this->template_engine->initialize();
			}
		}
	}

	/**
	 * Action - Render Piano JS
	 * @return void
	 */
	public function action_render_piano_vx_js() {
		if ( is_admin() ) {
			return;
		}

		?>
		<script type='text/javascript'>
			/* <![CDATA[ */
			<?php
			$piano_app_id = trim( get_option( 'pn_theme_piano_app_id' ) );
			if ( ! empty( $piano_app_id ) ) { ?>
 				var pn_theme_piano_app_id = "<?php echo esc_html( $piano_app_id ); ?>";
 			<?php } ?>
			/* ]]> */
		</script>
		<?php
	}
}
