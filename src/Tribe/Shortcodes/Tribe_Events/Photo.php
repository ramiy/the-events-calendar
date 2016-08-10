<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Events__Pro__Shortcodes__Tribe_Events__Photo {
	protected $shortcode;
	protected $date = '';

	public function __construct( Tribe__Events__Pro__Shortcodes__Tribe_Events $shortcode ) {
		$this->shortcode = $shortcode;
		$this->setup();
		$this->hooks();
	}

	protected function hooks() {
		add_action( 'tribe_events_pro_tribe_events_shortcode_pre_render', function() {
			add_filter( 'tribe_events_force_ugly_link', '__return_true' );
			add_filter( 'tribe_events_ugly_link_baseurl', array( $this, 'filter_baseurl' ) );
			add_filter( 'tribe_events_header_attributes', array( $this, 'header_attributes' ) );
		} );

		add_action( 'tribe_events_pro_tribe_events_shortcode_post_render', function() {
			remove_filter( 'tribe_events_force_ugly_link', '__return_true' );
			remove_filter( 'tribe_events_ugly_link_baseurl', array( $this, 'filter_baseurl' ) );
			remove_filter( 'tribe_events_header_attributes', array( $this, 'header_attributes' ) );
		} );
	}

	/**
	 * Add header attributes for the shortcode month view
	 *
	 * @return string
	 **/
	public function header_attributes( $attrs ) {

		$attrs['data-source']    = 'shortcode-photo';
		$attrs['data-baseurl'] = get_permalink();

		return $attrs;
	}

	protected function setup() {
		Tribe__Events__Main::instance()->displaying = 'photo';
		$this->shortcode->prepare_default();

		Tribe__Events__Pro__Main::instance()->enqueue_pro_scripts();
		Tribe__Events__Pro__Template_Factory::asset_package( 'events-pro-css' );
		Tribe__Events__Pro__Template_Factory::asset_package( 'ajax-photoview' );

		$this->shortcode->set_template_object( new Tribe__Events__Pro__Templates__Photo( $this->shortcode->get_query_args() ) );

	}

	/**
	 * Filters the baseurl of ugly links
	 *
	 * @param string $url URL to filter
	 *
	 * @return string
	 */
	public function filter_baseurl( $url ) {
		return trailingslashit( get_home_url( null, $GLOBALS['wp']->request ) );
	}
}
