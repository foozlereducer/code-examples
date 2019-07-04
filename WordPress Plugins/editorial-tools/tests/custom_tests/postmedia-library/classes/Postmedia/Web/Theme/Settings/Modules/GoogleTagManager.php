<?php

namespace Postmedia\Web\Theme\Settings\Modules;

use Postmedia\Web\Theme\Settings\Module;

class GoogleTagManager extends Module {

	/**
	 * Register Settings for Module
	 *  - Abstract implementation
	 * @param  string $option_group
	 * @return void
	 */
	public function register_settings() {
		$section = 'google-tag-manager';

		register_setting( $this->option_group, 'google_tag_manager_enabled', array( $this, 'sanitize_input_checkbox' ) );
		register_setting( $this->option_group, 'google_tag_manager_ids', array( $this, 'sanitize_input_text' ) );

		add_settings_section(
			$section,
			'Google Tag Manager',
			'__return_false',
			$this->option_group
		);

		add_settings_field(
			'google_tag_manager_enabled',
			'Enable',
			array( $this, 'render_input_checkbox' ),
			$this->option_group,
			$section,
			array( 'key' => 'google_tag_manager_enabled' )
		);

		add_settings_field(
			'google_tag_manager_ids',
			'ID(s)',
			array( $this, 'render_input_text' ),
			$this->option_group,
			$section,
			array( 'key' => 'google_tag_manager_ids', 'help' => 'Separate multiple tags by comma (eg. ABC123,XYZ543). Limit of 2.' )
		);
	}



	public function __construct() {
		add_action( 'after_body_tag', array( $this, 'action_render_google_tag_manager' ) );
	}

	/**
	 * Render the Google Tag Manager Code
	 * Limited to two ID's
	 * @return  void
	 */
	public function action_render_google_tag_manager() {
		$enabled = ( boolean ) get_option( 'google_tag_manager_enabled' );
		$ids = ( string ) get_option( 'google_tag_manager_ids' );

		$id_list = explode( ',', $ids );

		if ( $enabled && count( $id_list ) > 0 ) {
			for ( $i = 0; $i < count( $id_list ) && $i < 2; $i++ ) {
				$id = trim( $id_list[ $i ] );

				?>
				<noscript><iframe src="//www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $id ) ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
				<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
				new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
				j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
				'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
				})(window,document,"script","dataLayer",<?php echo wp_json_encode( $id ) ?>);</script>
				<?php
			}
		}
	}
}
