<?php

require_once('./classes/Postmedia/Plugins/Edash/Taxonomy.php');

class TaxonomyTest extends WP_UnitTestCase {


	function setUp() {
		// calling this allows hooks into the base WordPress classes such as the WP_User object.
		parent::setUp();
	}

	function test_set_taxonomy_sets_and_returns_a_string_name() {
		$o = new Postmedia\Plugins\Edash\Taxonomy;
		$taxonomy_name = $o->set_taxonomy( 'my-shady-plugin' );
		$this->assertEquals( 'my-shady-plugin', $taxonomy_name );
	}

	function test_get_taxonomy_gets_the_set_taxonomy_name() {
		$o = new Postmedia\Plugins\Edash\Taxonomy;
		$taxonomy_name = $o->set_taxonomy( 'my-shady-plugin' );
		$this->assertEquals( 'my-shady-plugin', $o->get_taxonomy() );
	}

	/**
	 * Create the data set for the data provider
	 * Inner array of bad values
	 * @return collections array
	 */
	function bad_values_provider() {
		return array( 
			array( 00567 ),
			array( 1.5 ), 
			array( new stdClass() ),
			array( true )
		);
	}

	/**
	 * @dataProvider bad_values_provider
	 * pass the bad data types and $expected values from the data providers data set
	 */
	function test_bad_values_create_random_md5_partial_for_taxonomy_name( $bad_value ) {
		$o = new Postmedia\Plugins\Edash\Taxonomy;
		$taxonomy_name = $o->set_taxonomy( $bad_value );
		// md5 partial match pattern for randomized taxonomy if bad value is entered.
		$pattern = '/[a-g0-9]+/';
		$this->assertEquals( 1, preg_match( $pattern, $taxonomy_name ) );
	}

	function test_long_taxonomy_strings_are_truncated_at_100_characters(){
		$o = new Postmedia\Plugins\Edash\Taxonomy;
		$long_taxonomy = ';oasjflkjdsflkasjlfasjdkfjlkdsjljfkldkjlfskjkfjlsdkdslkjfasjldfjkalskjdfalsdjlfsdlfsdjflsjdlkjsdldslkjfsdlsdkjflsdjkflklsjdlfjsaljdfodsjdp98ehp9ew80ewfijodsnfoapsdfosaijfoajodshfojdfofjodsjfoaj0tuear8tu 0aerjodnsogh9eru0eadjfosdj90fj9ejfosdkj[f0eaeur89 tu3q4utuerjodjr0ejt0jdogdsjogigj er0j0erjidfnigj[ae0r[t90aj [eigje0rsj0reajjir';
		$taxonomy_name = $o->set_taxonomy( $long_taxonomy );
		$this->assertEquals( 
			';oasjflkjdsflkasjlfasjdkfjlkdsjljfkldkjlfskjkfjlsdkdslkjfasjldfjkalskjdfalsdjlfsdlfsdjflsjdlkjsdldsl',
			$taxonomy_name
		);
	}

	function tearDown(){}
}
