<?php
$prefix = 'wilcity_';

return array(
	'id'               => $prefix . 'edit',
	'title'            => 'Wilcity User Meta',
	'object_types'     => array( 'user' ),
	'show_names'       => true,
	'new_user_section' => 'add-new-user',
	'fields' => array(
		array(
			'name'    => 'Cover Image',
			'id'      => $prefix . 'cover_image',
			'type'    => 'file',
		),
		array(
			'name'    => 'Avatar',
			'id'      => $prefix . 'avatar',
			'type'    => 'file',
		),
		array(
			'name'    => 'Picture',
			'id'      => $prefix . 'picture',
			'type'    => 'file',
		),
		array(
			'name'    => 'Phone',
			'id'      => $prefix . 'phone',
			'type'    => 'text',
		),
		array(
			'name'    => 'Address',
			'id'      => $prefix . 'address',
			'type'    => 'text',
		),
		array(
			'name'    => 'Position',
			'id'      => $prefix . 'position',
			'type'    => 'text',
		),
		array(
			'name'  => 'Social Networks',
			'is'    => 'usermeta',
			'id'    => $prefix.'social_networks',
			'type'  => 'wilcity_social_networks'
		),
		array(
			'name'  => 'Message Assistant',
			'desc'  => 'Send instant replies to anyone who messages you',
			'id'    => $prefix.'instant_message',
			'type'  => 'textarea'
		),
		array(
			'name'  => 'Is Confirmed',
			'id'    => $prefix.'confirmed',
			'type'  => 'select',
			'options' => array(
				true    => 'Yes',
				false   => 'No'
			)
		),
		array(
			'name'  => 'Send an email if I receive a message from admin',
			'id'    => $prefix.'send_email_if_reply_message',
			'type'  => 'select',
			'default' => 'yes',
			'options' => array(
				'yes'  => 'Yes',
				'no'   => 'No'
			)
		),
		array(
			'name'  => 'App Token',
			'id'    => $prefix.'app_token',
			'type'  => 'text'
		)
	)
);