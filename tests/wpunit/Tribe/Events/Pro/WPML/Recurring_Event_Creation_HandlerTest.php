<?php
namespace Tribe\Events\Pro\WPML;

use tad\FunctionMocker\FunctionMocker;
use Tribe__Events__Pro__WPML__Recurring_Event_Creation_Handler as Handler;

class Recurring_Event_Creation_HandlerTest extends \Codeception\TestCase\WPTestCase {

	protected $listener = null;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		FunctionMocker::setUp();
		$this->listener = $this->prophesize( 'Tribe__Events__Pro__WPML__Event_Listener' );
	}

	public function tearDown() {
		// your tear down methods here
		FunctionMocker::tearDown();

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

	public function wpml_add_translatable_content_exit_stati() {
		return [
			[ WPML_API_INVALID_CONTENT_TYPE ],
			[ WPML_API_INVALID_LANGUAGE_CODE ],
			[ WPML_API_INVALID_TRID ],
			[ WPML_API_CONTENT_EXISTS ],
			[ WPML_API_ERROR ],
			[ WPML_API_SUCCESS ],
		];
	}

	/**
	 * @test
	 * it should return wpml_add_translatable_content exit status when handling
	 * @dataProvider wpml_add_translatable_content_exit_stati
	 * @env wpml
	 */
	public function it_should_return_wpml_add_translatable_content_exit_status_when_handling( $exit_status ) {
		FunctionMocker::replace( 'wpml_add_translatable_content', $exit_status );

		$sut = $this->make_instance();

		$exit = $sut->handle( 12, 112 );

		$this->assertEquals( $exit_status, $exit );
	}


	private function make_instance() {
		return new Handler( $this->listener->reveal() );
	}
}