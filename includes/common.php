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

use \infuse\Config as Config;

// register autoloader
spl_autoload_register( function( $class ) {
	$classPaths = explode('\\', $class);
	if( $classPaths[ 0 ] == 'infuse' && count( $classPaths ) == 2 )
	{
		$path = INFUSE_BASE_DIR . '/libs/' . $classPaths[1] . '.php';
		if( file_exists($path) && is_readable($path) )
			include_once $path;
	}
});

// set the time zone
if( Config::value( 'site', 'time-zone' ) )
	date_default_timezone_set( Config::value( 'site', 'time-zone' ) );

// check if site disabled, still allow access to admin panel
if( Config::value( 'site', 'disabled' ) && !strpos( '4dm1n', $_SERVER[ 'REQUEST_URI' ] ) )
	message_die( Config::value( 'site', 'disabled-message' ) );

// load site constants
require_once "includes/constants.php";

$req = new \infuse\Request();
$res = new \infuse\Response();

if( $req->isCli() )
{
	// load required modules
	\infuse\Modules::loadRequired();
	
	if( $argc >= 2 ) {
		$req->setPath( $argv[ 1 ] );
	}
	
	// super user permissions
	\infuse\models\User::elevateToSuperUser();

	//route request
	\infuse\Router::route( $req, $res );
		
	echo $res->getBody();
}
else if( !Config::value( 'site', 'installed' ) )
{
	include 'install.php';
}
else
{
	// TODO this could be middleware
	// check if ip is banned
	if( \infuse\Database::select(
		'Bans',
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
	
	// TODO oauth call out of place
	// if no oauth conditions, use sessions
	if( !oauthCredentialsSupplied() )
	{
		// initialize sessions
		ini_set('session.use_trans_sid', false);
		ini_set('session.use_only_cookies', true); 
		ini_set('url_rewriter.tags', '');
		ini_set('session.gc_maxlifetime', Config::value( 'session', 'lifetime' ) );
	
		// set the session name
		session_name(str_replace(array ('.',' ',"'", '"'), array('','_','',''), Config::value( 'site', 'title' ).'-'.$req->host()));
		
		// set the session cookie parameters
		session_set_cookie_params( 
		    Config::value( 'session', 'lifetime' ), // lifetime = 1 day
		    '/', // path
		    $req->host(), // domain
		    $req->isSecure(), // secure
		    true // http only
		);
	
		// session handler
		if( Config::value( 'session', 'adapter' ) == 'redis' )
		{
			\infuse\RedisSession::start(array(
    			'scheme' => Config::value('redis','scheme'),
    			'host'   => Config::value('redis','host'),
		    	'port'   => Config::value('redis','port')
			));
		}
		// default: database
		else
		{
			$session = New \infuse\DatabaseSession();
			
			session_set_save_handler ( array (&$session, "_open"),
			                           array (&$session, "_close"),
			                           array (&$session, "_read"),
			                           array (&$session, "_write"),
			                           array (&$session, "_destroy"),
			                           array (&$session, "_gc"));
			           
			session_start();
		}
		
		// set the cookie by sending it in a header.
		set_cookie_fix_domain(session_name(),session_id(),time() + Config::value( 'session', 'lifetime' ),'/',$req->host());
	}
	
	// load required modules
	\infuse\Modules::loadRequired();	
	
	// middleware
	\infuse\Modules::middleware( $req, $res );
	
	//route request
	\infuse\Router::route( $req, $res );
	
	// send the response
	$res->send( $req );
}