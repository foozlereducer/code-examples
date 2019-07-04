<?php

namespace Postmedia\Web\Theme\Modifiers;

/**
 * Modifiers (actions / filters) that are specific to Custom fields
 */
class CustomFields {

	public function __construct() {
		// Workaround to allow custom fields preview
		// Source: http://wordpress.org/support/topic/preview-and-post-meta-custom-fields-solution
		add_filter( '_wp_post_revision_fields', array( $this, 'filter_add_field_debug_preview' ) );
		add_action( 'edit_form_after_title', array( $this, 'action_add_input_debug_preview' ) );
	}

	/**
	 * Filter to add field debug preview
	 * @param  array $fields
	 * @return array
	 */
	public function filter_add_field_debug_preview( $fields ) {
		$fields['debug_preview'] = 'debug_preview';

		return $fields;
	}

	/**
	 * Action to add debug input field
	 * @return void
	 */
	public function action_add_input_debug_preview() {
		echo '<input type="hidden" name="debug_preview" value="debug_preview">';
	}
}
