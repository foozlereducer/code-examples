<?php

namespace Postmedia\Web\Theme\Capabilities;

class CoAuthors {

	public function __construct() {
		add_filter( 'coauthors_plus_edit_authors', array( $this, 'coauthors_plus_edit_authors' ) );
		add_filter( 'coauthors_guest_author_manage_cap', array( $this, 'coauthors_guest_author_manage_cap' ) );
	}

	/**
	 * Filter callback to modify guest capability
	 * @return string
	 */
	public function coauthors_guest_author_manage_cap() {
		return 'manage_coauthors';
	}

	/**
	 * Filter callback to modify manage capability
	 * @return string
	 */
	public function coauthors_plus_edit_authors() {
		return 'manage_coauthors';
	}
}
