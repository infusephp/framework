<?php
/*
 * @package Infuse
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
 
namespace infuse\controllers;

class Groups extends \infuse\Controller
{
	public static $properties = array(
		'title' => 'Groups',
		'description' => 'Admin panel for managing user groups',
		'version' => '1.0',
		'author' => array(
			'name' => 'Jared King',
			'email' => 'j@jaredtking.com',
			'website' => 'http://jaredtking.com'
		),
		'admin' => true,
		'api' => true,
		'models' => array(
			'Group',
			'GroupMember'
		)
	);
		
	function find( $req, $res )
	{
		if( !$req->isHtml() )
			return parent::find( $req, $res );
		
		$group = new \infuse\models\Group( $req->params( 'id' ) );
		
		if( !$group->can( 'view' ) )
			return $res->setCode( 401 );
			
		$res->render( 'view', array(
			'title' => $group->name(),
			'group' => $group ) );
	}
}