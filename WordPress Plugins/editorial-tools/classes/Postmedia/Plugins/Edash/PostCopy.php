<?php
namespace Postmedia\Plugins\Edash;

// require the traits, the classloader won't load these
require_once( plugin_dir_path( __FILE__ ) . 'CoreMethods.php' );

/**
* Phase 1 copy titles, excerpt, canonical, cats, tags, text body copy, main cat?
*/
class PostCopy {
	// include the Core Methods trait
	use CoreMethods;

	private $meta_wcm_url = 'pn_wcm_origin_url';
	private $origin_site;

	/**
	 * Constuctor
	 * @return ~ a PostCopy instance
	 */
	public function __construct( $_origin_site ) {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		$this->origin_site = $_origin_site;
	}

	/**
	 * Admin Init ~ called via an early action
	 * @return null
	 */
	public function admin_init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		// AJAX endpoint
		add_action( 'wp_ajax_json_copy_post', array( $this, 'json_copy_post' ) );
	}

	/**
	 * Admin Enqueue Scripts
	 */
	public function admin_enqueue_scripts() {
		$_obj = get_current_screen();
		$_screen = isset( $_obj->base ) ? $_obj->base : '';
		$_screen = '_' . str_replace( '-', '_', $_screen );

		// Ensure that the copy js only runs on the Editorial Dashboard admin page
		if ( 0 < strpos( '_' . $_screen, 'editorial_dashboard' ) ) {
			wp_enqueue_script( 'pn_edash_copy_js', PN_EDASH_URI . 'js/copy.js', array(), false, false );
		}
	}

	/**
	 * JSON Copy Post ~ AJAX method that copies a post from the WCM to the current site
	 * Echo JSON resspons ~ success or failure of copying a post
	 * @return null
	 */
	public function json_copy_post() {
		check_ajax_referer( 'wcm_editorial_dashboard_api_nonce', 'nonce' );

		// current_user_can() validation in the one function to be used on all the ajax endpoints and settings
		if ( false === $this->validate_role() ) {
			wp_die( esc_html__( 'Sorry, you do not have permission to access this api.' ) );
		} else {
			$_output = null;

			if ( isset( $_POST['wcm_id'] ) && '' !== trim( $_POST['wcm_id'] ) ) {
				$_wcm_id = sanitize_text_field( wp_unslash( $_POST['wcm_id'] ) );

				// validate $_wcm_id
				if ( '' !== trim( $_wcm_id ) ) {
					// check the nonce
					$_client_id = isset( $_POST['client_id'] ) ? sanitize_text_field( wp_unslash( $_POST['client_id'] ) ) : '';
					$_output = $this->copy_post( $_wcm_id, $_client_id );
				}

				print( wp_json_encode( $_output ) );	// encode output in JSON + send it back to the caller
			}
		}

		die();
	}

	/**
	 * Copy Post
	 * @param string $_wcm_id ~ a WCM post id
	 * @param string $_client_id
	 * @return object ~ success or failure object with message
	 */
	public function copy_post( $_wcm_id, $_client_id ) {
		$_output = new \stdClass;
		$_output->success = false;
		$_output->post_id = 0;
		$_output->message = '';
		$_output->code = '';
		$_output->edit_url = '';

		$_post = $this->get_post_single( $_wcm_id, $_client_id );

		// $_post->statusCode no longer exists so ensure $_post is set.
		if ( empty( $_post ) || isset( $_post['error'] ) ) {
			$_output->success = false;
			$_output->message = 'Post was not valid. Failed copying';
			$_output->code = 'failure';
		} else {
			$_post_data = $this->parse_post_data( $_post );

			if ( ! is_object( $_post_data ) || ! isset( $_post_data->_id ) || ! isset( $_post_data->type ) ) {
				$_output->success = false;
				$_output->message = 'Invalid post data';
				$_output->code = 'failed';

				return $_output;
			}

			if ( 'story' === $_post_data->type ) {
				// copy only regular posts for mvp
				$_post_id = $this->get_post_by_wcm_and_client_id( $_wcm_id, $_client_id, 'post' );

				if ( 0 === $_post_id ) {
					// a post does not already exists with this wcm_id so copy and capture the new post ID value
					$_post_id = $this->add_post( $_post_data, $_client_id );
					$_output->success = true;
					$_output->post_id = absint( $_post_id );
					$_output->message = 'Post successfully copied.';
					$_output->code = 'copied';
					$_output->edit_url = admin_url( 'post.php?action=edit&post=' . absint( $_post_id ) );
				} else {
					$_output->success = false;
					$_output->message = 'This post has already been copied to this site.';
					$_output->code = 'exists';
				}
			} else {
				$_output->success = false;
				$_output->message = 'Only regular posts can be copied at this time.';
				$_output->code = 'failed';
			}
		}

		return $_output;
	}

	/**
	 * Add Post Meta
	 * @param int $_post_id ~ WordPress post id
	 * @param array $_post_metadata ~ a posts' meta data
	 * @return array ~ A list of the added meta data
	 */
	public function add_post_meta( $_post_id, $_post_metadata ) {
		// save metadata
		$_output = false;
		if ( is_array( $_post_metadata ) ) {
			$_exclude_keys = array(
				'instant_articles_submission_id',
				'pni_saxotech_story_id',
				'pn_wcm_id',
				'pn_wcm_license',
				'pni_slug',
				'pn_wcm_origin_id',
			);

			$_exclude_keys = apply_filters( 'pn_edash_post_copy_exclude_keys', $_exclude_keys );
			foreach ( $_post_metadata as $_key => $_val ) {
				// save meta data
				if ( ! in_array( $_key, $_exclude_keys ) ) {
					if ( is_array( $_val ) && ( 1 === count( $_val ) ) ) {
						$_meta = $_val[0];
					} else {
						$_meta = $_val;
					}
					add_post_meta( $_post_id, $_key, $_meta );
					$_output = array();
					$_output[ $_key ] = $_meta;
				}
			}
		}
		return $_output;
	}

	/**
	 * Parse Title
	 * @param object $_titles ~ a wCM titles object
	 * @return string ~ the main or alternate title
	 */
	private function parse_title( $_titles ) {
		if ( ( isset( $_titles->main ) ) && ( '' !== trim( $_titles->main ) ) ) {
			return trim( $_titles->main );
		}

		if ( ( isset( $_titles->alternate ) ) && ( '' !== trim( $_titles->alternate ) ) ) {
			return trim( $_titles->alternate );
		}

		return '';
	}

	/**
	 * Parse Post Type
	 * @param string $_type ~ a posts' type
	 * @return string ~ the post type
	 */
	private function parse_post_type( $_type ) {
		if ( ( isset( $_type ) ) && ( '' !== trim( $_type ) ) ) {
			 // only support regular posts for now
			if ( 'story' === $_type ) {
				return 'post';
			} else {
				return;
			}
		}

		return 'post';
	}

	/**
	 * Parse Content
	 * @param object $_data
	 * @return object ~ the data if set otherwise an empty string
	 */
	private function parse_content( $_data ) {
		return isset( $_data ) ? $_data : '';
	}

	/**
	 * Parse Author
	 * @param object $_author_obj ~ a WordPress author object
	 * @return integer ~ the author id
	 */
	private function parse_author( $_author_obj ) {
		if ( ( isset( $_author_obj->id ) ) && ( 0 < intval( $_author_obj->id ) ) ) {
			return $_author_obj->id ;
		} else {
			return get_current_user_id();
		}

		return 0;
	}

	/**
	 * Save origin data for Canonical use as meta data
	 * @param integer $_post_id ~ a WordPress post id
	 * @param object $_post_data ~ a WordPress post object
	 */
	private function save_origin_data_for_canonical_use( $_post_id, $_post_data ) {
		// save origin data for canonical
		if ( isset( $_post_data->origin_cms ) ) {
			update_post_meta( $_post_id, 'pn_wcm_origin_cms', $_post_data->origin_cms, true );
		}

		if ( isset( $_post_data->origin_url ) ) {
			update_post_meta( $_post_id, 'pn_wcm_origin_url', $_post_data->origin_url, true );
			add_post_meta( $_post_id, 'pn_org', $this->origin_site->get_brand( $_post_data->origin_url ), true );
		}
	}

	/**
	 * Add Post
	 * @param  object $_post_data ~ a WordPress Post's data
	 * @param  string $_client_id
	 * @return integer ~ a newly inserted post id
	 */
	public function add_post( $_post_data, $_client_id ) {
		// create new post
		$_post_array = array(
			'post_status' => 'draft',
			'post_type' => $this->parse_post_type( $_post_data->type ),
			'post_title' => $this->parse_title( $_post_data->titles ),
			'post_excerpt' => $this->parse_content( $_post_data->excerpt ),
			'post_author' => $this->parse_author( $_post_data->author ),
			'post_content' => $this->parse_content( $_post_data->body ),
		);

		$_post_id = wp_insert_post( $_post_array, false );

		if ( is_wp_error( $_post_id ) ) {
			return esc_html__( 'Sorry, the post did not copy and exited with this message: ' . $_post_id->get_error_message() );
		}

		// Save metadata while you have the newly created $_post_id
		if ( 0 < $_post_id ) {

			// $this->meta_wcm_id is stored as a hard-coded value in CoreMethods.php @TODO refactor
			add_post_meta( $_post_id, $this->meta_wcm_id, $_post_data->_id, true );

			// This flag is used by themes and the Postmedia Library to help set canonical and
			// organization rules
			add_post_meta( $_post_id, 'pn_copied', true, true );

			if ( isset( $_post_data->metadata ) ) {
				$this->add_post_meta( $_post_id, $_post_data->metadata );
			}

			if ( isset( $_post_data->byline ) ) {
				add_post_meta( $_post_id, 'pn_author_byline', $_post_data->byline, true );
			}

			$this->set_client_and_license( $_post_id, $_client_id );

			if ( isset( $_post_data->cat_ids ) ) {
				$this->add_post_terms( $_post_id, $_post_data->cat_ids, 'category' );

			}

			if ( isset( $_post_data->tag_ids ) ) {
				$this->add_post_terms( $_post_id, $_post_data->tag_ids, 'post_tag' );
			}

			if ( isset( $_post_data->featured_image ) ) {
				$this->sideload_image( $_post_id, 'featured', $_post_data->featured_image );
			}

			if (
				isset( $_post_data->origin_cms )
				|| isset( $_post_data->origin_url )
			) {
				$this->save_origin_data_for_canonical_use( $_post_id, $_post_data );
			}
		}

		return $_post_id;
	}
}
