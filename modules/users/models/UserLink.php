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
use \infuse\Util;

class UserLink extends \infuse\Model
{
	public static $idProperty = array( 'uid', 'link' );

	public static $properties = array(
		'uid' => array(
			'type' => 'id',
			'mutable' => true,
			'required' => true,
			'auto_increment' => false,
			'filter' => '<a href="/4dm1n/users/User#/{uid}">{uid}</a>'
		),
		'link' => array(
			'type' => 'id',
			'db_type' => 'varchar',
			'length' => 32,
			'required' => true,
			'mutable' => true,
			'validate' => 'string:32'
		),
		'link_type' => array(
			'type' => 'enum',
			'mutable' => true,
			'enum' => array(
				0 => 'Forgot',
				1 => 'Verify',
				2 => 'Temporary'
			),
			'db_type' => 'tinyint',
			'length' => 2,
			'validate' => 'enum:0,1,2',
			'required' => true
		),
		'link_timestamp' => array(
			'type' => 'date',
			'required' => true,
			'validate' => 'timestamp'
		)
	);
	
	public static $verifyTimeWindow = 86400; // one day
	
	public static $forgotLinkTimeframe = 1800; // 30 minutes	
	
	function can( $permission, $requestor = null )
	{
		if( !$requestor )
			$requestor = User::currentUser();
		
		if( $permission == 'create' )
			return true;
		
		return parent::can( $permission, $requestor );
	}
	
	protected function preCreateHook( &$data )
	{	
		// guid
		if( !isset( $data[ 'link' ] ) )
			$data[ 'link' ] = str_replace( '-', '', Util::guid() );

		// timestamp
		if( !isset( $data[ 'link_timestamp' ] ) )
			$data[ 'link_timestamp' ] = time();
	
		return true;
	}
	
	/**
	 * Clears out expired user links
	 */
	static function garbageCollect()
	{
		return
			// verify links
			Database::delete(
				'UserLinks',
				array(
					'link_timestamp < ' . (time() - self::$verifyTimeWindow),
					'link_type' => 1 ) ) &&
			// forgot password links
			Database::delete(
				'UserLinks',
				array(
					'link_timestamp < ' . (time() - self::$forgotLinkTimeframe),
					'link_type' => 0 ) );
	}
}