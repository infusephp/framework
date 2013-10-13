<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.4
 * @copyright 2013 Jared King
 * @license MIT
 */

namespace infuse\models;

class UserLoginHistory extends \infuse\Model
{
	public static $scaffoldApi = true;

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
				3 => 'OAuth',
				4 => 'Persistent'
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