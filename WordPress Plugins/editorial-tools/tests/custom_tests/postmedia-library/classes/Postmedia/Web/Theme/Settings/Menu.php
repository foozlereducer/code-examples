<?php

namespace Postmedia\Web\Theme\Settings;

class Menu {

	/**
	 * Key for this menu
	 * @var string
	 */
	public $key;

	/**
	 * Menu Name
	 * @var string
	 */
	public $name;

	/**
	 * List of Settings Modules
	 * @var array Modules
	 */
	public $modules = array();


	public function __construct( $key, $name ) {
		$this->key = $key;
		$this->name = $name;

		add_action( 'admin_menu', array( $this, 'action_register_menu' ) );
		add_action( 'admin_init', array( $this, 'action_register_settings' ) );
	}

	/**
	 * Register Menu
	 * @return void
	 */
	public function action_register_menu() {
		add_options_page( $this->name, $this->name, 'manage_options', 'theme_settings-' . $this->key, array( $this, 'options_setup' ) );
	}

	/**
	 * Register module settings
	 * @return void
	 */
	public function action_register_settings() {
		foreach ( $this->modules as $module ) {
			$module->register_settings();
		}
	}

	/**
	 * Output for options page
	 * @return void
	 */
	public function options_setup() {
		?>
		<div class="wrap">
			<h2><?php echo esc_html( $this->name ) ?></h2>

			<form method="post" action="options.php">
				<?php

				settings_fields( $this->key );
				do_settings_sections( $this->key );

				submit_button();

				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Add a settings module to be loaded
	 * @param string $key
	 * @param Module $module
	 */
	public function add_module( $key, $module ) {
		// Set option group to match menu key
		$module->option_group = $this->key;

		$this->modules[ $key ] = $module;
	}
}
