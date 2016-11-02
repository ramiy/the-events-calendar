<?php
class Tribe__Events__Views {
	/**
	 * Container for our registered views.
	 *
	 * @var array
	 */
	protected $registered_views = array();

	/**
	 * Default view properties.
	 *
	 * @var array
	 */
	protected $default_properties = array(
		'autogenerate_rewrite_rules' => true,
		'rewrite_slug' => '',
		'is_single' => false,
	);

	/**
	 * Registers a view.
	 *
	 * @param string        $slug
	 * @param string        $title
	 * @param string|object $implementation
	 * @param array         $properties
	 */
	public function register( $slug, $title, $implementation, array $properties = array() ) {
		$view = array_merge( $properties, $this->default_properties );

		$view['title'] = $title;
		$view['implementation'] = $implementation;

		if ( empty( $view['rewrite_slug'] ) ) {
			$view['rewrite_slug'] = $slug;
		}

		$this->registered_views[ $slug ] = $view;

		/**
		 * Fires immediately after a view has been registered.
		 *
		 * Listening for this event may be useful in cases where you wish to
		 * overwrite a view with a custom implementation.
		 *
		 * @param string $slug
		 * @param array  $properties
		 */
		do_action( 'tribe_events_view_registered', $slug, $properties );
	}

	/**
	 * Returns a list of currently registered views.
	 *
	 * @return array
	 */
	public function get_registered_views() {
		return $this->registered_views;
	}

	/**
	 * Returns the definition (an array of properties) for the specified view, or
	 * boolean false if it has not been registered.
	 *
	 * @param string $slug
	 *
	 * @return bool|array
	 */
	public function get_registered_view( $slug ) {
		return isset( $this->registered_views[ $slug ] ) ? $this->registered_views[ $slug ] : false;
	}

	/**
	 * Indicates if the specified view has been registered.
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function is_registered( $slug ) {
		return isset( $this->registered_views[ $slug ] );
	}

	/**
	 * Indicates if the specified view has been enabled (it must also have been
	 * registered for this to be true).
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function is_enabled( $slug ) {
		return in_array( $slug, tribe_get_option( 'tribeEnableViews' ) );
	}

	/**
	 * Attempts to enable the specified view.
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function enable( $slug ) {
		if ( ! $this->is_registered( $slug ) ) {
			return false;
		}

		if ( $this->is_enabled( $slug ) ) {
			return false;
		}

		$views = (array) tribe_get_option( 'tribeEnableViews' );
		$views[] = $slug;

		return tribe_update_option( 'tribeEnableViews', $views );
	}

	/**
	 * Returns a list of the currently enabled views.
	 *
	 * @return array
	 */
	public function get_enabled_views() {
		$enabled_views = array();

		foreach ( $this->registered_views as $slug => $view ) {
			if ( $this->is_enabled( $slug ) ) {
				$enabled_views[ $slug ] = $view;
			}
		}

		return $enabled_views;
	}

	/**
	 * Returns the default view, providing a fallback if the default is no longer availble.
	 *
	 * This can be useful is for instance a view added by another plugin (such as PRO) is
	 * stored as the default but can no longer be generated due to the plugin being deactivated.
	 *
	 * @return string
	 */
	public function get_default_view() {
		// Compare the stored default view option to the list of available views
		$default         = Tribe__Settings_Manager::instance()->get_option( 'viewOption', 'month' );
		$available_views = (array) apply_filters( 'tribe-events-bar-views', array(), false );

		foreach ( $available_views as $view ) {
			if ( $default === $view['displaying'] ) {
				return $default;
			}
		}

		// If the stored option is no longer available, pick the first available one instead
		$first_view = array_shift( $available_views );
		$view       = $first_view['displaying'];

		// Update the saved option
		Tribe__Settings_Manager::instance()->set_option( 'viewOption', $view );

		return $view;
	}
}