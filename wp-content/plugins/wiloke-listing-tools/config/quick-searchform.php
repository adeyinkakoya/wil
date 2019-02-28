<?php
return [
	array(
		'key'       => 'toggle_quick_search_form',
		'label'     => 'Toggle Quick Search Form',
		'type'      => 'checkbox2',
		'value'     => 'yes'
	),
	array(
		'key'       => 'taxonomy_suggestion',
		'label'     => 'Suggestions',
		'desc'      => 'Show the Terms in the Listing Location/Listing Category/Listing Tag at the firs time',
		'type'      => 'select',
		'value'     => 'listing_cat',
		'options'   => array(
			'listing_cat'       => 'Listing Category',
			'listing_location'  => 'Listing Location',
			'listing_tag'       => 'Listing Tag'
		)
	),
	array(
		'key'       => 'taxonomy_suggestion_title',
		'label'     => 'Taxonomy Suggestion Title',
		'type'      => 'text',
		'value'     => 'Categories'
	),
	array(
		'key'       => 'number_of_term_suggestions',
		'label'     => 'Maximum Terms will be shown',
		'type'      => 'text',
		'value'     => 6
	),
	array(
		'key'       => 'suggestion_order_by',
		'label'     => 'Suggestion Order By',
		'type'      => 'select',
		'options'   =>  array(
			'count' => 'Count',
			'id'    => 'ID',
			'slug'  => 'Slug',
			'name'  => 'Name',
			'none'  => 'None'
		)
	),
	array(
		'key'       => 'suggestion_order',
		'label'     => 'Suggestion Order',
		'type'      => 'select',
		'options'   =>  array(
			'DESC' => 'DESC',
			'ASC'  => 'ASC'
		)
	)
];