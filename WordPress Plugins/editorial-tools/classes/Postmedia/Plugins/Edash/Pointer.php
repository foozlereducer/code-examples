<?php

namespace Postmedia\Plugins\Edash;

use Postmedia\Web\Utilities;
use Postmedia\Web\Utilities\URL;

// need this required as Travis can't load the trait via the autoloader'
require_once( plugin_dir_path( __FILE__ ) . 'CoreMethods.php' );

/**
 * Class Pointer ~ Manage Pointers
 */
class Pointer {
	// include the core methods via a trait
	use CoreMethods;

	private $origin_site;

	public function __construct( $_origin_site ) {
		$this->origin_site = $_origin_site;
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( 'the_guid', array( $this, 'the_guid' ), 10, 2 );
		add_filter( 'post_type_link', array( $this, 'pointer_link' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}
	/**
	 * Admin Init ~ called by a WordPress action and is an early hook do functionality early
	 * @return null
	 */
	function admin_init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_json_add_pointer', array( $this, 'json_add_pointer' ) );
	}

	/**
	 * Filters the guid used in feeds.
	 * @param  string $guid
	 * @param  int    $id
	 * @return string
	 */
	function the_guid( $guid, $id ) {
		if ( 'pn_pointer' === get_post_type( $id ) ) {
			return home_url( '?p=' . $id );
		}
		return $guid;
	}

	/**
	* Returns the link we need to use for the pointer
	* At this phase, the logic is as follows:
	* - If the pointer has a url (it's actually the absolute path), create link to url for current site
	* - If the pointer doesn't have a url, but has an alternate url, use that link
	* - If the pointer has neither, link to the home page
	* @param string $link - WP permalink for the post
	* @param int $post - Post ID
	* @return string $post_link - The link to use for the pointer
	*/
	function pointer_link( $link, $post ) {
		if ( is_object( $post ) ) {
			// If this is a pointer, override its url
			if ( 'pn_pointer' === $post->post_type ) {
				$post_link = '';
				$post_url = get_post_meta( $post->ID, 'pn_pointer_url', true );
				$post_ext_url = get_post_meta( $post->ID, 'pn_pointer_ext_url', true );

				if ( ! empty( $post_url ) ) {
					$post_link = get_home_url( null, $post_url, 'http' );
				} elseif ( ! empty( $post_ext_url ) ) {
					$post_link = $post_ext_url;
				} else {
					$post_link = get_home_url( null, null, 'http' );
				}

				$post_link = esc_url( filter_var( $post_link, FILTER_VALIDATE_URL ) );

				if ( strpos( $post_link, '/wcm/' ) !== false && $pn_wcm_id = get_post_meta( $post->ID, 'pn_wcm_id', true ) ) {
					$post_link = preg_replace( '/\/wcm\/[^\/]+/', '/wcm/' . $pn_wcm_id, $post_link );
				}

				return URL::multi_market( $post->ID, $post_link );
			} else {
				// It's not a pointer, so return url as is.
				return $link;
			}
		} else {
			// link can't be derived from post as it is not a valid post object
			// so return url as is.
			return $link;
		}
	}

	/**
	 * Admin Equeue Scripts
	 * @return null
	 */
	public function admin_enqueue_scripts() {
		$_obj = get_current_screen();
		$_screen = isset( $_obj->base ) ? $_obj->base : '';
		$_screen = '_' . str_replace( '-', '_', $_screen );
		// Ensure that the copy js only runs on the Editorial Dashboard admin page
		if ( 0 < strpos( '_' . $_screen, 'editorial_dashboard' ) ) {
			wp_enqueue_script( 'pn_edash_copy_js', PN_EDASH_URI . 'js/copy.js', false, false, false );
		}
	}

	/**
	 * Dies if the post has an error.
	 * @param  array|null $_post
	 * @return void
	 */
	private function die_on_error_post( $_post ) {
		if ( empty( $_post ) || isset( $_post['error'] ) ) {
			$_output = new \stdClass;
			$_output->success = false;
			$_output->message = 'A post was not retrieved';
			$_output->code = isset( $_post['error'] ) ? $_post['error'] : '';
			print( wp_json_encode( $_output ) );
			die();
		}
	}

	private function isset_and_trim( $value ) {
		return isset( $value ) ? trim( $value ) : '';
	}

	/**
	 * Creates a new pointer.
	 * @param  array        $_post
	 * @return int|WP_Error
	 */
	private function insert_pointer_post( $_post ) {
		$_title = isset( $_post['titles']['main'] ) ? trim( $_post['titles']['main'] ) : '';
		$_excerpt = isset( $_post['excerpt'] ) ? trim( $_post['excerpt'] ) : '';
		$_data = array(
			'post_id'      => null,
			'post_title'   => sanitize_text_field( $_title ),
			'post_excerpt' => sanitize_text_field( $_excerpt ),
			'post_type'    => 'pn_pointer',
			'post_status'  => 'draft',
		);
		return wp_insert_post( $_data );
	}

	/**
	 * Gets post data.
	 * @param integer $_pointer_id
	 * @param array   $_post
	 */
	private function get_post_data( $_pointer_id, $_post ) {
		$_post_data = $this->parse_post_data( $_post );
		if ( isset( $_post_data->cat_ids ) ) {
			$this->add_post_terms( $_pointer_id, $_post_data->cat_ids, 'category' );
		}
		if ( isset( $_post_data->tag_ids ) ) {
			$this->add_post_terms( $_pointer_id, $_post_data->tag_ids, 'post_tag' );
		}
		// sideload featured image
		if ( isset( $_post_data->featured_image ) ) {
			$this->sideload_image( $_pointer_id, 'featured', $_post_data->featured_image );
		}
	}

	private function get_output( $action, $_post_id = 0 ) {
		$_output = new \stdClass;
		$_output->success = false;
		$_output->post_id = 0;
		$_output->message = '';
		$_output->code = '';
		$_output->edit_url = '';

		switch ( $action ) {
			case 'inserted':
				$_output->success = true;
				$_output->post_id = $_post_id;
				$_output->message = 'Pointer created.';
				$_output->code = 'copied';
				$_output->edit_url = admin_url( 'post.php?action=edit&post=' . intval( $_post_id ) );
				break;
			case 'pointer_exists':
				$_output->message = 'This pointer has already been created on this site.';
				$_output->code = 'exists';
				break;
			case 'nonce_failed':
				$_output->message = 'Pointer could not be created';
				$_output->code = 'failed';
				break;
		}

		return $_output;
	}
	/**
	 * JSON Add Pointer ~ AJAX action method that processes the request and adds a pointer
	 * Echos a JSON response
	 * @return null
	 */
	public function json_add_pointer() {
		// check the nonce
		check_ajax_referer( 'wcm_editorial_dashboard_api_nonce', 'nonce' );
		if ( false === $this->validate_role() ) {
			 wp_die( esc_html__( 'Sorry, you do not have permission to this API.' ) );
		}

		// get $_wcm_id
		$wcm_id    = isset( $_POST['wcm_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wcm_id'] ) ) : '';
		$client_id = isset( $_POST['client_id'] ) ? sanitize_text_field( wp_unslash( $_POST['client_id'] ) ) : '';

		if ( '' !== trim( $wcm_id ) && '' !== $client_id ) {
			$_pointer_id = $this->get_post_by_wcm_and_client_id( $wcm_id, $client_id, 'pn_pointer' );
			// ! wcm_id?
			if ( 0 === $_pointer_id ) {
				$_post = $this->get_post_single( $wcm_id, $client_id );
				$this->die_on_error_post( $_post );
				$_pointer_id = $this->insert_pointer_post( $_post );

				if ( is_wp_error( $_pointer_id ) ) {
					wp_die(
						esc_html__(
							'Sorry, the pointer was not created and exited with this message: '
							. $_pointer_id->get_error_message()
						)
					);
				}

				if ( 0 < intval( $_pointer_id ) ) {
					$this->add_pointer_meta( $_pointer_id, $_post, $wcm_id, $client_id );
					$this->get_post_data( $_pointer_id, $_post );
					$_output = $this->get_output( 'inserted', $_pointer_id );
				}
			} else {
				$_output = $this->get_output( 'pointer_exists' );
			}
		}

		print( wp_json_encode( $_output ) );	// encode output in JSON + send it back to the caller
		die();
	}

	/**
	 * Set the pn_pointer's posts meta data
	 * @param integer $_pointer_id ~ the newly inserted post id
	 * @param array $_post ~ the $_POST object
	 * @param string $_wcm_id
	 * @param string $_client_id
	 */
	private function add_pointer_meta( $_pointer_id, $_post, $_wcm_id, $_client_id ) {
		$_pointer_id = intval( $_pointer_id );
		if ( 0 < $_pointer_id ) {
			if ( isset( $_wcm_id ) ) {
				$_meta = trim( $_wcm_id );
				add_post_meta( $_pointer_id, $this->meta_wcm_id, $_wcm_id );
			}
			if ( isset( $_post['published_on'] ) ) {
				$_meta = trim( $_post['published_on'] );
				add_post_meta( $_pointer_id, 'pn_pointer_date', $_meta, true );
			}
			if ( isset( $_post['origin_url_path'] ) ) {
				$_path = trim( $_post['origin_url_path'] );
				if ( '/' === substr( $_path, 0, 1 ) ) {
					$_path = substr( $_path, 1 );
				}
				$_path = sprintf( '/%s/wcm/%s', $_path, $_wcm_id );
				add_post_meta( $_pointer_id, 'pn_pointer_url', $_path, true );
			}
			if ( isset( $_post['origin_url'] ) ) {
				$_meta = trim( $_post['origin_url'] );
				add_post_meta( $_pointer_id, 'pn_pointer_ext_url', $_meta, true );
				add_post_meta( $_pointer_id, 'pn_org', $this->origin_site->get_brand( $_meta ), true );
			}
			if ( isset( $_post['credits']['authors'][0]['name'] ) ) {
				$_meta = trim( $_post['credits']['authors'][0]['name'] );
				add_post_meta( $_pointer_id, 'pn_author_byline', $_meta, true );
			}
			if ( isset( $_post['credits']['authors'][0]['email'] ) ) {
				$_meta = trim( $_post['credits']['authors'][0]['email'] );
				add_post_meta( $_pointer_id, 'pn_pointer_author_email', $_meta, true );
			}
			if ( isset( $_post['credits']['distributor'] ) ) {
				$_meta = trim( $_post['credits']['distributor'] );
				add_post_meta( $_pointer_id, 'pn_pointer_distributor', $_meta, true );
			}
			// Add custom field to mark post as noindex,nofollow for SEO purposes
			add_post_meta( $_pointer_id, 'robots', 'noindex,nofollow', true );

			// This flag is used by themes and the Postmedia Library to help set canonical and
			// organization rules
			add_post_meta( $_pointer_id, 'pn_copied', true, true );
			$this->set_client_and_license( $_pointer_id, $_client_id );
		}
		return $_pointer_id;
	}
}
