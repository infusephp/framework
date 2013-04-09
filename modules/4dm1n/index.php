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
 
class nFuse_Controller_4dm1n extends Controller
{
	public static $title = '4dm1n';
	public static $description = 'Generates an administration panel for each module.';
	public static $version = '1.1';
	public static $author = array(
		'name' => 'Jared King',
		'email' => 'j@jaredtking.com',
		'web_site' => 'http://jaredtking.com'
	);

	function get( $url, $params, $accept )
	{
		return $this->route( 'GET', $url, $params, $accept );
	}

	function post( $url, $params, $accept )
	{
		return $this->route( 'POST', $url, $params, $accept );		
	}
	
	function put( $url, $params, $accept )
	{
		return $this->route( 'PUT', $url, $params, $accept );		
	}
	
	function delete( $url, $params )
	{
		return $this->route( 'DELETE', $url, $params, $accept );
	}
	
	function route( $method, $url, $params, $accept )
	{
		$module = urlParam( 1, $url );
		
		if( empty( $module ) || !Modules::exists( $module ) )
		{
			$defaultModule = Config::value( 'site', 'default-admin-module' );
			if( !empty( $defaultModule ) && Modules::exists( $defaultModule ) )
				redirect( '/4dm1n/' . $defaultModule );
			else
				return 'No default module setup';
		}
		
		// check that the user has authenticated at least once
		// TODO
				
		// load requested module
		Modules::load( $module );
		
		// get the controller for the requested module
		$controller = Modules::controller( $module );
		
		// check that the user has permission to view the admin section
		if( !$controller->can( 'view-admin' ) )
			sendResponse( 401 );
		
		// info about the module
		$info = Modules::info( $module );
				
		Globals::$calledPage->title( $info[ 'title' ] );
		
		return $controller->routeAdmin( $method, str_replace( '4dm1n/', '', $url ), $params, $accept );
	}
}