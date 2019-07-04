<?php

namespace Postmedia\Web;

use Wholesite\Foundation\Component;
use Postmedia\Web\Data;

/**
 * Properties from: https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/develop/schema/0.1/list.json
 */
class ContentList extends Component {

	public $id;

	public $type;

	public $origin_id;

	public $origin_cms;

	public $client_id;

	public $modified_on;

	public $title;

	public $slug;

	public $description;

	public $status;

	public $content = array();

	public $query;



	public function __construct( $id, $source = '' ) {
		$data = Data::get_content_list( $id, $source );

		$this->id = $data['id'];
		$this->type = $data['type'];
		$this->origin_id = $data['origin_id'];
		$this->origin_cms = $data['origin_cms'];
		$this->client_id = $data['client_id'];
		$this->modified_on = $data['modified_on'];
		$this->title = $data['title'];
		$this->slug = $data['slug'];
		$this->description = $data['description'];
		$this->status = $data['status'];
		$this->query = $data['query'];

		foreach ( (array) $data['content'] as $content_data_item ) {
			$id = ( $content_data_item['id'] ) ? $content_data_item['id'] : $content_data_item['origin_id'];
			$this->content[] = new Content( $id );
		}
	}
}
