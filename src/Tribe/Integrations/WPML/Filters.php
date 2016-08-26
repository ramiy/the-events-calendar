<?php


class Tribe__Events__Pro__Integrations__WPML__Filters {

	/**
	 * @var Tribe__Events_Pro__Integrations__WPML__Filters
	 */
	protected static $instance;

	/**
	 * @var int
	 */
	protected $recurring_event_parent_id;

	/**
	 * @return Tribe__Events_Pro__Integrations__WPML__Filters
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function filter_wpml_is_redirected_event( $redirect_target, $post_id, $query ) {
		if ( $redirect_target ) {
			if ( 'all' === $query->get( 'eventDisplay' ) || $query->get( 'eventDate' ) ) {
				$redirect_target = false;
			}
		}

		if ( $this->is_single_event_main_query( $query ) ) {
			$this->recurring_event_parent_id = $post_id;
			add_action( 'tribe_events_pro_recurring_event_parent_id', array( $this, 'filter_recurring_event_parent_id' ) );
		}

		return $redirect_target;
	}

	public function filter_wpml_ls_languages_event( $languages ) {
		if ( ! tribe_is_showing_all() ) {
			return $languages;
		}

		foreach ( $languages as $key => $language ) {
			$parts = explode( '/', untrailingslashit( $language['url'] ) );
			array_pop( $parts );
			$parts[]           = 'all';
			$language['url']   = trailingslashit( implode( '/', $parts ) );
			$languages[ $key ] = $language;
		}

		return $languages;
	}

	public function filter_wpml_get_ls_translations_event( $translations, $query ) {
		if ( $this->is_all_event_query( $query ) ) {
			$translations = apply_filters( 'wpml_content_translations', null, $query->get( 'post_parent' ), 'tribe_events' );
		}

		return $translations;
	}

	public function filter_tribe_events_pre_get_posts( $query ) {
		if ( $query->get( 'p' ) && $query->get( 'post_parent' ) ) {
			unset( $query->query_vars['p'] );
		}

		return $query;
	}

	public function filter_wpml_pre_parse_query_event( $query ) {
		if ( $this->is_all_event_query( $query ) ) {
			$query->set( 'tribe_name_before_wpml_parse_query', $query->get( 'name' ) );
		}

		return $query;
	}

	public function filter_wpml_post_parse_query_event( WP_Query $query ) {
		$name_before_wpml_parse_query = $query->get( 'tribe_name_before_wpml_parse_query', '' );
		if ( $this->is_all_event_query( $query ) && ! empty( $name_before_wpml_parse_query ) ) {
			$query->set( 'name', $query->get( 'tribe_name_before_wpml_parse_query' ) );
		}

		return $query;
	}

	private function is_all_event_query( WP_Query $query ) {
		return $query->get( 'post_type' ) == 'tribe_events' && 'all' === $query->get( 'eventDisplay' );
	}

	public function filter_recurring_event_parent_id() {
		return $this->recurring_event_parent_id;
	}

	protected function is_single_event_main_query( WP_Query $query ) {
		return $query->is_main_query() && $query->is_single() && $query->get( 'post_type' ) === Tribe__Events__Main::POSTTYPE;
	}
}