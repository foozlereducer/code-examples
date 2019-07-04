<?php
/**
 * Postmedia Library
 *
 * Provides a common library of classes to support themes, plugins & more.
 * This is not a plugin and should never be loaded as an actual plugin.
 *
 * Author: Chris Murphy
 */

require_once( plugin_dir_path( __FILE__ ) . 'classes/Postmedia/ClassLoader.php' );

define( 'POSTMEDIA-LIBRARY-VERSION', '2.0.13' );
define( 'POSTMEDIA_LIBRARY_DIR', plugin_dir_path( __FILE__ ) );

$postmedia_library_class_loader = new Postmedia\ClassLoader();
$postmedia_library_class_loader->add_prefix( 'Postmedia', plugin_dir_path( __FILE__ ) . 'classes/Postmedia', true );
$postmedia_library_class_loader->add_prefix( 'Wholesite', plugin_dir_path( __FILE__ ) . 'classes/Wholesite', true );
$postmedia_library_class_loader->register();

new Postmedia\Library();
