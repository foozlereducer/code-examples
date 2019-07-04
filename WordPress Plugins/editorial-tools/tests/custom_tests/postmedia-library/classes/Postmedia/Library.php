<?php
namespace Postmedia;

use Postmedia\Library\Settings;

class Library {

	public function __construct() {
		$settings = new Settings();
		$settings->initialize();
	}
}
