<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

namespace app\infuse\models;

use infuse\Util;

use app\users\models\User;
use app\infuse\libs\Fusion;

class Module extends \infuse\Model
{
	public static $scaffoldApi = true;

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
		'admin' => array(
			'type' => 'boolean'
		)
	);
	
	function can( $permission, $requester = null )
	{
		if( !$requester )
			$requester = User::currentUser();
		
		// can only view
		if( $requester->isAdmin() && in_array( $permission, array( 'view' ) ) )
			return true;
		
		return false;
	}

	static function find( $start = 0, $limit = 100, $sort = '', $search = '', $where = array() )
	{
		// unpack parameters
		if( is_array( $start ) )
		{
			$where = (array)Util::array_value( $start, 'where' );
			$search = Util::array_value( $start, 'search' );
			$sort = Util::array_value( $start, 'sort' );
			$limit = Util::array_value( $start, 'limit' );
			$start = Util::array_value( $start, 'start' ); // must be last
		}
	
		if( empty( $start ) || !is_numeric( $start ) || $start < 0 )
			$start = 0;
		if( empty( $limit ) || !is_numeric( $limit ) || $limit > 1000 )
			$limit = 100;

		$return = array( 'models'=>array() );
		
		$modules = Fusion::allModules();
		
		foreach( $modules as $module )
			$return[ 'models' ][] = new Module( $module );

		$return[ 'models' ] = array_slice( $return[ 'models' ], $start, $limit );
		
		$return[ 'count' ] = count( $modules );
		
		return $return;
	}
	
	static function totalRecords( $where = array() )
	{
		return count( Fusion::allModules() );
	}
	
	function toArray( $exclude = array() )
	{
		$controller = '\\app\\' . $this->id . '\\Controller';
		$info = $controller::$properties;
		
		$author = $info[ 'author' ];
				
		$info[ 'author' ] = "<a href='{$author['website']}' target='_blank'>{$author['name']}</a> (<a href='mailto:{$author['email']}'>{$author['email']}</a>)";
		$info[ 'admin' ] = Util::array_value( $info, 'scaffoldAdmin' );
		
		return $info;
	}
}