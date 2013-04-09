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

$_REQUEST = $_GET + $_POST;

// set the time zone
date_default_timezone_set( Config::value( 'site', 'time-zone' ) );

// Remove slash in front of requested url
$_SERVER['REQUEST_URI'] = substr_replace (val( $_SERVER, 'REQUEST_URI' ), "", 0, 1);

// check if site disabled, still allow access to admin panel
if( Config::value( 'site', 'disabled' ) && urlParam( 0 ) != '4dm1n' )
	message_die( Config::value( 'site', 'disabled-message' ) );

// load site constants
require_once "includes/constants.php";

if( isCLI() )
{
	// initialize the page
	Globals::$calledPage = new Page();
	
	// initialize smarty
	Globals::$smarty = New Smarty_nFuse();
	
	// load required modules
	Modules::loadRequired();
}
else
{
	// check if ip is banned
	if( Database::select(
		'Ban',
		'count(*)',
		array(
			'where' => array(
				'type' => 1,
				'value' => $_SERVER[ 'REMOTE_ADDR' ] ),
			'single' => true ) ) > 0 )
		message_die ("You IP address has been banned.");
					
	// if no oauth conditions, use sessions
	if( !oauthCredentialsSupplied() )
	{
		if( isset( $_POST[ 'sid' ] ) && isset( $_POST[ 'user_agent' ] ) )
		{
			$_SERVER[ 'HTTP_USER_AGENT' ] = $_POST[ 'user_agent' ];
			session_id($_POST['sid']);
		}

		// initialize sessions
		ini_set('session.use_trans_sid', false);
		ini_set('session.use_only_cookies', true); 
		ini_set('url_rewriter.tags', '');
	
		require_once "libs/Session.php";
		$session = New Session();
		
		session_set_save_handler ( array (&$session, "_open"),
		                           array (&$session, "_close"),
		                           array (&$session, "_read"),
		                           array (&$session, "_write"),
		                           array (&$session, "_destroy"),
		                           array (&$session, "_gc"));
								   
		// set the session name
		session_name(str_replace(array ('.',' ',"'", '"'), array('','_','',''), Config::value( 'site', 'title' ).'-'.$_SERVER[ 'HTTP_HOST' ]));
		
		// set the session cookie parameters
		session_set_cookie_params( 
		    3600*24, // lifetime = 1 day
		    '/', // path
		    $_SERVER[ 'HTTP_HOST' ], // domain
		    urlPrefix() == 'https://', // secure
		    true // http only
		);
		
		// start the session
		session_start();
		
		//set the cookie by sending it in a header. 
		set_cookie_fix_domain(session_name(),session_id(),time() + 3600*24,'/',$_SERVER[ 'HTTP_HOST' ]);
	}

	// initialize the page
	Globals::$calledPage = new Page();
	
	// initialize smarty
	Globals::$smarty = New Smarty_nFuse();
	
	Modules::loadRequired();
	
	$response = null;
	
	// requested module
	$module = urlParam( 0 );

	// accept
	$accept = getAcceptType();
	
	// default module
	$defaultModule = Config::value( 'site', 'default-module' );
	
	if( ( !$module || !Modules::exists( $module ) ) && !defined( 'DO_NOT_SHOW_DEFAULT_MODULE' ) && Modules::exists( $defaultModule ) )
		$module = $defaultModule;

	// try to call the module
	if( Modules::exists( $module ) )
	{			
		// request method
		$requestMethod = $_SERVER[ 'REQUEST_METHOD' ];
		
		// sometimes the DELETE and PUT request method is set by forms via POST
		if( $requestMethod == 'POST' && isset( $_POST['method'] ) && ( $_POST['method'] == 'PUT' || $_POST['method'] == 'DELETE' ) )
			$requestMethod = $_POST[ 'method' ];
		
		$response = '';
		$url = $_SERVER[ 'REQUEST_URI' ];
		
		switch( $requestMethod )
		{
		case 'GET':
			$response = Modules::controller( $module )->get( $url, $_GET, $accept );
		break;
		case 'POST':
			$params = array();
			parse_str( file_get_contents( 'php://input' ), $params );
			$response = Modules::controller( $module )->post( $url, $params, $accept );
		break;
		case 'PUT':
			$params = array();
			
			if( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' )
				$params = $_POST;
			else
				parse_str( file_get_contents( 'php://input' ), $params );

			$response = Modules::controller( $module )->put( $url, $params, $accept );
		break;
		case 'DELETE':
			$response = Modules::controller( $module )->delete( $url, $accept );
		break;
		}

		// output the response
		switch( $accept )
		{
		case 'xml':
			sendResponse( 200, $response, 'text/xml' );
		break;
		case 'json':
			sendResponse( 200, json_encode( $response ), 'application/json' );
		break;
		default:
		case 'html':
			sendResponse( 200, $response, 'text/html' );
		break;
		}	
	}
	else if( !defined( 'DO_NOT_SHOW_404' ) )
	{
		// 404
		switch( $accept )
		{
		case 'xml':
			sendResponse( 404, '', 'text/xml' );
		break;
		case 'json':
			sendResponse( 404, '', 'application/json' );
		break;
		default:
		case 'html':
			sendResponse( 404, '', 'text/html' );
		break;
		}	
	}
}