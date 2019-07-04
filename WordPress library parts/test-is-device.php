<?php

require_once( dirname(__FILE__) . 'Device.php');

class DeviceTest extends WP_UnitTestCase {
	public $Device;

	public function setUp() {
		#setup code
		parent::setUp();

		require_once( dirname(__FILE__) . '/class.jetpack-user-agent.php' );
		$this->Device = new Postmedia\Web\Utilities\Device();
		$this->Device->unit_test_set_jetpack_user_agent_info( new Jetpack_User_Agent_Info() );
	}

	/**
	 * Create the data set for the data provider
	 * Inner array pairs are user agent ( $ua ) and $expected value
	 * @return collections array
	 */
	public function mobile_user_agent_data_provider() {
		return array(
				array( 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543 Safari/419.3', 'mobile' ),
				array( 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12A366 Safari/600.1.4', 'mobile' ),
				array( 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 5_1_1 like Mac OS X; en) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/9B206 Safari/7534.48.3', 'mobile' ),
				array( 'Mozilla/5.0 (Linux; Android 4.4; Nexus 5 Build/_BuildID_) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36', 'mobile' )
		);
	}

	/**
	 * Create the data set for the data provider
	 * Inner array pairs are user agent ( $ua ) and $expected value
	 * @return collections array
	 */
	public function is_mobile_user_agent_data_provider() {
		return array(
				array( 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543 Safari/419.3', true ),
				array( 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12A366 Safari/600.1.4', true ),
				array( 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 5_1_1 like Mac OS X; en) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/9B206 Safari/7534.48.3', true ),
				array( 'Mozilla/5.0 (Linux; Android 4.4; Nexus 5 Build/_BuildID_) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36', true )
		);
	}

	/**
	 * Create the data set for the data provider
	 * Inner array pairs are user agent ( $ua ) and $expected value
	 * @return collections array
	 */
	public function is_tablet_user_agent_data_provider() {
		return array(
				array( 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.3; Tablet PC 2.0)', true ),
				array( 'Opera/9.80 (Windows NT 6.1; Opera Tablet/15165; U; en) Presto/2.8.149 Version/11.1', true ),
				array( 'Mozilla/5.0 (Linux; U; Android 4.2.2; nl-nl; GT-P5210 Build/JDQ39) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30', true ),
				array( 'Mozilla/5.0 (Linux; U; Android 4.2.2; en-gb; SM-T311 Build/JDQ39) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30', true ),
				array( 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10', true ),
				array( 'Mozilla/5.0 (iPad; CPU OS 5_1 like Mac OS X; en-us) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9B176 Safari/7534.48.3', true )
		);
	}

	/**
	 * Create the data set for the data provider
	 * Inner array pairs are user agent ( $ua ) and $expected value
	 * @return collections array
	 */
	public function tablet_user_agent_data_provider() {
		return array(
				array( 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.3; Tablet PC 2.0)', 'tablet' ),
				array( 'Opera/9.80 (Windows NT 6.1; Opera Tablet/15165; U; en) Presto/2.8.149 Version/11.1', 'tablet' ),
				array( 'Mozilla/5.0 (Linux; U; Android 4.2.2; nl-nl; GT-P5210 Build/JDQ39) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30', 'tablet' ),
				array( 'Mozilla/5.0 (Linux; U; Android 4.2.2; en-gb; SM-T311 Build/JDQ39) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30', 'tablet' ),
				array( 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10', 'tablet' ),
				array( 'Mozilla/5.0 (iPad; CPU OS 5_1 like Mac OS X; en-us) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9B176 Safari/7534.48.3', 'tablet' )
		);
	}

	/**
	 * @dataProvider mobile_user_agent_data_provider
	 * pass the $ua and $expected values from the data providers data set
	 */
	public function testGetDeviceTypeIsMobile( $ua, $expected ) {
		// mobile user agent
		$this->Device->set_user_agent( $ua );
		$this->assertEquals( $expected, $this->Device->type() );
	}

	/**
	 * @dataProvider is_mobile_user_agent_data_provider
	 * pass the $ua and $expected values from the data providers data set
	 */
	public function testIsMobile( $ua, $expected ) {
		// mobile user agent
		$this->Device->set_user_agent( $ua );
		$this->assertEquals( $expected, $this->Device->is_mobile() );
	}

	/**
	 * @dataProvider tablet_user_agent_data_provider
	 * pass the $ua and $expected values from the data providers data set
	 */
	public function testGetDeviceTypeIsTablet( $ua, $expected ) {
		// tablet user agent
		$this->Device->set_user_agent( $ua );
		$this->assertEquals( $expected, $this->Device->type() );
	}

	/**
	 * @dataProvider is_tablet_user_agent_data_provider
	 * pass the $ua and $expected values from the data providers data set
	 */
	public function testIsTablet( $ua, $expected ) {
		// tablet user agent
		$this->Device->set_user_agent( $ua );
		$this->assertEquals( $expected, $this->Device->is_tablet() );
	}

	/**
	 * Create the data set for the data provider
	 * Inner array pairs are bad user agent and $expected value
	 * @return collections array
	 */
	public function bad_user_agent_data_provider() {
		return array(
				array( 12345, 'mobile' ),
				array( -12345, 'mobile' ),
				array( true, 'mobile' ),
				array( null, 'mobile' ),
				array( '', 'mobile' ),
				array( "<IMG SRC=j&#X41vascript:alert('test2')>", 'mobile' ),
				array( "<SCRIPT type='text/javascript'>var adr = '../evil.php?cakemonster=' + escape(document.cookie);</SCRIPT>", 'mobile' )
		);
	}

	/**
	 * @dataProvider bad_user_agent_data_provider
	 * pass the $ua and $expected values from the data providers data set
	 */
	public function testBadUserAgents( $bad_ua, $expected ) {
		// tablet user agent
		$this->Device->set_user_agent( $bad_ua );
		$this->assertEquals( $expected, $this->Device->type() );
	}

	public function test_get_user_agent_return_correct_user_agent() {
		$this->Device->set_user_agent( 'Opera/9.80 (Windows NT 6.1; Opera Tablet/15165; U; en) Presto/2.8.149 Version/11.1' );
		$this->assertEquals(
			'Opera/9.80 (Windows NT 6.1; Opera Tablet/15165; U; en) Presto/2.8.149 Version/11.1',
			$this->Device->get_user_agent()
		);
		$this->Device->set_user_agent( 1.567 );
		$this->assertEquals( $this->Device->get_user_agent(), null );
	}

	public function tearDown() {
		# tear down code
		parent::tearDown();
	}
}