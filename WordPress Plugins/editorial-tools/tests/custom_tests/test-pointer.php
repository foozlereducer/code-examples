<?php
require_once('./classes/Postmedia/Plugins/Edash/Pointer.php');
require_once('PHPUnitUtilities.php');
require_once('UnitTestingData.php');

class PointerTest extends WP_UnitTestCase {
	public $pointer;
	public $_POST;
	protected static $editor_id;

	function setUp() { 
		self::$editor_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		
		$os = new Postmedia\Plugins\Edash\OriginSite;
		$this->pointer = new Postmedia\Plugins\Edash\Pointer( $os );

		// Mock $_POST like data was sent from query builder
		$_POST = array(
			'originId' 			=> '13456',
			'publishedOn'		=> '03/21/2017 7:20 pm',
			'title'				=> 'EU trade chief sees rising anti-populism following Brexit and Trump',
			'excerpt' 			=> 'trade chief says she appreciates having Justin Trudeau and his international brand on her side in the fight against the forces of anti-trade populism.',
			'byline'			=> 'Charles Jaimet',
			'distributor'		=> 'National Post',
			'featuredImgUrl' 	=> 'http://postmediaprod.blob.core.windows.net/sp6images/cp/12577770/2016-12-23-16-20-9',
			'featuredImgTitle'=> 'FILE - In this July 25, 2016 file photo Brianna Wu, a software engineer and video-game developer, sits at her workstation in Boston. Wu, the co-founder of a gaming software company who made headlines two years ago when she was threatened, said she wants to run for one of Massachusetts nine U.S. House seats. Wu said her platform will focus on privacy rights and online harassment. (AP Photo/Elise Amendola, File)'
		);
		
		// Single Post
		$this->_POST = $_POST;
		$UTD = new UnitTestingData;
		$UTD->set_output_mode( $json = true );
		$this->single_post = json_decode( $UTD->single_story(), true );

		// Categories
		/* wp_insert_term( 'Entertainment', 'category' );
		$parent = term_exists( 'Entertainment', 'category' );
		wp_insert_term( 'Books', 'category', array( 'parent' => $parent['term_id'] ) );
		wp_insert_term( 'Business', 'category'); */
	}

	function the_guid_provider() {
		return array(
			array( 'post', 'http://example.org/example', 'http://example.org/example' ),
			array( 'pn_pointer', 'http://example.org/example', 'http://example.org/?p=%ID%' ),
		);
	}

	/**
	 * @dataProvider the_guid_provider
	 */
	function test_the_guid( $post_type, $guid, $expected ) {
		$id = $this->factory->post->create( array( 'post_type' => $post_type ) );
		$expected = str_replace( '%ID%', $id, $expected );

		$output = $this->pointer->the_guid( $guid, $id );
		$this->assertEquals( $expected, $output );
	}

	function test_valid_pointer_post_is_inserted() {
		$id = PHPUnitUtilities::callPrivateMethod( 
			$this->pointer,
			'insert_pointer_post',
			array( $this->single_post )
		);

		$this->assertTrue( is_int( $id ) );
		$this->assertTrue( is_numeric( $id ) );
		$this->assertTrue( $id > 0 );
	}

	function test_pointer_meta_value_and_default_links_are_generated_on_fall_back( ) {
		$post = array(
			'post_author' => self::$editor_id,
			'post_status' => 'publish',
			'post_content' => rand_str(),
			'post_title' => rand_str(),
			'post_type' => 'pn_pointer'
		);

		// insert a post and make sure the ID is ok
		$id = wp_insert_post($post);
		$this->assertTrue( is_numeric( $id ) );
		$this->assertTrue( $id > 0 );

		$post = get_post( $id );
		$relative_url = '/entertainment/books/test-video-in-body';
		$external_url = 'http://montrealgazette.wpdev5.canada.com/entertainment/books/test-video-in-body';
		$link = 'http://montrealgazette.wpdev5.canada.com';

		add_post_meta( $post->ID, 'pn_pointer_url', $relative_url );
		add_post_meta( $post->ID, 'pn_pointer_ext_url', $external_url );
		
		$pointer_url = $this->pointer->pointer_link( $link, $post );
		$this->assertEquals( $pointer_url, 'http://example.org/entertainment/books/test-video-in-body' );

		delete_post_meta( $post->ID, 'pn_pointer_url' );
		$pointer_url = $this->pointer->pointer_link( $link, $post );
		$this->assertEquals( $pointer_url, 'http://montrealgazette.wpdev5.canada.com/entertainment/books/test-video-in-body' );
		
		delete_post_meta( $post->ID, 'pn_pointer_ext_url' );
		$pointer_url = $this->pointer->pointer_link( $link, $post );
		$this->assertEquals( $pointer_url, 'http://example.org' );
	}

	function test_pointer_link_use_pointer_wcm_id() {
		$args = array(
			'post_name' => 'this-is-the-pointer-post-name',
			'post_type' => 'pn_pointer',
		);
		$post = $this->factory->post->create_and_get( $args );
		$relative_url = '/uncategorized/example-slug/wcm/10000000-0000-4000-8000-000000000000';
		$external_url = 'http://montrealgazette.wpdev5.canada.com/uncategorized/example-slug/wcm/10000000-0000-4000-8000-000000000000';
		$link = 'http://example.org/pn_pointer/this-is-the-pointer-post-name';
		$pn_wcm_id = 'f0000000-0000-4000-8000-000000000000';

		add_post_meta( $post->ID, 'pn_wcm_id', $pn_wcm_id );
		add_post_meta( $post->ID, 'pn_pointer_url', $relative_url );
		add_post_meta( $post->ID, 'pn_pointer_ext_url', $external_url );

		$pointer_url = $this->pointer->pointer_link( $link, $post );
		$this->assertEquals( 'http://example.org/uncategorized/example-slug/wcm/f0000000-0000-4000-8000-000000000000', $pointer_url );
	}

	function test_pointer_link_empty_vals_yeild_empty_url_without_object_errors() {
		$link = '';
		$post = '';

		$pointer_url = $this->pointer->pointer_link( $link, $post );
		$this->assertEmpty( $pointer_url );
	}

	function test_pointer_link_empty_post_with_valid_link_yeilds_valid_link_without_object_errors() {

		// just post is empty but link specifics the link to use so use this instead of default site url
		$post = '';
		$link = 'http://montrealgazette.wpdev5.canada.com';
		$pointer_url = $this->pointer->pointer_link( $link, $post );
		$this->assertEquals( $pointer_url, 'http://montrealgazette.wpdev5.canada.com' );
	}

	function test_pointer_link_valid_non_pointer_post_with_valid_link_yeilds_valid_link_withou_object_errors() {
		$pointer_url = 'http://montrealgazette.wpdev5.canada.com';
		$this->assertEquals( $pointer_url, 'http://montrealgazette.wpdev5.canada.com' );
	}

	/*function test_die_on_error_post_echos_nice_error() {
		$mock_post_error = new stdClass();
		$mock_post_error->error = 'something went really wrong with the post';
	
		$die_messaages = PHPUnitUtilities::callPrivateMethod( 
			$this->pointer,
			'die_on_error_post',
			array( $_post = $mock_post_error )
		);

		// $this->assertFalse( )
		var_dump( json_decode( $die_messaages ) );
	}*/

	function test_isset_and_trim() {
		$value = ' some text with leading and trailing space ';
		$trimmed_value = PHPUnitUtilities::callPrivateMethod( 
			$this->pointer,
			'isset_and_trim',
			array( $value )
		);

		$this->assertEquals( 'some text with leading and trailing space', $trimmed_value );
	}

	function insert_pointer_get_post_id( $terms_mode = false ) {
		$post_id = PHPUnitUtilities::callPrivateMethod( 
			$this->pointer,
			'insert_pointer_post',
			array( $this->single_post )
		);

		PHPUnitUtilities::callPrivateMethod(
			$this->pointer,
			'add_pointer_meta',
			array( $post_id, $this->single_post, $this->single_post['_id'], '' )
		);

		PHPUnitUtilities::callPrivateMethod( 
			$this->pointer,
			'get_post_data',
			array( $post_id, $this->single_post )
		);

		if ( true === $terms_mode ) {
			// Add categories to the WordPress test database
			wp_create_category( 'Books' );
			wp_create_category( 'Business' );
		}

		return $post_id;
	}

	function test_get_post_data_without_term_data() {
		$post_id = $this->insert_pointer_get_post_id();
		$stored_pointer = get_post( $post_id );

		$this->assertEquals( $stored_pointer->post_type, 'pn_pointer');
		$pointers_meta = get_post_meta( $post_id );
		$this->assertEquals( $pointers_meta['pn_wcm_origin_id'][0], 'bc4869eb-3c06-4a22-aa09-f89e7e04e856' );
		$this->assertEquals( $pointers_meta['pn_pointer_date'][0], '2016-10-23T19:17:32' );
		$this->assertEquals( $pointers_meta['pn_pointer_url'][0], '/entertainment/books/test-video-in-body/wcm/bc4869eb-3c06-4a22-aa09-f89e7e04e856' );
		$this->assertEquals( $pointers_meta['pn_pointer_ext_url'][0], 'http://montrealgazette.wpdev5.canada.com/entertainment/books/test-video-in-body' );
		$this->assertEquals( $pointers_meta['pn_author_byline'][0], 'mohamed' );
		$this->assertEquals( $pointers_meta['pn_pointer_author_email'][0], 'mmeddah@postmedia.com' );
		$this->assertEquals( $pointers_meta['pn_copied'][0], '1' );
		$this->assertEquals( $pointers_meta['pn_org'][0], 'Montreal Gazette - QA' );
		$this->assertEquals( $pointers_meta['robots'][0], 'noindex,nofollow' );
		$this->assertEquals( $pointers_meta['_thumbnail_id'][0], '11' ); // Note: Increase the expected ID if you create another post before this test is run.
	}

	function test_output_messages() {
		// Inserted
		$output = PHPUnitUtilities::callPrivateMethod( 
			$this->pointer,
			'get_output',
			array( 'inserted' )
		);

		$this->assertEquals( 'object', gettype( $output ) );
		$this->assertObjectHasAttribute( 'success', $output );
		$this->assertObjectHasAttribute( 'post_id', $output );
		$this->assertObjectHasAttribute( 'message', $output );
		$this->assertObjectHasAttribute( 'code', $output );
		$this->assertObjectHasAttribute( 'edit_url', $output );

		$this->assertTrue( $output->success );
		$this->assertEquals( $output->post_id, 0 );
		$this->assertEquals( $output->message, 'Pointer created.' );

	}
}