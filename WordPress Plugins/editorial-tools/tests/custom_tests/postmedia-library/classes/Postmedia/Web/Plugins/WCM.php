<?php

namespace Postmedia\Web\Plugins;

use Postmedia\Web\Data\DataHelper;
use Postmedia\Web\Data\StorageAdapter\WCM as WCMStorageAdapter;

class WCM {
	private $data = null;
	private $dummy_post_id = PHP_INT_MAX;
	private $dummy_featured_image_id = null;
	private $dummy_author_id = null;
	private $images = array();
	private $users = array();

	public function __construct() {
		$this->users = array(
			'id'    => array(),
			'email' => array(),
			'slug'  => array(),
		);

		add_action( 'init', array( $this, 'action_init' ) );
		add_action( 'template_redirect', array( $this, 'action_template_redirect' ) );
		add_action( 'parse_request', array( $this, 'action_parse_request' ), 5 );
		add_filter( 'query_vars', array( $this, 'filter_query_vars' ) );
	}

	/**
	 * Handles URLs with the format /(CATEGORY/)?POST-NAME/(SP-ID/story.html/)?wcm/WCM-ID as WCM posts.
	 *
	 * @return void
	 */
	public function action_init() {
		// SouthPARC
		add_rewrite_rule( '([^/]+/[0-9]+/story\.html)/wcm/([^/]+)/?$', 'index.php?name=$matches[1]&wcm_id=$matches[2]', 'top' );
		add_rewrite_rule( '(.+?)/([^/]+/[0-9]+/story\.html)/wcm/([^/]+)/?$', 'index.php?category_name=$matches[1]&name=$matches[2]&wcm_id=$matches[3]', 'top' );

		// WordPress
		add_rewrite_rule( '([^/]+)/wcm/([^/]+)/?$', 'index.php?name=$matches[1]&wcm_id=$matches[2]', 'top' );
		add_rewrite_rule( '(.+?)/([^/]+)/wcm/([^/]+)/?$', 'index.php?category_name=$matches[1]&name=$matches[2]&wcm_id=$matches[3]', 'top' );

		add_rewrite_tag( '%wcm_id%', '([^&]+)' );
	}

	/**
	 * Redirects singular posts from non-WCM URL to WCM URL if the post exists in WCM.
	 * Redirects unsupported post types to the original URL.
	 * Redirects posts with unsupported shortcodes to the original URL.
	 *
	 * @return void
	 */
	public function action_template_redirect() {
		$wcm_id = get_query_var( 'wcm_id' );

		// This is a post that exists in the current database.
		if ( is_singular() ) {
			global $post;
			return $this->redirect_singular( $post->ID );
		}

		// This page has nothing to do with WCM.
		if ( empty( $wcm_id ) ) {
			return;
		}

		// This is a WCM post that does not exist in the current database.
		return $this->redirect_wcm();
	}

	/**
	 * Redirects posts that exist in the current database to the WCM version of the same post.
	 *
	 * @param  int  $post_id
	 * @return void
	 */
	private function redirect_singular( $post_id ) {
		// If this is already the WCM version, do not redirect.
		$wcm_id = get_query_var( 'wcm_id' );
		if ( ! empty( $wcm_id ) ) {
			return;
		}

		// If a WCM version is available, redirect to it.
		$wcm_id = get_post_meta( $post_id, 'pn_wcm_id', true );
		if ( ! empty( $wcm_id ) ) {
			$url = $this->get_wcm_url( get_permalink( $post_id ), $wcm_id );
			return postmedia_library_redirect( $url );
		}

		// There is no WCM version available.
		return;
	}

	/**
	 * Redirects WCM posts that do not exist in the current database to the origin if necessary.
	 *
	 * @param  string $wcm_id
	 * @return void
	 */
	private function redirect_wcm() {
		// If there is no origin URL, redirecting to the original isn't possible.
		$origin_url = isset( $this->data['origin_url'] ) ? $this->data['origin_url'] : '';
		if ( empty( $origin_url ) ) {
			return;
		}

		// If the origin URL is on this domain, don't redirect because it could cause a loop.
		$current_url = parse_url( home_url() );
		$wcm_url = parse_url( $origin_url );
		if ( $current_url['host'] == $wcm_url['host'] ) {
			return;
		}

		// If the post has an unsupported post type, redirect to the origin.
		$type = isset( $this->data['type'] ) ? DataHelper::get_wp_type( $this->data['type'] ) : '';
		if ( empty( $type ) || ! post_type_exists( $type ) ) {
			return postmedia_library_redirect( $origin_url, 302 );
		}

		// If the post has an unsupported shortcode, redirect to the origin.
		if ( $this->has_unsupported_shortcode() ) {
			return postmedia_library_redirect( $origin_url, 302 );
		}

		// This is a valid WCM post that does not exist in the current database.
		return;
	}

	/**
	 * Filters WP functions on WCM pages to use WCM data.
	 *
	 * @param  WP   $wp
	 * @return void
	 */
	public function action_parse_request( $wp ) {
		// If this isn't a WCM post, don't do anything.
		$wcm_id = isset( $wp->query_vars['wcm_id'] ) ? $wp->query_vars['wcm_id'] : null;
		if ( empty( $wcm_id ) ) {
			return;
		}

		// Get the data for this WCM post.
		$this->set_data( $this->get_data( $wcm_id ) );

		// If there was an error or the post isn't visible, throw a 404.
		$post_id = null;
		if ( isset( $wp->query_vars['p'] ) ) {
			$post_id = $wp->query_vars['p'];
		} elseif ( isset( $wp->query_vars['name'] ) && isset( $wp->query_vars['category_name'] ) ) {
			$args = array(
				'posts_per_page' => 1,
				'category_name'  => $wp->query_vars['category_name'],
				'name'           => $wp->query_vars['name'],
				'fields'         => 'ids',
			);
			$q = new \WP_Query( $args );
			if ( ! empty( $q->posts ) ) {
				$post_id = $q->posts[0];
			}
		}
		if ( empty( $this->data ) || ! $this->is_valid_wcm_post( $wp->request, $wcm_id, $post_id ) ) {
			$this->data = null;
			add_action( 'template_redirect', array( $this, 'action_template_redirect_404' ) );
			return;
		}

		// Index related records so they can be retrieved by the query filter when calling WordPress functions like get_post() or get_userdata().
		// Index users first so images can set their author.
		$this->index_users();
		$this->index_images();

		// Add post filters.
		add_action( 'template_include', array( $this, 'action_template_include' ) );
		add_filter( 'posts_request', array( $this, 'filter_posts_request' ), 5, 2 );
		add_filter( 'query', array( $this, 'filter_query' ) );
		add_filter( 'the_posts', array( $this, 'filter_the_posts' ), 5, 2 );
		add_filter( 'get_post_metadata', array( $this, 'filter_get_post_metadata' ), 10, 4 );
		add_filter( 'get_the_terms', array( $this, 'filter_get_the_terms' ), 10, 3 );
		add_filter( 'post_link', array( $this, 'filter_post_link' ), 10, 3 );
		add_filter( 'post_type_link', array( $this, 'filter_post_type_link' ), 10, 4 );
		add_filter( 'the_content', array( $this, 'filter_the_content' ) );

		// Add image filters.
		add_filter( 'wp_get_attachment_image_src', array( $this, 'filter_wp_get_attachment_image_src' ), 10, 4 );

		// Add user filters.
		add_filter( 'get_the_author_aim', array( $this, 'filter_get_the_author_aim' ), 10, 3 );
		add_filter( 'get_the_author_bio', array( $this, 'filter_get_the_author_bio' ), 10, 3 );
		add_filter( 'get_the_author_description', array( $this, 'filter_get_the_author_bio' ), 10, 3 );
		add_filter( 'get_the_author_discourse_username', array( $this, 'filter_get_the_author_discourse' ), 10, 3 );
		add_filter( 'get_the_author_display_name', array( $this, 'filter_get_the_author_name' ), 10, 3 );
		add_filter( 'get_the_author_first_name', array( $this, 'filter_get_the_author_first_name' ), 10, 3 );
		add_filter( 'get_the_author_icq', array( $this, 'filter_get_the_author_icq' ), 10, 3 );
		add_filter( 'get_the_author_ID', array( $this, 'filter_get_the_author_id' ), 10, 3 );
		add_filter( 'get_the_author_last_name', array( $this, 'filter_get_the_author_last_name' ), 10, 3 );
		add_filter( 'get_the_author_msn', array( $this, 'filter_get_the_author_msn' ), 10, 3 );
		add_filter( 'get_the_author_nickname', array( $this, 'filter_get_the_author_slug' ), 10, 3 );
		add_filter( 'get_the_author_slug', array( $this, 'filter_get_the_author_slug' ), 10, 3 );
		add_filter( 'get_the_author_twitter', array( $this, 'filter_get_the_author_twitter' ), 10, 3 );
		add_filter( 'get_the_author_user_email', array( $this, 'filter_get_the_author_email' ), 10, 3 );
		add_filter( 'get_the_author_user_login', array( $this, 'filter_get_the_author_slug' ), 10, 3 );
		add_filter( 'get_the_author_user_nicename', array( $this, 'filter_get_the_author_slug' ), 10, 3 );
		add_filter( 'get_the_author_user_url', array( $this, 'filter_get_the_author_url' ), 10, 3 );
		add_filter( 'get_the_author_yim', array( $this, 'filter_get_the_author_yim' ), 10, 3 );
		add_filter( 'pre_get_avatar_data', array( $this, 'filter_pre_get_avatar_data' ), 10, 2 );
		add_filter( 'author_link', array( $this, 'filter_author_link' ), 10, 3 );
	}

	/**
	 * Retrieves data from WCM.
	 *
	 * @param  string $wcm_id
	 * @return array
	 */
	public function get_data( $wcm_id ) {
		return WCMStorageAdapter::get_content( $wcm_id );
	}

	/**
	 * Stores the data for the current post.
	 *
	 * @param  array $data
	 * @return void
	 */
	private function set_data( $data ) {
		$this->data = $data;
	}

	/**
	 * Makes wcm_id accessible through WP object query vars.
	 *
	 * @param  array $public_query_vars
	 * @return array
	 */
	public function filter_query_vars( $public_query_vars ) {
		$public_query_vars[] = 'wcm_id';
		return $public_query_vars;
	}

	/**
	 * Forces a 404 (using code from class-wp.php handle_404()).
	 *
	 * @return void
	 */
	public function action_template_redirect_404() {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	/**
	 * Indexes images for the current post.
	 *
	 * @return array
	 */
	private function index_images() {
		// Index the featured image. (Images are posts, so the ID must be different from the dummy post ID to differentiate them.)
		$image_index = $this->dummy_post_id;
		if ( isset( $this->data['featured_media']['image'] ) && ! empty( $this->data['featured_media']['image'] ) ) {
			// Get the image's ID.
			$image_index--;

			// Get the image data.
			$image_data = $this->get_image_from_data( $this->data['featured_media']['image'], $image_index );

			// Index the image data.
			$this->images[ $image_index ] = $this->data['featured_media']['image'];
			$this->dummy_featured_image_id = $image_index;
		}

		// Index embedded images.
		$content_elements = isset( $this->data['content_elements'] ) ? $this->data['content_elements'] : array();
		foreach ( $content_elements as $element ) {
			// Skip non-image elements.
			if ( 'image' !== $element['type'] ) {
				continue;
			}

			// Get the image's ID.
			$image_index--;

			// Index the image data.
			$this->images[ $image_index ] = $element;
		}

		return $this->images;
	}

	/**
	 * Indexes authors for the current post.
	 *
	 * @return array
	 */
	private function index_users() {
		$author_index = PHP_INT_MAX;
		$authors = isset( $this->data['credits']['authors'] ) ? $this->data['credits']['authors'] : array();
		foreach ( $authors as $author ) {
			// Save the user data so we can refer to it.
			$this->users['id'][ $author_index ] = $author;
			if ( empty( $this->dummy_author_id ) ) {
				$this->dummy_author_id = $author_index;
			}

			// Since get_user_by fetches users by ID, user_nicename, user_email, and user_login, create indexes for those fields as well.
			$email = isset( $author['email'] ) ? $author['email'] : '';
			if ( ! empty( $email ) ) {
				$this->users['email'][ $email ] = $author_index;
			}
			$slug = isset( $author['slug'] ) ? $author['slug'] : '';
			if ( ! empty( $slug ) ) {
				$this->users['slug'][ $slug ] = $author_index;
			}

			// Get the next user's ID.
			$author_index--;
		}

		return $this->users;
	}

	/**
	 * Sets the template for WCM pages.
	 *
	 * @param  string $template
	 * @return string
	 */
	public function action_template_include( $template ) {
		if ( ! $this->is_wcm_request() ) {
			return $template;
		}
		return get_single_template();
	}

	/**
	 * Cancels the main query on WCM pages.
	 *
	 * @param  string   $request
	 * @param  WP_Query $query
	 * @return string
	 */
	public function filter_posts_request( $request, $query ) {
		if ( ! $query->is_main_query() || is_admin() || ! $this->is_wcm_request() ) {
			return $request;
		}
		return '';
	}

	/**
	 * Replaces database queries for dummy image and user IDs.
	 *
	 * @param  string $query
	 * @return string
	 */
	public function filter_query( $query ) {
		global $wpdb;

		$image_id = $this->get_dummy_image_id( $query );

		if ( ! empty( $image_id ) ) {
			// This is a query for a WCM image.
			$image = $this->images[ $image_id ];
			$columns = array(
				'ID'                    => $image_id,
				'post_author'           => $this->dummy_author_id,
				'post_date'             => isset( $image['created_on'] ) ? $image['created_on'] : '',
				'post_date_gmt'         => isset( $image['created_on'] ) ? $image['created_on'] : '',
				'post_content'          => isset( $image['description'] ) ? $image['description'] : '',
				'post_title'            => isset( $image['title'] ) ? $image['title'] : '',
				'post_excerpt'          => isset( $image['caption'] ) ? $image['caption'] : '',
				'post_status'           => 'inherit',
				'comment_status'        => 'open',
				'ping_status'           => 'closed',
				'post_password'         => '',
				'post_name'             => '',
				'to_ping'               => '',
				'pinged'                => '',
				'post_modified'         => '',
				'post_modified_gmt'     => '',
				'post_content_filtered' => '',
				'post_parent'           => $this->dummy_post_id,
				'guid'                  => isset( $image['url'] ) ? $image['url'] : '',
				'menu_order'            => '',
				'post_type'             => 'attachment',
				'post_mime_type'        => isset( $image['mime_type'] ) ? $image['mime_type'] : '',
				'comment_count'         => '0',
			);
		} else {
			$user_id = $this->get_dummy_user_id( $query );
			if ( ! empty( $user_id ) ) {
				// This is a query for a WCM user.
				$user = $this->users['id'][ $user_id ];
				$columns = array(
					'ID'                  => $user_id,
					'user_login'          => isset( $user['slug'] ) ? $user['slug'] : '',
					'user_pass'           => '',
					'user_nicename'       => isset( $user['slug'] ) ? $user['slug'] : '',
					'user_email'          => isset( $user['email'] ) ? $user['email'] : '',
					'user_url'            => isset( $user['url'] ) ? $user['url'] : '',
					'user_registered'     => '',
					'user_activation_key' => '',
					'user_status'         => 0,
					'display_name'        => isset( $user['name'] ) ? $user['name'] : '',
					'spam'                => 0,
					'deleted'             => 0,
				);
			} else {
				return $query;
			}
		}

		$fields = array();
		$args = array();
		foreach ( $columns as $column => $field ) {
			$fields[] = '%s AS %s';
			$args[] = $field;
			$args[] = $column;
		}
		$fields = implode( ', ', $fields );

		return $wpdb->prepare( 'SELECT ' . $fields . ' LIMIT 1', $args ); // @codingStandardsIgnoreLine - $fields contains only "%s AS %s" joined with commas.
	}

	/**
	 * Replaces the retrieved posts with the WCM post.
	 *
	 * @param  array    $posts
	 * @param  WP_Query $query
	 * @return array
	 */
	public function filter_the_posts( $posts, $query ) {
		global $wp, $wp_query;

		// Ensure this filter runs only once.
		remove_filter( 'the_posts', array( $this, 'filter_the_posts' ), 5 );

		if ( empty( $this->data ) || ! $this->is_wcm_request() ) {
			return $posts;
		}

		$p = $this->get_post_from_data( $this->data );

		$wp_query->is_page             = false;
		$wp_query->is_single           = true;
		$wp_query->is_singular         = true;
		$wp_query->is_home             = false;
		$wp_query->is_archive          = false;
		$wp_query->is_category         = false;
		unset( $wp_query->query['error'] );
		$wp->query                     = array();
		$wp_query->query_vars['error'] = '';
		$wp_query->is_404              = false;

		$wp_query->current_post        = -1;
		$wp_query->found_posts         = 1;
		$wp_query->post_count          = 1;
		$wp_query->comment_count       = 0;
		$wp_query->current_comment     = null;

		$wp_query->post                = $p;
		$wp_query->posts               = array( $p );
		$wp_query->queried_object      = $p;
		$wp_query->queried_object_id   = $p->ID;

		return array( $p );
	}

	/**
	 * Uses WCM guid data for get_the_guid().
	 *
	 * @param  string $guid
	 * @param  int    $id
	 * @return string
	 */
	public function filter_get_the_guid( $guid, $id ) {
		if ( ! $this->is_wcm_post( $id ) ) {
			return $guid;
		}

		if ( isset( $this->data['origin_url'] ) ) {
			return $this->data['origin_url'];
		}

		return '';
	}

	/**
	 * Uses WCM term data for get_the_terms().
	 *
	 * @param  array|WP_Error $terms
	 * @param  int            $post_id
	 * @param  string         $taxonomy
	 * @return array|WP_Error
	 */
	public function filter_get_the_terms( $terms, $post_id, $taxonomy ) {
		if ( ! $this->is_wcm_post( $post_id ) ) {
			return $terms;
		}

		// Get the list of term data.
		$output = array();
		$found_terms = array();
		$label = DataHelper::taxonomy_to_label( $taxonomy );
		if ( 'author' == $label ) {
			$terms = isset( $this->data['credits']['authors'] ) ? $this->data['credits']['authors'] : array();
		} else {
			$terms = isset( $this->data['taxonomies'][ $label ] ) ? $this->data['taxonomies'][ $label ] : array();
		}

		// Get the corresponding WordPress term for each term.
		foreach ( $terms as $term ) {
			$term = $this->get_term_from_data( $term, $taxonomy );
			if ( ! empty( $term ) && ! in_array( $term->name, $found_terms ) ) {
				$output[] = $term;

				// Prevent duplicate terms (eg. Post has categories Senators and Canucks, which both have parent Hockey, but the current site does not have Senators or Canucks categories).
				$found_terms[] = $term->name;
			}
		}

		return $output;
	}

	/**
	 * Uses WCM meta data for get_post_meta().
	 *
	 * @param  null|array|string $meta_value
	 * @param  int               $object_id
	 * @param  string            $meta_key
	 * @param  boolean           $single
	 * @return string|array
	 */
	public function filter_get_post_metadata( $meta_value, $object_id, $meta_key, $single ) {
		if ( ! $this->is_wcm_post( $object_id ) ) {
			$image_data = $this->is_wcm_image( $object_id, true );
			if ( ! empty( $image_data ) ) {
				switch ( $meta_key ) {
					case '_wp_attached_file':
						return isset( $image_data['url'] ) ? $image_data['url'] : '';

					case '_wp_attachment_image_alt':
						return isset( $image_data['title'] ) ? $image_data['title'] : '';

					case '_wp_attachment_metadata':
						return array(
							'width'      => isset( $image_data['width'] ) ? $image_data['width'] : '',
							'height'     => isset( $image_data['height'] ) ? $image_data['height'] : '',
							'file'       => isset( $image_data['url'] ) ? $image_data['url'] : '',
							'sizes'      => array(),
							'image_meta' => array(
								'aperture'          => '',
								'credit'            => isset( $image_data['credit'] ) ? $image_data['credit'] : '',
								'camera'            => '',
								'caption'           => isset( $image_data['caption'] ) ? $image_data['caption'] : '',
								'created_timestamp' => isset( $image_data['created_on'] ) ? strtotime( $image_data['created_on'] ) : '',
								'copyright'         => '',
								'focal_length'      => '',
								'iso'               => '',
								'shutter_speed'     => '',
								'title'             => isset( $image_data['title'] ) ? $image_data['title'] : '',
								'orientation'       => '',
								'keywords'          => array(),
							),
						);

					case 'pn_attachment_credit':
						return isset( $image_data['credit'] ) ? $image_data['credit'] : '';

					case 'pn_attachment_distributor':
						return isset( $image_data['distributor'] ) ? $image_data['distributor'] : '';
				}
			}
			return $meta_value;
		}

		if ( isset( $this->data['metadata'][ $meta_key ] ) ) {
			return maybe_unserialize( $this->data['metadata'][ $meta_key ] );
		}

		switch ( $meta_key ) {
			case '_pn_main_category':
				if ( isset( $this->data['taxonomies']['main_taxonomies'] ) ) {
					foreach ( $this->data['taxonomies']['main_taxonomies'] as $term ) {
						$type = isset( $term['type'] ) ? $term['type'] : '';
						if ( 'category' != $type ) {
							continue;
						}
						$term = $this->get_term_from_data( $term );
						if ( ! empty( $term ) ) {
							return $term->term_id;
						}
					}
					return '';
				}
				break;

			case '_pn_related_links':
				if ( isset( $this->data['related_content'] ) ) {
					$related_content = $this->data['related_content'];
					$output = array();
					foreach ( $related_content as $content ) {
						$link = new \stdClass();
						$link->url = $content['url'];
						$link->text = $content['title'];
						$output[] = $link;
					}
					if ( $single ) {
						return array( $output );
					}
					return $output;
				}
				break;

			case '_pn_title_alternate':
				if ( isset( $this->data['titles']['alternate'] ) ) {
					return $this->data['titles']['alternate'];
				}
				break;

			case '_thumbnail_id':
				// Ensure has_post_thumbnail() and get_post_thumbnail_id() work.
				if ( ! empty( $this->dummy_featured_image_id ) ) {
					return $this->dummy_featured_image_id;
				}
				break;

			case 'advertorial_meta_box':
				if ( isset( $this->data['advertorial'] ) ) {
					return $this->data['advertorial'];
				}
				break;

			case 'featured-gallery-id':
				if ( isset( $this->data['featured_media']['gallery']['gallery_id'] ) ) {
					return $this->data['featured_media']['gallery']['gallery_id'];
				}
				break;

			case 'mt_seo_title':
				if ( isset( $this->data['titles']['seo'] ) ) {
					return $this->data['titles']['seo'];
				}
				break;

			case 'pn_featured_image_caption':
				if ( isset( $this->data['featured_media']['image']['caption'] ) ) {
					return $this->data['featured_media']['image']['caption'];
				}
				break;

			case 'pn_featured_video_description':
				if ( isset( $this->data['featured_media']['video']['description'] ) ) {
					return $this->data['featured_media']['video']['description'];
				}
				break;

			case 'pn_featured_video_id':
				if ( isset( $this->data['featured_media']['video']['_id'] ) ) {
					return $this->data['featured_media']['video']['_id'];
				}
				break;

			case 'pn_featured_video_inline':
				if ( isset( $this->data['featured_media']['video']['inline'] ) ) {
					return $this->data['featured_media']['video']['inline'];
				}
				break;

			case 'pn_featured_video_title':
				if ( isset( $this->data['featured_media']['video']['title'] ) ) {
					return $this->data['featured_media']['video']['title'];
				}
				break;

			case 'pn_sendtonews_player_id':
				if ( isset( $this->data['featured_media']['s2n_video']['_id'] ) ) {
					return $this->data['featured_media']['s2n_video']['_id'];
				}
				break;
		}

		return '';
	}

	/**
	 * Uses WCM permalink data for get_permalink().
	 *
	 * @param  string  $permalink
	 * @param  WP_Post $post
	 * @param  boolean $leavename
	 * @return string
	 */
	public function filter_post_link( $permalink, $post, $leavename ) {
		if ( ! $this->is_wcm_post( $post ) ) {
			return $permalink;
		}

		if ( isset( $this->data['origin_url'] ) ) {
			return $this->data['origin_url'];
		}

		return '';
	}

	/**
	 * Uses WCM permalink data for get_permalink().
	 *
	 * @param  string  $permalink
	 * @param  WP_Post $post
	 * @param  boolean $leavename
	 * @param  boolean $sample
	 * @return string
	 */
	public function filter_post_type_link( $post_link, $post, $leavename, $sample ) {
		if ( ! $this->is_wcm_post( $post ) ) {
			return $post_link;
		}

		if ( isset( $this->data['origin_url'] ) ) {
			return $this->data['origin_url'];
		}

		return '';
	}

	/**
	 * Adds dummy image IDs to caption shortcodes so credit/distributor meta can be fetched for images.
	 *
	 * @param  string $content
	 * @return string
	 */
	public function filter_the_content( $content ) {
		if ( ! $this->is_wcm_post() ) {
			return $content;
		}

		// Find all caption shortcodes without attachment IDs.
		$has_matches = preg_match_all( '/\[caption\](.*)\[\/caption\]/', $content, $matches );
		if ( ! $has_matches ) {
			return $content;
		}

		foreach ( $matches[0] as $i => $shortcode ) {
			// Get just the inner shortcode content (ie. img and caption).
			$inner_content = $matches[1][ $i ];

			// Get the image ID associated with this image and caption.
			$image_id = $this->get_image_id_from_shortcode( $shortcode );

			// Add the attachment ID to the shortcode in the post.
			$replace = '[caption id="attachment_'. $image_id . '"]' . $inner_content . '[/caption]';
			$content = str_replace( $shortcode, $replace, $content );
		}

		return $content;
	}

	/**
	 * @param  array|false  $image
	 * @param  int          $attachment_id
	 * @param  string|array $size
	 * @param  boolean      $icon
	 * @return array|false
	 */
	public function filter_wp_get_attachment_image_src( $image, $attachment_id, $size, $icon ) {
		$image_data = $this->is_wcm_image( $attachment_id );
		if ( empty( $image_data ) ) {
			return $image;
		}

		$url = isset( $image_data['url'] ) ? $image_data['url'] : '';
		$width = isset( $image_data['width'] ) ? $image_data['width'] : '';
		$height = isset( $image_data['height'] ) ? $image_data['height'] : '';

		return array(
			$url,
			$width,
			$height,
		);
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_bio( $value, $user_id, $original_user_id ) {
		return $this->get_the_author_meta( 'bio', $value, $user_id );
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_email( $value, $user_id, $original_user_id ) {
		return $this->get_the_author_meta( 'email', $value, $user_id );
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_first_name( $value, $user_id, $original_user_id ) {
		$name = $this->get_the_author_meta( 'name', $value, $user_id );
		if ( $name != $value ) {
			$name = preg_replace( '/^([^\s]+)\s.*$/', '$1', $name );
		}
		return $name;
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_id( $value, $user_id, $original_user_id ) {
		return $this->get_the_author_meta( '_id', $value, $user_id );
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_last_name( $value, $user_id, $original_user_id ) {
		$name = $this->get_the_author_meta( 'name', $value, $user_id );
		if ( $name != $value ) {
			$name = preg_replace( '/^[^\s]+\s(.*)$/', '$1', $name );
		}
		return $name;
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_name( $value, $user_id, $original_user_id ) {
		return $this->get_the_author_meta( 'name', $value, $user_id );
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_slug( $value, $user_id, $original_user_id ) {
		return $this->get_the_author_meta( 'slug', $value, $user_id );
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_url( $value, $user_id, $original_user_id ) {
		return $this->get_the_author_meta( 'url', $value, $user_id );
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string $key
	 * @param  string $value
	 * @param  mixed  $id_or_email
	 * @return string
	 */
	private function get_the_author_meta( $key, $value, $id_or_email ) {
		if ( ! $this->is_wcm_post() ) {
			return $value;
		}

		if ( is_int( $id_or_email ) ) {
			$author = $this->is_wcm_user( $id_or_email );
		} else {
			$author = $this->get_author_from_key( 'email', $id_or_email );
		}

		if ( ! empty( $author ) && isset( $author[ $key ] ) ) {
			return $author[ $key ];
		}

		return '';
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_aim( $value, $user_id, $original_user_id ) {
		return $this->get_the_author_social( $value, 'aim', $user_id );
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_discourse( $value, $user_id, $original_user_id ) {
		return $this->get_the_author_social( $value, 'discourse', $user_id );
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_icq( $value, $user_id, $original_user_id ) {
		return $this->get_the_author_social( $value, 'icq', $user_id );
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_msn( $value, $user_id, $original_user_id ) {
		return $this->get_the_author_social( $value, 'msn', $user_id );
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_twitter( $value, $user_id, $original_user_id ) {
		return $this->get_the_author_social( $value, 'twitter', $user_id );
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string   $value
	 * @param  int      $user_id
	 * @param  int|bool $original_user_id
	 * @return string
	 */
	public function filter_get_the_author_yim( $value, $user_id, $original_user_id ) {
		return $this->get_the_author_social( $value, 'yim', $user_id );
	}

	/**
	 * Uses WCM user data for get_the_author_meta().
	 *
	 * @param  string $site
	 * @param  int    $user_id
	 * @return string
	 */
	private function get_the_author_social( $value, $site, $user_id ) {
		if ( ! $this->is_wcm_post() ) {
			return $value;
		}

		$user_data = $this->is_wcm_user( $user_id );
		$site = strtolower( $site );
		if ( ! empty( $user_data ) && isset( $user_data['social_links'] ) ) {
			foreach ( $user_data['social_links'] as $link ) {
				if ( isset( $link['site'] ) && strtolower( $link['site'] ) == $site ) {
					return $link['url'];
				}
			}
		}

		return '';
	}

	/**
	 * @param  array $args
	 * @param  mixed $id_or_email
	 * @return array
	 */
	public function filter_pre_get_avatar_data( $args, $id_or_email ) {
		if ( ! $this->is_wcm_post() ) {
			return $args;
		}

		$photo = $this->get_the_author_meta( 'photo', array(), $id_or_email );
		if ( isset( $photo['url'] ) ) {
			$args['url'] = $photo['url'];
		}
		if ( isset( $photo['width'] ) ) {
			$args['width'] = $photo['width'];
		}
		if ( isset( $photo['height'] ) ) {
			$args['height'] = $photo['height'];
		}

		return $args;
	}

	/**
	 * @param  string $link
	 * @param  int    $author_id
	 * @param  string $author_nicename
	 * @return string
	 */
	public function filter_author_link( $link, $author_id, $author_nicename ) {
		if ( ! $this->is_wcm_post() ) {
			return $link;
		}

		$author = $this->get_author_from_key( 'slug', $author_nicename );
		if ( isset( $author['url'] ) ) {
			return $author['url'];
		}

		return '';
	}

	/**
	 * Fakes a WP_Post object using WCM data.
	 *
	 * @param  array  $data
	 * @return object
	 */
	private function get_post_from_data( $data ) {
		$format = 'Y-m-d H:i:s';

		$p                    = new \stdClass();
		$p->ID                = $this->dummy_post_id;
		$p->post_author       = $this->dummy_author_id;
		$p->post_date_gmt     = isset( $data['published_on'] ) ? gmdate( $format, strtotime( $data['published_on'] ) ) : '';
		$p->post_date         = get_date_from_gmt( $p->post_date_gmt, $format );
		$p->post_content      = isset( $data['content_elements'] ) ? DataHelper::build_post_content( $data['content_elements'] ) : '';
		$p->post_title        = isset( $data['titles']['main'] ) ? $data['titles']['main'] : '';
		$p->post_excerpt      = isset( $data['excerpt'] ) ? $data['excerpt'] : '';
		$p->post_status       = isset( $data['status'] ) ? DataHelper::get_wp_status( $data['status'] ) : '';
		$p->comment_status    = isset( $data['commenting_enabled'] ) && ! empty( $data['commenting_enabled'] ) ? 'open' : 'closed';
		$p->post_name         = isset( $data['origin_slug'] ) ? $data['origin_slug'] : '';
		$p->post_modified_gmt = isset( $data['modified_on'] ) ? gmdate( $format, strtotime( $data['modified_on'] ) ) : '';
		$p->post_modified     = get_date_from_gmt( $p->post_modified_gmt, $format );
		$p->post_parent       = isset( $data['parent_id'] ) ? (int) $data['parent_id'] : 0; // TODO what if the post comes from a different origin?
		$p->guid              = isset( $data['origin_url'] ) ? $data['origin_url'] : '';
		$p->menu_order        = isset( $data['order'] ) ? (string) $data['order'] : '';
		$p->post_type         = isset( $data['type'] ) ? DataHelper::get_wp_type( $data['type'] ) : '';
		$p->filter            = 'raw';

		return new \WP_Post( $p );
	}

	/**
	 * Fakes a WP_Post object using WCM featured image data.
	 *
	 * @param  array  $data
	 * @param  int    $id
	 * @return object
	 */
	private function get_image_from_data( $data, $id ) {
		$format = 'Y-m-d H:i:s';

		$p                 = new \stdClass();
		$p->ID             = $id;
		$p->post_author    = $this->dummy_author_id;
		$p->post_date_gmt  = isset( $data['created_on'] ) ? gmdate( $format, strtotime( $data['created_on'] ) ) : '';
		$p->post_date      = get_date_from_gmt( $p->post_date_gmt, $format );
		$p->post_content   = isset( $data['description'] ) ? $data['description'] : '';
		$p->post_title     = isset( $data['title'] ) ? $data['title'] : '';
		$p->post_excerpt   = isset( $data['caption'] ) ? $data['caption'] : '';
		$p->post_status    = 'inherit';
		$p->post_parent    = $this->dummy_post_id;
		$p->guid           = isset( $data['url'] ) ? $data['url'] : '';
		$p->post_type      = 'attachment';
		$p->post_mime_type = isset( $data['mime_type'] ) ? $data['mime_type'] : '';
		$p->filter         = 'raw';

		return new \WP_Post( $p );
	}

	/**
	 * Retrieves the closest WP_Term or fakes a WP_Term object using WCM data.
	 *
	 * @param  array       $data
	 * @return object|null
	 */
	private function get_term_from_data( $data, $default_type = 'post_tag' ) {
		// Ensure we have the fields required to find this term.
		$name = isset( $data['name'] ) ? $data['name'] : '';
		$taxonomy = isset( $data['type'] ) ? $data['type'] : $default_type;
		if ( empty( $name ) || empty( $taxonomy ) ) {
			return null;
		}

		// Get the term by name (rather than ID because the ID will differ per site).
		$term = wpcom_vip_get_term_by( 'name', $name, $taxonomy );
		if ( ! empty( $term ) ) {
			return $term;
		}

		// The term doesn't exist on this site. Try to find a parent term.
		$path = isset( $data['path'] ) ? $data['path'] : '';
		if ( ! empty( $path ) ) {
			$path = explode( '/', $path );
			$path = array_filter( $path );

			// Remove the current term, because we've already tried to find it, then look at the deepest category.
			array_pop( $path );
			$path = array_reverse( $path );

			foreach ( $path as $term_name ) {
				$term = wpcom_vip_get_term_by( 'name', $term_name, $taxonomy );
				if ( ! empty( $term ) ) {
					return $term;
				}
			}
		}

		if ( 'author' == $taxonomy ) {
			// Fake the term.
			$t                   = new \stdClass();
			$t->term_id          = null;
			$t->name             = $name;
			$t->slug             = isset( $data['slug'] ) ? $data['slug'] : sanitize_title( $name );
			$t->term_group       = ''; // TODO
			$t->term_taxonomy_id = 0; // TODO
			$t->taxonomy         = $taxonomy;
			$t->description      = ''; // TODO
			$t->parent           = 0; // TODO
			$t->count            = 0; // TODO
			$t->filter           = 'raw'; // TODO

			return $t;
		}

		// The term doesn't exist, so pretend it isn't even there.
		return null;
	}

	/**
	 * Finds WCM author data.
	 *
	 * @param  string      $key
	 * @param  string      $value
	 * @return object|null
	 */
	private function get_author_from_key( $key, $value ) {
		if ( ! isset( $this->data['credits']['authors'] ) ) {
			return null;
		}

		foreach ( $this->data['credits']['authors'] as $author ) {
			if ( isset( $author[ $key ] ) && $author[ $key ] == $value ) {
				return $author;
			}
		}

		return null;
	}

	/**
	 * Fakes a WP_User object using WCM data.
	 *
	 * @param  array  $data
	 * @param  int    $id
	 * @return object
	 */
	private function get_user_from_data( $data, $id = null ) {
		$u                      = new \WP_User();
		$u->data->ID            = $id;
		$u->data->user_login    = isset( $data['slug'] ) ? $data['slug'] : '';
		$u->data->user_nicename = isset( $data['slug'] ) ? $data['slug'] : '';
		$u->data->user_email    = isset( $data['email'] ) ? $data['email'] : '';
		$u->data->user_url      = isset( $data['url'] ) ? $data['url'] : '';
		$u->data->display_name  = isset( $data['name'] ) ? $data['name'] : '';
		$u->ID                  = $u->data->ID;
		$u->filter              = 'raw';

		return $u;
	}

	/**
	 * Determines whether the given post/ID is for a WCM post.
	 *
	 * @param  int|WP_Post $id
	 * @return boolean
	 */
	private function is_wcm_post( $id = null ) {
		if ( ! $this->is_wcm_request() ) {
			return false;
		}

		if ( ! empty( $id ) ) {
			if ( ! is_int( $id ) && isset( $id->ID ) ) {
				$id = $id->ID;
			}

			return $this->dummy_post_id == $id;
		}

		return true;
	}

	/**
	 * Determines whether the given post/ID is for a WCM image.
	 *
	 * @param  int|WP_Post $id
	 * @return array|false
	 */
	private function is_wcm_image( $id = null ) {
		if ( ! empty( $id ) ) {
			if ( ! is_int( $id ) && isset( $id->ID ) ) {
				$id = $id->ID;
			}

			foreach ( $this->images as $image_id => $data ) {
				if ( $image_id == $id ) {
					return $data;
				}
			}
		}
		return false;
	}

	/**
	 * Determines whether the given user/ID is for a WCM user.
	 *
	 * @param  int|WP_User $id
	 * @return array|false
	 */
	private function is_wcm_user( $id = null ) {
		if ( ! empty( $id ) ) {
			if ( ! is_int( $id ) && isset( $id->ID ) ) {
				$id = $id->ID;
			}

			foreach ( $this->users['id'] as $user_id => $data ) {
				if ( $user_id == $id ) {
					return $data;
				}
			}
		}
		return false;
	}

	/**
	 * Determines whether we are currently on a WCM page.
	 *
	 * @return boolean
	 */
	private function is_wcm_request() {
		return ! empty( $this->data );
	}

	/**
	 * Determines if the current post should be visible from this URL.
	 * Non-published posts should never be visible.
	 * Posts with the wrong WCM ID should not be visible.
	 * Posts with a URL slug that does not match the current URL slug should not be visible.
	 *
	 * @param  string   $current_request
	 * @param  string   $wcm_id
	 * @param  int|null $post_id
	 * @return boolean
	 */
	private function is_valid_wcm_post( $current_request, $wcm_id, $post_id = null ) {
		// Ensure the post is published.
		$status = isset( $this->data['status'] ) ? $this->data['status'] : null;
		if ( 'published' != $status ) {
			return false;
		}

		// This is a post that exists in the current database.
		if ( ! empty( $post_id ) ) {
			// Make sure the WCM ID in the database matches the ID in the URL.
			return get_post_meta( $post_id, 'pn_wcm_id', true ) == $wcm_id;
		}

		// If pretty permalinks are not enabled, the URL is fine.
		$pretty_permalinks = get_option( 'permalink_structure' );
		if ( ! $pretty_permalinks ) {
			return true;
		}

		// This is not a post page, so this post does not exist in WP.
		// Ensure the URL matches the origin path from WCM.
		if ( ! isset( $this->data['origin_url_path'] ) && ! isset( $this->data['origin_path'] ) ) {
			// There is no slug to match, so all slugs are valid.
			return true;
		}
		$match_request = isset( $this->data['origin_url_path'] ) ? $this->data['origin_url_path'] : $this->data['origin_path'];
		$current_slug = $this->unleadingslashit( str_replace( '/wcm/' . $wcm_id, '', untrailingslashit( $current_request ) ) );
		$match_slug = $this->unleadingslashit( untrailingslashit( $match_request ) );
		return $current_slug == $match_slug;
	}

	/**
	 * Removes leading slash from URL.
	 *
	 * @param  string $string
	 * @return string
	 */
	private function unleadingslashit( $string ) {
		return ltrim( $string, '/\\' );
	}

	/**
	 * Determines if the current post body contains any shortcodes/elements that are not supported by the current site.
	 *
	 * @return boolean
	 */
	private function has_unsupported_shortcode() {
		$types = array_keys( DataHelper::get_supported_content_element_types() );
		$shortcodes = array_keys( DataHelper::get_supported_shortcodes() );
		$content_elements = isset( $this->data['content_elements'] ) ? $this->data['content_elements'] : array();
		foreach ( $content_elements as $element ) {
			if ( ! in_array( $element['type'], $types ) && ! in_array( $element['type'], $shortcodes ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Searches embedded images with a URL and caption that match the given shortcode.
	 *
	 * @param  string      $shortcode
	 * @return string|null            Dummy ID of matching image.
	 */
	private function get_image_id_from_shortcode( $shortcode ) {
		// Get the img URL from the shortcode (required).
		$has_url = preg_match( '/<img[^>]*src="([^"]*)"/', $shortcode, $url );
		if ( $has_url ) {
			$url = $url[1];
		} else {
			return null;
		}

		// Get the caption from the shortcode (optional).
		$has_caption = preg_match( '/\[caption\]<img[^>]*>\s*(.*)\[\/caption\]/', $shortcode, $caption );
		$caption = $has_caption ? $caption[1] : '';

		// Search each image to find one with a matching URL and caption.
		foreach ( $this->images as $image_id => $data ) {
			$image_url = isset( $data['url'] ) ? $data['url'] : '';
			$image_caption = isset( $data['caption'] ) ? $data['caption'] : '';
			if ( $url == $image_url && $caption == $image_caption ) {
				return $image_id;
			}
		}

		return null;
	}

	/**
	 * Returns the index key associated with the given database column.
	 *
	 * @param  string      $db_key 'ID', 'user_email', 'user_login', 'user_nicename'
	 * @return string|null
	 */
	private function get_index_key_from_column( $column ) {
		$column = strtolower( $column );

		switch ( $column ) {
			case 'id':
				return 'id';

			case 'user_email':
				return 'email';

			case 'user_login':
			case 'user_nicename':
				return 'slug';
		}

		return null;
	}

	/**
	 * Returns the dummy image ID if this is a query for a WCM image.
	 *
	 * @param  string      $query
	 * @return string|null
	 */
	private function get_dummy_image_id( $query ) {
		global $wpdb;

		$is_post_query = preg_match( '/^SELECT \* FROM ' . $wpdb->posts . ' WHERE ID = (-?[0-9]+) LIMIT 1$/', $query, $id );

		if ( $is_post_query ) {
			$id = $id[1];
			if ( isset( $this->images[ $id ] ) ) {
				return $id;
			}
		}

		return null;
	}

	/**
	 * Returns the dummy user ID if this is a query for a WCM user.
	 *
	 * @param  string      $query
	 * @return string|null
	 */
	private function get_dummy_user_id( $query ) {
		global $wpdb;

		$find = '/^SELECT \* FROM ' . $wpdb->users . ' WHERE (ID|user_nicename|user_email|user_login) = (-?[0-9]+|\'[^\']+\')$/'; // @codingStandardsIgnoreLine - We reference the users table only to match a string.
		$is_user_query = preg_match( $find, $query, $matches );

		if ( $is_user_query ) {
			$key = $this->get_index_key_from_column( $matches[1] );
			$value = str_replace( '\'', '', $matches[2] );

			if ( 'id' == $key ) {
				$id = $value;
			} else {
				if ( ! isset( $this->users[ $key ][ $value ] ) ) {
					return null;
				}
				$id = $this->users[ $key ][ $value ];
			}

			if ( isset( $this->users['id'][ $id ] ) ) {
				return $id;
			}
		}

		return null;
	}

	/**
	 * Returns given URL with WCM ID appended.
	 *
	 * @param  string $url
	 * @param  string $wcm_id
	 * @return string
	 */
	private function get_wcm_url( $url, $wcm_id ) {
		$pretty_permalinks = get_option( 'permalink_structure' );
		if ( $pretty_permalinks ) {
			return $url . '/wcm/' . $wcm_id;
		}
		return add_query_arg( 'wcm_id', $wcm_id, $url );
	}
}

/**
 * Redirects to given URL. Makes it easier to test.
 *
 * @param  string  $url
 * @param  integer $status
 * @return void
 */
if ( ! function_exists( 'postmedia_library_redirect' ) ) {
	function postmedia_library_redirect( $url, $status = 301 ) {
		wp_redirect( $url, $status );
		exit();
	}
}
