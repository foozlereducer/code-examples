<?php

namespace Postmedia\Web\Content;

use Wholesite\Foundation\Component;

class Sponsor extends Component {

	/**
	 * Type (advertisement, sponsor, promoted, presented)
	 * @var string
	 */
	private $type;

	/**
	 * Sponsor name
	 * @var string
	 */
	public $name;

	/**
	 * Reader Education Text
	 * @var string
	 */
	public $info;

	/**
	 * FQ url for logo eg. http://...
	 * @var string
	 */
	public $logo_url;

	/**
	 * URL for click
	 * @var string
	 */
	public $logo_click_url;

	/**
	 * Pressboard setting
	 * @var bool
	 */
	public $pressboard_enabled = false;

	/**
	 * Allow third party advertisements on sponsored page
	 * eg. FlyerCity, LIBI
	 * @var bool
	 */
	public $allow_advertisements = false;

	/**
	 * If sponsorship is from a category. The Category object is attached.
	 * @var Category
	 */
	public $category = null;


	/**
	 * SET - Type
	 * @param string $value
	 */
	public function set_type( $value ) {
		$this->type = $value;
	}

	/**
	 * GET - Type
	 * Friendly Type
	 * @return string
	 */
	public function get_type() {
		switch ( $this->type ) {
			case 'advertisement':
				$label = 'Advertisement';
				break;
			case 'sponsored_by':
				$label = 'Sponsored';
				break;
			case 'promoted_by':
				$label = 'Promoted';
				break;
			case 'presented_by':
				$label = 'Presented';
				break;
			default:
				$label = $this->type;
				break;
		}

		return $label;
	}
}
