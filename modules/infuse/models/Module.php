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

class Module extends \infuse\Model
{
	protected static $hasSchema = false;
	public static $properties = array(
		'title' => array(
			'type' => 'text'
		),
		'version' => array(
			'type' => 'text'
		),
		'description' => array(
			'type' => 'text',
			'truncate' => false
		),
		'author' => array(
			'type' => 'html',
			'truncate' => false
		),
		'api' => array(
			'type' => 'boolean'
		),
		'admin' => array(
			'type' => 'boolean'
		)
	);
	
	function can( $permission, $requester = null )
	{
		if( !$requester )
			$requester = \infuse\models\User::currentUser();
		
		// can only view
		if( $requester->isAdmin() && in_array( $permission, array( 'view' ) ) )
			return true;
		
		return false;
	}

	static function find( $start = 0, $limit = 100, $sort = '', $search = '', $where = array() )
	{
		$return = array('models'=>array());
		
		$modules = \infuse\Modules::all();
		
		foreach( $modules as $module )
			$return['models'][] = new Module( $module );
		
		$return['count'] = count( $modules );
		
		return $return;
	}
	
	static function totalRecords( $where = array() )
	{
		return count( \infuse\Modules::all() );
	}
	
	function toArray( $exclude = array() )
	{
		$info = \infuse\Modules::info( $this->id );
		
		$author = $info['author'];
		
		unset( $info[ 'author' ] );
		
		$info[ 'author' ] = "<a href='{$author['website']}' target='_blank'>{$author['name']}</a> (<a href='mailto:{$author['email']}'>{$author['email']}</a>)";
		
		return $info;
	}
}