<?php
require_once('./classes/Postmedia/Plugins/Edash/CoreMethods.php');
require_once('PHPUnitUtilities.php');
require_once('UnitTestingData.php');

class CoreMethodsTest extends WP_UnitTestCase {
	use Postmedia\Plugins\Edash\CoreMethods;
	public $os;
	public $_POST;
	public $UTD;
	protected static $editor_id;

	function setUp() { 
		self::$editor_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		
		$this->os = new Postmedia\Plugins\Edash\OriginSite;
		$this->UTA = new UnitTestingData;

		// QA options
		new WCMRequestHelper();
		add_option( 'wcm_api_url', 'https://qa.api.pddataservices.com' );
		add_option( 'wcm_read_key', 'example-read-key' );
		add_option( 'wcm_super_key', 'example-super-key' );
		add_option( 'wcm_write_key', 'example-write-key' );
		add_option( 'wcm_client_id', '458fbe41-2662-4227-9998-702ceb24b346' );
	}

	function get_single_unit_testing_story() {
		$this->UTA->set_output_mode( $json = true );
		$post = $this->UTA->single_story();
		return json_decode( $post );
	}

	function test_is_true() {
		$this->assertTrue( true );
	}
}