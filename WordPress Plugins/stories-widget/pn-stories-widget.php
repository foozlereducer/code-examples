<?php
/***************************************************************************
 Plugin Name: Postmedia Stories Widget
 Version: 0.0.1
 Author: Postmedia Network Inc.
 Contributor: Steve Browning ver - 0.0.1 converted by Joe Mittler
 Copyright: © 2014 Postmedia Network Inc.
 Description: Displays a feed with a thumbnail, title, and excerpt
***************************************************************************/

class PN_Stories_Widget_Init {


	protected $pn_plugin_url;

	private static $instance;

	/**
	 * instance(): singleton
	 *
	 * @return self instance
	 */
	public static function instance() {

		if ( ! self::$instance ) {
			$self           = __CLASS__;
			self::$instance = new $self();
		}
		return self::$instance;
	}

	public function pn_stories_widget() {

		/* Define Paths */
		$this->pn_plugin_url = plugins_url( '', __FILE__ );

		// Includes
		add_action( 'wp_enqueue_scripts', array( $this, 'pn_load_public_css' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'pn_register_admin_css' ) );
	}

	function pn_load_public_css() {

		wp_register_style( 'pn_feed_stories_css', $this->pn_plugin_url . '/pn-stories-widget.css' );

		wp_enqueue_style( 'pn_feed_stories_css' );
	}

	public function pn_register_admin_css() {

		wp_register_style( 'pn_admin_stories_widget_css', $this->pn_plugin_url . '/pn-admin-stories-widget.css' );

		wp_enqueue_style( 'pn_admin_stories_widget_css' );
	}
}

// New Plugin
$pn_story_widget = PN_Stories_Widget_Init::instance();

// enqueue css
$pn_story_widget->pn_stories_widget();

// add widget
require_once plugin_dir_path( __FILE__ ) . 'pn-widgets-stories.php';

add_action(
	'widgets_init',
	function() {
		register_widget( 'pn_Stories_Widget' );
	}
);
