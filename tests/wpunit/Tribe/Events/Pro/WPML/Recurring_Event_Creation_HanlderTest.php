<?php
namespace Tribe\Events\Pro\WPML;

use Tribe__Events__Pro__WPML__Recurring_Event_Creation_Handler as Handler;

class Recurring_Event_Creation_HandlerTest extends \Codeception\TestCase\WPTestCase {

	protected $listener = null;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->listener = $this->prophesize( 'Tribe__Events__Pro__WPML__Event_Listener' );
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

		$this->assertInstanceOf( 'Tribe__Events__Pro__WPML__Recurring_Event_Creation_Handler', $sut );
	}

	private function make_instance() {
		return new Handler( $this->listener->reveal() );
	}
}