<?php

namespace Postmedia\Web\Content;

use Wholesite\Foundation\Component;

class Image extends Component {

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
	 * Mime
	 * @var string
	 */
	public $mime;

	/**
	 * Created Date
	 * @var datetime
	 */
	public $created_on;

	/**
	 * URL
	 * @var string
	 */
	public $url;

	/**
	 * Title
	 * @var string
	 */
	public $title;

	/**
	 * Caption
	 * @var string
	 */
	public $caption;

	/**
	 * Description
	 * @var string
	 */
	public $description;

	/**
	 * Credit
	 * @var string
	 */
	public $credit;

	/**
	 * Distributor
	 * @var string
	 */
	public $distributor;

	/**
	 * Width
	 * @var int
	 */
	public $width;

	/**
	 * Height
	 * @var int
	 */
	public $height;

	/**
	 * Alt
	 * @var string
	 */
	private $alt;



	/**
	 * TODO Get alt
	 * @return string
	 */
	public function get_alt() {
		return $this->description;
	}
}
