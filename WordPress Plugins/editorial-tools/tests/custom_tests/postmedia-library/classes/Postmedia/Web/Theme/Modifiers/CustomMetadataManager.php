<?php

namespace Postmedia\Web\Theme\Modifiers;

/**
 * Modifiers (actions / filters) that are specific to the Custom Metadata Manager
 */
class CustomMetadataManager {

	public function __construct() {
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
	}

	/**
	 * WP admin init action
	 * @return void
	 */
	public function action_admin_init() {
		add_action( 'custom_metadata_manager_init_metadata', array( $this, 'action_custom_metadata_manager_init_metadata' ) );
	}

	/**
	 * Setup custom meta tags for the Custom Meta Plugin
	 * @return void
	 */
	public function action_custom_metadata_manager_init_metadata() {
		// Add Alternate Title Metabox in Posts
		x_add_metadata_group(
			'Pn_Alternate_title_meta_box ',
			array( 'post' ),
			array(
				'label' => 'Alternate title',
				'context' => 'normal',
				'priority' => 'high',
			)
		);

		x_add_metadata_field(
			'_pn_title_alternate',
			array( 'post' ),
			array(
				'group' => 'Pn_Alternate_title_meta_box',
				'label' => '',
				'description' => 'This title will appear on index pages where possible. The main title will appear in feeds and on the story page.',
			)
		);

		// Add Google News Featured Metabox in Posts.
		x_add_metadata_group(
			'Pn_GoogleNews_Meta ',
			array( 'post' ),
			array(
				'label' => 'Feature Story for Google News',
				'context' => 'normal',
				'priority' => 'high',
			)
		);

		x_add_metadata_field(
			'_pn_feature_googlenews',
			array( 'post' ),
			array(
				'group' => 'Pn_GoogleNews_Meta',
				'label' => 'Is this a featured story?',
				'field_type' => 'checkbox',
				'description' => 'Please make sure you have not used standout on your own articles more than seven times in the past calendar week.',
			)
		);

		// Add SouthParc Topic Metabox in Posts.
		x_add_metadata_group(
			'Pn_SouthParc_meta_box ',
			array( 'post' ),
			array(
				'label' => 'Enter SouthPARC Topic',
				'context' => 'normal',
				'priority' => 'high',
			)
		);

		x_add_metadata_field(
			'postmedia_syndication_topic',
			array( 'post' ),
			array(
				'group' => 'Pn_SouthParc_meta_box',
				'label' => '',
			)
		);
	}
}
