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

namespace infuse;

use \infuse\models\User as User;

// set the root app directory
define( 'INFUSE_BASE_DIR', dirname(__DIR__));
set_include_path( get_include_path() . PATH_SEPARATOR . INFUSE_BASE_DIR );

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

// composer
require 'vendor/autoload.php';

// load configuration
Config::load( INFUSE_BASE_DIR . '/config.yml' );

// error reporting
if( Config::value( 'site', 'production-level' ) )
{
	ini_set("display_errors", 0);
	ini_set("log_errors", 1);
	ini_set("error_log", "syslog");
	error_reporting  (E_ERROR | E_WARNING | E_PARSE);
}
else
{
	ini_set( 'display_errors', 'On' );
	error_reporting(E_ALL);
}

// time zone
if( Config::value( 'site', 'time-zone' ) )
	date_default_timezone_set( Config::value( 'site', 'time-zone' ) );

// load messages for site language
require_once 'lang/' . Config::value( 'site', 'language' ) . '.php';

// setup some useful constants and functions
require_once 'includes/constants.php';

// finally, we can begin parsing the request and generating a response
$req = new Request();
$res = new Response();

// check if site disabled, still allow access to admin panel
if( Config::value( 'site', 'disabled' ) && $req->paths( 0 ) != '4dm1n' )
{
	$res->setBody( Config::value( 'site', 'disabled-message' ) );
	$res->send();
} 

// run installer if the framework has not been installed yet, cli requests exlcuded
if( !Config::value( 'site', 'installed' ) && !$req->isCli() )
{
	include 'install.php';
	exit;
}

// only use sessions if this is not an api call
if( !$req->isApi() )
{
	// initialize sessions
	ini_set('session.use_trans_sid', false);
	ini_set('session.use_only_cookies', true); 
	ini_set('url_rewriter.tags', '');
	ini_set('session.gc_maxlifetime', Config::value( 'session', 'lifetime' ) );

	// set the session name
	$sessionTitle = Config::value( 'site', 'title' ) . '-' . $req->host();
	$safeSessionTitle = str_replace( array ( '.',' ',"'", '"' ), array( '','_','','' ), $sessionTitle );
	session_name( $safeSessionTitle );
	
	// set the session cookie parameters
	session_set_cookie_params( 
	    Config::value( 'session', 'lifetime' ), // lifetime
	    '/', // path
	    $req->host(), // domain
	    $req->isSecure(), // secure
	    true // http only
	);

	// session handler
	if( Config::value( 'session', 'adapter' ) == 'redis' )
	{
		RedisSession::start(array(
			'scheme' => Config::value('redis','scheme'),
			'host'   => Config::value('redis','host'),
	    	'port'   => Config::value('redis','port')
		));
	}
	// default: database
	else
	{
		DatabaseSession::start();
	}
	
	// set the cookie by sending it in a header.
	Util::set_cookie_fix_domain(
		session_name(),
		session_id(),
		time() + Config::value( 'session', 'lifetime' ),
		'/',
		$req->host()
	);
}

// load required modules
Modules::loadRequired();

// handle cli requests
if( $req->isCli() )
{
	if( $argc >= 2 ) {
		$req->setPath( $argv[ 1 ] );
	}
	
	// super user permissions
	User::elevateToSuperUser();
}

// middleware
Modules::middleware( $req, $res );

//route request
Router::route( $req, $res );

// send the response
$res->send( $req );