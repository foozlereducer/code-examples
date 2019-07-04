<?php

namespace Postmedia\Library;

use Postmedia\Web\Theme\Settings\Menu;

class Settings {

	/**
	 * List of Menus
	 * @var array
	 */
	public $menus = array();



	public function __construct() {
		// Create menus
		$menu_library = new Menu( 'library', 'Theme (Library)' );

		// Add menus
		$this->menus['library'] = $menu_library;
	}

	/**
	 * Initialize Settings
	 * @return void
	 */
	public function initialize() {
		// Common Menu Modules
		$this->menus['library']->add_module( 'wcm', new Settings\WCM() );
		$this->menus['library']->add_module( 'external_logging', new Settings\Logging() );
	}
}
