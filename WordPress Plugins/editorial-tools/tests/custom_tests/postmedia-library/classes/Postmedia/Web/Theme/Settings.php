<?php

namespace Postmedia\Web\Theme;

use Postmedia\Web\Theme\Settings\Menu;

class Settings {

	/**
	 * An array of sites that the(child) theme supports [optional]
	 * @var array
	 */
	public $supported_sites = array();

	/**
	 * List of Menus
	 * @var array
	 */
	public $menus = array();



	public function __construct() {
		// Create menus
		$menu_common = new Menu( 'common', 'Theme (Common)' );
		$menu_analytics = new Menu( 'analytics', 'Theme (Analytics)' );

		// Add menus
		$this->menus['common'] = $menu_common;
		$this->menus['analytics'] = $menu_analytics;
	}

	/**
	 * Initialize Settings
	 * @return void
	 */
	public function initialize() {
		// Common Menu Modules
		$this->menus['common']->add_module( 'site', new Settings\Modules\Site( $this->supported_sites ) );
		$this->menus['common']->add_module( 'storyline-template', new Settings\Modules\StorylineTemplate() );
		$this->menus['common']->add_module( 'janrain-capture', new Settings\Modules\JanrainCapture() );
		$this->menus['common']->add_module( 'pianovx', new Settings\Modules\PianoVX() );
		$this->menus['common']->add_module( 'seo', new Settings\Modules\SEO() );
		$this->menus['common']->add_module( 'images', new Settings\Modules\Images() );
		$this->menus['common']->add_module( 'social-media', new Settings\Modules\SocialMedia() );
		$this->menus['common']->add_module( 'pixels', new Settings\Modules\Pixels() );
		$this->menus['common']->add_module( 'iperceptions', new Settings\Modules\Iperceptions() );
		$this->menus['common']->add_module( 'google-survey', new Settings\Modules\GoogleSurvey() );
		$this->menus['common']->add_module( 'likeit-buyit', new Settings\Modules\LikeItBuyIt() );

		// Analytics Menu Modules
		$this->menus['analytics']->add_module( 'google-tag-manager', new Settings\Modules\GoogleTagManager() );
	}

	/**
	 * Return the currently configured site.
	 * @return string
	 */
	public function current_site() {
		if ( ! isset( $this->menus['common']->modules['site'] ) ) {
			return false;
		}

		return $this->menus['common']->modules['site']->current_site();
	}
}
