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
}