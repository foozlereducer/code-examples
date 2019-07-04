<?php
error_reporting( E_ERROR & ~E_DEPRECATED & ~E_STRICT );

class StorageTest extends WP_UnitTestCase {

	public $s;

	/**
	 * Set up the test environment.
	 */
	function setUp() {
		// calling this allows hooks into the base WordPress classes such as the WP_User object.
		parent::setUp();

		// Regenerate the Storage class each test
		$this->s = new Postmedia\Web\Storage();
		// Re-initialize storage each test
		$this->s->initialize_storage( $this->storage_hook = 'my_plugin_or_theme_hook' );
		$this->s->add_option( 'status', 'initialized' );
	}

	function test_storage_initializes() {
		$this->assertTrue( $this->s->initialize_storage( 'a_test_plugin_hook' ) );
	}

	function test_storage_requires_string_hook_otherwise_it_fails_to_initialize() {
		$this->assertFalse( $this->s->initialize_storage( 12345987 ) );
	}

	function test_get_option_returns_array_by_default() {
		$value = '{ "base":["tomato","vinger","basil"] }';
		$key = 'katchup-ingredients';
		$this->s->add_option( $key , $value );
		$this->assertTrue( is_array( $this->s->get_options() ) );
	}

	function test_add_full_json_option_to_db() {
		$value = '{ "base":["tomato","vinger","basil"] }';
		$key = 'katchup-ingredients';
		$this->s->add_option( $key, $value );
		$options = $this->s->get_options();
		$this->assertEquals( $options['status'], 'initialized' );
		$this->assertEquals( $options[ $key ]['base'][0], 'tomato' );
		$this->assertEquals( $options[ $key ]['base'][1], 'vinger' );
		$this->assertEquals( $options[ $key ]['base'][2], 'basil' );
	}

	function test_existing_key_updates_options() {
		$key = 'katchup-ingredients';
		$value = '{ "base":["tomato","vinger","basil"] }';
		$this->s->add_option( $key, $value );
		$value = '{ "base":["tomato","vinger","basil","ground chilly pepper"] }';
		$this->s->add_option( $key, $value );
		$options = $this->s->get_options();
		$this->assertEquals( $options['status'], 'initialized' );
		$this->assertEquals( $options[ $key ]['base'][0], 'tomato' );
		$this->assertEquals( $options[ $key ]['base'][1], 'vinger' );
		$this->assertEquals( $options[ $key ]['base'][2], 'basil' );
		$this->assertEquals( $options[ $key ]['base'][3], 'ground chilly pepper' );
	}

	function test_add_key_value_pairs_write_json_to_db_as_option() {
		$this->s->add_option( $key = 'bottle-color', $value = array( 'clear', 'blue', 'brown' ) );
		$options = $this->s->get_options();
		$this->assertEquals( $options['status'], 'initialized' );
		$this->assertEquals( $options['bottle-color'][0], 'clear' );
		$this->assertEquals( $options['bottle-color'][1], 'blue' );
		$this->assertEquals( $options['bottle-color'][2], 'brown' );
	}

	function test_delete_removes_correct_option_key_and_value() {
		$key = 'katchup-ingredients';
		$value = '{ "base":["tomato","vinger","basil"] }';
		$this->s->add_option( $key, $value );
		$this->s->delete_option( 'status' );
		$options = $this->s->get_options();
		$this->assertFalse( array_key_exists( 'status', $options ) );
		$this->assertEquals( $options[ $key ]['base'][0], 'tomato' );
		$this->assertEquals( $options[ $key ]['base'][1], 'vinger' );
		$this->assertEquals( $options[ $key ]['base'][2], 'basil' );
	}

	function test_deleting_absent_key_and_value_does_not_throw_fatal_error() {
		$key = 'katchup-ingredients';
		$value = '{ "base":["tomato","vinger","basil"] }';
		$this->s->add_option( $key, $value );
		$this->assertFalse( $this->s->delete_option( 'does-not-exit' ) );
	}

	function test_get_option_returns_single_option() {
		$key = 'katchup-ingredients';
		$value = '{ "base":["tomato","vinger","basil"] }';
		// Add JSON values by calling get_option() the default status key will also be created
		$this->s->add_option( $key, $value );

		// Get katchup-ingredients option as JSON
		$katchup_ingredients = $this->s->get_option( 'katchup-ingredients', $get_json = true );
		$this->assertEquals( '{"base":["tomato","vinger","basil"]}', $katchup_ingredients );

		// Get katchup-ingredient option as Array
		$katchup_ingredients = $this->s->get_option( 'katchup-ingredients' );
		$this->assertEquals( $katchup_ingredients['base'][0], 'tomato' );
		$this->assertEquals( $katchup_ingredients['base'][1], 'vinger' );
		$this->assertEquals( $katchup_ingredients['base'][2], 'basil' );
	}

	function test_single_value_option_always_returns_string() {
		// Get Status single key and value
		$this->status = $this->s->get_option( 'status', $get_json = false );
		$this->assertEquals( 'initialized', $this->status );
	}

	function test_single_value_option_always_returns_string_as_json() {
		// Get Status single key and value
		$this->status = $this->s->get_option( 'status', $get_json = true );
		$this->assertEquals( 'initialized', $this->status );
	}

	function test_expunge_plugins_settings() {
		$this->s->add_option( $key = 'katchup-ingredients', $value = '{ "base":["tomato","vinger","basil"] }' );
		$this->s->add_option( $key = 'bottle-color', $value = array( 'clear', 'blue', 'brown' ) );
		$options = $this->s->get_options();

		// assert that options exist:
		$this->assertEquals( $options['status'], 'initialized' );
		$this->assertEquals( $options['katchup-ingredients']['base'][0], 'tomato' );
		$this->assertEquals( $options['katchup-ingredients']['base'][1], 'vinger' );
		$this->assertEquals( $options['katchup-ingredients']['base'][2], 'basil' );
		$this->assertEquals( $options['bottle-color'][0], 'clear' );
		$this->assertEquals( $options['bottle-color'][1], 'blue' );
		$this->assertEquals( $options['bottle-color'][2], 'brown' );

		$this->assertTrue( $this->s->expunge_settings( 'my_plugin_or_theme_hook' ) );
		$this->assertNull( $this->s->get_options() );
	}

	public function test_update_option_is_adapter_method_for_add_option() {
		$json = '{ "katchup-ingredients": { "base":["tomato","vinger","basil"] } }';
		$this->s->update_option( $key = '', $value = '', $json );
		$update_options = $this->s->get_options( $get_array = true );
		$this->s->add_option( $key = '', $value = '', $json );
		$add_options = $this->s->get_options( $get_array = true );
		$this->assertEquals( $update_options, $add_options );
	}

	/**
	 * Destroy the test environment.
	 */
	function tearDown() {
		parent::tearDown();
	}
}
