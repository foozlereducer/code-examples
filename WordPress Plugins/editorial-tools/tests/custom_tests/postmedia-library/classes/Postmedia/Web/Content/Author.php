<?php

namespace Postmedia\Web\Content;

use Wholesite\Foundation\Component;

class Author extends Component {

	/**
	 * Id
	 * @var string
	 */
	public $id;

	/**
	 * Name
	 * @var string
	 */
	public $name;

	/**
	 * Slug
	 * @var string
	 */
	public $slug;

	/**
	 * Email
	 * @var string
	 */
	public $email;

	/**
	 * Author Image
	 * @var Image
	 */
	public $photo;

	/**
	 * Organization
	 * @var string
	 */
	public $org;

	/**
	 * Bio
	 * @var string
	 */
	public $bio;

	/**
	 * URL
	 * @var string
	 */
	public $url;

	/**
	 * TODO: What is this.. array, obj, string
	 * Social Links
	 * @var ???
	 */
	public $social;

	// TODO - Sort out this junk
	// 
	// get the co-authors
	// if ( function_exists( 'get_coauthors' ) ) {
	// 	$authors = get_coauthors();
	// }

	// // Fallback to WP users
	// if ( empty( $authors ) || ! is_array( $authors ) ) {
	// 	$authors = array( get_userdata( get_the_author_meta( 'ID' ) ) );
	// }

	// $_args = array(
	// 	'href' => get_author_posts_url( $author->ID, $author->user_nicename ),
	// );

	// $_args = apply_filters( 'coauthors_posts_link', $_args, $author );
	// $_name = esc_html( $author->display_name );

	// get_author_posts_url( $post->post_author )

	/**
	 * Author Meta Data
	 * @var array
	 */
	private $author_meta;
}
