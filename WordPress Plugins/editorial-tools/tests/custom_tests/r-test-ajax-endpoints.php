<?php
require_once('./classes/Postmedia/Plugins/Edash/Paths.php');
require_once('./classes/Postmedia/Plugins/Edash/Trunk.php');
require_once('./classes/Postmedia/Plugins/Edash/Settings.php');
require_once('./classes/Postmedia/Plugins/Edash/Pointer.php');
require_once('postmedia-library/classes/Postmedia/Web/Storage.php');
require_once('UnitTestingData.php');
require_once('PHPUnitUtilities.php');

class AjaxEndpointsTests extends WP_Ajax_UnitTestCase {
	/*public $Trunk;
	public $mock;

	function setUp() {
		// calling this allows hooks into the base WordPress classes such as the WP_User object.
		parent::setUp();
		// Taxonomy
		$Taxonomy = new Postmedia\Plugins\Edash\Taxonomy;
		$Taxonomy->set_taxonomy( 'postmedia-plugin-editorial-dashboard' );
		// Storage
		$Storage = new Postmedia\Web\Storage();
		$Storage->initialize_storage( $Taxonomy->get_taxonomy() );
		// Settings
		$Settings = new Postmedia\Plugins\Edash\Settings( $Taxonomy );
		// Paths
		$Paths = new Postmedia\Plugins\Edash\Paths();
		// Trunk
		$this->Trunk = new Postmedia\Plugins\Edash\Trunk();
		$this->Trunk->initialize( $Paths, $Settings, $Storage );

		// Mock Data
		$this->mock = new UnitTestingData;
	}

	function test_json_lookup_posts() {
		$this->_setRole( 'administrator' );
		$nonce = wp_create_nonce( 'wcm_editorial_dashboard_api_nonce' );
		$wcm_obj_list = $this->mock->wcm_obj_list1;
	
		// Mocked wcm_obj_list
		$_POST = array( 'wcm_obj_list' => $wcm_obj_list, 'nonce' => $nonce );

		try {
			add_action( 'wp_ajax_json_lookup_posts', array( $this->Trunk, 'json_lookup_posts' ) );
			$this->_handleAjax( 'json_lookup_posts' );
		} catch ( WPAjaxDieStopException $e ) {
			 // We expected this, do nothing.
			 var_dump( $e );
		}

		$response = json_decode( $this->_last_response );
		print_r( $response );

		// $this->assertEquals( 'foozle', $response );
	}*/
}