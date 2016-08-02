<?php
/**
 * Represents individual [tribe_events] shortcodes.
 *
 * @todo extend loading of template classes to more than just month view
 * @todo look at how/what data to pass into those template objects before rendering
 */
class Tribe__Events__Pro__Shortcodes__Tribe_Events {
	/**
	 * Container for the shortcode attributes.
	 *
	 * @var array
	 */
	protected $atts = array();

	/**
	 * Container for the relevant template manager. This may not always be needed
	 * and so may be empty.
	 *
	 * @var object|null
	 */
	protected $template_object;

	/**
	 * @var string
	 */
	protected $output = '';

	/**
	 * Query arguments required to setup the requested view.
	 *
	 * @var array
	 */
	protected $query_args = array();

	/**
	 * Default query arguments generally shared when setting things up for each of our
	 * supported views.
	 *
	 * @var array
	 */
	protected $default_args = array(
		'post_type' => Tribe__Events__Main::POSTTYPE,
	);

	/**
	 * The strings that the shortcode considers to be "truthy" in the context of
	 * various attributes.
	 *
	 * @var array
	 */
	protected $truthy_values = array();


	/**
	 * Generates output for the [tribe_events] shortcode.
	 *
	 * @param $atts
	 */
	public function __construct( $atts ) {
		$this->setup( $atts );
		$this->prepare();
		$this->render();
	}

	/**
	 * Parse the provided attributes and hook into the shortcode processes.
	 *
	 * @param $atts
	 */
	protected function setup( $atts ) {
		$defaults = array(
			'view'     => 'month',
			'redirect' => '',
			'date'     => '',
		);

		$this->atts = shortcode_atts( $defaults, $atts, 'tribe_events' );

		add_action( 'tribe_events_pro_tribe_events_shortcode_prepare_month', array( $this, 'prepare_month' ) );
		add_action( 'tribe_events_pro_tribe_events_shortcode_prepare_list', array( $this, 'prepare_list' ) );
		add_action( 'tribe_events_pro_tribe_events_shortcode_prepare_day', array( $this, 'prepare_day' ) );
		add_action( 'tribe_events_pro_tribe_events_shortcode_prepare_map', array( $this, 'prepare_map' ) );
		add_action( 'tribe_events_pro_tribe_events_shortcode_post_render', array( $this, 'reset_query' ) );
	}

	/**
	 * Facilitates preparation of template classes and anything else required to setup
	 * a given view or support particular attributes that have been set.
	 */
	protected function prepare() {
		/**
		 * Provides an opportunity for template classes to be instantiated and/or
		 * any other required setup to be performed, for a specific view.
		 *
		 * @param Tribe__Events__Pro__Shortcodes__Tribe_Events $shortcode
		 */
		do_action( 'tribe_events_pro_tribe_events_shortcode_prepare_' . $this->atts[ 'view' ], $this );

		/**
		 * Provides an opportunity for template classes to be instantiated and/or
		 * any other required setup to be performed.
		 *
		 * @param string $view
		 * @param Tribe__Events__Pro__Shortcodes__Tribe_Events $shortcode
		 */
		do_action( 'tribe_events_pro_tribe_events_shortcode_prepare_view', $this->atts[ 'view' ], $this );
	}

	/**
	 * Prepares month view.
	 */
	public function prepare_month() {
		if ( ! class_exists( 'Tribe__Events__Template__Month' ) ) {
			return;
		}

		$this->set_query( array(
			'eventDisplay' => 'month',
			'eventDate'    => $this->get_attribute( 'date', '' ),
		) );

		$this->default_preparation();

		Tribe__Events__Bar::instance()->load_script();
		Tribe__Events__Template_Factory::asset_package( 'bootstrap-datepicker' );
		Tribe__Events__Template_Factory::asset_package( 'calendar-script' );
		Tribe__Events__Template_Factory::asset_package( 'ajax-calendar' );

		$this->template_object = new Tribe__Events__Template__Month( $this->query_args );
	}

	/**
	 * Prepares list view.
	 */
	public function prepare_list() {
		if ( ! class_exists( 'Tribe__Events__Template__List' ) ) {
			return;
		}

		$this->set_query( array(
			'eventDisplay' => 'list',
			'eventDate'    => $this->get_attribute( 'date', '' ),
		) );

		$this->default_preparation();

		Tribe__Events__Template_Factory::asset_package( 'ajax-list' );

		$this->template_object = new Tribe__Events__Template__List( $this->query_args );
	}

	/**
	 * Prepares day view.
	 *
	 * @todo troubleshoot total lack of functionality
	 */
	public function prepare_day() {
		if ( ! class_exists( 'Tribe__Events__Template__Day' ) ) {
			return;
		}

		$this->set_query( array(
			'eventDisplay' => 'day',
			'eventDate'    => $this->get_attribute( 'date', '' ),
		) );

		$this->default_preparation();

		Tribe__Events__Template_Factory::asset_package( 'ajax-dayview' );

		$this->template_object = new Tribe__Events__Template__Day( $this->query_args );
	}

	/**
	 * Prepares photo view.
	 *
	 * @todo troubleshoot total lack of functionality
	 */
	public function prepare_photo() {
		if ( ! class_exists( 'Tribe__Events__Pro__Templates__Photo' ) ) {
			return;
		}

		$this->set_query( array(
			'eventDisplay' => 'photo',
			'eventDate'    => $this->get_attribute( 'date', '' ),
		) );

		$this->default_preparation();

		Tribe__Events__Template_Factory::asset_package( 'ajax-photoview' );

		$this->template_object = new Tribe__Events__Pro__Templates__Photo( $this->query_args );
	}

	/**
	 * Ensures the base assets required by all default supported views require are enqueued,
	 * including for the Tribe Events Bar if enabled.
	 */
	protected function default_preparation() {
		global $wp_query;

		// We overwrite the global $wp_query object to facilitate embedding the requested view (the
		// original will be restored during tribe_events_pro_tribe_events_shortcode_post_render)
		$wp_query = new WP_Query( $this->query_args );

		// Assets required by all our supported views
		wp_enqueue_script( 'jquery' );
		Tribe__Events__Template_Factory::asset_package( 'jquery-resize' );
		Tribe__Events__Template_Factory::asset_package( 'bootstrap-datepicker' );
		Tribe__Events__Template_Factory::asset_package( 'events-css' );

		// Tribe Events Bar support
		if ( $this->is_attribute_truthy( 'tribe-bar', true ) ) {
			add_filter( 'tribe-events-bar-should-show', array( $this, 'enable_tribe_bar' ) );

			Tribe__Events__Template_Factory::asset_package( 'jquery-resize' );
			Tribe__Events__Bar::instance()->load_script();
		}

		// Add the method responsible for rendering each of the default supported views
		add_filter( 'tribe_events_pro_tribe_events_shortcode_output', array( $this, 'render_view' ) );
	}

	/**
	 * Expects to be called during "tribe-events-bar-should-show" - will unhook itself
	 * and return true.
	 *
	 * @return bool true
	 */
	public function enable_tribe_bar() {
		remove_filter( 'tribe-events-bar-should-show', array( $this, 'enable_tribe_bar' ) );
		return true;
	}

	/**
	 * Sets the query arguments needed for
	 * @param array $arguments
	 */
	protected function set_query( array $arguments ) {
		$this->query_args = array_merge( $this->default_args, $arguments );
	}

	/**
	 * Once the view has been rendered, restore the origin WP_Query object.
	 */
	public function reset_query() {
		remove_action( 'tribe_events_pro_tribe_events_shortcode_post_render', array( $this, 'reset_query' ) );
		wp_reset_query();
	}

	/**
	 * Returns the currently set shortcode attributes.
	 *
	 * @return array
	 */
	public function get_attributes() {
		return $this->atts;
	}

	/**
	 * Returns the value of the specified shortcode attribute or else returns
	 * $default if $name is not set.
	 *
	 * @param string $name
	 * @param mixed  $default = null
	 *
	 * @return mixed
	 */
	public function get_attribute( $name, $default = null ) {
		return isset( $this->atts[ $name ] ) ? $this->atts[ $name ] : $default;
	}

	/**
	 * Tests to see if the specified attribute has a truthy value (typically "on",
	 * "true", "yes" or "1").
	 *
	 * In cases where the attribute is not set, it will return false unless
	 * $true_by_default is set to true.
	 *
	 * @param string $name
	 * @param bool   $true_by_default = false
	 *
	 * @return bool
	 */
	public function is_attribute_truthy( $name, $true_by_default = false ) {
		// If the attribute is not set, return the default
		if ( ! isset( $this->atts[ $name ] ) ) {
			return (bool) $true_by_default;
		}

		$value = strtolower( $this->get_attribute( $name ) );
		return in_array( $value, $this->get_truthy_values() );
	}

	/**
	 * Returns an array of strings that can be regarded as "truthy".
	 *
	 * @return array
	 */
	protected function get_truthy_values() {
		if ( empty( $this->truthy_values ) ) {
			/**
			 * Allows the set of strings regarded as truthy (in the context of the [tribe_events]
			 * shortcode attributes) to be altered.
			 *
			 * These should generally be lowercase strings for those languages where such a thing
			 * makes sense.
			 *
			 * @param array $truthy_values
			 */
			$this->truthy_values = (array) apply_filters( 'tribe_events_pro_tribe_events_shortcode_truthy_values', array(
				'1',
				'on',
				'yes',
				'true',
			) );
		}

		return $this->truthy_values;
	}

	/**
	 * Returns the current template class object, if one has been found and loaded.
	 *
	 * @return object|null
	 */
	public function get_template_object() {
		return $this->template_object;
	}

	/**
	 * Triggers rendering of the currently requested view.
	 */
	protected function render() {
		/**
		 * Triggers the rendering of the requested view.
		 *
		 * @param string $html
		 * @param string $view
		 * @param Tribe__Events__Pro__Shortcodes__Tribe_Events $shortcode
		 */
		$this->output = (string) apply_filters( 'tribe_events_pro_tribe_events_shortcode_output', '', $this->atts[ 'view' ], $this );
	}

	/**
	 * For default supported views, performs rendering and returns the result.
	 */
	public function render_view() {
		/**
		 * Fires before the embedded view is rendered.
		 *
		 * @param Tribe__Events__Pro__Shortcodes__Tribe_Events $shortcode
		 */
		do_action( 'tribe_events_pro_tribe_events_shortcode_pre_render', $this );

		ob_start();

		echo '<div class="' . $this->get_wrapper_classes() . '">';
		tribe_get_view( $this->atts['view'] );
		echo '</div>';

		$html = ob_get_clean();

		/**
		 * Fires after the embedded view is rendered.
		 *
		 * @param Tribe__Events__Pro__Shortcodes__Tribe_Events $shortcode
		 */
		do_action( 'tribe_events_pro_tribe_events_shortcode_post_render', $this );

		return $html;
	}

	/**
	 * Returns a set of (already escaped) CSS class names intended for use in the div
	 * wrapping the shortcode output.
	 *
	 * @return string
	 */
	protected function get_wrapper_classes() {
		$classes = array(
			'tribe-events-shortcode',
			'tribe-events-view-wrapper',
			esc_attr( $this->atts[ 'view' ] ),
			$this->is_attribute_truthy( 'redirect', true ) ? 'redirect' : 'no-redirect',
		);

		/**
		 * Sets the CSS classes applied to the [tribe_events] wrapper div.
		 *
		 * @param array $classes
		 * @param Tribe__Events__Pro__Shortcodes__Tribe_Events $shortcode
		 */
		$classes = (array) apply_filters( 'tribe_events_pro_tribe_events_shortcode_wrapper_classes', $classes, $this );

		$classes = implode( ' ', array_filter( $classes ) );
		return esc_attr( $classes );
	}

	/**
	 * Returns the output of this shortcode.
	 *
	 * @return string
	 */
	public function output() {
		return $this->output;
	}
}
