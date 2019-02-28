<?php
return [
	'tokenSessionIDRelationship'    => 'paypal_token_id_relationship',
	'customPostTypesKey'            => 'custom_posttypes',
	'storeIsTrial'                  => 'paypal_is_trial',
	'productIDPaymentID'            => 'product_id_payment_id',
	'orderIDPaymentID'              => 'order_id_payment_id',
	'paypalAgreementID'             => 'paypal_agreement_id',
	'nextBillingDateGMT'            => 'next_billing_date_gmt',
	'planStartedAtGMT'              => 'plan_started_at_gmt',
	'isUsingTrial'                  => 'is_using_trial',
	'sessionStore'                  => 'session_store',
	'customMetaBoxPrefix'           => 'wilcity_custom_',
	'usedSectionKey'                => 'add_listing_sections',
	'isAddingListingSession' => 'wilcity_is_adding_listing_session',
	'aPriceRange'   => array(
		array(
			'name'  => esc_html__('Not to say', 'wiloke-listing-tools'),
			'value' => 'nottosay'
		),
		array(
			'name'  => esc_html__('Cheap', 'wiloke-listing-tools'),
			'value' => 'cheap'
		),
		array(
			'name'  => esc_html__('Moderate', 'wiloke-listing-tools'),
			'value' => 'moderate'
		),
		array(
			'name'  => esc_html__('Expensive', 'wiloke-listing-tools'),
			'value' => 'expensive'
		),
		array(
			'name'  => esc_html__('Ultra high', 'wiloke-listing-tools'),
			'value' => 'ultra_high'
		)
	),
	'aTimeRange' => array(
		'from'  => esc_html__('From', 'wiloke-listing-tools'),
		'to'    => esc_html__('To', 'wiloke-listing-tools')
	),
	'aFormBusinessHour' => array(
		array(
			'value' => '00::00:00',
			'name'  => '00:00 AM'
		),
		array(
			'value' => '00::15:00',
			'name'  => '00:15 AM'
		),
		array(
			'value' => '00::30:00',
			'name'  => '00:30 AM'
		),
		array(
			'value' => '00::45:00',
			'name'  => '00:45 AM'
		)
	)
];