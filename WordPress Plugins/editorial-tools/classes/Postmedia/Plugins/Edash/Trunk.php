<?php
namespace Postmedia\Plugins\Edash;

// require the traits, the classloader won't load these
require_once( plugin_dir_path( __FILE__ ) . 'CoreMethods.php' );

class Trunk {
	// include the Core Methods trait
	use CoreMethods;

	private $path;
	private $url;
	private $settings;
	private $storage;
	private $pointer;
	private $suffix_hook = '';

	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_ajax_endpoints' ) );
	}

	/**
	 * Admin Init ~ called in an early occuring WordPress action
	 * define AJAX endpoints
	 * @return null;
	 */
	function admin_ajax_endpoints() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_json_lookup_posts', array( $this, 'json_lookup_posts' ) );
		add_action( 'wp_ajax_json_lookup_posts1', array( $this, 'json_lookup_posts1' ) );
		add_action( 'wp_ajax_json_get_clients', array( $this, 'json_get_clients' ) );
		add_action( 'wp_ajax_json_get_licenses', array( $this, 'json_get_licenses' ) );
		add_action( 'wp_ajax_json_get_client_licenses', array( $this, 'json_get_client_licenses' ) );
	}

	/**
	 * Initialize ~ used to defer setup of core plugin objects ( mainly for unit testing )
	 * @param object $_paths ~ a Paths instance
	 * @param object $_settings ~ a Settings instance
	 * @param object $_storage ~ a storage instance
	 * @return null
	 */
	public function initialize( $_paths, $_settings, $_storage ) {
		$this->path = $_paths->path();
		$this->url = $_paths->url();
		$this->settings = $_settings;
		$this->storage = $_storage;
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Setup ~ add the plugins menu
	 * @return null
	 */
	public function menu() {
		add_action( 'admin_menu', array( $this->settings, 'set_main_menu' ) );
	}

	/**
	 * Init ~ bound in an early running WordPress hook
	 * @return null
	 */
	public function init() {
		$this->menu();
	}

	/**
	 * Admin Enqueue Scripts
	 */
	function admin_enqueue_scripts( $suffix_hook ) {
		$this->suffix_hook = $suffix_hook;
		// ensure css is loaded only on the appropriate admin page
		if ( 'editorial-dashboard_page_ed_wcm_story_pointer' === $this->suffix_hook ) {
			wp_enqueue_style( 'dashboard_splash_page', $this->url . '/css/ed-styles.css' );
		}

		// ensure css is loaded only on the appropriate admin pages
		if (
			'editorial-dashboard_page_ed_wcm_story_pointer' === $this->suffix_hook
			|| 'editorial-dashboard_page_ed_wcm_post_copy' === $this->suffix_hook
			|| 'toplevel_page_ed_wcm_editorial_dashboard' === $this->suffix_hook
			) {
			// Styles
			wp_enqueue_style( 'edash_google_fonts', 'https://fonts.googleapis.com/icon?family=Material+Icons' );
			wp_enqueue_style( 'edash_font_awesome', $this->url . '/css/font-awesome.min.css' );
			wp_enqueue_style( 'edash_interface_default', $this->url . '/css/default.css', array(), '', 'screen,projection' );

			// Scripts
			wp_enqueue_script( 'pointer_interface_common', $this->url . '/js/common/wcmConfig.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'pointer_interface_config', $this->url . '/js/config.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'pointer_interface_keys', $this->url . '/js/keys.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'pointer_interface_data', $this->url . '/js/data.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'pointer_interface_utils', $this->url . '/js/utils.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'pointer_interface_templates', $this->url . '/js/templates.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'pointer_interface_filters', $this->url . '/js/filters.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'pointer_interface_list', $this->url . '/js/list.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'pointer_interface_edit', $this->url . '/js/edit.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'pointer_interface_pointers', $this->url . '/js/pointers.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'pointer_interface_pager', $this->url . '/js/pager.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'pointer_interface', $this->url . '/js/interface.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'pointer_interface_main', $this->url . '/js/main.js', array( 'jquery' ), '0.9', true );
			wp_enqueue_script( 'edash_localize_vars', $this->edash_localize_vars() );
		}
	}

	/**
	 * Edash Localize Vars ~ localize current users name and site for the email to prefill
	 * @return null
	 */
	private function edash_localize_vars() {
		$current_user = wp_get_current_user();
		$api_url = get_option( 'wcm_api_url' );
		$edash_vars = array(
			'api' => esc_url( trailingslashit( $api_url ) ),
			'name' => $current_user->user_firstname . ' ' . $current_user->user_lastname,
			'site' => bloginfo( 'name' ),
			'security' => wp_create_nonce( 'wcm_editorial_dashboard_api_nonce' ),
		);
		wp_register_script( 'edash_scripts', $this->url . '/js/postmedia-edash-localize.js' );
		wp_enqueue_script( 'edash_scripts' );
		wp_localize_script( 'edash_scripts', 'postmedia_edash', $edash_vars );
	}

	/**
	* AJAX endpoint - JSON Lookup Posts
	* Get local posts based on WCM ID and return whether they have been copied here, originated here,
	* and/or have had pointers made here.
	* Echo JSON object back to the caller of this endpoint
	* @return null
	*/
	public function json_lookup_posts1() {
		check_ajax_referer( 'wcm_editorial_dashboard_api_nonce', 'nonce' );
		if ( true === $this->validate_role() ) {
			$_output = array();

			if ( isset( $_POST['wcm_obj_list'] ) && is_array( $_POST['wcm_obj_list'] ) ) {
				$_wcm_obj_list = json_decode( sanitize_text_field( wp_unslash( $_POST['wcm_obj_list'] ) ) );
				if ( is_array( $_wcm_obj_list ) ) {
					$_ids = $this->get_postlist_by_wcm_id( $_wcm_obj_list );
					foreach ( $_ids as $_wid => $_obj ) {
						$_obj->id = $_wid;
						$_output[] = $_obj;
					}
				}
			}
			print( wp_json_encode( $_output ) ); // encode output in JSON + send it back to the caller
			die();
		}

		wp_die( 'unauthorized access' );
	}
}
