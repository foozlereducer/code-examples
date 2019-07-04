<?php

namespace Postmedia\Web\Content;

use Wholesite\Foundation\Component;

class Video extends Component {

	/**
	 * Id
	 * @var string
	 */
	public $id;

	/**
	 * Type
	 * @var string
	 */
	public $type;

	/**
	 * Title
	 * @var string
	 */
	public $title;

	/**
	 * [$description description]
	 * @var [type]
	 */
	public $description;

	/**
	 * Associated images
	 * @var array Image
	 */
	private $images = array();

	/**
	 * URL
	 * @var string
	 */
	public $url;

	/**
	 * Inline
	 * @var bool
	 */
	public $inline;

	/**
	 * Shortcode
	 * @var string
	 */
	public $shortcode;


	/**
	 * Add an image
	 * @param string $type  eg. thumb
	 * @param Image $image
	 */
	public function add_image( $type, $image ) {
		$this->images[ $type ] = $image;
	}

	/**
	 * Get associated Image
	 * @param  string $type eg. thumb
	 * @return Image
	 */
	public function image( $type = 'thumb' ) {
		return $images[ $type ];
	}

	/**
	 * Render video player
	 * @param  array  $options
	 * @return void
	 */
	public function display( $options = array() ) {
		$options = '';
		foreach ( $options as $key => $value ) {
			$options .= $key . '="' . $value . '" ';
		}

		// TODO - Is send to news even used on stories now? - Follow Up
		if ( 's2n' == $this->type ) {
			$s2n_id = str_replace( '-', '', $this->id );
			$s2n_arr = explode( '-', $this->id );

			echo "<iframe id='s2nEmbedFrame" . esc_attr( $s2n_id ) . "' class='embedFrame' frameborder='0' width='840' height='474' allowfullscreen='' scrolling='no' src='" . esc_url( 'http://embed.sendtonews.com/player/embed.php?SK=' . $s2n_arr[0] . '&MK=' . $s2n_arr[1] . '&PK=' . $s2n_arr[2] ) . "&autoplay=on'></iframe>";
		} else {
			echo do_shortcode( '[kaltura-widget entryid="' . $this->id . '" ' . $options . ']' );
		}
	}
}
