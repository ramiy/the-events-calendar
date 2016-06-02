<?php

namespace Helper\Recurring;


/**
 * Class EventSeries
 *
 * Models a series of recurring events.
 *
 * @package Helper\Recurring
 */
class EventSeries {

	/**
	 * @var array
	 */
	protected $series;

	/**
	 * EventSeries constructor.
	 *
	 * @param int $post_id The recurring event series master event post ID.
	 */
	public function __construct( $post_id ) {
		/** @var \wpdb $wpdb */
		global $wpdb;

		if ( empty( $wpdb ) ) {
			throw new \RuntimeException( __CLASS__ . ': $wpdb is not defined.' );
		}

		$event_post_type = \Tribe__Events__Main::POSTTYPE;

		if ( get_post( $post_id )->post_type !== $event_post_type ) {
			throw new \RuntimeException( __CLASS__ . ': post with ID [' . $post_id . '] is not an event.' );
		}

		if ( ! tribe_is_recurring_event( $post_id ) ) {
			throw new \RuntimeException( __CLASS__ . ': event with ID [' . $post_id . '] is not a recurring event.' );
		}

		$tribe_get_events = tribe_get_events( [
			'post_parent' => $post_id,
			'post_fields' => 'ids',
		] );

		$ids          = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE post_type = '{$event_post_type}' 
				AND post_parent = {$post_id}
				AND pm.meta_key = '_EventStartDate'
				ORDER BY pm.meta_key ASC, p.ID ASC
				" );
		$this->series = array_merge( [$post_id], $ids );
	}

	/**
	 * Returns the first recurring event child instance ID in the series.
	 *
	 * Index 1 as 0 is the master event.
	 * Equivalent to `EventSeries::index(1)`.
	 *
	 * @return int
	 */
	public function first_child() {
		return $this->series[1];
	}

	/**
	 * Returns the last recurring event child instance ID in the series.
	 *
	 * @return int
	 */
	public function last_child() {
		return end( $this->series );
	}

	/**
	 * Return the nth child event in the series.
	 *
	 * 0 is the master event.
	 *
	 * @param int $n
	 *
	 * @return mixed
	 */
	public function index( $n ) {
		return $this->series[ $n ];
	}

	/**
	 * Returns the post ID of the series parent event.
	 *
	 * @return int
	 */
	public function parent() {
		return $this->series[0];
	}
}