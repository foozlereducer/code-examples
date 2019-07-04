<?php

namespace Postmedia\Web;

/**
 * Postmedia Theme Class
 */
class Theme {

	/**
	 * Google API key
	 * @var string
	 */
	public $api_key_google_maps;

	/**
	 * Default list of VIP plugins to load
	 * eg. array( 'plugin_one', array( 'plugin_two_versioned', '1.5' ), 'plugin_three' )
	 * @var array
	 */
	public $vip_plugins;

	/**
	 * Default list of Postmedia plugins to load
	 * @var array
	 */
	public $postmedia_plugins;

	/**
	 * Default list of Postmedia widgets to register
	 * @var array
	 */
	public $postmedia_widgets;

	/**
	 * Navigation menu list
	 * @var array
	 */
	public $navigation_menus;

	/**
	 * Custom query vars to enable
	 * @var array
	 */
	public $custom_query_vars;

	/**
	 * List of site names (stylesheet strings) to disable the CDN for
	 * Eg. 'vip/edmontonjournal-web'
	 * @var array
	 */
	public $disabled_cdn_sites;

	/**
	 * User Whitelist for Management
	 * @var array
	 */
	public $user_management_whitelist;

	/**
	 * A list of valid VIP library names ( Found under '/vip-plugins/lib' )
	 * If one of these is set in $vip_plugins we will load via a different method
	 * NOTE: These will not be loaded unless they are listed in $vip_plugins
	 * @var array
	 */
	private $valid_vip_libraries;

	/**
	 * A list of image sizes to register/add
	 * @var array
	 */
	public $image_sizes;

	/**
	 * Collect or not to collect
	 * @var boolean
	 */
	public $stats_enabled;

	/**
	 * Stats object
	 * @var Stats
	 */
	public $stats;

	/**
	 * Class used to handle event tracking related tasks
	 * @var EventTracking
	 */
	private $event_tracking;

	/**
	 * Capabilities object
	 * @var Capabilities
	 */
	public $capabilities;

	/**
	 * Modifiers object
	 * @var Modifiers
	 */
	public $modifiers;

	/**
	 * SEO object
	 * @var SEO
	 */
	public $seo;

	/**
	 * Settings object
	 * @var Settings
	 */
	public $settings;

	/**
	 * An array of strings that should be added to the Robots.txt file
	 * @var array
	 */
	public $robots_entries;

	/**
	 * Global content_width option used for oembeds, plugins and other stuff
	 * @var int
	 */
	public $content_width;

	/**
	 * Default width for image thumbnails
	 * @var integer
	 */
	public $image_width_thumbnail;



	public function __construct() {
		// Setup default values
		$this->api_key_google_maps = 'AIzaSyAaLwBxvUb6e8JdEy_CvDEMMycm6_5h0BA';

		$this->vip_plugins = array(
			'add-meta-tags-mod',
			'wpcom-allow-contributors-to-upload',
			'co-authors-plus',
			'easy-custom-fields',
			'custom-metadata',
			'edit-flow',
			'zoninator',
			'json-feed',
			'responsive-images',
			'safe-redirect-manager',
			'wp-large-options',
			'adbusters',
			'msm-sitemap',
			'es-wp-query',
			'cache-nav-menu',
		);

		$this->postmedia_plugins = array(
			'easy-sidebars',
			'inform',
			'main-category',
			'related-links',
			'bad-character-cleaner',
			'dfp-ads',
			'analytics-plugin',
			'storyline',
			'paywall-whitelist',
			'pm-advertorial-plugin',
			'photo-galleries',
			'pointer-plugin',
			'pn-ad-preview',
			'pn-video-override',
			'postmedia-geolocation',
			'postmedia-layouts',
			'postmedia-alerts',
			'postmedia-newsroom',
			'custom-feeds',
			'pni-wordpress-to-saxotech-plugin',
			'sponsored-widget',
			'skedtool',
			'pn-widget-device-visibility',
			'all-in-one-video-pack',
		);

		$this->postmedia_widgets = array();

		$this->navigation_menus = array();

		$this->custom_query_vars = array();

		$this->disabled_cdn_sites = array();

		$this->user_management_whitelist = array(
			'subzeroblue',
			'postmediabarbaraklose',
			'barbaraklosepostmedia',
		);

		$this->valid_vip_libraries = array(
			'bc-mapi',
			'codebird',
			'facebook',
			'Mustache',
			'OAuth',
		);

		$this->image_sizes = array();

		$this->stats_enabled = false;

		$this->settings = new Theme\Settings();

		$this->robots_entries = array(
			'User-agent: *',
			'Disallow: /?s=',
			'Disallow: /search',
			'Disallow: /3081/',
		);

		$this->content_width = 640;

		$this->image_width_thumbnail = 150;
	}

	/**
	 * Call this after you have configured theme settings
	 */
	public function initialize() {
		// Create stats engine
		$this->stats = new Theme\Stats( $this->stats_enabled );

		// VIP
		$this->vip_load_plugins();

		$this->vip_setup();

		// Postmedia
		$this->load_plugins();

		$this->setup();
	}

	/**
	 * Setup VIP items
	 */
	private function vip_setup() {
		// Load the default permastructure
		if ( function_exists( 'wpcom_vip_load_permastruct' ) ) {
			wpcom_vip_load_permastruct( '/%category%/%postname%' );
		}

		// Enable Open Graph
		if ( function_exists( 'wpcom_vip_enable_opengraph' ) ) {
			wpcom_vip_enable_opengraph();
		}

		// Enable Bulk User Management
		if ( function_exists( 'wpcom_vip_bulk_user_management_whitelist' ) ) {
			if ( is_array( $this->user_management_whitelist ) && count( $this->user_management_whitelist ) > 0 ) {
				wpcom_vip_bulk_user_management_whitelist( $this->user_management_whitelist );
			}
		}

		if ( function_exists( 'make_tags_local' ) ) {
			make_tags_local();
		}

		if ( function_exists( 'wpcom_vip_disable_postpost' ) ) {
			wpcom_vip_disable_postpost();
		}

		// Helps with slowness with saving on edit post page (VIP:40366)
		// If this plugin loads anywhere but in Production you cannot set the featured image on a brand new post
		// Only after saving the post once can you set the featured image. Seems to affect the wp.media.settings.post JS object.
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV && function_exists( 'wpcom_vip_load_plugin' ) ) {
			wpcom_vip_load_plugin( 'wp_enqueue_media_override', 'plugins', true );
		}

		/**
		 * Performance function
		 * 	- Disables instapost
		 * 	- 30 min cache for full comment count shown in the menu under comments in admin
		 * 	- Disables adjacent post rel links in head
		 */
		if ( function_exists( 'wpcom_vip_enable_performance_tweaks' ) ) {
			wpcom_vip_enable_performance_tweaks();
		}

		/**
		 * Override the $content_width to remove image constraints
		 * Issue was impacting open graph image width and height (FB image sharing)
		 */
		if ( function_exists( 'wpcom_vip_allow_full_size_images_for_real' ) ) {
			wpcom_vip_allow_full_size_images_for_real();
		}

		// Disable filter that injects nbsp's to prevent orphans (title & body)
		if ( function_exists( 'vip_allow_title_orphans' ) ) {
			vip_allow_title_orphans();
		}

		// Enable CDN
		add_action( 'wp', array( $this, 'enable_image_cdn' ) );

		// Optimize author pages
		add_filter( 'coauthors_plus_should_query_post_author', '__return_false' );

		if ( is_array( $this->robots_entries ) && count( $this->robots_entries ) > 0 ) {
			add_action( 'do_robotstxt', array( $this, 'action_robots_entries' ) );
		}
	}

	/**
	 * Setup Postmedia items
	 */
	private function setup() {
		// Set global content width
		if ( isset( $this->content_width ) ) {
			global $content_width;

			$content_width = (int) $this->content_width;
		}

		// Actions
		add_action( 'wp_enqueue_styles', array( $this, 'action_enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'action_enqueue_scripts' ) );
		add_action( 'after_setup_theme', array( $this, 'action_after_setup_theme' ) );
		add_action( 'widgets_init', array( $this, 'action_register_widgets' ) );
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
		add_action( 'init', array( $this, 'action_image_sizes' ) );
		add_filter( 'image_size_names_choose', array( $this, 'filter_image_size_names_choose' ) );

		// Filters
		add_filter( 'query_vars', array( $this, 'filter_query_vars' ) );

		// Load capabilities
		$this->capabilities = new Theme\Capabilities();

		// Load modifiers
		$this->modifiers = new Theme\Modifiers();

		// Load SEO
		$this->seo = new Theme\SEO();

		// Load plugins
		$this->wcm = new Plugins\WCM();

		$this->settings->initialize();
	}

	/**
	 * Load required VIP plugins
	 */
	private function vip_load_plugins() {
		$this->stats->new_timer( 'vip_load_plugins' );

		$vip_plugin_list = $this->parse_vip_plugin_list( $this->vip_plugins );

		foreach ( $vip_plugin_list as $name => $version ) {
			if ( in_array( $name, $this->valid_vip_libraries ) ) {
				wpcom_vip_require_lib( $name );
			} else {
				wpcom_vip_load_plugin( $name, 'plugins', $version );
			}
		}

		$this->stats->stop_timer( 'vip_load_plugins' );
	}

	/**
	 * Load required Postmedia plugins
	 */
	private function load_plugins( $folder = 'postmedia-plugins' ) {
		$this->stats->new_timer( 'load_plugins' );

		if ( is_array( $this->postmedia_plugins ) && count( $this->postmedia_plugins ) > 0 ) {
			foreach ( $this->postmedia_plugins as $postmedia_plugin ) {
				wpcom_vip_load_plugin( $postmedia_plugin, $folder );
			}
		}

		$this->stats->stop_timer( 'load_plugins' );
	}

	/**
	 * Enable CDN for live sites and not for previews
	 * To disable for sites not yet live, add check for stylesheet name
	 * 	if ( 'vip/edmontonjournal-web' != get_stylesheet() ) {}
	 */
	public function enable_image_cdn() {
		if ( ! is_preview() && ! in_array( get_stylesheet(), $this->disabled_cdn_sites ) ) {

			if ( isset( $_SERVER['HTTP_HOST'] ) ) {
				$cdn_host = sprintf( 'wpmedia.%s', sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) );
				wpcom_vip_load_custom_cdn( array( 'cdn_host_media' => $cdn_host, 'disable_ssl' => true ) );
			}
		}
	}

	/**
	 * Access to Event Tracking - Using this function allows the Event Tracking
	 * class to only initialize if requested
	 * @return EventTracking
	 */
	public function event_tracking() {
		if ( ! $this->event_tracking ) {
			$this->event_tracking = new Theme\EventTracking();
		}

		return $this->event_tracking;
	}

	/**
	 * WP Filter - query_vars
	 * @param  array $vars
	 * @return array
	 */
	public function filter_query_vars( $vars ) {
		if ( is_array( $this->custom_query_vars ) && count( $this->custom_query_vars ) > 0 ) {
			return array_merge( $vars, $this->custom_query_vars );
		}

		return $vars;
	}

	/**
	 * WP Action - wp_enqueue_styles
	 */
	public function action_enqueue_styles() {

	}

	/**
	 * WP Action - wp_enqueue_scripts
	 */
	public function action_enqueue_scripts() {

	}

	/**
	 * WP Action - admin_init
	*/
	public function action_admin_init() {

	}

	/**
	 * Register custom image sizes
	 */
	public function action_image_sizes() {
		if ( is_array( $this->image_sizes ) && count( $this->image_sizes ) > 0 ) {
			foreach ( $this->image_sizes as $image_size ) {
				add_image_size( $image_size[0], $image_size[1], $image_size[2], ( isset( $image_size[3] ) ) ? $image_size[3] : false );
			}
		}
	}

	/**
	 * Add custom image size names to filter
	 *
	 * @param  array $sizes
	 * @return array
	 */
	public function filter_image_size_names_choose( $sizes ) {
		$image_size_names = array();
		if ( is_array( $this->image_sizes ) && count( $this->image_sizes ) > 0 ) {
			foreach ( $this->image_sizes as $image_size ) {
				if ( isset( $image_size[4] ) ) {
					$image_size_names[ $image_size[0] ] = trim( $image_size[4] );
				}
			}
		}
		return array_merge( $sizes, $image_size_names );
	}

	/**
	 * Register required widgets
	 */
	public function action_register_widgets() {
		$this->stats->new_timer( 'register_widgets' );

		if ( is_array( $this->postmedia_widgets ) && count( $this->postmedia_widgets ) > 0 ) {
			foreach ( $this->postmedia_widgets as $postmedia_widget ) {
				register_widget( 'Postmedia\\Web\\Widgets\\' . $postmedia_widget );
			}
		}

		$this->stats->stop_timer( 'register_widgets' );
	}

	/**
	 * WP Action - after_setup_theme
	 */
	public function action_after_setup_theme() {
		// Register menus
		if ( is_array( $this->navigation_menus ) && count( $this->navigation_menus ) > 0 ) {
			register_nav_menus( $this->navigation_menus );
		}

		if ( ! is_admin() ) {
			set_post_thumbnail_size( $this->image_width_thumbnail, 9999, true );
		}

		// Add default posts and comments RSS feed links to head
		add_theme_support( 'automatic-feed-links' );

		// Enable support for Post Formats
		add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link' ) );

		// Setup the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'pn_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Enable post thumbnails. Add custom image sizes here if needed.
		add_theme_support( 'post-thumbnails' );

		// Add theme support for Infinite Scroll. (Jetpack)
		add_theme_support(
			'infinite-scroll',
			array(
				'container' => 'main',
				'footer'    => 'page',
			)
		);
	}

	/**
	 * Inject strings into the robots file
	 * @return void
	 */
	public function action_robots_entries() {
		if ( is_array( $this->robots_entries ) && count( $this->robots_entries ) > 0 ) {
			foreach ( $this->robots_entries as $entry ) {
				echo esc_html( $entry ) . esc_html( PHP_EOL );
			}
		}
	}

	/**
	 * Helper to create a list (name => version) of vip plugins to require
	 * Output eg. array( 'plugin_one' => false, 'plugin_two' => 1.5, 'plugin_three' => false )
	 * @param  array $vip_plugins array
	 * @return array
	 */
	private function parse_vip_plugin_list( $vip_plugins ) {
		$list = array();

		if ( is_array( $vip_plugins ) && count( $vip_plugins ) > 0 ) {
			foreach ( $vip_plugins as $vip_plugin ) {
				$name = $vip_plugin;
				$version = false;

				if ( is_array( $vip_plugin ) && isset( $vip_plugin[0] ) ) {
					$name = $vip_plugin[0];

					if ( isset( $vip_plugin[1] ) ) {
						$version = $vip_plugin[1];
					}
				}

				$list[ $name ] = $version;
			}
		}

		return $list;
	}
}
