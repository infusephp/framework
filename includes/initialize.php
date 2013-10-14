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

namespace infuse;

use \infuse\models\User;

// composer
require 'vendor/autoload.php';

// load configuration
$config = @include 'config.php';
if( !is_array( $config ) )
	die( 'Could not load configuration' );
Config::load( $config );

// setup logging
Logger::setConfig( Config::get( 'logger' ) );

// setup error reporting
ini_set( 'display_errors', !Config::get( 'site', 'production-level' ) );
ini_set( 'log_errors', 1 );
error_reporting( E_ALL | E_STRICT );

// error and exception handlers
set_error_handler( '\infuse\Logger::phpErrorHandler' );
set_exception_handler( '\infuse\Logger::exceptionHandler' );

// show errors on shutdown
register_shutdown_function( function()
{
	if( $error = error_get_last() )
		Logger::phpErrorHandler( $error[ 'type' ], $error[ 'message' ], $error[ 'file' ], $error[ 'line' ], null );
} );

// time zone
if( Config::get( 'site', 'time-zone' ) )
	date_default_timezone_set( Config::get( 'site', 'time-zone' ) );

// load messages for site language
require_once 'assets/lang/' . Config::get( 'site', 'language' ) . '.php';

// setup some useful constants and functions
require_once 'includes/constants.php';

// finally, we can begin parsing the request and generating a response
$req = new Request();
$res = new Response();

// check if site disabled, still allow access to admin panel
if( Config::get( 'site', 'disabled' ) && $req->paths( 0 ) != 'admin' )
{
	$res->setBody( Config::get( 'site', 'disabled-message' ) );
	$res->send();
} 

// run installer if the framework has not been installed yet, cli requests exlcuded
if( !Config::get( 'site', 'installed' ) && !$req->isCli() )
{
	include 'install.php';
	exit;
}

// setup sessions if this request is not an api call
if( !$req->isApi() )
{
	// initialize sessions
	ini_set( 'session.use_trans_sid', false );
	ini_set( 'session.use_only_cookies', true ); 
	ini_set( 'url_rewriter.tags', '' );
	ini_set( 'session.gc_maxlifetime', Config::get( 'session', 'lifetime' ) );

	// set the session name
	$sessionTitle = Config::get( 'site', 'title' ) . '-' . $req->host();
	$safeSessionTitle = str_replace( array ( '.',' ',"'", '"' ), array( '','_','','' ), $sessionTitle );
	session_name( $safeSessionTitle );
	
	// set the session cookie parameters
	session_set_cookie_params(
	    Config::get( 'session', 'lifetime' ), // lifetime
	    '/', // path
	    '.' . $req->host(), // domain
	    $req->isSecure(), // secure
	    true // http only
	);

	// redis sessions
	if( Config::get( 'session', 'adapter' ) == 'redis' )
		RedisSession::start( Config::get( 'redis' ), Config::get( 'session', 'prefix' ) );
	// default: database
	else if( Config::get( 'session', 'adapter' ) == 'database' )
		DatabaseSession::start();
	// default: built-in sessions
	else
		session_start();

	// set the cookie by sending it in a header.
	Util::set_cookie_fix_domain(
		session_name(),
		session_id(),
		time() + Config::get( 'session', 'lifetime' ),
		'/',
		$req->host(),
		$req->isSecure(),
		true
	);
	
	// update the session in our request
	$req->setSession( $_SESSION );
}

Modules::$moduleDirectory = INFUSE_MODULES_DIR;

// autoload modules
spl_autoload_register( 'infuse\\Modules::autoloader' );

// load required modules
Modules::load( Config::get( 'modules', 'required' ) );

// make exception for cli requests
if( $req->isCli() )
{
	if( $argc >= 2 ) {
		$req->setPath( $argv[ 1 ] );
	}
	
	// super user permissions
	User::su();
}

if( $req->isHtml() )
{
	// setup the view engine
	ViewEngine::configure( array(
		'engine' => Config::get( 'views', 'engine' ),
		'viewsDir' => INFUSE_VIEWS_DIR,
		'compileDir' => INFUSE_TEMP_DIR . '/smarty',
		'cacheDir' => INFUSE_TEMP_DIR . '/smarty/cache'
	) );
}

// execute middleware
foreach( Config::get( 'modules', 'middleware' ) as $module )
	Modules::controller( $module )->middleware( $req, $res );

// setup the router
Router::configure( array(
	'use_modules' => true
) );

/*
	Routing Steps:
	1) main config.yml routes
	2) module routes (i.e. /users/:id/friends)
	   i) static routes
	   ii) dynamic routes
	3) module admin routes
	4) view without a controller (i.e. /contact-us displays views/contact-us.tpl)
	5) not found
*/

// try to find a match using various techniques in order
$routed = false;
$routeStep = 1;

while( !$routed )
{
	if( $routeStep == 1 )
	{
		/* main routes */
		$routed = Router::route( Config::get( 'routes' ), $req, $res );
	}
	else if( $routeStep == 2 )
	{
		/* module routes */
	
		// check if the first part of the path is a controller
		$module = $req->paths( 0 );
		
		if( Modules::exists( $module ) )
		{
			Modules::load( $module );
			
			$moduleInfo = Modules::info( $module );

			$moduleRoutes = $moduleInfo[ 'routes' ];
			
			$req->setParams( array( 'controller' => $module ) );
			
			$routed = Router::route( $moduleRoutes, $req, $res );
		}
	}
	else if( $routeStep == 3 )
	{
		/* module admin routes */
		
		if( $req->paths( 0 ) == 'admin' )
		{
			$module = $req->paths( 1 );
						
			if( Modules::exists( $module ) )
			{
				Modules::load( $module );
				
				$moduleInfo = Modules::info( $module );

				$moduleRoutes = $moduleInfo[ 'routes' ];

				$req->setParams( array( 'controller' => $module ) );
				
				ViewEngine::engine()->assignData( array(
					'modulesWithAdmin' => Modules::adminModules(),
					'selectedModule' => $module,
					'title' => $moduleInfo[ 'title' ] ) );
				
				$routed = Router::route( $moduleRoutes, $req, $res );
			}
		}
	}
	else if( $routeStep == 4 )
	{
		/* view without a controller */
		$basePath = $req->basePath();
		
		// make sure the route does not touch any special files
		if( strpos( $basePath, '/emails/' ) !== 0 && !in_array( $basePath, array( '/error', '/parent' ) ) )
		{
			$view = substr_replace( $basePath, '', 0, 1 );
			if( file_exists( INFUSE_VIEWS_DIR . '/' . $view . '.tpl' ) )
				$routed = $res->render( $view );
		}
	}
	else
	{
		/* not found */
		$res->setCode( 404 );
		
		$routed = true;
	}
	
	// move on to the next step
	$routeStep++;
}

// send the response
$res->send( $req );