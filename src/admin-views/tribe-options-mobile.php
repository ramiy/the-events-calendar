<?php
/**
 * Filter the array of views that are registered for the tribe bar
 * @param array array() {
 *     Array of views, where each view is itself represented by an associative array consisting of these keys:
 *
 *     @type string $displaying         slug for the view
 *     @type string $anchor             display text (i.e. "List" or "Month")
 *     @type string $event_bar_hook     not used
 *     @type string $url                url to the view
 * }
 * @param boolean
 */
$views = apply_filters( 'tribe-events-bar-views', array(), false );

$views_options = array();
foreach ( $views as $view ) {
	$views_options[ $view['displaying'] ] = $view['anchor'];
}

$display_tab_fields = Tribe__Main::array_insert_before_key(
	'tribeEventsAdvancedSettingsTitle',
	$display_tab_fields,
	array(
		'_block_mobile_title'   => array(
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Basic Mobile Settings', 'tribe-events-calendar-pro' ) . '</h3>',
		),
		'mobile_default_view'                => array(
			'type'            => 'dropdown_select2',
			'label'           => esc_html__( 'Default view', 'tribe-events-calendar-pro' ),
			'validation_type' => 'options',
			'size'            => 'large',
			'default'         => 'month',
			'options'         => $views_options,
		),
	)
);

add_filter( 'tribe_settings_tab_fields', array( $this, 'inject_settings' ), 10, 2 );
