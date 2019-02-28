<?php
return [
	'general' => array(
		'toggle_event' => 'enable',
		'toggle' => 'enable',
		'toggle_comment_discussion' => 'enable',
		'immediately_approved' => 'enable'
	),
	'keys' => array(
		'general' => 'event_general_settings'
	),
	'designFields' => array(
		'usedSectionKey' => 'add_event_sections'
	),
	'aFrequencies' => array(
		'occurs_once' => esc_html__('Occurs Once', 'wiloke-listing-tools'),
		'daily' => esc_html__('Daily', 'wiloke-listing-tools'),
		'weekly' => esc_html__('Weekly', 'wiloke-listing-tools'),
	)
];