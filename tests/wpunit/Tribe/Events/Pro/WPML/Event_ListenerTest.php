<?php
namespace Tribe\Events\Pro\WPML;

use Prophecy\Argument;
use Tribe__Events__Main as Main;
use Tribe__Events__Pro__WPML__Event_Listener as Event_Listener;

class Event_ListenerTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var array
	 */
	protected $handlers_map;

	/**
	 * @var \Tribe__Log__Logger
	 */
	protected $logger;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->logger = $this->prophesize( 'Tribe__Log__Logger' );
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

	/**
	 * @test
	 * it should throw if handling non int post_id
	 */
	public function it_should_throw_if_handling_non_int_post_id() {
		$sut = $this->make_instance();

		$this->expectException( 'InvalidArgumentException' );

		$sut->handle_recurring_event_creation( 'foo' );
	}

	/**
	 * @test
	 * it should throw if handling non existent post_id
	 */
	public function it_should_throw_if_handling_non_existent_post_id() {
		$sut = $this->make_instance();

		$this->expectException( 'InvalidArgumentException' );

		$sut->handle_recurring_event_creation( 516 );
	}

	/**
	 * @test
	 * it should throw if handling non event post_id
	 */
	public function it_should_throw_if_handling_non_event_post_id() {
		$sut = $this->make_instance();

		$this->expectException( 'InvalidArgumentException' );
		$non_event_post_id = $this->factory()->post->create( [ 'post_type' => 'post' ] );

		$sut->handle_recurring_event_creation( $non_event_post_id );
	}

	/**
	 * @test
	 * it should trhow if handling non int and non null parent_post_id
	 */
	public function it_should_trhow_if_handling_non_int_and_non_null_parent_post_id() {
		$sut = $this->make_instance();

		$this->expectException( 'InvalidArgumentException' );
		$event_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );

		$sut->handle_recurring_event_creation( $event_id, 'foo' );
	}

	/**
	 * @test
	 * it should throw if handling non existing parent_post_id
	 */
	public function it_should_throw_if_handling_non_existing_parent_post_id() {
		$sut = $this->make_instance();

		$this->expectException( 'InvalidArgumentException' );
		$event_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );

		$sut->handle_recurring_event_creation( $event_id, 123 );
	}

	/**
	 * @test
	 * it should throw if handling non event parent_post_id
	 */
	public function it_should_throw_if_handling_non_event_parent_post_id() {
		$sut = $this->make_instance();

		$this->expectException( 'InvalidArgumentException' );
		$event_id     = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$non_event_id = $this->factory()->post->create( [ 'post_type' => 'post' ] );

		$sut->handle_recurring_event_creation( $event_id, $non_event_id );
	}

	/**
	 * @test
	 * it should throw if handling parent event that's not parent
	 */
	public function it_should_throw_if_handling_parent_event_that_s_not_parent() {
		$sut = $this->make_instance();

		$this->expectException( 'InvalidArgumentException' );
		$event_id            = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$non_parent_event_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );

		$sut->handle_recurring_event_creation( $event_id, $non_parent_event_id );
	}

	/**
	 * @test
	 * it should dispatch creation to handler
	 */
	public function it_should_dispatch_creation_to_handler() {
		$parent_event_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$event_id        = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE, 'post_parent' => $parent_event_id ] );

		$handler = $this->prophesize( 'Tribe__Events__Pro__WPML__Handler_Interface' );
		$handler->handle( $event_id, $parent_event_id )->shouldBeCalled();
		$this->handlers_map = [ 'event.recurring.created' => $handler->reveal() ];

		$sut = $this->make_instance();

		$sut->handle_recurring_event_creation( $event_id, $parent_event_id );
	}

	/**
	 * @test
	 * it should log handler exit status
	 */
	public function it_should_log_handler_exit_status() {
		$parent_event_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$event_id        = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE, 'post_parent' => $parent_event_id ] );

		$handler = $this->prophesize( 'Tribe__Events__Pro__WPML__Handler_Interface' );
		$handler->handle( $event_id, $parent_event_id )->willReturn( 'an exit status' );
		$this->logger->log( Argument::type( 'string' ), Argument::type( 'string' ), Argument::type( 'string' ) )->shouldBeCalled();
		$this->handlers_map = [ 'event.recurring.created' => $handler->reveal() ];

		$sut = $this->make_instance();

		$sut->handle_recurring_event_creation( $event_id, $parent_event_id );
	}

	public function exit_stati_and_expected_log_entries() {
		return [
			[ 'foo', 'foo' ],
			[ 'foo bar', 'foo bar' ],
			[ [ 'foo' => 'bar' ], json_encode( [ 'foo' => 'bar' ] ) ],
			[ [ 'foo' => 'bar', 'baz' => 23 ], json_encode( [ 'foo' => 'bar', 'baz' => 23 ] ) ],
		];
	}

	/**
	 * @test
	 * it should stringify non string exit stati
	 * @dataProvider exit_stati_and_expected_log_entries
	 */
	public function it_should_stringify_non_string_exit_stati( $exit_status, $expected_log_entry ) {
		$parent_event_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$event_id        = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE, 'post_parent' => $parent_event_id ] );

		$handler = $this->prophesize( 'Tribe__Events__Pro__WPML__Handler_Interface' );
		$handler->handle( $event_id, $parent_event_id )->willReturn( $exit_status );
		$this->logger->log( Argument::containingString( $expected_log_entry ), Argument::type( 'string' ), Argument::type( 'string' ) )->shouldBeCalled();
		$this->handlers_map = [ 'event.recurring.created' => $handler->reveal() ];

		$sut = $this->make_instance();

		$sut->handle_recurring_event_creation( $event_id, $parent_event_id );
	}

	private function make_instance() {
		return new Event_Listener( $this->handlers_map, $this->logger->reveal() );
	}
}