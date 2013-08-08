<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.2
 * @copyright 2013 Jared King
 * @license MIT
	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
	associated documentation files (the "Software"), to deal in the Software without restriction,
	including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
	and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
	subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in all copies or
	substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
	LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
	IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
	SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace infuse\models;

class Module extends \infuse\Model
{
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