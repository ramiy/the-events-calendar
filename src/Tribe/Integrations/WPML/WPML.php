<?php


/**
 * Class Tribe__Events__Pro__Integrations__WPML__WPML
 *
 * A facade class to wrap and customize access to WPML API through adapters.
 * Any call should be forwarded to specialized adapter classes.
 */
class Tribe__Events__Pro__Integrations__WPML__WPML {

	/**
	 * The key WPML will store the current post language code while saving in the $_POST global.
	 *
	 * @var string
	 */
	public static $post_language_post_global_key = 'icl_post_language';

	/**
	 * @var Tribe__Events__Pro__Integrations__WPML__WPML
	 */
	protected static $instance;
	/**
	 * @var Tribe__Events__Pro__Integrations__WPML__API__Translations
	 */
	private $translations;

	/**
	 * The class singleton constructor.
	 *
	 * @return Tribe__Events__Pro__Integrations__WPML__WPML
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Tribe__Events__Pro__Integrations__WPML__WPML constructor.
	 *
	 * @param Tribe__Events__Pro__Integrations__WPML__API__Translations|null $translations
	 */
	public function __construct( Tribe__Events__Pro__Integrations__WPML__API__Translations $translations = null ) {
		$this->translations = $translations ? $translations : new Tribe__Events__Pro__Integrations__WPML__API__Translations();
	}

	/**
	 * Returns a post parent post language code from the globals or from the database.
	 *
	 * @param int $parent_post_id
	 *
	 * @return bool|string The language code string (e.g. `en`) or `false` on failure.
	 */
	public function get_parent_language_code( $parent_post_id ) {
		return $this->translations->get_parent_language_code( $parent_post_id );
	}

	/**
	 * Returns the `trid` of a recurring event master series recurring event instance.
	 *
	 * @param int $event_id
	 * @param int $parent_event_id
	 *
	 * @return bool|int Either the master series recurring event instance trid (an int) or `false` on failure.
	 */
	public function get_master_series_instance_trid( $event_id, $parent_event_id ) {
		return $this->translations->get_master_series_instance_trid( $event_id, $parent_event_id );
	}

	/**
	 * Inserts an event translation in the WPML tables.
	 *
	 * @param int    $event_id              The event post ID.
	 * @param string $language_code         The WPML language code to insert, e.g. 'en'.
	 * @param  int   $trid                  A translation group identifier.
	 *                                      On a website with 4 languages 4 different posts will share the same `trid` value.
	 * @param bool   $overwrite_if_existing Whether the translation line should owerwrite an existing one or not.
	 *                                      By default the translation entry will not be overwritten.
	 *
	 *
	 * @return array
	 */
	public function insert_event_translation_for_language_code( $event_id, $language_code, $trid, $overwrite_if_existing = false ) {
		return $this->translations->insert_event_translation_for_language_code( $event_id, $language_code, $trid, $overwrite_if_existing );
	}

	public function hook() {
		// the WPML API is not included by default
		require_once ICL_PLUGIN_PATH . '/inc/wpml-api.php';

		$this->hook_actions();
		$this->hook_filters();
	}

	protected function hook_actions() {
		$listener = Tribe__Events__Pro__Integrations__WPML__Event_Listener::instance();

		add_action( 'tribe_events_pro_recurring_event_instance_inserted', array( $listener, 'handle_recurring_event_creation' ), 10, 2 );
	}

	protected function hook_filters() {
		$filters = Tribe__Events__Pro__Integrations__WPML__Filters::instance();

		// Modern Tribe filters
		add_filter( 'tribe_events_pre_get_posts', array( $filters, 'filter_tribe_events_pre_get_posts' ), 10, 1 );

		// WPML filters
		add_filter( 'wpml_is_redirected', array( $filters, 'filter_wpml_is_redirected_event' ), 10, 3 );
		add_filter( 'icl_ls_languages', array( $filters, 'filter_wpml_ls_languages_event' ), 10, 1 );
		add_filter( 'wpml_get_ls_translations', array( $filters, 'filter_wpml_get_ls_translations_event' ), 10, 2 );
		add_filter( 'wpml_pre_parse_query', array( $filters, 'filter_wpml_pre_parse_query_event' ), 10, 1 );
		add_filter( 'wpml_post_parse_query', array( $filters, 'filter_wpml_post_parse_query_event' ), 10, 1 );
	}
}
