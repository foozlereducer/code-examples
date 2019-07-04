<?php

namespace Postmedia\Web\Content;

use Postmedia\Web\Content;

/**
 * TODO: Move to Gallery Plugin
 */
class Gallery extends Content {

	/**
	 * TODO - Review
	 * List of Gallery Images
	 * @var array
	 */
	private $media = null;


	/**
	 * TODO - Review ( To data layer )
	 * @param  string $size [description]
	 * @return [type]       [description]
	 */
	public function get_media( $size = 'sg_840x630' ) {
		if ( ! $this->media ) {
			// TODO this needs to go to the data layer
			$media_id_list = get_post_meta( $this->id, '_pn_used_images', true );

			foreach ( $media_id_list as $media_id ) {
				$this->media[] = new \Postmedia\Web\Post\Image( $size, $media_id, $media_id );
			}
		}

		return $this->media;
	}

	/**
	 * Render the gallery
	 * @param $options
	 */
	public function display( $options = array() ) {
		echo do_shortcode( '[snapgallery id="' . esc_attr( $this->id ) . '"]' );
	}
}
