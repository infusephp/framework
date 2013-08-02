<?php

namespace infuse\models;

class UserLoginHistory extends \infuse\Model
{
	public static $properties = array(
		'id' => array(
			'type' => 'id'
		),
		'uid' => array(
			'type' => 'uid'
		),
		'timestamp' => array(
			'type' => 'date'
		),
		'type' => array(
			'type' => 'enum',
			'enum' => array(
				0 => 'Regular',
				1 => 'Facebook',
				2 => 'Twitter',
				3 => 'OAuth'
			),
			'db_type' => 'tinyint',
			'length' => 1
		),
		'ip' => array(
			'type' => 'text',
			'length' => 15
		)
	);
}