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

		$template_args = array(
			'eventDisplay' => 'month',
			'eventDate'    => $this->get_attribute( 'date', '' ),
		);

		$this->default_preparation();
		$this->template_object = new Tribe__Events__Template__Month( $template_args );
	}

	/**
	 * Ensures the events-css asset package is setup and hooks up the
	 * default view renderer.
	 */
	protected function default_preparation() {
		Tribe__Events__Template_Factory::asset_package( 'events-css' );
		add_filter( 'tribe_events_pro_tribe_events_shortcode_output', array( $this, 'render_view' ) );
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
		ob_start();

		echo '<div class="' . $this->get_wrapper_classes() . '">';
		tribe_get_view( $this->atts['view'] );
		echo '</div>';
		
		return  ob_get_clean();
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
			esc_attr( $this->atts[ 'view' ] )
		);

		if ( $this->is_attribute_truthy( 'redirect' ) ) {
			$classes[] = 'redirect';
		}

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
