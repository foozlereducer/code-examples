<?php

namespace Postmedia\Web\Theme\Modifiers\CustomMeta;

use Postmedia\Web\Theme\Modifiers\CustomMeta;

class PageMeta extends CustomMeta {

	protected $objects = 'page';

	protected $page_slug;


	protected function get_fields() {}


	public function get_value( $slug, $post = null, $is_multifield = false ) {
		$post = get_post( $post );

		return get_post_meta(
			$post->ID,
			$this->get_meta_key( $slug, $is_multifield ),
			true
		);
	}


	public function _maybe_include( $thing_slug, $thing, $object_type, $object_id, $object_slug ) {
		$page = get_post( $object_id );

		return ( 'page' === $page->post_type && $this->page_slug === $page->post_name );
	}


	protected function is_target_page() {
		$page = get_queried_object();

		return $page->post_name === $this->page_slug;
	}
}
