<?php

namespace Postmedia\Web\Theme\Capabilities;

class EditFlow {

	public function __construct() {
		add_filter( 'ef_manage_usergroups_cap', array( $this, 'ef_manage_usergroups_cap' ) );
	}

	/**
	 * Filter callback to modify capability
	 * @return string
	 */
	public function ef_manage_usergroups_cap() {
		return 'manage_zones';
	}
}
