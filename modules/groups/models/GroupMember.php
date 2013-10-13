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

class GroupMember extends \infuse\Model
{
	public static $scaffoldApi = true;

	public static $idProperty = array(
		'gid',
		'uid'
	);

	public static $properties = array(
		'gid' => array(
			'type' => 'id',
			'mutable' => true,
			'auto_increment' => false
		),
		'uid' => array(
			'type' => 'id',
			'mutable' => true,
			'auto_increment' => false
		)
	);
}