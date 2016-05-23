<?php


class Tribe__Events__Pro__Supports__WPML__Recurring_Event_Creation_Handler implements Tribe__Events__Pro__Supports__WPML__Handler_Interface {

	/**
	 * @var array
	 */
	protected $master_series_ids_and_start_dates_cache = array();

	/**
	 * @var array
	 */
	protected $master_parent_event_ids_cache = array();

	/**
	 * @var Tribe__Events__Pro__Supports__WPML__Event_Listener
	 */
	private $event_listener;

	public function __construct( Tribe__Events__Pro__Supports__WPML__Event_Listener $event_listener ) {
		$this->event_listener = $event_listener;
	}

	/**
	 * @param int      $event_id
	 * @param int|null $parent_event_id
	 *
	 * @return mixed
	 */
	public function handle( $event_id, $parent_event_id = null ) {
		$language_code = $this->get_parent_language_code( $parent_event_id );
		$trid          = $language_code === wpml_get_default_language() ? false : $this->get_master_series_instance_trid( $event_id, $parent_event_id );

		if ( empty( $language_code ) ) {
			return - 1;
		}

		return $this->insert_event_translation_for_language_code( $event_id, $language_code, $trid );
	}

	/**
	 * @return bool
	 */
	private function is_created_from_post_edit_screen() {
		return ! empty( $_POST['icl_post_language'] );
	}

	/**
	 * @return string
	 */
	private function get_language_code_from_globals() {
		return $_POST['icl_post_language'];
	}

	/**
	 * @param int $parent_event_id
	 *
	 * @return bool|string
	 */
	private function get_language_code_from_db( $parent_event_id ) {
		$language_information = wpml_get_language_information( null, $parent_event_id );
		if ( empty( $language_information ) || empty( $language_information['language_code'] ) ) {
			return false;
		}

		return $language_information['language_code'];
	}

	/**
	 * @param $parent_event_id
	 *
	 * @return bool|string
	 */
	private function get_parent_language_code( $parent_event_id ) {
		if ( $this->is_created_from_post_edit_screen() ) {
			$language_code = $this->get_language_code_from_globals();

			return $language_code;
		} else {
			$language_code = $this->get_language_code_from_db( $parent_event_id );

			return $language_code;
		}
	}

	/**
	 * @param $event_id
	 * @param $language_code
	 *
	 * @return array
	 */
	private function insert_event_translation_for_language_code( $event_id, $language_code, $trid ) {
		$default_language = wpml_get_default_language();
		$element_type     = 'post_' . Tribe__Events__Main::POSTTYPE;

		$insertion_result = wpml_add_translatable_content( $element_type, $event_id, $language_code, $trid );

		if ( $insertion_result === WPML_API_CONTENT_EXISTS && $language_code !== $default_language ) {
			global $sitepress;
			if ( ! empty( $sitepress ) ) {
				/** @var Sitepress $sitepress */
				$sitepress->set_element_language_details( $event_id, $element_type, $trid, $language_code, $default_language, false );
			}
		}

		$result = array( $language_code => $insertion_result );

		return $result;
	}

	private function get_master_series_instance_trid( $event_id, $parent_event_id ) {
		$master_parent_event_id = $this->get_master_parent_event_id( $parent_event_id );

		if ( empty( $master_parent_event_id ) ) {
			return false;
		}

		$this_event_start_date = get_post_meta( $event_id, '_EventStartDate', true );

		if ( empty( $this_event_start_date ) ) {
			return false;
		}

		$ids_and_start_dates = $this->get_master_series_ids_and_start_dates( $master_parent_event_id );

		return isset( $ids_and_start_dates[ $this_event_start_date ] ) ? $ids_and_start_dates[ $this_event_start_date ]->trid : false;
	}

	/**
	 * @return bool
	 */
	private function get_master_parent_event_id( $parent_event_id ) {
		if ( empty( $this->master_parent_event_ids_cache[ $parent_event_id ] ) ) {
			$this->master_parent_event_ids_cache[ $parent_event_id ] = isset( $_POST['icl_translation_of'] ) ? $_POST['icl_translation_of'] : $this->get_master_parent_event_id_from_db( $parent_event_id );
		}

		return ! empty( $this->master_parent_event_ids_cache[ $parent_event_id ] ) ? $this->master_parent_event_ids_cache[ $parent_event_id ] : false;
	}

	/**
	 * @param int $master_parent_event_id
	 */
	private function get_master_series_ids_and_start_dates( $master_parent_event_id ) {
		if ( empty( $this->master_series_ids_and_start_dates_cache[ $master_parent_event_id ] ) ) {
			$master_series_recurrence_dates = implode( "','", tribe_get_recurrence_start_dates( $master_parent_event_id ) );
			/** @var \wpdb $wpdb */
			global $wpdb;
			$wpml_translations_table = $wpdb->prefix . 'icl_translations';
			$post_type               = Tribe__Events__Main::POSTTYPE;
			$default_language        = wpml_get_default_language();
			$results                 = $wpdb->get_results( "SELECT p.ID AS 'event_id', pm.meta_value AS 'start_date', wpml.trid as 'trid'
					FROM {$wpdb->posts} p
					LEFT JOIN {$wpdb->postmeta} pm 
					ON p.ID = pm.post_id 
					LEFT JOIN {$wpml_translations_table} wpml 
					ON wpml.element_id = p.ID
					WHERE pm.meta_key = '_EventStartDate' 
					AND pm.meta_value IN ('{$master_series_recurrence_dates}')
					AND wpml.element_type = 'post_{$post_type}'
					AND wpml.trid IS NOT NULL
					AND wpml.language_code = '{$default_language}'
					AND wpml.source_language_code IS NULL
					AND p.post_type = '{$post_type}'" );

			$this->master_series_ids_and_start_dates_cache[ $master_parent_event_id ] = ! empty( $results ) ? array_combine( wp_list_pluck( $results, 'start_date' ),
				$results ) : array();
		}

		return $this->master_series_ids_and_start_dates_cache[ $master_parent_event_id ];
	}

	private function get_master_parent_event_id_from_db( $parent_event_id ) {
		/** @var \wpdb $wpdb */
		global $wpdb;
		$table                  = $wpdb->prefix . 'icl_translations';
		$post_type              = Tribe__Events__Main::POSTTYPE;
		$master_parent_event_id = $wpdb->get_var( "SELECT element_id FROM {$table}
			WHERE trid = (
				SELECT trid FROM {$table} 
				WHERE element_id = {$parent_event_id}
				AND element_type= 'post_{$post_type}' ) 
			AND source_language_code IS NULL" );

		return ! empty( $master_parent_event_id ) ? $master_parent_event_id : false;
	}
}
