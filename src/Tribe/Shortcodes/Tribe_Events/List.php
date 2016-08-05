<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Events__Pro__Shortcodes__Tribe_Events__List {
	protected $shortcode;
	protected $date = '';

	public function __construct( Tribe__Events__Pro__Shortcodes__Tribe_Events $shortcode ) {
		$this->shortcode = $shortcode;
		$this->setup();
	}

	protected function setup() {
		Tribe__Events__Main::instance()->displaying = 'list';
		$this->shortcode->prepare_default();
		$this->set_current_month();

		Tribe__Events__Pro__Template_Factory::asset_package( 'ajax-listview' );

		$this->shortcode->set_template_object( new Tribe__Events__Template__List( $this->shortcode->get_query_args() ) );

	}

	protected function set_current_month() {
		$default = date_i18n( 'Y-m-d' );
		$this->date = $this->shortcode->get_url_param( 'date' );

		if ( empty( $this->date ) ) {
			$this->date = $this->shortcode->get_attribute( 'date', $default );
		}

		// Expand "yyyy-mm" dates to "yyyy-mm-dd" format
		if ( preg_match( '/^[0-9]{4}-[0-9]{2}$/', $this->date ) ) {
			$this->date .= '-01';
		}
		// If we're not left with a "yyyy-mm-dd" date, override with the today's date
		elseif ( ! preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $this->date ) ) {
			$this->date = $default;
		}

		$this->shortcode->update_query( array(
			'eventDate' => $this->date,
		) );
	}
}