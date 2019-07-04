<?php
namespace Postmedia\Plugins\Edash;

// require the traits, the classloader won't load these
require_once( plugin_dir_path( __FILE__ ) . 'CoreMethods.php' );

/**
 * API Proxy Class
 * Performs READ queries to the WCM API without exposing keys.
 */
class ApiProxy {
	// include the Core Methods trait
	use CoreMethods;

	private $url;

	/**
	 * Constuctor
	 * @return ~ a ApiProxy instance
	 */
	public function __construct( $paths ) {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		$this->url = $paths->url();
	}

	/**
	 * Admin Init ~ called via an early action
	 * @return null
	 */
	public function admin_init() {
		// AJAX endpoints
		add_action( 'wp_ajax_call_wcm_api', array( $this, 'call_wcm_api' ) );
		add_action( 'wp_ajax_no_priv_call_wcm_api', array( $this, 'call_wcm_api' ) );
	}

	/**
	 * Call WCM api ~ query the lists, clients, licenses, and content apis
	 * @return null ~ echos a json text object
	 */
	public function call_wcm_api() {
		check_ajax_referer( 'wcm_editorial_dashboard_api_nonce', 'nonce' );

		if ( $this->validate_role() ) {
			$_allowed_events = array( 'licenses', 'clients', 'lists', 'content' );

			if ( isset( $_POST['event'] ) ) {
				$_event = sanitize_key( wp_unslash( $_POST['event'] ) );
			}

			if ( isset( $_POST['query'] ) ) {
				$_query = sanitize_text_field( wp_unslash( $_POST['query'] ) );
			}
			if ( in_array( $_event , $_allowed_events, true ) ) {

				if ( isset( $_query ) ) {
					$_path = sanitize_text_field( $_event ) . '/?' . $_query;
				} else {
					$_path = sanitize_text_field( $_event );
				}
				// print as JSON the API response
				print( wp_json_encode( $this->call_wcm_data( $_path ) ) );

			} else {
				$_output = new \stdClass;
				$_output->success = false;
				$_output->post_id = 0;
				$_output->message = 'action not defined';
				$_output->code = '300';
				print( wp_json_encode( $_output ) );
			}
		}
		die();
	}
}
