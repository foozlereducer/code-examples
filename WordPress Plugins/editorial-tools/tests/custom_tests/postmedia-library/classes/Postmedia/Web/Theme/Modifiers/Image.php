<?php

namespace Postmedia\Web\Theme\Modifiers;

/**
 * Modifier (actions / filters) that are specific to Images
 */
class Image {

	public function __construct() {
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
	}

	/**
	 * WP admin init action
	 * @return void
	 */
	public function action_admin_init() {
		add_filter( 'attachment_fields_to_edit', array( $this, 'filter_add_image_fields' ), 10, 2 );
		add_action( 'attachment_fields_to_save', array( $this, 'pn_save_image_fields' ), 10, 2 );
	}

	/**
	 * Add credit and distributor fields for images
	 * @param  array $form_fields
	 * @param  mixed $post
	 * @return array
	 */
	public function filter_add_image_fields( $form_fields, $post ) {
		$field_credit_value = get_post_meta( $post->ID, 'pn_attachment_credit', true );

		$field_distributor_value = get_post_meta( $post->ID, 'pn_attachment_distributor', true );

		$form_fields['pn_attachment_credit'] = array(
			'value' => $field_credit_value ? $field_credit_value : '',
			'label' => __( 'Credit' ),
			'input' => 'text',
		);

		$form_fields['pn_attachment_distributor'] = array(
			'value' => $field_distributor_value ? $field_distributor_value : '',
			'label' => __( 'Distributor' ),
			'input' => 'text',
		);

		return $form_fields;
	}

	/**
	 * Update post meta for custom fields on save
	 * @param  array $post
	 * @param  array $attachment
	 * @return array
	 */
	public function pn_save_image_fields( $post, $attachment ) {
		if ( ! empty( $attachment['pn_attachment_credit'] ) ) {
			update_post_meta( $post['ID'], 'pn_attachment_credit', sanitize_text_field( $attachment['pn_attachment_credit'] ) );
		} else {
			delete_post_meta( $post['ID'], 'pn_attachment_credit' );
		}

		if ( ! empty( $attachment['pn_attachment_distributor'] ) ) {
			update_post_meta( $post['ID'], 'pn_attachment_distributor', sanitize_text_field( $attachment['pn_attachment_distributor'] ) );
		} else {
			delete_post_meta( $post['ID'], 'pn_attachment_distributor' );
		}

		return $post;
	}
}
