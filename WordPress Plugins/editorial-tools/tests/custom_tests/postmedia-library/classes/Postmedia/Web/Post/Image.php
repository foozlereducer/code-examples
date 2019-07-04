<?php

namespace Postmedia\Web\Post;

/**
 * PostImage
 *
 * Combine all information related to images for a post into one object.
 * Business rules for image properties are managed here.
 *
 * Example:
 * 		$pi = new Postmedia\Web\Post\Image( null, null, 23 );
 *
 * 		echo $pi->credit();
 * 		echo $pi->description();
 * 		echo $pi->title();
 * 		echo $pi->distributor();
 * 		echo $pi->caption();
 * 		echo $pi->alt();
 * 		echo $pi->url();
 * 		echo $pi->width();
 * 		echo $pi->height();
 *
 * @param string/array $size [optional] Size of image
 * @param string $post_id [optional] Id of post to load thumb for. Otherwise the current post.
 * @param string $image_id [optional] Id of image to load. Otherwise load the default thumb for post.
 *
 * @author Chris Murphy
 */
class Image {

	// global WP post variable reference
	private $_post;

	// optional size
	private $_size = null;

	/**
	 * Image Id
	 */
	private $id = null;
	public function id() {
		if ( ! $this->id ) {
			$gallery = get_post_meta( $this->_post->ID, 'featured_photogallery', true );

			if ( ! empty( $gallery ) ) {
				$regex_pattern = get_shortcode_regex();

				preg_match( '/'.$regex_pattern.'/s', $gallery, $matches );

				if ( ! empty( $matches[3] ) ) {
					$ids = str_replace( '"', '', $matches[3] );

					if ( ! empty( $ids ) ) {
						$ids = explode( ',', str_replace( 'ids=', '', $ids ) );

						$this->id = $ids[0];
					}
				}
			} else if ( has_post_thumbnail( $this->_post->ID ) ) {

				$this->id = get_post_thumbnail_id( $this->_post->ID );
			}
		}

		return $this->id;
	}

	/**
	 * WP Image SRC based on provided size
	 * or the default image
	 *
	 * @return array
	 */
	public function src() {
		$key = 'postmedia-web-post-image-src-' . $this->id() . $this->_size;

		$src = get_transient( $key );

		if ( ! $src ) {
			$src = wp_get_attachment_image_src( $this->id() , $this->_size );

			set_transient( $key, (array) $src, 5 * 60 );
		}

		return $src;
	}

	/**
	 * Post permalink
	 * Previously seen in (site.php > drv_wp_get_attachment_data)
	 * Added here for compatibility
	 */
	public function href() {
		return get_permalink( $this->_post->ID );
	}

	/**
	 * Credit - Custom field
	 */
	private $credit;
	public function credit( $meta_key = 'attachment_credit' ) {
		if ( ! $this->credit ) {
			$post_meta = $this->get_post_meta();

			if ( isset( $post_meta[ $meta_key ][0] ) ) {
				$this->credit = $post_meta[ $meta_key ][0];
			}
		}

		return $this->credit;
	}

	/**
	 * Distributor - Custom field
	 */
	private $distributor;
	public function distributor( $meta_key = 'attachment_distributor' ) {
		if ( ! $this->distributor ) {
			$post_meta = $this->get_post_meta();

			if ( isset( $post_meta[ $meta_key ][0] ) ) {
				$this->distributor = $post_meta[ $meta_key ][0];
			}
		}

		return $this->distributor;
	}

	/**
	 * Image Title (title="")
	 */
	public function title() {
		return $this->get_post_data()->post_title;
	}

	/**
	 * Image Description
	 */
	public function description() {
		return $this->get_post_data()->post_content;
	}

	/**
	 * Image Caption
	 */
	public function caption() {
		if ( ! empty( $this->get_post_data()->post_excerpt ) ) {
			return $this->get_post_data()->post_excerpt;
		} else {
			if ( isset( $this->description ) && ! empty( $this->description ) ) {
				return $this->description; // fall back to description as this was normally populated
			}
		}
	}

	/**
	 * URL for image
	 */
	public function url() {
		$url = '';

		$thumb_src = $this->src();

		if ( isset( $thumb_src[0] ) ) {
			$url = $thumb_src[0];
		} else {
			$url = $this->get_post_data()->guid;
		}

		return $url;
	}

	/**
	 * Image width
	 */
	public function width() {
		$width = '';

		$thumb_src = $this->src();

		if ( isset( $thumb_src[1] ) ) {
			$width = $thumb_src[1];
		} else if ( $this->get_post_attachment_meta() ) {
			$pam = $this->get_post_attachment_meta();
			$width = $pam['width'];
		}

		return $width;
	}

	/**
	 * Image height
	 */
	public function height() {
		$height = '';

		$thumb_src = $this->src( $this->_size );

		if ( isset( $thumb_src[2] ) ) {
			$height = $thumb_src[2];
		} else if ( $this->get_post_attachment_meta() ) {
			$pam = $this->get_post_attachment_meta();
			$height = $pam['height'];
		}

		return $height;
	}

	/**
	 * Image alt (alt="")
	 */
	public function alt() {
		$alt = $this->_post->post_title;

		$post_meta = $this->get_post_meta();

		if ( isset( $post_meta['_wp_attachment_image_alt'][0] ) && strlen( trim( $post_meta['_wp_attachment_image_alt'][0] ) ) > 0 ) {
			// alt of image
			$alt = $post_meta['_wp_attachment_image_alt'][0];
		} else if ( $this->caption() ) {
			// caption/description of image
			$alt = $this->caption();
		} else if ( $this->title() ) {
			// title of image
			$alt = $this->title();
		}

		return $alt;
	}




	/**
	 * Constructor
	 * @param mixed $size     [description]
	 * @param mixed $post_id  [description]
	 * @param mixed $image_id [description]
	 */
	public function __construct( $size = null, $post_id = null, $image_id = null ) {
		if ( $post_id ) {
			$this->_post = get_post( $post_id );
		} else {
			// reference global WP post variable
			global $post;

			$this->_post =& $post;
		}

		// get/set thumbnail id ( if image id is provided load that image instead of the default thumb )
		if ( $image_id ) {
			$this->id = $image_id;
		}

		// if size is not set we don't get (url,width,height)
		if ( $size ) {
			$this->_size = $size;
		}
	}

	/**
	 * Check if image id exists
	 * If Image Id is explicitly set this will be true.
	 * Otherwise we get the id through either an available gallery image or the posts thumbnail. See id().
	 */
	public function exists() {
		return ( $this->id() );
	}

	/**
	 * Get Post Data
	 */
	private $_post_data = null;
	public function get_post_data() {
		if ( ! $this->_post_data ) {
			$key = 'postmedia-web-post-image-get_post_data-' . $this->id();

			$data = get_transient( $key );

			if ( $data ) {
				$this->_post_data = $data;
			} else {
				$this->_post_data = get_post( $this->id() );

				set_transient( $key, $this->_post_data, 5 * 60 );
			}
		}

		return $this->_post_data;
	}

	/**
	 * Get Post Meta Data
	 */
	private $_post_meta = array();
	public function get_post_meta() {
		if ( ! $this->_post_meta ) {
			$key = 'postmedia-web-post-image-get_post_meta-' . $this->id();

			$data = get_transient( $key );

			if ( $data ) {
				$this->_post_meta = $data;
			} else {
				$this->_post_meta = get_post_meta( $this->id() );

				set_transient( $key, $this->_post_meta, 5 * 60 );
			}
		}

		return $this->_post_meta;
	}

	/**
	 * Get Post Attachment Meta Data
	 */
	private $_post_attachment_meta = null;
	public function get_post_attachment_meta() {
		if ( ! $this->_post_attachment_meta ) {
			$post_meta = $this->get_post_meta();

			if ( isset( $post_meta['_wp_attachment_metadata'][0] ) ) {
				$this->_post_attachment_meta = unserialize( $post_meta['_wp_attachment_metadata'][0] );
			}
		}

		return $this->_post_attachment_meta;
	}
}
