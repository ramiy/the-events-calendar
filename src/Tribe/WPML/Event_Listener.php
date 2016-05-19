<?php


/**
 * Class Tribe__Events__Pro__WPML__Event_Listener
 *
 * Listens for Tribe Events events (actions and filters) and dispatches
 */
class Tribe__Events__Pro__WPML__Event_Listener {

	/**
	 * @var Tribe__Events__Pro__WPML__Event_Listener
	 */
	protected static $instance;

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
}