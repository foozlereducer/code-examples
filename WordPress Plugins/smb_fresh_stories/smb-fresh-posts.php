<?php
/* 
Plugin Name: SMB Fresh Posts
Plugin URI: http://www.liviam.ca
Description: Add a number of posts to a home or index page, if posts are older than a 
certain age existing posts will be randomized.
Author: Steve Browning
Version: 1.0.1
*/

class smb_Fresh_Posts {

	private $plugin_url;
	private $hook = 'smb_fresh_posts';

	public function __construct() {
		$this->plugin_url = plugin_dir_url( __FILE__ );

		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		}

		require_once( 'smb-widget-fresh-stories.php' );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'widgets_init', array ( $this, 'widgets_init' ) );
	}

	/**********************
	Prepares all the front end JS and CSS
	***********************/
	public function wp_enqueue_scripts() {
		wp_enqueue_style( 'fresh-stories-css', $this->plugin_url . 'styles.css', '', '0.2' );
	}

	/**********************
	Initializes options and other early filters and actions
	***********************/
	public function admin_init() {
		add_action( 'widgets_init', function(){
			register_widget( 'smb_Widget_Fresh_Stories' );
		});
		
	}

	/**********************
	widgets_init(): add widget
	***********************/
    public function widgets_init() {

        register_widget( 'smb_Widget_Fresh_Stories' );
    
    }
}

$smb_fresh_posts = new smb_Fresh_Posts();