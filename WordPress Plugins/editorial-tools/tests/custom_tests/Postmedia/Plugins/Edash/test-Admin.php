<?php

use Postmedia\Plugins\EDash\Admin;

require_once('./classes/Postmedia/Plugins/Edash/Admin.php');

class PostmediaPluginsEdashAdmin extends WP_UnitTestCase {

	/**
	 * Set up the test environment.
	 */
	function setUp() {
		parent::setUp();
		$this->obj = new Admin();
	}

	function admin_notices_provider() {
		return array(
			array( 'post-new.php', 'post', '/^$/' ),
			array( 'post.php', 'post', '/^$/' ),
			array( 'post-new.php', 'pn_pointer', '/This page is for external pointers only/' ),
			array( 'post.php', 'pn_pointer', '/^$/' ),
		);
	}

	/**
	 * @dataProvider admin_notices_provider
	 */
	function test_admin_notices( $page, $type, $expected ) {
		global $pagenow, $typenow;
		$pagenow = $page;
		$typenow = $type;
		$this->expectOutputRegex( $expected );
		$this->obj->admin_notices();
	}

	function pointer_meta_box_provider() {
		return array(
			array( 'post-new.php', '/Relative URL/' ),
			array( 'post.php', '/(?!Relative URL)/' ),
		);
	}

	/**
	 * @dataProvider pointer_meta_box_provider
	 */
	function test_pointer_meta_box( $page, $expected ) {
		set_current_screen( 'post' );

		$post_id = $this->factory->post->create();
		$url = $page;
		if ( 'post.php' === $page ) {
			$url .= '?post=' . $post_id . '&action=edit';
		}
		self::go_to( admin_url( $url ) );

		$this->expectOutputRegex( $expected );
		$this->obj->pointer_meta_box();

		set_current_screen( 'front' );
	}

	/**
	 * Destroy the test environment.
	 */
	function tearDown() {
		parent::tearDown();
	}
}
