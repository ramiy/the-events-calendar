<?php


class Tribe__Events__Pro__WPML__Recurring_Event_Creation_Handler implements Tribe__Events__Pro__WPML__Handler_Interface {

	/**
	 * @var Tribe__Events__Pro__WPML__Event_Listener
	 */
	private $event_listener;

	public function __construct( Tribe__Events__Pro__WPML__Event_Listener $event_listener ) {
		$this->event_listener = $event_listener;
	}

	/**
	 * @param int      $event_id
	 * @param int|null $parent_event_id
	 *
	 * @return mixed
	 */
	public function handle( $event_id, $parent_event_id = null ) {
		// TODO: Implement handle() method.
	}
}