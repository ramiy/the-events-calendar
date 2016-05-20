<?php


class Tribe__Events__Pro__WPML__Recurring_Event_Creation_Handler implements Tribe__Events__Pro__WPML__Handler_Interface {

	/**
	 * @var Tribe__Events__Pro__WPML__Event_Listener
	 */
	private $event_listener;

	public function __construct( Tribe__Events__Pro__WPML__Event_Listener $event_listener ) {
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

		if ( empty( $language_code ) ) {
			return - 1;
		}

		return $this->insert_event_translation_for_language_code( $event_id, $language_code );
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
	private function insert_event_translation_for_language_code( $event_id, $language_code ) {
		// @todo: set the trid to the trid of other recurrence instances
		return array( $language_code => wpml_add_translatable_content( 'post_' . Tribe__Events__Main::POSTTYPE, $event_id, $language_code ) );
	}
}
