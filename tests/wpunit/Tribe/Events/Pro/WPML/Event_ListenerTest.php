<?php
namespace Tribe\Events\Pro\WPML;

use Tribe__Events__Pro__WPML__Event_Listener as Event_Listener;

class Event_ListenerTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( 'Tribe__Events__Pro__WPML__Event_Listener', $sut );
	}

	private function make_instance() {
		return new Event_Listener();
	}
}