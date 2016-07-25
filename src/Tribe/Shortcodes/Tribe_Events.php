<?php
/**
 * Implements a shortcode
 */
class Tribe__Events__Pro__Shortcodes__Tribe_Events {

	/**
	 * The shortcode allows filtering by event categories and by post tags,
	 * in line with what the calendar widget itself supports.
	 *
	 * @var array
	 */
	protected $tax_relationships = array(
		'categories' => Tribe__Events__Main::TAXONOMY,
		'tags' => 'post_tag',
	);


	public function __construct() {
		Tribe__Events__Template_Factory::asset_package( 'events-css' );
	}

	public function tribe_events_shortcode() {
		$args = array(
			'eventDate' => date( 'Y-m-d' ),
		);
		ob_start();
		tribe_show_month( $args );

		$html = ob_get_clean();

		return $html;
	}

	public static $instance;

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return Tribe__Events__Pro__Shortcodes__Tribe_Events
	 */
	public static function instance() {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
add_shortcode( 'tribe_events', array( 'Tribe__Events__Pro__Shortcodes__Tribe_Events', 'tribe_events_shortcode' ) );
