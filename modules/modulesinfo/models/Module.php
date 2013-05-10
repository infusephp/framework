<?php
/*
 * @package nFuse
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0
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

namespace nfuse\models;

class Module extends \nfuse\Model
{
	public static $properties = array(
		array(
			'title' => 'Module',
			'name' => 'title',
			'type' => 2
		),
		array(
			'title' => 'Version',
			'name' => 'version',
			'type' => 2
		),
		array(
			'title' => 'Description',
			'name' => 'description',
			'type' => 2
		),
		array(
			'title' => 'Author',
			'name' => 'author',
			'type' => 2,
			'truncate' => false
		),
		array(
			'title' => 'Auto API',
			'name' => 'api',
			'type' => 4
		),
		array(
			'title' => 'Auto Admin',
			'name' => 'admin',
			'type' => 4
		)
	);	

	static function find( $start = 0, $limit = 100, $sort = '', $search = '' )
	{
		$return = array('models'=>array());
		
		$modules = \nfuse\Modules::all();
		foreach( $modules as $info )
			$return['models'][] = new Module( $info[ 'name' ] );
		
		$return['count'] = count( $modules );
		
		return $return;
	}
	
	static function totalRecords()
	{
		return count( \nfuse\Modules::all() );
	}
	
	function toArray( $exclude = array() )
	{
		$info = \nfuse\Modules::info( $this->id );
		
		$author = $info['author'];
		
		unset( $info[ 'author' ] );
		
		$info[ 'author' ] = "<a href='{$author['website']}' target='_blank'>{$author['name']}</a> (<a href='mailto:{$author['email']}'>{$author['email']}</a>)";
		
		return $info;
	}
	
	function can( $permission, $requester = null )
	{
		// no CRUD besides view
		if( $permission == 'view' )
			return parent::can( $permission, $requester );

		return false;
	}
}