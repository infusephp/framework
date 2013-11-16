<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

namespace infuse;

/* Composer */

require 'vendor/autoload.php';

/* Configuration - load */

$config = @include 'config.php';
if( !is_array( $config ) )
	die( 'Could not load configuration' );
Config::load( $config );

/* Logging - setup */

Logger::configure( array(
	'handlers' => Config::get( 'logger' ),
	'productionLevel' => Config::get( 'site.production-level' ) ) );

/* Error Reporting */

ini_set( 'display_errors', !Config::get( 'site.production-level' ) );
ini_set( 'log_errors', 1 );
error_reporting( E_ALL | E_STRICT );

set_error_handler( '\infuse\Logger::phpErrorHandler' );
set_exception_handler( '\infuse\Logger::exceptionHandler' );

// show errors when script is halted (when possible)
register_shutdown_function( function()
{
	if( $error = error_get_last() )
		Logger::phpErrorHandler( $error[ 'type' ], $error[ 'message' ], $error[ 'file' ], $error[ 'line' ], null );
} );

/* Time Zone - setup */

if( $tz = Config::get( 'site.time-zone' ) )
	date_default_timezone_set( $tz );

/* Constants - load */

require_once 'includes/constants.php';

/* Locale - setup */

$locale = Locale::locale();
$locale->setLocaleDataDir( INFUSE_ASSETS_DIR . '/locales' );
$locale->setLocale( Config::get( 'site.language' ) );

/* Validator - setup */

Validate::configure( array( 'salt' => Config::get( 'site.salt' ) ) );

/* Database - setup */

$dbSettings = Config::get( 'database' );
$dbSettings[ 'productionLevel' ] = Config::get( 'site.production-level' );
Database::configure( $dbSettings );

/* Request + Response - initialize */

$req = new Request();
$res = new Response();

/* Disabled Site - check */

if( Config::get( 'site.disabled' ) && $req->paths( 0 ) != 'admin' )
{
	$res->setBody( Config::get( 'site.disabled-message' ) );
	$res->send();
}

/* Session - setup */

if( !$req->isApi() )
{
	// initialize sessions
	ini_set( 'session.use_trans_sid', false );
	ini_set( 'session.use_only_cookies', true ); 
	ini_set( 'url_rewriter.tags', '' );
	ini_set( 'session.gc_maxlifetime', Config::get( 'session.lifetime' ) );

	// set the session name
	$sessionTitle = Config::get( 'site.title' ) . '-' . $req->host();
	$safeSessionTitle = str_replace( array ( '.',' ',"'", '"' ), array( '','_','','' ), $sessionTitle );
	session_name( $safeSessionTitle );
	
	// set the session cookie parameters
	session_set_cookie_params(
	    Config::get( 'session.lifetime' ), // lifetime
	    '/', // path
	    '.' . $req->host(), // domain
	    $req->isSecure(), // secure
	    true // http only
	);

	// setup the desired session adapter
	$adapter = Config::get( 'session.adapter' );

	if( $adapter == 'redis' )
		Session\Redis::start( Config::get( 'redis' ), Config::get( 'session.prefix' ) );
	else if( $adapter == 'database' )
		Session\Database::start();
	else
		session_start();

	// set the cookie by sending it in a header.
	Util::set_cookie_fix_domain(
		session_name(),
		session_id(),
		time() + Config::get( 'session.lifetime' ),
		'/',
		$req->host(),
		$req->isSecure(),
		true
	);
	
	// update the session in our request
	$req->setSession( $_SESSION );
}

/* Queue - setup */

$queueConfig = Config::get( 'queue' );
if( is_array( $queueConfig ) )
	Queue::configure( array_merge( array(
		'namespace' => '\\app' ), $queueConfig ) );

/* Model - setup caching */

if( Config::get( 'memcache.enabled' ) )
	Model::configure( array(
		'cache' => array(
			'strategies' => array(
				'memcache' => Config::get( 'memcache' ),
				'local' => array() ) ) ) );

/* ViewEngine - setup */

if( $req->isHtml() )
	ViewEngine::configure( array(
		'engine' => Config::get( 'views.engine' ),
		'viewsDir' => INFUSE_VIEWS_DIR,
		'compileDir' => INFUSE_TEMP_DIR . '/smarty',
		'cacheDir' => INFUSE_TEMP_DIR . '/smarty/cache'
	) );

/* CLI Requests */

if( $req->isCli() )
{
	global $argc;
	if( $argc >= 2 )
		$req->setPath( $argv[ 1 ] );
	
	// super user permissions
	if( class_exists( '\\app\\users\\models\\User' ) )
		\app\users\models\User::su();
}

/* Middleware - execute */

foreach( Config::get( 'modules.middleware' ) as $module )
{
	$class = '\\app\\' . $module . '\\Controller';
	$controller = new $class();
	$controller->middleware( $req, $res );
}

/* ViewEngine - useful template parameters */

if( $req->isHtml() )
{
	$parameters = array();

	if( class_exists( '\\app\\users\\models\\User' ) )
		$parameters[ 'currentUser' ] = \app\users\models\User::currentUser();
	
	$parameters[ 'baseUrl' ] = ((Config::get('site.ssl-enabled'))?'https':'http') . '://' . Config::get('site.host-name') . '/';
	$parameters[ 'errorStack' ] = ErrorStack::stack();
	$parameters[ 'locale' ] = $locale;

	ViewEngine::engine()->assignData( $parameters );
}

/* Routing - option to skip (useful for unit tests) */

if( defined( 'DISABLE_INFUSE_ROUTING' ) && DISABLE_INFUSE_ROUTING )
	return;

/* Routing - setup */

Router::configure( array(
	'namespace' => '\\app' ) );

/*
	Routing Steps:
	1) routes from Config
	2) module routes (i.e. /users/:id/friends)
	   i) static routes
	   ii) dynamic routes
	3) module admin routes
	4) view without a controller (i.e. /contact-us displays views/contact-us.tpl)
	5) not found
*/

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

		$controller = '\\app\\' . $module . '\\Controller';
		
		if( class_exists( $controller ) )
		{
			$moduleRoutes = Util::array_value( $controller::$properties, 'routes' );
			
			$req->setParams( array( 'controller' => $module . '\\Controller' ) );
			
			$routed = Router::route( $moduleRoutes, $req, $res );
		}
	}
	else if( $routeStep == 3 )
	{
		/* module admin routes */
		
		if( $req->paths( 0 ) == 'admin' )
		{
			$module = $req->paths( 1 );
			
			$controller = '\\app\\' . $module . '\\Controller';

			if( class_exists( $controller ) )
			{
				$moduleInfo = $controller::$properties;

				$moduleRoutes = Util::array_value( $moduleInfo, 'routes' );

				$req->setParams( array( 'controller' => $module . '\\Controller' ) );

				$adminViewParams = array(
					'selectedModule' => $module,
					'title' => Util::array_value( $moduleInfo, 'title' ) );

				$adminLib = '\\app\\admin\\libs\\Admin';
				if( class_exists( $adminLib ) )
					$adminViewParams[ 'modulesWithAdmin' ] = $adminLib::adminModules();
				
				ViewEngine::engine()->assignData( $adminViewParams );
				
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