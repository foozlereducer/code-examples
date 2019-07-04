<?php

namespace Postmedia\Web\Theme\Settings\Modules;

use Postmedia\Web\Theme\Settings\Module;

class LikeItBuyIt extends Module {

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @return void
	 */
	public function register_settings() {
		$section = 'likeit-buyit';

		register_setting( $this->option_group, 'libi_options_page', array( $this, 'libi_options_page_validate' ) );

		add_settings_section( $section, 'Like it Buy it Options', '__return_false', $this->option_group );
		add_settings_field( 'pn_like_it_buy_it_option_id', 'Enable', array( $this, 'render_input_checkbox' ), $this->option_group, $section, array( 'key' => 'libi_options_page[pn_like_it_buy_it_option]' ) );
		add_settings_field( 'pn_like_it_buy_it_image_url_id', 'Image', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'libi_options_page[pn_like_it_buy_it_image_url]' ) );
		add_settings_field( 'pn_like_it_buy_it_title_id', 'Title Text', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'libi_options_page[pn_like_it_buy_it_title]' ) );
		add_settings_field( 'pn_like_it_buy_it_url_id', 'URL', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'libi_options_page[pn_like_it_buy_it_url]' ) );
		add_settings_field( 'pn_like_it_buy_it_content_id', 'Content', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'libi_options_page[pn_like_it_buy_it_content]' ) );
		add_settings_field( 'pn_like_it_buy_it_button_id', 'Button', array( $this, 'render_input_text' ), $this->option_group, $section, array( 'key' => 'libi_options_page[pn_like_it_buy_it_button]' ) );
	}

	/**
	 * Validate settings
	 * @param  array $input
	 * @return array
	 */
	public function libi_options_page_validate( $input ) {
		$options = '';

		$options['pn_like_it_buy_it_option'] = $this->sanitize_input_checkbox( $input['pn_like_it_buy_it_option'] );
		$options['pn_like_it_buy_it_image_url'] = esc_url_raw( $input['pn_like_it_buy_it_image_url'] );
		$options['pn_like_it_buy_it_title'] = $this->sanitize_input_text( $input['pn_like_it_buy_it_title'] );
		$options['pn_like_it_buy_it_url'] = esc_url_raw( $input['pn_like_it_buy_it_url'] );
		$options['pn_like_it_buy_it_content'] = $this->sanitize_input_text( $input['pn_like_it_buy_it_content'] );
		$options['pn_like_it_buy_it_button'] = esc_url_raw( $input['pn_like_it_buy_it_button'] );

		return $options;
	}

	/**
	 * Display/Render the LIBI section on the site
	 * @return void
	 */
	public static function display() {
		$options = get_option( 'libi_options_page' );

		if ( $options['pn_like_it_buy_it_option'] ) {
			?>
			<div class="likeitbuyit" style="float: left; width: 100%; padding: 20px 0 20px;">
				<a href="<?php echo esc_url( $options['pn_like_it_buy_it_url'] ); ?>" target="_blank">
					<img src="<?php echo esc_url( $options['pn_like_it_buy_it_image_url'] ); ?>" alt="<?php echo esc_attr( $options['pn_like_it_buy_it_title'] ); ?>" border="0" style="width: 100%;" /><br />
				</a>
				<a href="<?php echo esc_url( $options['pn_like_it_buy_it_url'] ); ?>" target="_blank" style="text-decoration: none;">
					<p><?php echo esc_html( $options['pn_like_it_buy_it_content'] ); ?></p>
				</a>
				<a href="<?php echo esc_url( $options['pn_like_it_buy_it_url'] ); ?>" target="_blank">
					<img src="<?php echo esc_url( $options['pn_like_it_buy_it_button'] ); ?>" alt="<?php echo esc_attr( $options['pn_like_it_buy_it_title'] ); ?>" border="0" align="right" />
				</a>
			</div>
			<?php
		}
	}
}
