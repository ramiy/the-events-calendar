<?php

/**
 * Class My_Shortcode
 */
class My_Shortcode {

	/**
	 * The shortcode attributes
	 *
	 * @var array
	 */
	protected $_atts = array();

	/**
	 * The shortcode content
	 *
	 * @var string
	 */
	protected $_content = '';

	/**
	 * The default shortcode attributes
	 *
	 * @var array
	 */
	protected static $_default_atts = array();

	/**
	 * The shortcode callback to be registered
	 *
	 * @param array  $atts
	 * @param string $content
	 * @return string
	 */
	public static function callback( $atts = array(), $content = '' ) {
		$shortcode = new self( wp_parse_args( $atts ), $content );
		return $shortcode->process();
	}

	/**
	 * Render the shortcode immediately
	 *
	 * @param array  $atts
	 * @param string $content
	 */
	public static function render( $atts = array(), $content = '' ) {
		echo self::callback( $atts, $content );
	}

	/**
	 * Setup the shortcode's properties
	 *
	 * @param array  $atts
	 * @param string $content
	 */
	public function __construct( array $atts = array(), $content = '' ) {
		$this->initialize();
		$this->_atts = shortcode_atts( self::$_default_atts, $atts );
		$this->_content = $content;
	}

	/**
	 * Function used for setting up dynamically generated default attributes.
	 */
	public function initialize() {

	}

	/**
	 * Process the shortcode and return the output
	 *
	 * @return string
	 */
	public function process() {
		// Do stuff
		return '';
	}

}

// Register the [my_shortcode] shortcode
add_shortcode( 'my_shortcode', array( 'My_Shortcode', 'callback' ) );

// Enable the ability to call the 'my_shortcode' action
add_action( 'my_shortcode', array( 'My_Shortcode', 'render' ), 10, 2 );

// Example usage of the 'my_shortcode' action in a theme
do_action( 'my_shortcode', $atts = array(), $content = '' );