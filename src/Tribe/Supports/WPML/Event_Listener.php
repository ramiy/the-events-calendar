<?php


/**
 * Class Tribe__Events__Pro__Supports__WPML__Event_Listener
 *
 * Listens for Tribe Events events (actions and filters) and dispatches
 */
class Tribe__Events__Pro__Supports__WPML__Event_Listener {

	/**
	 * @var Tribe__Events__Pro__Supports__WPML__Event_Listener
	 */
	private static $instance;

	/**
	 * @var array
	 */
	private $handlers_map;
	/**
	 * @var Tribe__Logger_Interface
	 */
	private $logger;
	/**
	 * @var string
	 */
	private $name_before_wpml_parse_query;

	/**
	 * The class singleton constructor.
	 *
	 * @return Tribe__Events__Pro__Supports__WPML__Event_Listener
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Tribe__Events__Pro__Supports__WPML__Event_Listener constructor.
	 *
	 * @param array|null                               $handlers_map An associative array of event type to handling class instances.
	 * @param Tribe__Log__Logger                       $logger
	 * @param Tribe__Events__Pro__Supports__WPML__WPML $wpml
	 */
	public function __construct( array $handlers_map = null, Tribe__Log__Logger $logger = null, Tribe__Events__Pro__Supports__WPML__WPML $wpml = null ) {
		$this->handlers_map = $handlers_map ? $handlers_map : $this->get_handlers_map();
		$this->logger       = $logger ? $logger : Tribe__Main::instance()->log()->get_current_logger();
		$this->wpml         = $wpml ? $wpml : Tribe__Events__Pro__Supports__WPML__WPML::instance();
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
			/** @var Tribe__Events__Pro__Supports__WPML__Handler_Interface $handler */
			$handler       = $this->get_handler_for_event( 'event.recurring.created' );
			$handling_exit = $handler->handle( $post_id, $post_parent_id );

			$handling_exit = $this->format_exit_status( $handling_exit );

			if ( null !== $this->logger ) {
				$message = $this->get_log_line_header() . 'handled recurring event instance creation [ID ' . $post_id . '; Parent ID ' . $post_parent_id . '] with exit status "' . $handling_exit . '"';
				$this->logger->log( $message, Tribe__Log::DEBUG, __CLASS__ );
			}
		}
	}

	public function handle_wpml_is_redirected_event( $redirect_target, $post_id, $query ) {

		if ( $redirect_target ) {
			if ( 'all' === $query->get( 'eventDisplay' ) || $query->get( 'eventDate' ) ) {
				$redirect_target = false;
			}
		}

		return $redirect_target;
	}

	public function handle_wpml_ls_languages_event( $languages ) {
		if ( tribe_is_showing_all() ) {
			foreach ( $languages as $key => $language ) {

				// TODO: This should be handled properly for all permalink structures.

				// We need to remove the date and replace it with 'all'

				$parts = explode( '/', untrailingslashit($language['url'] ) );
				array_pop( $parts );
				$parts[] = 'all';
				$language['url'] = trailingslashit( implode( '/', $parts ) );
				$languages[ $key ] = $language;
			}
		}
		return $languages;
	}

	public function handle_wpml_get_ls_translations_event( $translations, $query ) {
		if ( $this->is_event_query( $query ) ) {
			$translations = apply_filters( 'wpml_content_translations', null, $query->get( 'post_parent' ), 'tribe_events' );
		}

		return $translations;
	}

	public function handle_tribe_events_pre_get_posts( $query ) {
		if ( $query->get( 'p' ) && $query->get( 'post_parent' ) ) {
			unset( $query->query_vars['p'] );
		}
		return $query;
	}

	public function handle_wpml_pre_parse_query_event( $query ) {
		if ( $this->is_event_query( $query ) ) {
			$this->name_before_wpml_parse_query = $query->get( 'name' );
		} else {
			$this->name_before_wpml_parse_query = '';
		}
		return $query;
	}

	public function handle_wpml_post_parse_query_event( $query ) {
		if ( $this->name_before_wpml_parse_query ) {
			$query->set( 'name', $this->name_before_wpml_parse_query );
		}

		return $query;
	}

	private function is_event_query( $query ) {
		return $query->get( 'post_type' ) == 'tribe_events' && 'all' === $query->get( 'eventDisplay' );
	}

	/**
	 * @return array
	 */
	private function get_handlers_map() {
		return array( 'event.recurring.created' => 'Tribe__Events__Pro__Supports__WPML__Recurring_Event_Creation_Handler' );
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
	 * @return Tribe__Events__Pro__Supports__WPML__Handler_Interface
	 */
	protected function get_handler_for_event( $event ) {
		if ( ! is_a( $this->handlers_map[ $event ], 'Tribe__Events__Pro__Supports__WPML__Handler_Interface' ) ) {
			$this->handlers_map[ $event ] = new $this->handlers_map[$event]( $this, $this->wpml );
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

	/**
	 * @return string
	 */
	protected function get_log_line_header() {
		return 'PRO - WPML Event Listener: ';
	}

	/**
	 * @param $handling_exit
	 *
	 * @return mixed|string|void
	 */
	private function format_exit_status( $handling_exit ) {
		if ( is_array( $handling_exit ) ) {
			$handling_exit = json_encode( $handling_exit );

			return $handling_exit;
		}

		return $handling_exit;
	}
}