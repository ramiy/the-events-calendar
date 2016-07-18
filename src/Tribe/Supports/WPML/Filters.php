<?php


class Tribe__Events__Pro__Supports__WPML__Filters {

	/**
	 * @var Tribe__Events_Pro__Supports__WPML__Filters
	 */
	protected static $instance;

	/**
	 * @return Tribe__Events_Pro__Supports__WPML__Filters
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

		return $redirect_target;
	}

	public function filter_wpml_ls_languages_event( $languages ) {
		if ( ! tribe_is_showing_all() ) {
			return $languages;
		}

		foreach ( $languages as $key => $language ) {

			// TODO: This should be filterd properly for all permalink structures.

			// We need to remove the date and replace it with 'all'

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

	public function filter_tribe_events_pre_get_posts( $query ) { if ( $query->get( 'p' ) && $query->get( 'post_parent' ) ) {
			unset( $query->query_vars['p'] );
		}

		return $query;
	}

	public function filter_wpml_pre_parse_query_event( $query ) {
		if ( $this->is_all_event_query( $query ) ) {
			$this->name_before_wpml_parse_query = $query->get( 'name' );
		} else {
			$this->name_before_wpml_parse_query = '';
		}

		return $query;
	}

	public function filter_wpml_post_parse_query_event( $query ) {
		if ( $this->name_before_wpml_parse_query ) {
			$query->set( 'name', $this->name_before_wpml_parse_query );
		}

		return $query;
	}

	private function is_all_event_query( $query ) {
		return $query->get( 'post_type' ) == 'tribe_events' && 'all' === $query->get( 'eventDisplay' );
	}

}