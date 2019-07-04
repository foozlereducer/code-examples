<?php

namespace Postmedia\Web\Theme\Modifiers;

abstract class CustomMeta {

	protected $key;

	protected $objects;

	protected $title;


	public function __construct() {
		add_action( 'custom_metadata_manager_init_metadata', array( $this, '_register' ) );
	}


	abstract protected function get_fields();


	public function _maybe_include( $thing_slug, $thing, $object_type, $object_id, $object_slug ) {
		return true;
	}


	protected function get_meta_key( $slug, $is_multifield = false ) {
		$key = sprintf( '_%s_%s', $this->key, $slug );
		if ( true === $is_multifield ) {
			$key = "_x_multifield_{$key}";
		}

		return $key;
	}


	public function _register() {
		x_add_metadata_group(
			$this->key,
			$this->objects,
			array(
				'label'   => $this->title,
				'include' => array( $this, '_maybe_include' ),
			)
		);

		foreach ( $this->get_fields() as $slug => $field ) {
			if ( ! empty( $field['_subfields'] ) ) {
				$_subfields = $field['_subfields'];
				unset( $field['_subfields'] );

				x_add_metadata_multifield(
					$this->get_meta_key( $slug ),
					$this->objects,
					array_merge( array( 'group' => $this->key ), $field )
				);

				foreach ( $_subfields as $sub_slug => $sub_field ) {
					$sub_field['multifield'] = $this->get_meta_key( $slug );
					$this->register_field( $sub_slug, $sub_field );
				}
			} else {
				$this->register_field( $this->get_meta_key( $slug ), $field );
			}
		}
	}


	protected function register_field( $slug, $field ) {
		x_add_metadata_field(
			$slug,
			$this->objects,
			array_merge( array( 'group' => $this->key ), $field )
		);
	}
}
