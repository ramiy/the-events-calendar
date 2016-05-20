<?php


/**
 * Class Tribe__Events__Pro__WPML__Event_Listener
 *
 * Listens for Tribe Events events (actions and filters) and dispatches
 */
// @todo: extract this to an interface and abstract class
class Tribe__Events__Pro__WPML__Event_Listener {

	/**
	 * @var Tribe__Events__Pro__WPML__Event_Listener
	 */
	private static $instance;

	/**
	 * @var array
	 */
	private $handlers_map;

	/**
	 * The class singleton constructor.
	 *
	 * @return Tribe__Events__Pro__WPML__Event_Listener
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Tribe__Events__Pro__WPML__Event_Listener constructor.
	 *
	 * @param array|null $handlers_map An associative array of event type to handling class instances.
	 */
	public function __construct( array $handlers_map = null ) {
		$this->handlers_map = $handlers_map ? $handlers_map : $this->get_handlers_map();
	}

	/**
	 * @param int      $post_id
	 * @param int|null $post_parent_id
	 */
	public function handle_recurring_event_creation( $post_id, $post_parent_id = null ) {
		$this->ensure_is_event( $post_id );
		$this->ensure_is_event( $post_parent_id );
		$this->ensure_event_is_parent_to( $post_parent_id, $post_id );

		if ( $this->has_handler_for_event( 'event.recurring.created' ) ) {
			/** @var Tribe__Events__Pro__WPML__Handler_Interface $handler */
			$handler = $this->get_handler_for_event( 'event.recurring.created' );
			$handler->handle( $post_id, $post_parent_id );
		}
	}

	/**
	 * @return array
	 */
	private function get_handlers_map() {
		return array( 'event.recurring.created' => new Tribe__Events__Pro__WPML__Recurring_Event_Creation_Handler( $this ) );
	}

	/**
	 * @param $post_id
	 */
	protected function ensure_is_event( $post_id ) {
		if ( ! tribe_is_event( $post_id ) ) {
			throw new InvalidArgumentException( 'Post ID [' . $post_id . '] is not an int, does not exist or is not that of an event.' );
		}
	}

	/**
	 * @param $event
	 *
	 * @return bool
	 */
	protected function has_handler_for_event( $event ) {
		return isset( $this->handlers_map[ $event ] );
	}

	/**
	 * @param $event
	 *
	 * @return Tribe__Events__Pro__WPML__Handler_Interface
	 */
	protected function get_handler_for_event( $event ) {
		if ( ! is_a( $this->handlers_map[ $event ], 'Tribe__Events__Pro__WPML__Handler_Interface' ) ) {
			$this->handlers_map[ $event ] = new $this->handlers_map[$event];
		}

		return $this->handlers_map[ $event ];
	}

	/**
	 * @param $post_parent_id
	 * @param $post_id
	 */
	private function ensure_event_is_parent_to( $post_parent_id, $post_id ) {
		if ( get_post( $post_id )->post_parent !== $post_parent_id ) {
			throw new InvalidArgumentException( 'Event with ID [' . $post_parent_id . '] is not parent of event with ID [' . $post_id . ']' );
		}
	}
}