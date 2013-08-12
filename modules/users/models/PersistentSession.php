<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.3
 * @copyright 2013 Jared King
 * @license MIT
 */
 
namespace infuse\models;

use \infuse\Database;

class PersistentSession extends \infuse\Model
{
	public static $idProperty = 'token';

	public static $properties = array(
		'token' => array(
			'type' => 'id',
			'mutable' => true,
			'required' => true,
			'auto_increment' => false,
			'db_type' => 'char',
			'length' => 128
		),
		'user_email' => array(
			'type' => 'text',
			'validate' => 'email'
		),
		'series' => array(
			'type' => 'text',
			'db_type' => 'char',
			'length' => 128,
			'required' => true,
			'validate' => 'string:128'
		),		
		'created' => array(
			'type' => 'date',
			'required' => true,
			'validate' => 'timestamp',
			'default' => 'today'
		)
	);
	
	public static $sessionLength = 7776000; // 3 months
	
	/**
	 * Clears out expired user links
	 */
	static function garbageCollect()
	{
		return Database::delete(
			'PersistentSessions',
			array(
				'created < ' . (time() - self::$sessionLength) ) );
	}
}