<?php
namespace Tribe\Events\Pro\Supports\WPML;

use Prophecy\Argument;
use Tribe__Events__Main as Main;
use Tribe__Events__Pro__Supports__WPML__Recurring_Event_Creation_Handler as Handler;

class Recurring_Event_Creation_HandlerTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \Tribe__Events__Pro__Supports__WPML__Event_Listener
	 */
	protected $listener = null;

	/**
	 * @var \Tribe__Events__Pro__Supports__WPML__WPML
	 */
	protected $wpml;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->listener = $this->prophesize( 'Tribe__Events__Pro__Supports__WPML__Event_Listener' );
		$this->wpml     = $this->prophesize( 'Tribe__Events__Pro__Supports__WPML__WPML' );
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

		$this->assertInstanceOf( 'Tribe__Events__Pro__Supports__WPML__Recurring_Event_Creation_Handler', $sut );
	}

	/**
	 * @test
	 * it should exit -1 if parent post language code could not be found
	 */
	public function it_should_exit_minus_1_if_parent_post_language_code_could_not_be_found() {
		$parent_event_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$event_id        = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE, 'post_parent' => $parent_event_id ] );

		$this->wpml->get_parent_language_code( $parent_event_id )->willReturn( false );

		$sut         = $this->make_instance();
		$exit_status = $sut->handle( $event_id, $parent_event_id );

		$this->assertEquals( - 1, $exit_status );
	}

	/**
	 * @test
	 * it should add a translation entry for the recurring event instance when created
	 */
	public function it_should_add_a_translation_entry_for_the_recurring_event_instance_when_created() {
		$parent_event_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$event_id        = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE, 'post_parent' => $parent_event_id ] );

		$language_code = 'it';
		$trid          = '23';

		$this->wpml->get_parent_language_code( $parent_event_id )->willReturn( $language_code );
		$this->wpml->get_master_series_instance_trid( $event_id, $parent_event_id )->willReturn( $trid );
		$this->wpml->insert_event_translation_for_language_code( $event_id, $language_code, $trid, true )->shouldBeCalled();

		$sut = $this->make_instance();
		$sut->handle( $event_id, $parent_event_id );
	}

	private function make_instance() {
		return new Handler( $this->listener->reveal(), $this->wpml->reveal() );
	}
}