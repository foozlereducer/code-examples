<?php
require_once('./classes/Postmedia/Plugins/Edash/Paths.php');
require_once('./classes/Postmedia/Plugins/Edash/Trunk.php');
require_once('./classes/Postmedia/Plugins/Edash/Settings.php');
require_once('./classes/Postmedia/Plugins/Edash/Pointer.php');
require_once('postmedia-library/classes/Postmedia/Web/Storage.php');
require_once('./classes/Postmedia/Plugins/Edash/CoreMethods.php');
require_once('PHPUnitUtilities.php');

class TrunkTest extends WP_UnitTestCase  {
	use Postmedia\Plugins\Edash\CoreMethods;

	function setUp() {
		// calling this allows hooks into the base WordPress classes such as the WP_User object.
		parent::setUp();
		
	}

	function test_json_lookup_posts() {
		/*$nonce = wp_create_nonce( 'wcm_editorial_dashboard_api_nonce' );
		$wcm_obj_list = $this->mock->json_lookup_posts1;
		// Mocked wcm_obj_list
		$_POST = array( 'wcm_obj_list' => $wcm_obj_list );

		ob_start();
		$this->json_lookup_posts();
		print_r( ob_get_clean() );*/
		
	}

	function test_init_action_is_run() {
		/*global $wp_filter;
		$this->assertEquals( 10, has_action( 'init', array( $this->trunk, 'init' ) ) );*/
	}
}