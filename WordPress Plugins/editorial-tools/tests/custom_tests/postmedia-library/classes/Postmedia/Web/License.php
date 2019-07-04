<?php

namespace Postmedia\Web;

use Wholesite\Foundation\Component;
use Postmedia\Web\Data;

/**
 * Properties from: https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/develop/schema/0.1/license.json
 */
class License extends Component {

	public $id;

	public $name;

	public $description;

	public $status;

	public $created_on;

	public $modified_on;

	public $duration;

	public $default = false;



	public function __construct( $id, $source ) {
		$data = Data::get_license( $id, $source );
		$this->id = $data['_id'];
		$this->name = $data['name'];
		$this->status = $data['status'];
		$this->description = $data['description'];
		$this->created_on = $data['created_on'];
		if ( isset( $data['modified_on'] ) ) {
			$this->modified_on = $data['modified_on'];
		} else {
			$this->modified_on = $data['created_on'];
		}
		if ( isset( $data['duration'] ) ) {
			$this->duration = $data['duration'];
		}
		if ( isset( $data['default'] ) ) {
			$this->default = $data['default'];
		}
	}
}
