<?php
namespace Postmedia\Plugins;

class Edash {
	public function __construct() {
		// Paths
		$_paths = new Edash\Paths;

		//Admin
		$_admin = new Edash\Admin();

		// Taxonomy object
		$_taxonomy = new Edash\Taxonomy();
		$_taxonomy->set_taxonomy( 'postmedia-plugin-editorial-dashboard' );

		// Storage Class
		$_storage = new \Postmedia\Web\Storage();

		//VIP: We believe this is the source of many millions of WLO queries
		if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$_storage->initialize_storage( $_taxonomy->get_taxonomy() );
		}

		// Pointer
		$_pointer = new Edash\Pointer( new Edash\OriginSite );

		// Settings
		$_settings = new Edash\Settings( $_taxonomy );

		$_trunk = new Edash\Trunk();
		$_trunk->initialize( $_paths , $_settings, $_storage );

		// Copy object
		$_postcopy = new Edash\PostCopy( new Edash\OriginSite );

		// API Proxy
		$_api_proxy = new Edash\ApiProxy( $_paths );
	}
}
