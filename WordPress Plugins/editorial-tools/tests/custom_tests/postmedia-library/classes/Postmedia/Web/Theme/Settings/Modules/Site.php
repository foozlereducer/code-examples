<?php

namespace Postmedia\Web\Theme\Settings\Modules;

use Postmedia\Web\Utilities;
use Postmedia\Web\Theme\Settings\Module;

class Site extends Module {

	/**
	 * An array of sites that the(child) theme supports [optional]
	 * @var array
	 */
	public $supported_sites;



	public function __construct( $supported_sites = array() ) {
		$this->supported_sites = $supported_sites;

		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue admin scripts
	 * @return void
	 */
	public function action_admin_enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_script( 'site-settings-image-uploader-admin-js', plugins_url( '', __FILE__ ) . 'Site/js/image-uploader-admin.js', array( 'jquery' ) );
	}

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'site';

		register_setting( $this->option_group, 'theme_current_site', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_city', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_logo_url', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_wordmark_url', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_newsletter_logo_url', array( $this, 'sanitize_input_text' ) );
		register_setting( $this->option_group, 'pn_theme_morestuff', array( $this, 'sanitize_input_text' ) );

		add_settings_section( $section, 'Site Settings', '__return_false', $this->option_group );
		add_settings_field( 'theme_current_site', 'Current Site', array( $this, 'render_current_site' ), $this->option_group, $section, array( 'key' => 'theme_current_site' ) );
		add_settings_field( 'pn_theme_city', 'Site Name', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'pn_theme_city' ) );
		add_settings_field( 'pn_theme_logo_url', 'Site Logo', array( $this, 'render_file_upload' ), $this->option_group, $section, array( 'key' => 'pn_theme_logo_url' ) );
		add_settings_field( 'pn_theme_wordmark_url', 'Site Wordmark', array( $this, 'render_file_upload' ), $this->option_group, $section, array( 'key' => 'pn_theme_wordmark_url' ) );
		add_settings_field( 'pn_newsletter_logo_url', 'Newsletter Logo', array( $this, 'render_file_upload' ), $this->option_group, $section, array( 'key' => 'pn_newsletter_logo_url' ) );
	}

	/**
	 * Render multi select input
	 * @param  array $args
	 * @return void
	 */
	public function render_current_site() {
		if ( ! is_array( $this->supported_sites ) || count( $this->supported_sites ) == 0 ) {
			?>
			<select disabled><option value="">Theme Supports Single Site</option></select>
			<?php
		}

		if ( is_array( $this->supported_sites ) && count( $this->supported_sites ) > 0 ) {
			?>
			<select id="theme_current_site" name="theme_current_site">
				<?php

				$current_site = get_option( 'theme_current_site', null );

				foreach ( $this->supported_sites as $key => $description ) {
					?>
					<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $current_site, $key ) ?>><?php echo esc_html( $description ) ?></option>
					<?php
				}

				?>
			</select>
			<?php
		}
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

	/**
	 * Return the currently configured site. Based on 'supported_sites' set by child theme.
	 * If none have been configured yet, the first in the list will be returned.
	 * @return string
	 */
	public function current_site() {
		$current_site = get_option( 'theme_current_site', null );

		if ( ! $current_site || '' == $current_site ) {
			if ( is_array( $this->supported_sites ) && count( $this->supported_sites ) > 0 ) {
				// By default, if the theme has set the sites it supports choose the first
				foreach ( $this->supported_sites as $key => $description ) {
					$current_site = $key;

					break;
				}
			}
		}

		return $current_site;
	}
}
