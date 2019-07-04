<?php

namespace Postmedia\Plugins\Edash;

use Postmedia\Web\Data;
use Postmedia\Web\Data\Ajax;

/**
 * Editorial Dashboard Core Methods Trait - other Editorial classes can use this trait to reuse central methods
 */
trait CoreMethods {
	private $meta_wcm_id = 'pn_wcm_origin_id';
	private $meta_market_id = 'pn_market';
	private $debug = '';
	private $client_id;
	private $featured_image_attr;

	/**
	 * Validate current user can by setting this to a trait reusable function when / if we need
	 * to change capabilities it can be done once here.
	 * @return boolean
	 */
	public function validate_role() {
		return current_user_can( 'edit_others_posts' );
	}

	private function get_api_url( $_path ) {
		$_api_url = trim( get_option( 'wcm_api_url', '' ) );
		if ( '/' !== substr( $_api_url, -1 ) ) {
			$_api_url .= '/';
		}
		return $_api_url . $_path;
	}

	private function get_header_args( $_read_key ) {
		return array(
			'method' => 'GET',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(
				'x-api-key' => sanitize_text_field( $_read_key ),
			),
			'body' => '',
		);
	}

	/**
	 * Process response ~ Attempts to json_decode the response body, if it does not exist or is a WordPress
	 * error or a WCM error and error object is set and returned
	 * @param string $_response ~ a json string that contains the api response
	 * @return object ~ either a object representing the post or an error object
	 */
	private function process_response( $_response ) {
		if ( false === is_wp_error( $_response ) && isset( $_response['body'] ) ) {
			return json_decode( $_response['body'] );
		} else {
			$_error = new \stdClass;
			$_error->success = false;
			$_error->code = '300';

			if ( is_wp_error( $_response ) ) {
				$_error->message = $_response->get_error_message();
			} else {
				$_error->message = 'Unuseable response; check your query';
			}

			return $_error;
		}
	}

	/**
	 *	Call the API and get data from the WCM API based on the set path.
	 * This is used to query only Content, Licenses or Clients that a given client has licenses to access.
	 * @param string $_path ~ the path action with is appended to the WCM API REST call
	 * @return object ~ either a object representing the post or an error object
	 */
	public function get_wcm_data( $_path ) {
		$_read_key = get_option( 'wcm_read_key', '' );
		if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
			$_response = vip_safe_wp_remote_get( $this->get_api_url( $_path ), '', 3, 3, 20, $this->get_header_args( $_read_key ) );
		} else {
			$_response = wp_remote_get( $this->get_api_url( $_path ), $this->get_header_args( $_read_key ) );
		}

		return $this->process_response( $_response );
	}

	/**
	 * Call WCM Data -  a duplicate of get_wcm_data() in CoreMethods; however this method is kept
	 * seperate as it works at the early stage of loading a filter and display results which all
	 * require super key api calls ( so not to be restricted by client licensing )
	 * @param string $_path ~ 'licence', 'client', or 'content'
	 * @param string $_query ~ if it is not empty then we know it is a content call and can properly route output
	 * @return object ~ either a object representing the post or an error object
	 */
	public function call_wcm_data( $_path, $_query = '' ) {
		$_read_key = get_option( 'wcm_super_key', '' );

		if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
			$_response = vip_safe_wp_remote_get( $this->get_api_url( $_path ), '', 3, 3, 20, $this->get_header_args( $_read_key ) );
		} else {
			$_response = wp_remote_get( $this->get_api_url( $_path ),$this->get_header_args( $_read_key ) );
		}

		return $this->process_response( $_response );
	}

	/**
	 * Check for slash ~ check for the existance of a foward slash on the string
	 * @param string $_string
	 * @return string ~ the string with a forward slash on the end of the string
	 */
	private function ensure_slash( $_string ) {
		if ( is_string( $_string ) ) {
			if ( '/' !== substr( $_string, -1 ) ) {
				return $_string . '/';
			}
			// has a '/' already
			return $_string;
		}
	}

	/**
	 * Get Single WCM Post
	 * @param  string $_wcm_id ~ Alphanumeric string
	 * @param  string $_client_id
	 * @return array
	 */
	public function get_post_single( $_wcm_id, $_client_id ) {
		return Data::get_content( $_wcm_id, array( 'client_id' => $_client_id ) );
	}

	/**
	 * Sets the client and license meta for the post.
	 * @param integer $_post_id
	 * @param string  $_client_id
	 */
	public function set_client_and_license( $_post_id, $_client_id ) {
		if ( empty( $_client_id ) ) {
			return;
		}
		add_post_meta( $_post_id, 'pn_market', $_client_id, true );

		$_licenses = Ajax::get_license_options( $_client_id );
		if ( ! empty( $_licenses ) && isset( $_licenses[0] ) && isset( $_licenses[0]['value'] ) ) {
			add_post_meta( $_post_id, 'pn_wcm_license', $_licenses[0]['value'], true );
		}
	}

	/**
	 * Is Empty ~ used a method to run empty variable checks against arrays using array_map()
	 * @param string $_var ~ a variable to check for empty
	 * @return boolean ~ True if empty and False if not empty
	 */
	private function is_empty( $_var = '' ) {
		if ( empty( $_var ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get Post By WCM ID and client ID ~ checks to see if a post already exists on the local server
	 * @param string $_wcm_id ~ A post's wcm_id
	 * @param string $_client_id ~ A post's market id
	 * @param string $_post_type ~ post ( default ), page, revision, attachment, nav_menu_item, any and custom post type
	 * @return int ~ 0 for posts that don't yet exist, or a post_id of an existing matching local post
	 */
	public function get_post_by_wcm_and_client_id( $_wcm_id, $client_id, $_post_type = 'post' ) {
		$_args = array(
			'post_type' => $_post_type,
			'post_status' => 'all',
			'posts_per_page' => 1,
			'suppress_filters' => true,
			'meta_query' => array(
				array(
					'key' => $this->meta_wcm_id,
					'value' => $_wcm_id,
					'compare' => '=',
				),
				array(
					'key' => $this->meta_market_id,
					'value' => $client_id,
					'compare' => '=',
				),
			),
		);

		$required_params = array( $_post_type, $_wcm_id, $client_id );
		$is_not_empty = array_filter( array_map( array( $this, 'is_empty' ) , $required_params ) );
		if ( ! empty( $is_not_empty ) ) {
			return true;
		}

		$_get_posts = new \WP_Query;
		$_posts = $_get_posts->query( $_args );

		if ( 0 < count( $_posts ) ) {
			return intval( $_posts[0]->ID );
		} else {
			return 0;
		}
	}

	/**
	 * Add Post Terms
	 * @param interger $_post_id ~ a WordPress post id
	 * @param array $_terms ~ a WordPress term array
	 * @param string $_type ~ a taxonomy type
	 * @return true || null
	 */
	private function add_post_terms( $_post_id = 0, $_terms = array(), $_type = 'category' ) {
		$_type = ( 'category' === $_type ) ? 'category' : 'post_tag'; // whitelist
		if ( is_array( $_terms ) ) {
			wp_set_object_terms( $_post_id, $_terms, $_type, false );
			return true;
		}
		return null;
	}

	/**
	 * Filter Allowed Image Types ~ take pathinfo and match allowed types against an image url
	 * @param string $_img_url ~ A WordPress image url
	 * @return string || null ~ a validated string or null
	 */
	private function filter_allowed_image_types( $_img_url ) {
		if ( is_string( $_img_url ) ) {
			$_url = wp_parse_url( $_img_url );
			if ( ! isset( $_url['path'] ) || false === $_url['path'] ) {
				return null;
			}

			$_ext = pathinfo( $_url['path'], PATHINFO_EXTENSION );
			$_allowed_types = array( 'jpg', 'jpe', 'jpeg', 'gif', 'png' );
			if ( in_array( $_ext, $_allowed_types, true ) ) {
				return esc_url_raw( $_img_url );
			}
			return null;
		}
	}

	/**
	 * Sideload WordPress Image
	 * @param integer $_post_id ~ a WordPress post id
	 * @param string $_mode ~ An image mode. Default is featured images
	 * @param object $_img_data ~ an object containing an image's properties
	 * @return null || integer ~ null on error or missing data; otherwise new image id'
	 */
	public function sideload_image( $_post_id, $_mode = 'featured', $_img_data = null ) {
		$_new_img_id = 0;

		if ( ! isset( $_img_data->url ) ) {
			return null;
		}

		$_img_url = trim( $_img_data->url );

		if ( '' !== $_img_url ) {
			$_img_url = $this->filter_allowed_image_types( $_img_url );

			if ( true === $this->is_empty( $_img_url ) ) {
				return null;
			}

			// Download a locaal temporary image using the WordPress
			// function specifically designed for this purpose
			$_temp_img = download_url( $_img_url );

			if ( is_wp_error( $_temp_img ) ) {
				// download failed, handle error
				return $_temp_img;
			}

			$_file_array = array();
			$_file_array['name'] = basename( wp_parse_url( $_img_url, PHP_URL_PATH ) );
			$_file_array['tmp_name'] = $_temp_img;

			// Set values for storage
			$_new_img = array();
			$_new_img['post_type'] = 'attachment';
			$_new_img['post_author'] = get_current_user_id();

			if ( isset( $_img_data ) ) {

				if ( isset( $_img_data->title ) ) {
					$_new_img['post_title'] = sanitize_text_field( $_img_data->title );
				}

				if ( isset( $_img_data->mime_type ) ) {
					$_new_img['post_mime_type'] = $_img_data->mime_type;
				}

				if ( isset( $_img_data->caption ) ) {
					$_new_img['post_excerpt'] = trim( sanitize_text_field( $_img_data->caption ) );
				}
			}

			// Need to require these files
			if ( ! function_exists( 'media_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
			}

			// Set up the image and connect it to the post
			$_new_img_id = media_handle_sideload( $_file_array, $_post_id, '', $_new_img );

			// If error storing permanently, unlink
			if ( is_wp_error( $_new_img_id ) ) {
				unlink( $_file_array['tmp_name'] ); // @codingStandardsIgnoreLine - need to remove temporary file unless VIP has a preferred approach
				return $_file_array;
			}

			if ( isset( $_img_data->_wp_attachment_image_alt ) ) {
				update_post_meta( $_new_img_id, '_wp_attachment_image_alt', $_img_data->_wp_attachment_image_alt );
			}

			if ( isset( $_img_data->credit ) ) {
				add_post_meta( $_new_img_id, 'pn_attachment_credit', $_img_data->credit, true );
				$_new_img['pn_attachment_credit'] = sanitize_text_field( $_img_data->credit );
			}

			if ( isset( $_img_data->distributor ) ) {
				add_post_meta( (int) $_new_img_id, 'pn_attachment_distributor', $_img_data->distributor, true );
				$_new_img['pn_attachment_distributor'] = sanitize_text_field( $_img_data->distributor );
			}

			if ( 'featured' === $_mode ) {
				set_post_thumbnail( $_post_id, $_new_img_id ); // make this image the featured one
			}

			return $_new_img_id;
		} else {
			return null;
		}
	}

	/**
	 * Parse Post Data
	 * @param  array $_post ~ A WCM post
	 * @return object ~ a post prepared for WordPress by combining keys, titles, tags, categories,
	 * meta data, featured image and post body content.
	 */
	public function parse_post_data( $_post ) {
		$_output = new \stdClass;
		$_keys = array( '_id', 'type', 'excerpt', 'origin_id', 'origin_cms', 'origin_url', 'origin_slug', 'global_slug' );

		foreach ( $_keys as $_key ) {
			$_output->{ $_key } = isset( $_post[ $_key ] ) ? trim( $_post[ $_key ] ) : '';
		}

		$_output->titles = $this->get_titles( $_post );
		$_output->tag_ids = $this->get_term_list( $_post, 'post_tag' );
		$_output->cat_ids = $this->get_term_list( $_post, 'category' );
		$_output->author = $this->get_author( $_post );
		$_output->byline = $this->get_byline( $_post );
		$_output->metadata = $this->get_post_meta( $_post );
		$_output->featured_image = $this->get_featured_image( $_post );
		$_output->body = '';

		if ( ( 0 === $_output->author->id ) && ( '' !== $_output->author->name ) ) {
			$_output->body .= '<p class="pn_wcm_byline">By: ' . esc_html( $_output->author->name ) . "</p>\n\n";
		}

		$_output->body .= $this->get_body_content( $_post );

		return $_output;
	}

	/**
	 * Get The Titles ~ get the titles of the WCM Post
	 * @param  array $_post ~ a WCM post
	 * @return object ~ a object containing WordPress formatted titles
	 */
	public function get_titles( $_post ) {
		$_output = null;
		$_keys = array( 'main', 'subtitle', 'alternate', 'concise', 'seo' );
		if ( isset( $_post['titles'] ) ) {
			$_output = new \stdClass;
			$_titles = $_post['titles'];
			foreach ( $_keys as $_key ) {
				$_output->{ $_key } = isset( $_titles[ $_key ] ) ? trim( $_titles[ $_key ] ) : '';
			}
		}
		return $_output;
	}

	/**
	 * Get Featured Image from WCM post
	 * @param object $_post ~ A WCM post object
	 * @return object ~ contains a WordPress formatted featured image
	 */
	public function get_featured_image( $_post ) {
		$_output = null;
		if ( isset( $_post['featured_media']['image']['url'] ) ) {
			$_output = (object) $_post['featured_media']['image'];
			unset( $_output->type );
			unset( $_output->origin_id );
			unset( $_output->created_on );
			unset( $_output->channels );

			$this->featured_image_attr = new \stdClass;
			if ( isset( $_post['featured_media']['image']['caption'] ) ) {
				$this->featured_image_attr->caption = $_post['featured_media']['image']['caption'];
			}

			if ( isset( $_post['featured_media']['image']['credit'] ) ) {
				$this->featured_image_attr->credit = $_post['featured_media']['image']['credit'];
			}

			if ( isset( $_post['featured_media']['image']['distributor'] ) ) {
				$this->featured_image_attr->distributor = $_post['featured_media']['image']['distributor'];
			}
		}
		return $_output;
	}

	/**
	 * Get Post Meta from WCM post
	 * @param  array $_post ~ A WCM post object
	 * @return array ~ an array of WordPress formatted Post Meta key / values
	 */
	public function get_post_meta( $_post ) {
		$_output = array();
		if ( isset( $_post['metadata'] ) && is_array( $_post['metadata'] ) ) {
			foreach ( $_post['metadata'] as $_key => $_val ) {
				if ( ( '_' !== substr( $_key, 0, 1 ) ) && ( ! empty( $_val ) ) ) {
					$_output[ $_key ] = $_val;
				}
			}
		}
		return $_output;
	}

	/**
	 * Get the Term List for a given Post
	 * @param array $_post ~ A WCM post object
	 * @param string $_term_type ~ a taxonomoy term type ie. category, post_tag...
	 * @return array ~ An array of post terms
	 */
	public function get_term_list( $_post, $_term_type = 'category' ) {
		$_term_ids = array();
		if ( isset( $_post['taxonomies'] ) ) {
			if ( 'post_tag' === $_term_type ) {
				$_wcm_terms = isset( $_post['taxonomies']['tags'] ) ? $_post['taxonomies']['tags'] : null;
			} else {
				$_wcm_terms = isset( $_post['taxonomies']['categories'] ) ? $_post['taxonomies']['categories'] : null;
				$_term_type = 'category'; // whitelisted now
			}
			if ( ! empty( $_wcm_terms ) ) {
				foreach ( $_wcm_terms as $_elem ) {
					$_tid = $this->get_term_id( $_elem['slug'], $_term_type );
					if ( 0 < $_tid ) {
						$_term_ids[] = $_tid;
					}
				}
				$_term_ids = array_unique( $_term_ids );
			}
		}
		return $_term_ids;
	}

	/**
	 * From ONR: Get a term ID from the slug and the term type
	 *
	 * @param $_slug (string) The term slug
	 * @param $_term_type (string) The term type (category|tag)
	 *
	 * @return (bool|object) Returns false if validation fails, object containing post data if succeeds
	 */
	function get_term_id( $_slug, $_term_type = 'category' ) {
		$_term_id = 0;
		if ( function_exists( 'wpcom_vip_get_term_by' ) ) {
			$_term = wpcom_vip_get_term_by( 'slug', $_slug, $_term_type, OBJECT, 'raw' );
		} else {
			$_term = get_term_by( 'slug', $_slug, $_term_type, OBJECT, 'raw' ); // @codingStandardsIgnoreLine - WPEngine use only
			// override okay
		}
		if ( false !== $_term ) {
			// if the term exists return its ID
			$_term_id = intval( $_term->term_id );
		}
		return $_term_id;
	}

	/**
	 * Set Author Byline(s) as meta-data of original story author to maintain copyright
	 * @param  array       $_post ~ WCM post object
	 * @return string|null
	 */
	public function get_byline( $_post ) {

		if ( isset( $_post['credits']['authors'][0] ) ) {
			$_byline = '';
			$_authors_count = count( $_post['credits']['authors'] );
			$i = 1;

			foreach ( $_post['credits']['authors'] as $_author ) {

				if ( $_authors_count !== $i ) {
					$_byline .= $_author['name'] . ', ';
				} else {
					$_byline .= $_author['name'];
				}
			}

			return $_byline;
		}
	}

	/**
	 * Get the author object from the WCM post
	 * @param  array $_post ~ WCM post object
	 * @return object ~ author object
	 */
	public function get_author( $_post ) {
		$_output = new \stdClass;
		$_slug = ( isset( $_post['credits']['authors'][0]['slug'] ) ) ? $_post['credits']['authors'][0]['slug'] : '';
		$_output->id = $this->get_author_by_username( $_slug );
		$_output->name = ( isset( $_post['credits']['authors'][0]['name'] ) ) ? $_post['credits']['authors'][0]['name'] : '';
		return $_output;
	}

	/**
	 * Update the author on the post
	 * @param string $_author_login ~ Username of the author on the originating post
	 * @return null
	 */
	function get_author_by_username( $_author_slug = '' ) {
		$_author_uid = 0;
		$_author_slug = trim( $_author_slug );
		if ( '' !== $_author_slug ) {
			$_author = get_user_by( 'login', $_author_slug );
			if ( isset( $_author->user_login ) ) {
				$_author_uid = intval( $_author->ID );
			}
		}
		return $_author_uid;
	}

	/**
	 * Get the WCN post body - it will be individual content elements
	  * @param object $_post ~ WCM post object
	 */
	public function get_body_content( $_post ) {
		$_output = '';
		if ( isset( $_post['content_elements'] ) && is_array( $_post['content_elements'] ) ) {
			foreach ( $_post['content_elements'] as $_elem ) {
				if ( isset( $_elem['type'] ) ) {
					switch ( $_elem['type'] ) {
						case 'text':
						case 'raw_html':
							$_output .= $this->get_element_text( $_elem );
							break;
						case 'oembed':
							// for mvp grab the rendered HTML rather than the original text in $_elem->object_url
							$_output .= $this->get_element_oembed( $_elem );
							break;
						case 'image':
							$_output .= $this->get_element_image( $_elem );
							break;
						default:
							break;
					}
				}
			}
		}
		return $_output;
	}

	/**
	 * Get elements' image
	 * @param object $_content ~ the content object inside the post body
	 * @return object ~ a content elements' image object'
	*/
	public function get_element_image( $_content ) {
		$_output = '';
		if ( isset( $_content['url'] ) && ( '' !== trim( $_content['url'] ) ) ) {
			$_output = '<img class="wp-post-image" src="' . esc_url( trim( $_content['url'] ) ). '"';
			if ( isset( $_content['title'] ) && ( '' !== trim( $_content['title'] ) ) ) {
				$_output .= ' alt="' . esc_attr( trim( $_content['title'] ) ) . '"';
			}
			if ( isset( $_content['width'] ) && ( 0 < intval( $_content['width'] ) ) ) {
				$_output .= ' width="' . intval( $_content['width'] ) . '"';
			}
			if ( isset( $_content['height'] ) && ( 0 < intval( $_content['height'] ) ) ) {
				$_output .= ' height="' . intval( $_content['height'] ) . '"';
			}
			$_output .= ' />' . "\n\n";
		}
		return $_output;
	}

	/**
	 * Get elements' text
	 * @param object $_content ~ the content object inside the post body
	 * @return string ~ a content elements' text string
	*/
	public function get_element_text( $_content ) {
		$_text = isset( $_content['content'] ) ? trim( $_content['content'] ) : '';
		if ( '' !== $_text ) {
			if ( ( '[' === substr( $_text, 0, 1 ) ) && ( ']' === substr( $_text, -1 ) ) ) {
				$_text = ''; // exclude untranslated embed codes
			}
		}
		$_output = ( '' !== $_text ) ? $_text . "\n\n" : '';
		return $_output;
	}

	/**
	 * Get elements' oembed codes
	 * @param object $_content ~ the content object inside the post body
	 * @return string ~ a content elements' oembed html
	*/
	public function get_element_oembed( $_content ) {
		$_output .= isset( $_content['html'] ) ? $_content['html'] . "\n\n" : '';
		return $_output;
	}

	/**
	 * Santize and unslash a value
	 * @param string $value - a value that will be sanitized
	 */
	public function postmedia_sanitize_text_field( $value ) {
		return  sanitize_text_field( wp_unslash( $value ) );
	}

	/**
	* AJAX endpoint - JSON Lookup Posts
	* Get local posts based on WCM ID and return whether they have been copied here, originated here,
	* and/or have had pointers made here.
	* Echo JSON object back to the caller of this endpoint
	* @return null
	*/
	public function json_lookup_posts() {
		check_ajax_referer( 'wcm_editorial_dashboard_api_nonce', 'nonce' );

		if ( true === $this->validate_role() ) {
			$_output = array();

			if ( isset( $_POST['wcm_obj_list'] ) ) {
				$_wcm_obj_list = json_decode( sanitize_text_field( wp_unslash( $_POST['wcm_obj_list'] ) ) );

				if ( is_array( $_wcm_obj_list ) ) {
					$_client_id = isset( $_POST['client_id'] ) ? sanitize_text_field( wp_unslash( $_POST['client_id'] ) ) : '';
					$_ids = $this->get_postlist_by_wcm_id( $_wcm_obj_list, $_client_id );

					foreach ( $_ids as $_wid => $_obj ) {
						$_obj->id = $_wid;
						$_output[] = $_obj;
					}
				}
			}

			print( wp_json_encode( $_output ) );
		}
		die();
	}

	/**
	* Get Postlist by WCM id
	* @param array $_list ~ a list of WCM post objects
	* @param string $_client_id
	* @return object ~ A post list of license access to the current list of posts
	*/
	public function get_postlist_by_wcm_id( $_list = array(), $_client_id = '' ) {
		$_output = array();
		$_wcm_id_list = array();
		$_wcm_ary_list = array();

		foreach ( $_list as $_wcm_obj ) {
			$_wcm_id_list[] = $_wcm_obj->wcm_id;
			$_wcm_ary_list[] = array(
				'wcm_id' => $_wcm_obj->wcm_id,
				'license_id' => $_wcm_obj->license_id,
			);
			$_output[ $_wcm_obj->wcm_id ] = new \stdClass;
			$_output[ $_wcm_obj->wcm_id ]->id = '';
			$_output[ $_wcm_obj->wcm_id ]->license = $_wcm_obj->license_id;
			$_output[ $_wcm_obj->wcm_id ]->is_origin = $this->get_is_origin( $_wcm_obj );
			$_output[ $_wcm_obj->wcm_id ]->can_copy = true;
			$_output[ $_wcm_obj->wcm_id ]->can_point = true;
			$_output[ $_wcm_obj->wcm_id ]->has_license = true;
		}

		$_output = $this->get_copies_by_wcm_list( $_wcm_id_list, $_output, $_client_id );
		$_output = $this->get_pointers_by_wcm_list( $_wcm_id_list, $_output, $_client_id );
		$_output = $this->determine_license( $_wcm_ary_list, $_output, $_client_id );

		return $_output;
	}

	/**
	 * Get copies by WCM List
	 * @param array $_list ~ a list of WCM post objects
	 * @param array $_output a filtered version of the WCM list
	 * @return array ~ filtered $_output
	 */
	public function get_copies_by_wcm_list( $_wcm_id_list = array(), $_output = array(), $_client_id ) {
		// check for copy
		$_args = array(
			'post_type' => 'post',
			'post_status' => 'all',
			'posts_per_page' => 50,
			'suppress_filters' => true,
			'meta_query' => array(
				array(
					'key' => $this->meta_wcm_id,
					'value' => $_wcm_id_list,
					'compare' => 'IN',
				),
			),
		);

		if ( get_option( 'wcm_multi_market', false ) ) {
			$_args['meta_query'][] = array(
				'key' => $this->meta_market_id,
				'value' => $_client_id,
				'compare' => '=',
			);
		}

		$_get_posts = new \WP_Query;
		$_posts = $_get_posts->query( $_args );

		foreach ( $_posts as $_post ) {
			$_wcm_id = get_post_meta( $_post->ID, $this->meta_wcm_id, true );

			if ( '' !== trim( $_wcm_id ) ) {

				if ( ! isset( $_output[ $_wcm_id ] ) ) {
					$_output[ $_wcm_id ] = new \stdClass;
				}

				$_output[ $_wcm_id ]->can_copy = false;
			}
		}
		return $_output;
	}

	/**
	 * Get clients from the API ~ adapter that sets the path for $this->get_wcm_data()
	 * @param string $_id ~ WCM id
	 * @return string ~ JSON object of clients
	 */
	public function get_clients( $_id = '' ) {
		$_path = 'clients/' . $_id;
		return $this->call_wcm_data( $_path );
	}

	/**
	 * Get licenses from the API ~ adapter that sets the path for $this->get_wcm_data()
	 * @param string $_id ~ WCM id
	 * @return string ~ JSON object of licenses
	 */
	public function get_licenses( $_id = '' ) {
		$_path = 'licenses/' . $_id;
		return $this->get_wcm_data( $_path );
	}

	/**
	 * Get client license
	 * @param string $_client_id
	 * @return string ~ current client's licencse
	 */
	public function get_client_licenses( $_client_id ) {
		if ( empty( $_client_id ) ) {
			$_client_id = get_option( 'wcm_client_id', '' );
		}
		if ( isset( $_client_id ) ) {
			$_client_obj = $this->get_clients( $_client_id );
			if ( isset( $_client_obj ) ) {
				if ( isset( $_client_obj->licenses ) ) {
					return $_client_obj->licenses;
				}
			}
		}
		return false;
	}

	/**
	 * Determine license
	 * @param array $_wcm_ary_list ~ a list of wcm posts
	 * @param array $_output
	 * @param string $_client_id
	 * @return array ~ array matching wcm post id with has license boolean
	 */
	public function determine_license( $_wcm_ary_list = array(), $_output = array(), $_client_id = '' ) {
		$_client_licenses = $this->get_client_licenses( $_client_id );
		foreach ( $_wcm_ary_list as $_posts ) {
			if ( isset( $_posts['license_id'], $_posts['wcm_id'] ) ) {
				$_output[ $_posts['wcm_id'] ]->has_license = in_array( $_posts['license_id'], $_client_licenses, true );
			}
		}
		return $_output;
	}

	/**
	 * Get is_origin
	 * @param object $_wcm_obj ~ current object of the  iteration while looping the wcm objects
	 * @return boolean ~ true or false
	 */
	public function get_is_origin( $_wcm_obj ) {
		if ( ! isset( $this->client_id ) ) {
			$this->client_id = get_option( 'wcm_client_id', '' );
		}
		if ( isset( $_wcm_obj, $_wcm_obj->client_id ) ) {
			if ( $_wcm_obj->client_id === $this->client_id ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Get pointers by WCM List
	 * @param array $_list ~ a list of WCM post objects
	 * @param array $_output a filtered version of the WCM list
	 * @return array ~ filtered $_output
	 */
	public function get_pointers_by_wcm_list( $_wcm_id_list = array(), $_output = array(), $_client_id ) {
		// check for pointers
		$_args = array(
			'post_type' => 'pn_pointer',
			'post_status' => 'all',
			'posts_per_page' => 50,
			'suppress_filters' => true,
			'meta_query' => array(
				array(
					'key' => $this->meta_wcm_id,
					'value' => $_wcm_id_list,
					'compare' => 'IN',
				),
			),
		);

		if ( get_option( 'wcm_multi_market', false ) ) {
			$_args['meta_query'][] = array(
				'key' => $this->meta_market_id,
				'value' => $_client_id,
				'compare' => '=',
			);
		}

		$_get_posts = new \WP_Query;
		$_posts = $_get_posts->query( $_args );

		foreach ( $_posts as $_post ) {
			$_wcm_id = get_post_meta( $_post->ID, $this->meta_wcm_id, true );
			if ( '' !== trim( $_wcm_id ) ) {
				if ( ! isset( $_output[ $_wcm_id ] ) ) {
					$_output[ $_wcm_id ] = new \stdClass;
				}
				$_output[ $_wcm_id ]->can_point = false;
			}
		}
		return $_output;
	}

	/**
	 * Log ~ log a message to the syslog. Helps with Ajax debugging of 500 errors plus anything that does not
	 * return results.
	 * @param string $_msg ~ a message to log
	 * @param integer $_priority ~ a syslog priority. Default is LOG_DEBUG
	 * @return null;
	 */
	public function log( $msg, $priority = LOG_DEBUG ) {
		syslog( $priority, $msg );
		return null;
	}
}
