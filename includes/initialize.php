<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.3
 * @copyright 2013 Jared King
 * @license MIT
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
	    $req->host(), // domain
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
Modules::load( Config::get( 'site', 'required-modules' ) );

// make exception for cli requests
if( $req->isCli() )
{
	if( $argc >= 2 ) {
		$req->setPath( $argv[ 1 ] );
	}
	
	// super user permissions
	User::elevateToSuperUser();
}

// execute middleware
Modules::middleware( $req, $res );

if( $req->isHtml() )
{
	// setup the view engine
	ViewEngine::configure( array(
		'engine' => Config::get( 'views', 'engine' ),
		'viewsDir' => INFUSE_VIEWS_DIR,
		'compileDir' => INFUSE_TEMP_DIR . '/smarty',
		'cacheDir' => INFUSE_TEMP_DIR . '/smarty/cache'
	) );
	
	$engine = ViewEngine::engine();

    // create temp and output dirs
    if( !file_exists( INFUSE_TEMP_DIR . '/css' ) )
	   	@mkdir( INFUSE_TEMP_DIR . '/css' );
	if( !file_exists( INFUSE_APP_DIR . '/css' ) )
	   	@mkdir( INFUSE_APP_DIR . '/css' );
	if( !file_exists( INFUSE_TEMP_DIR . '/js' ) )
		@mkdir( INFUSE_TEMP_DIR . '/js' );
	if( !file_exists( INFUSE_APP_DIR . '/js' ) )
		@mkdir( INFUSE_APP_DIR . '/js' );
	
	// CSS asset compilation
	$cssFile = INFUSE_BASE_DIR . '/assets/css/styles.less';
	if( file_exists( $cssFile ) )
		$engine->compileLess( $cssFile, INFUSE_TEMP_DIR . '/css/styles.css.cache', INFUSE_APP_DIR . '/css/styles.css' );
	
	// JS asset compilation
	$jsDir = INFUSE_BASE_DIR . '/assets/js';
	if( is_dir( $jsDir ) )
		$engine->compileJs( $jsDir, INFUSE_TEMP_DIR . '/js/header.js.cache', INFUSE_APP_DIR . '/js/header.js' );
}

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
	
	// move on to the next step
	$routeStep++;
}

// send the response
$res->send( $req );