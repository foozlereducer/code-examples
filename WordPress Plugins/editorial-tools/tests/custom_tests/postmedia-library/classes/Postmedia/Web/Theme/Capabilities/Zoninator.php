<?php

namespace Postmedia\Web\Theme\Capabilities;

class Zoninator {

	public function __construct() {
		add_filter( 'zoninator_add_zone_cap', array( $this, 'zoninator_add_zone_cap' ) );
		add_filter( 'zoninator_edit_zone_cap', array( $this, 'zoninator_edit_zone_cap' ) );
		add_filter( 'zoninator_manage_zone_cap', array( $this, 'zoninator_manage_zone_cap' ) );
	}

	/**
	 * Filter callback to modify zone add capability
	 * @return string
	 */
	public function zoninator_add_zone_cap() {
		return 'manage_zones';
	}

	/**
	 * Filter callback to modify zone edit capability
	 * @return string
	 */
	public function zoninator_edit_zone_cap() {
		return 'manage_zones';
	}

	/**
	 * Filter callback to modify zone manage capability
	 * @return string
	 */
	public function zoninator_manage_zone_cap() {
		return 'manage_zones';
	}
}
