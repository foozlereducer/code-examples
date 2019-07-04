<?php
require_once('./classes/Postmedia/Plugins/Edash/OriginSite.php');
require_once('PHPUnitUtilities.php');
require_once('UnitTestingData.php');

class OriginSiteTest extends WP_UnitTestCase {
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

	function test_get_clients() {
		$this->assertEmpty( get_transient( 'edash_wcm_clients' ) );
		$clients = $this->os->get_clients();
		$this->assertNotEmpty( get_transient( 'edash_wcm_clients' ) );
		
		$this->assertEquals( 'array' , gettype( $clients ) );
		$this->assertEquals( 'object', gettype( $clients[0] ) );
		$this->assertTrue( isset( $clients[0]->name ) );
		$this->assertTrue( isset( $clients[0]->domain ) );
	}

	function test_get_stored_clients_from_transient() {
		$clients = $this->os->get_stored_clients();
		$this->assertNotEmpty( $clients );
	}

	function test_parse_valid_domain() {
		$domain = parse_url( 'http://montrealgazette.wpdev5.canada.com/entertainment/books/test-video-in-body' );
		$domain = $this->os->parse_domain( $domain['host'] );
		$this->assertEquals( 'montrealgazette.com', $domain );
	}

	function test_parsing_invalid_domain_returns_default_domain() {
		$domain = parse_url( 'http:///mossgrowth.com' );
		$domain = $this->os->parse_domain( $domain['host'] );
		$this->assertEquals( 'canadianpress.com', $domain );

	}

	function test_get_brand() {
		$brand = $this->os->get_brand( 'http://montrealgazette.wpdev5.canada.com/entertainment/books/test-video-in-body' );
		$this->assertEquals( 'Montreal Gazette - QA', $brand );
	}	

	function test_parse_brand_finds_valid_client_domain() {
		$search_domain = 'http://montrealgazette.com';
		$clients = $this->os->get_clients();
		$brand = PHPUnitUtilities::callPrivateMethod( 
			$this->os,
			'parse_brand',
			array( $clients, $search_domain )
		);
		$this->assertEquals( 'http://montrealgazette.com', $brand );
	}

	function test_parse_brand_return_search_domain_if_valid_client_brand_is_not_matched() {
		$search_domain = 'mossmonster.com';
		$clients = $this->os->get_clients();
		$brand = PHPUnitUtilities::callPrivateMethod( 
			$this->os,
			'parse_brand',
			array( $clients, $search_domain )
		);
		$this->assertEquals( 'mossmonster.com', $brand );
	}
}