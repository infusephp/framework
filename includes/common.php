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

// register autoloader
spl_autoload_register( function( $class ) {
	$classPaths = explode('\\', $class);
	if( $classPaths[ 0 ] == 'nfuse' && count( $classPaths ) == 2 )
	{
		$path = NFUSE_BASE_DIR . '/libs/' . $classPaths[1] . '.php';		
		if( file_exists($path) && is_readable($path) )
			include_once $path;
	}
});

// set the time zone
date_default_timezone_set( \nfuse\Config::value( 'site', 'time-zone' ) );

// check if site disabled, still allow access to admin panel
if( \nfuse\Config::value( 'site', 'disabled' ) && urlParam( 0 ) != '4dm1n' )
	message_die( Config::value( 'site', 'disabled-message' ) );

// load site constants
require_once "includes/constants.php";

$req = new \nfuse\Request();
$res = new \nfuse\Response();

if( $req->isCLI() )
{
	// load required modules
	\nfuse\Modules::loadRequired();
}
else
{
	// TODO this could be middleware
	// check if ip is banned
	if( \nfuse\Database::select(
		'Ban',
		'count(*)',
		array(
			'where' => array(
				'type' => 1,
				'value' => $req->ip() ),
			'single' => true ) ) > 0 )
	{
		$res->setCode(403);
		$res->send();
	}
	
	// TODO this could be middleware
	// if no oauth conditions, use sessions
	if( !oauthCredentialsSupplied() )
	{
		// initialize sessions
		ini_set('session.use_trans_sid', false);
		ini_set('session.use_only_cookies', true); 
		ini_set('url_rewriter.tags', '');
	
		// set the session name
		session_name(str_replace(array ('.',' ',"'", '"'), array('','_','',''), \nfuse\Config::value( 'site', 'title' ).'-'.$req->host()));
		
		// set the session cookie parameters
		session_set_cookie_params( 
		    3600*24, // lifetime = 1 day
		    '/', // path
		    $req->host(), // domain
		    $req->isSecure(), // secure
		    true // http only
		);	
	
		// session handler
		if( \nfuse\Config::value( 'session', 'adapter' ) == 'redis' )
		{
			require_once "libs/RedisSession.php";
			\nfuse\RedisSession::start(array(
    			'scheme' => \nfuse\Config::value('redis','scheme'),
    			'host'   => \nfuse\Config::value('redis','host'),
		    	'port'   => \nfuse\Config::value('redis','port')
			));
		}
		// default: database
		else
		{
			require_once "libs/DatabaseSession.php";
			$session = New DatabaseSession();
			
			session_set_save_handler ( array (&$session, "_open"),
			                           array (&$session, "_close"),
			                           array (&$session, "_read"),
			                           array (&$session, "_write"),
			                           array (&$session, "_destroy"),
			                           array (&$session, "_gc"));
			           
			session_start();
		}
		
		//set the cookie by sending it in a header. 
		set_cookie_fix_domain(session_name(),session_id(),time() + 3600*24,'/',$req->host());
	}
	
	// load required modules
	\nfuse\Modules::loadRequired();	
	
	// middleware
	\nfuse\Modules::middleware( $req, $res );
	
	//route request
	\nfuse\Router::route( $req, $res );
	
	// send the response
	$res->send( $req );
}