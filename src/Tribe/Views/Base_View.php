<?php

abstract class Tribe__Events__Views__Base_View {
	protected $slug = '';
	protected $properties = array();
	protected $data = array();
	protected $output = '';

	/**
	 * Create a new view object.
	 *
	 * @param string     $slug
	 * @param array      $properties
	 * @param array|null $data
	 */
	public function __construct( $slug, array $properties, array $data = null ) {
		$this->slug = $slug;
		$this->properties = $properties;

		if ( null !== $this->data ) {
			$this->set_data( $data );
		}
	}

	/**
	 * Sets the data available to the view.
	 *
	 * If $data contains a 'query' key, then that element ought to contain a WP_Query
	 * object (and that will be used to populate the view).
	 *
	 * If $data contains a 'posts' key, then that element ought to contain an array of
	 * WP_Post objects (and those will be used to populate the view).
	 *
	 * @param array $data
	 */
	public function set_data( array $data = array() ) {
		$this->data = $data;
		$this->set_query();
		$this->set_posts();
	}

	/**
	 * Sets the WP_Query object to be used when rendering the view.
	 *
	 * If this is not available or hasn't been set it will fall back on the global
	 * $wp_query object (and if this is not available, returns a new instance of
	 * WP_Query).
	 *
	 * @param WP_Query $query
	 */
	public function set_query( WP_Query $query = null ) {
		global $wp_query;

		// Use $query if provided
		if ( null !== $query ) {
			$this->data['query'] = $query;
		}

		// If the query has already been set then reset the posts array and bail
		if ( ! empty( $this->data['query'] ) && is_a( $this->data['query'], 'WP_Query' ) ) {
			$this->reset_posts();
			return;
		}

		// Fallback: if $query wasn't provided and the global $wp_query isn't set, generate a blank WP_Query object
		if ( ! is_a( $wp_query, 'WP_Query' ) ) {
			$this->data['query'] = new WP_Query;
		}

		// Set the query and reset the posts array
		$this->data['query'] = $wp_query;
		$this->reset_posts();
	}

	/**
	 * Resets the posts array to whatever is contained in the query object.
	 */
	protected function reset_posts() {
		unset( $this->data['posts'] );
		$this->set_posts();
	}

	/**
	 * Sets the array of posts to be displayed.
	 *
	 * If a set hasn't explicitly been passed to the view via set_data() then it will
	 * utilize the current query object.
	 *
	 * @param WP_Post[] $posts
	 */
	public function set_posts( array $posts = null ) {
		// If for any reason the query object has not yet been set, do that now
		if ( empty( $this->data['query'] ) ) {
			$this->set_query();
		}

		// If $posts was provided, then use that array
		if ( null !== $posts ) {
			$this->data['posts'] = $posts;
			$this->update_query_post_data();
		}

		// Otherwise, if we still don't have an array of posts, extract them from the query object
		if ( ! isset( $this->data['posts'] ) || ! is_array( $this->data['posts'] ) ) {
			$this->data['posts'] = (array) $this->data['query']->get_posts();
		}
	}

	/**
	 * When required, can be used to update the query object's post array and post count.
	 */
	protected function update_query_post_data() {
		$this->data['query']->posts = $this->data['posts'];
		$this->data['query']->post_count = count( $this->data['posts'] );
		$this->data['query']->rewind_posts();
	}

	/**
	 * Returns the view output.
	 *
	 * @return string
	 */
	public function output() {
		if ( empty( $this->output ) ) {
			$this->generate();
		}

		return $this->output;
	}

	/**
	 * Generates the view output.
	 */
	abstract public function generate();
}