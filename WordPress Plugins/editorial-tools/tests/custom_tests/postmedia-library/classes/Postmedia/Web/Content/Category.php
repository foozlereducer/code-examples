<?php

namespace Postmedia\Web\Content;

use Wholesite\Foundation\Component;

class Category extends Component {

	/**
	 * Id
	 * @var string
	 */
	public $id;

	/**
	 * Type eg. main, standard
	 * @var string
	 */
	public $type = 'standard';

	/**
	 * Name
	 * @var string
	 */
	public $name;

	/**
	 * Slug / Key
	 * @var string
	 */
	public $slug;

	/**
	 * Path
	 * @var string
	 */
	public $path;



	/**
	 * Find and return the local WordPress term that matches this slug
	 * @return WP_Term
	 */
	public function get_local() {
		return wpcom_vip_get_term_by( 'slug', $this->slug, 'category' );
	}
}
