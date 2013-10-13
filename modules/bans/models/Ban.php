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

use \infuse\Database;

define( 'BAN_TYPE_IP', 1 );
define( 'BAN_TYPE_USERNAME', 2 );
define( 'BAN_TYPE_EMAIL', 3 );

class Ban extends \infuse\Model
{
	public static $scaffoldApi = true;

	public static $properties = array(
		'id' => array(
			'type' => 'id'
		),
		'type' => array(
			'type' => 'enum',
			'enum' => array (
				1 => 'IP',
				2 => 'Username',
				3 => 'E-mail Address' ),
			'db_type' => 'tinyint',
			'length' => 1
		),
		'value' => array(
			'type' => 'text',
			'length' => 40
		),	
		'reason' => array(
			'type' => 'text'
		)
	);
	
	
	/**
	 * Checks if a value has been banned.
	 *
	 * @param string $value value
	 * @param int $type type
	 *
	 * @return boolean
	 */
	static function isBanned( $value, $type )
	{
		return Ban::totalRecords( array( 'type' => $type, 'value' => $value ) ) > 0;
	}
}