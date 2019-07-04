<?php

require_once('./classes/Postmedia/Plugins/Edash/Paths.php');

class PathsTest extends WP_UnitTestCase {

	public $root_dir = 'postmedia-plugin-wp-editorial-dashboard';

	function setUp() {
		// calling this allows hooks into the base WordPress classes such as the WP_User object.
		parent::setUp();
	}

	function test_path_contains_postmedia_plugin_wp_editorial_dashboard() {
		$o = new Postmedia\Plugins\Edash\Paths( 'postmedia-plugins' );
		$p = explode( '/', $o->path() );
		$this->assertEquals( $this->root_dir, array_pop( $p ) );
	}

	function test_custom_libray_path_returns_path_to_root_directory() {
		$o = new Postmedia\Plugins\Edash\Paths( 'library' );
		$path = explode( "/", 'plugin/library/with/some/file');
		$this->assertEquals('plugin', $o->unit_test_get_base_path_private_method( $path ) );
	}

	function test_url_begins_with_protocol() {
		$o = new Postmedia\Plugins\Edash\Paths();
		$url = explode( '/', $o->url() );
		// protocol 
		$this->assertEquals( 'http:', $url[0] );
	}

	function test_url_ends_with_root_folder() {
		$o = new Postmedia\Plugins\Edash\Paths( 'postmedia-plugins' );
		$url = explode( '/', $o->url() );
		$this->assertEquals( $this->root_dir, array_pop( $url ) );
	}

	function test_custom_delimiter_returns_correct_path() {
		$o = new Postmedia\Plugins\Edash\Paths( 'library', ':' );
		$path = explode( ":", 'plugin:library:with:special:delimiter');
		$this->assertEquals( 'plugin', $o->unit_test_get_base_path_private_method( $path ) );
	}

	function test_get_processed_path_contains_valid_path() {
		$o = new Postmedia\Plugins\Edash\Paths( 'postmedia-plugins' );
		// As get_this_path() implodes the array returned by get_processed_path(), in this case,
		// a direct call to get_processed_path() returns an array, so array_pop returns the last
		// index in the array which should be the root directory
		$processed_path = $o->get_processed_path();
		$root_dir = array_pop( $processed_path );
		$this->assertEquals( $this->root_dir,  $root_dir );
	}

	
	function test_proccess_custom_path_returns_root_custom_directory() {
		$o = new Postmedia\Plugins\Edash\Paths( 'includes' );
		$o->proccess_custom_path( explode( '/', 'plugin/includes/mystuff' ) );
		$this->assertEquals( 'plugin', implode( '/',  $o->get_processed_path() ) );
	}

	/*
	 * Boundary / Edge Case Tests
	 */
	
	function test_path_url_calls_return_string() {
		$o = new Postmedia\Plugins\Edash\Paths();
		$this->assertTrue( 'string' == gettype( $o->path() ) );
		$this->assertTrue( 'string' == gettype( $o->url() ));
	}
	
	/**
	 * Create the data set for the data provider
	 * Inner array pairs are bad types of data and expected value
	 * @return collections array
	 */
	function bad_data_provider() {
		return array( 
				array( 00567,  $this->root_dir ),
				array( 1.5,  $this->root_dir ), 
				array( new stdClass(),  $this->root_dir ),
		);
	}

	/**
	 * @dataProvider bad_data_provider
	 * pass the bad data types and $expected values from the data providers data set
	 */
	function test_bad_top_library_dir_data_returns_default_path_and_url( $bad_data_type, $expected ) {
		$o = new Postmedia\Plugins\Edash\Paths( $top_library_dir = $bad_data_type );
		$this->assertContains( $expected , $o->path() );
		$this->assertContains( $expected, $o->url() ); 
	}

	/**
	 * @dataProvider bad_data_provider
	 * pass the bad data types and $expected values from the data providers data set
	 */
	function test_bad_delimiter_data_returns_default_path_and_url( $bad_data_type, $expected ) {
		$o = new Postmedia\Plugins\Edash\Paths( $top_library_dir = false, $path_delimiter = $bad_data_type );
		$this->assertContains( $expected , $o->path() );
		$this->assertContains( $expected, $o->url() ); 
	}
	
	function tearDown(){}
}
