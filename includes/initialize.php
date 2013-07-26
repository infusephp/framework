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

use \infuse\models\User;

// set the root app directory
define( 'INFUSE_BASE_DIR', dirname(__DIR__));
set_include_path( get_include_path() . PATH_SEPARATOR . INFUSE_BASE_DIR );

// composer
require 'vendor/autoload.php';

// load configuration
$config = @include 'config.php';
if( !is_array( $config ) )
	die( 'Could not load configuration' );
Config::load( $config );

// setup logging
Logger::setConfig( Config::get( 'logging' ) );

// error handling
function handleError( $errno, $errstr, $errfile, $errline, $errcontext )
{
	$formattedErrorString = Logger::formatPhpError( $errno, $errstr, $errfile, $errline, $errcontext );
	
	if( !Config::get( 'site', 'production-level' ) )
		echo "<pre>$formattedErrorString</pre>";

	switch( $errno )
	{
	case E_CORE_ERROR:
	case E_COMPILE_ERROR:
	case E_ERROR:
	case E_PARSE:
		Logger::error( $formattedErrorString );
		die();
	break;
	case E_USER_ERROR:
	case E_RECOVERABLE_ERROR:
		Logger::error( $formattedErrorString );
	break;
	case E_WARNING:
	case E_CORE_WARNING:
	case E_COMPILE_WARNING:
	case E_USER_WARNING:
	case E_NOTICE:
	case E_USER_NOTICE:
	case E_DEPRECATED:
	case E_USER_DEPRECATED:
	case E_STRICT:
		Logger::warning( $formattedErrorString );
	break;
	}
	
	return true;
}

set_error_handler( '\infuse\handleError' );

// exception handling
set_exception_handler( function( $exception )
{
	$formattedExceptionString = Logger::formatException( $exception );
		
	if( !Config::get( 'site', 'production-level' ) )
		echo $formattedExceptionString;

	Logger::error( $formattedExceptionString );
	
	die();
} );

// show errors on shutdown
register_shutdown_function( function()
{
	if( $error = error_get_last() )
	{
		handleError( $error[ 'type' ], $error[ 'message' ], $error[ 'file' ], $error[ 'line' ], null );
    }
} );

ini_set( 'display_errors', !Config::get( 'site', 'production-level' ) );
ini_set( 'log_errors', 1 );
error_reporting( E_ALL | E_STRICT );

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
if( Config::get( 'site', 'disabled' ) && $req->paths( 0 ) != '4dm1n' )
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

// only use sessions if this is not an api call
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
	    $req->host(), // domain
	    $req->isSecure(), // secure
	    true // http only
	);

	// redis sessions
	if( Config::get( 'session', 'adapter' ) == 'redis' )
		RedisSession::start( Config::get( 'redis' ) );
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
		$req->host()
	);
	
	// update the session in our request
	$req->setSession( $_SESSION );
}

// enable modules autoloader
spl_autoload_register( 'infuse\\Modules::autoloader' );

// load required modules
Modules::load( Config::get( 'site', 'required-modules' ) );

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

if( $req->isHtml() )
{
	$engine = ViewEngine::engine();
	
	// CSS asset compilation
	$cssFile = INFUSE_BASE_DIR . '/assets/css/styles.less';
	if( file_exists( $cssFile ) )
		$engine->compileLess( $cssFile, 'styles.css');
	
	// JS asset compilation
	$jsFile = INFUSE_BASE_DIR . '/assets/js';
	if( file_exists( $jsFile ) )
		$engine->compileJs( $jsFile, 'header.js' );
}

/*
	Routing Steps:
	1) main config.yml routes
	2) module routes (i.e. /users/:id/friends)
	   i) static routes
	   ii) dynamic routes
	   iii) api scaffolding
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

			/* API scaffolding routes */
						
			if( $moduleInfo[ 'api' ] )
			{
				$models = Modules::controller( $module )->models();
				
				$defaultModel = false;
						
				if( isset( $moduleInfo[ 'default-model' ] ) )
					$defaultModel = $moduleInfo[ 'default-model' ];
				
				if( count( $models ) == 1 )
				{
					$modelKeys = array_keys( $models );
					$defaultModel = $modelKeys[ 0 ];
				}
					
				// this comes from /:module/:model
				$secondPath = Util::array_value( $req->paths(), 1 );
				$possibleModel = Inflector::singularize( Inflector::camelize( $secondPath ) );
				
				// default model?
				if( $defaultModel && !isset( $models[ $possibleModel ] ) )
				{
					$req->setParams( array( 'model' => $defaultModel ) );
					
					$moduleRoutes = array_merge( $moduleRoutes, array(
						'get /:controller' => 'findAll',
						'get /:controller/:id' => 'find',
						'post /:controller' => 'create',
						'put /:controller/:id' => 'edit',
						'delete /:controller/:id' => 'delete'
					) );
				}
				// no default model
				else
				{
					$req->setParams( array( 'model' => $secondPath ) );
					
					$moduleRoutes = array_merge( $moduleRoutes, array(
						'get /:controller/:model' => 'findAll',
						'get /:controller/:model/:id' => 'find',
						'post /:controller/:model' => 'create',
						'put /:controller/:model/:id' => 'edit',
						'delete /:controller/:model/:id' => 'delete'
					) );
				}
			}
			
			$routed = Router::route( $moduleRoutes, $req, $res );
		}
	}
	else if( $routeStep == 3 )
	{
		/* admin panel routes */	
			
		if( $req->paths( 0 ) == '4dm1n' )
		{
			$module = $req->paths( 1 );
			
			/* Redirect /4dm1n -> /4dm1n/:default */
			
			if( empty( $module ) && $default = Config::get( 'site', 'default-admin-module' ) )
				return $res->redirect( '/4dm1n/' . $default );
			
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
				
				/* automatic admin routes */
				
				if( !$routed && $req->method() == 'GET' && ( Util::array_value( $moduleInfo, 'admin' ) ) )
				{					
					Modules::controller( $module )->routeAdmin( $req, $res );
					
					$routed = true;
				}
			}
		}
	}
	else if( $routeStep == 4 )
	{
		/* view without a controller */
		$basePath = $req->basePath();
		
		// make sure the route does not peek into admin directory or touch special files
		if( strpos( $basePath, '/admin/' ) !== 0 && strpos( $basePath, '/emails/' ) !== 0 && !in_array( $basePath, array( '/error', '/parent' ) ) )
		{
			$view = INFUSE_VIEWS_DIR . $basePath . '.tpl';
			if( file_exists( $view ) )
				$routed = $res->render( $view );
		}
	}
	else
	{
		/* not found */
		
		$res->setCode( 404 );
		
		$routed = true;
	}
	
	// move onto the next step
	$routeStep++;
}

// send the response
$res->send( $req );