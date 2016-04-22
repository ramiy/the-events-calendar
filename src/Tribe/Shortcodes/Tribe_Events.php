<?php
/**
 * Implements a shortcode
 */
class Tribe__Events__Pro__Shortcodes__Tribe_Events {

	public function __construct() {
		// Ensure the expected CSS is available to style the shortcode output (this will
		// happen automatically in event views, but not elsewhere)
		Tribe__Events__Template_Factory::asset_package( 'events-css' );
	}

	public function do_shortcode() {

		// Start to record the Output
		ob_start();

		echo '<div>';
		echo tribe_show_month(array( 'eventDate' => '2016-05-01' ));

		echo '</div>';

		// Save it to a variable
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
add_shortcode( 'tribe_events', array( 'Tribe__Events__Pro__Shortcodes__Tribe_Events', 'do_shortcode' ) );
