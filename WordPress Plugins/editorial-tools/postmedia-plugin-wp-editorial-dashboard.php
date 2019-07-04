<?php
/*
Plugin Name: Editorial Dashboard
Plugin Uri: https://github.com/Postmedia-Digital/postmedia-plugin-wp-editorial-dashboard
Description: Editorial dashboard manages WCM data; search, access, copy and request content from WCM.
Contributors:
-- Charles Jaimet ~ Query Builder Architect
-- Rob Clarkson ~ JavaScript Query Builder Business Logic Code
-- Matt Garvin ~ Query Builder business logic to front-end wiring
-- Sergey Dragunsky ~ Query Builder front-end JavaScript / HTML
-- Steve Browning ~ WordPress plugin JavaScript integration & PHP unit tests
Author URI: http://www.postmedia.com/
License: Private;

Version: 1.0
March 14th, 2017
Overview

Version 1.0 features the ability for Editors to login to the wp-admin, go to the Editorial Dashboard
and search for stories that would fit the Editors' preference to create as a pointer. When a post is
saved it will create a entry as a pointers custom post type and be added to a list that is most often
used as a site 'Featured Stories' lists

In future versions of this dashboards, it will allow editors to edit, and create lists

some test comment
*/

define( 'PN_EDASH_URI', plugins_url( '', __FILE__ ) . '/' );

// Initialize Postmedia Library
if ( is_dir( WP_CONTENT_DIR . '/themes/vip' ) ) {
	require_once( WP_CONTENT_DIR . '/themes/vip/postmedia-plugins/postmedia-library/init.php' );
} else {
	require_once( WP_CONTENT_DIR . '/themes/postmedia-plugins/postmedia-library/init.php' );
}

// Set the classloader up and bind Postmedia namespace both to local classes/Postmedia and postmedia-library
$class_loader = new Postmedia\ClassLoader();
$class_loader->add_prefix( 'Postmedia',  plugin_dir_path( __FILE__ ) . 'classes/Postmedia', true );
$class_loader->register();

// Edash Main
$pn_edash = new Postmedia\Plugins\Edash;
