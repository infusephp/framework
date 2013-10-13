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

class Permission extends \infuse\Model
{
	public static $scaffoldApi = true;

	public static $properties = array(
		'id' => array(
			'type' => 'id'
		),	
		'model' => array(
			'type' => 'text'
		),
		'model_id' => array(
			'type' => 'id',
			'null' => true
		),
		'uid' => array(
			'type' => 'id',
			'null' => true
		),	
		'gid' => array(
			'type' => 'id',
			'null' => true
		),	
		'permission' => array(
			'type' => 'text'
		)
	);
}