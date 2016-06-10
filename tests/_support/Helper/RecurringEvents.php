<?php

namespace Helper;

use Helper\Recurring\EventSeries;
use Tribe__Events__API as API;
use Tribe__Events__Pro__Recurrence__Queue_Processor as Queue_Processor;

/**
 * Class RecurringEvents
 *
 * A test utility class to generate recurring events for testing purposes.
 *
 * @package Helper
 */
class RecurringEvents extends \Codeception\Module {

	/**
	 * @var int
	 */
	protected static $count = 0;

	/**
	 * @var int The post ID of the parent event of the last recurring series created.
	 */
	protected $last;

	/**
	 * Creates a recurring event in the database and processes the whole queue.
	 *
	 * @param array      $overrides        {
	 *                                     An array of values to override the default ones:
	 *
	 * @type string      $post_title       The event title
	 * @type string      $post_content     The event content
	 * @type string      $EventStartDate   The event start date
	 * @type string      $EventEndDate     The event end date
	 * @type int         $EventStartHour   The event start hour
	 * @type int         $EventEndHour     The event end hour
	 * @type int         $EventStartMinute The event start minute
	 * @type int         $EventEndMinute   The event end minute
	 *                              }
	 *
	 * @param
	 * @param array|null $recurrence       An array describing a recurrence pattern to override the default one.
	 *                                     Beware that the recurrence array pattern is an array of associative arrays; see the `get_recurrence_array` method.
	 *
	 * @return int The created event series parent event post ID.
	 */
	public function create_recurring_event( array $overrides = [ ], array $recurrence = null ) {
		$start_date = date( 'Y-m-d' );
		$count      = $this->count();

		$args = array_merge( [
			'post_title'       => 'Recurring Event ' . $count,
			'post_content'     => 'Recurring Event Content ' . $count,
			'post_status'      => 'publish',
			'EventStartDate'   => $start_date,
			'EventEndDate'     => $start_date,
			'EventStartHour'   => 16,
			'EventEndHour'     => 17,
			'EventStartMinute' => 0,
			'EventEndMinute'   => 0,
		], $overrides );

		$args['recurrence'] = null !== $recurrence ? $recurrence : $this->get_recurrence_array();

		$post_id = API::createEvent( $args );

		$queue_processor = new Queue_Processor;
		$queue_processor->process_batch( $post_id, 200 );

		$this->last = $post_id;

		return $post_id;
	}

	/**
	 * Returns a recurrence array meant to be a good start for number and recurrence pattern.
	 *
	 * Recurrence pattern is daily for 30 times.
	 *
	 * The recurrence pattern contains one entry with default arguments.
	 *
	 * @see Tribe__Events__API::createEvent
	 *
	 * @param array $overrides An array of arguments to override the only recurrence entry defaults.
	 *
	 * @return array An array ready to be used as `recurrence` argument of the `Tribe__Events__API::createEvent` method.
	 */
	public function get_recurrence_array( array $overrides = [ ] ) {
		$defaults = [
			'type'      => 'Every Day',
			'end-type'  => 'After',
			'end'       => null,
			'end-count' => 30,
		];

		return [
			'rules' => [
				0 => array_merge( $defaults, $overrides ),
			],
		];
	}

	/**
	 * @return int
	 */
	private function count() {
		return ++ self::$count;
	}

	/**
	 * @return EventSeries
	 */
	public function last_series() {
		return new EventSeries( $this->last );
	}
}