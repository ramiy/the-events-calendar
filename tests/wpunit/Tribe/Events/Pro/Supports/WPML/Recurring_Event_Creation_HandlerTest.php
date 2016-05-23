<?php
namespace Tribe\Events\Pro\Supports\WPML;

use tad\FunctionMocker\FunctionMocker;
use Tribe__Events__Main as Main;
use Tribe__Events__Pro__Supports__WPML__Recurring_Event_Creation_Handler as Handler;

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

	/**
	 * @test
	 * it should exit -1 if parent post language code could not be found
	 */
	public function it_should_exit_1_if_parent_post_language_code_could_not_be_found() {
		$parent_event_id = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$event_id        = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE, 'post_parent' => $parent_event_id ] );
		FunctionMocker::replace( 'wpml_get_language_information', false );
		$wpml_add_translatable_content = FunctionMocker::replace( 'wpml_add_translatable_content' );

		$sut         = $this->make_instance();
		$exit_status = $sut->handle( $event_id, $parent_event_id );

		$wpml_add_translatable_content->wasNotCalled();
		$this->assertEquals( - 1, $exit_status );
	}

	/**
	 * @test
	 * it should add a translation entry for the recurring event instance when created from the post edit screen
	 */
	public function it_should_add_a_translation_entry_for_the_recurring_event_instance_when_created_from_the_post_edit_screen() {
		$language_code                 = 'foo';
		$_POST['icl_post_language']    = $language_code;
		$parent_event_id               = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$event_id                      = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE, 'post_parent' => $parent_event_id ] );
		$wpml_add_translatable_content = FunctionMocker::replace( 'wpml_add_translatable_content', 'some exit status' );

		$sut         = $this->make_instance();
		$exit_status = $sut->handle( $event_id, $parent_event_id );

		$wpml_add_translatable_content->wasCalledWithOnce( [ 'post_' . Main::POSTTYPE, $event_id, $language_code ] );
		$this->assertArrayHasKey( $language_code, $exit_status );
		$this->assertEquals( 'some exit status', $exit_status[ $language_code ] );
	}

	/**
	 * @test
	 * it should add a translation entry for the recurring event instance when created from the cron process
	 */
	public function it_should_add_a_translation_entry_for_the_recurring_event_instance_when_created_from_the_cron_process() {
		$language_code                 = 'foo';
		$parent_event_id               = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE ] );
		$event_id                      = $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE, 'post_parent' => $parent_event_id ] );
		$wpml_add_translatable_content = FunctionMocker::replace( 'wpml_add_translatable_content', 'some exit status' );
		FunctionMocker::replace( 'wpml_get_language_information', function ( $_, $id ) use ( $parent_event_id, $language_code ) {
			return $id === $parent_event_id ? [ 'language_code' => $language_code ] : false;
		} );

		$sut         = $this->make_instance();
		$exit_status = $sut->handle( $event_id, $parent_event_id );

		$wpml_add_translatable_content->wasCalledWithOnce( [ 'post_' . Main::POSTTYPE, $event_id, $language_code ] );
		$this->assertArrayHasKey( $language_code, $exit_status );
		$this->assertEquals( 'some exit status', $exit_status[ $language_code ] );
	}

	private function make_instance() {
		return new Handler( $this->listener->reveal() );
	}
}