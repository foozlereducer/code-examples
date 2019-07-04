<?php

namespace Postmedia\Web\Content;

use Wholesite\Foundation\Component;

class Tag extends Component {

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
	 * Find and return the local WordPress term that matches this slug
	 * @return WP_Term
	 */
	public function get_local() {
		return wpcom_vip_get_term_by( 'slug', $this->slug, 'post_tag' );
	}
}
