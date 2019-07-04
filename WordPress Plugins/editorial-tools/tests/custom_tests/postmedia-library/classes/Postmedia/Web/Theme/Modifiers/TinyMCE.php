<?php

namespace Postmedia\Web\Theme\Modifiers;

use Postmedia\Web\Utilities;

/**
 * Modifiers (actions / filters) that are specific to TinyMCE
 */
class TinyMCE {

	public function __construct() {
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
	}

	/**
	 * WP admin init action
	 * @return void
	 */
	public function action_admin_init() {
		if ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) && get_user_option( 'rich_editing' ) ) {
			add_filter( 'tiny_mce_before_init', array( $this, 'filter_tiny_mce_before_init' ) );
		}
	}

	/**
	 * TinyMCE Change Options
	 * @param  array $init_array
	 * @return array
	 */
	public function filter_tiny_mce_before_init( $init_array ) {
		$ext = 'a[href|alt|title|target|class|data-storyline],figure[width|style|class|align|id],figcaption[style|class]';

		if ( isset( $init_array['extended_valid_elements'] ) ) {
			$init_array['extended_valid_elements'] .= ',' . $ext;
		} else {
			$init_array['extended_valid_elements'] = $ext;
		}

		return $init_array;
	}
}
