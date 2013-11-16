<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */
 
namespace app\infuse;

use infuse\Config;
use infuse\ViewEngine;

use app\infuse\libs\Fusion;

class Controller extends \infuse\Acl
{
	public static $properties = array(
		'title' => 'Infuse',
		'description' => 'CLI and admin dashboard to manage Infuse Framework.',
		'version' => '1.0',
		'author' => array(
			'name' => 'Jared King',
			'email' => 'j@jaredtking.com',
			'website' => 'http://jaredtking.com'
		),
		'scaffoldAdmin' => true,
		'models' => array( 'Module' ),
		'routes' => array(
			'get /infuse/schema' => 'schema',
			'get /infuse/compileAssets' => 'compileAssets'
		)
	);

	function middleware( $req, $res )
	{
		// compile assets on each request if we are not in production
		if( $req->isHtml() && !Config::get( 'site.production-level' ) )
			$this->compileSiteAssets();

		/* Installer - run if framework has not been installed yet */

		if( !Config::get( 'site.installed' ) && !$req->isCli() )
		{
			include 'install.php';
			exit;
		}
	}

	function schema( $req, $res )
	{
		if( !$req->isCli() )
			return $res->setCode( 404 );

		if( in_array( $req->cliArgs( 2 ), array( 'install', 'update' ) ) )
		{
			echo "-- Installing schema...\n";

			if( Fusion::installSchema( true ) )
				echo "-- Schema installed successfully\n";
			else
				echo "-- Problem installing schema\n";
		}
	}

	function compileAssets( $req, $res )
	{
		if( !$req->isCli() )
			return $res->setCode( 404 );

		echo "-- Compiling assets\n";

		$this->compileSiteAssets( true );
	}

	private function compileSiteAssets( $echoOutput = false )
	{
		$engine = ViewEngine::engine();

	    // create temp and output dirs
	    if( !file_exists( INFUSE_TEMP_DIR . '/css' ) )
		   	@mkdir( INFUSE_TEMP_DIR . '/css' );
		if( !file_exists( INFUSE_PUBLIC_DIR . '/css' ) )
		   	@mkdir( INFUSE_PUBLIC_DIR . '/css' );
		if( !file_exists( INFUSE_TEMP_DIR . '/js' ) )
			@mkdir( INFUSE_TEMP_DIR . '/js' );
		if( !file_exists( INFUSE_PUBLIC_DIR . '/js' ) )
			@mkdir( INFUSE_PUBLIC_DIR . '/js' );

		if( $echoOutput )
			echo "Compiling LESS...";
		
		// CSS asset compilation
		$cssFile = INFUSE_ASSETS_DIR . '/css/styles.less';
		if( file_exists( $cssFile ) )
		{
			$success = $engine->compileLess(
				$cssFile,
				INFUSE_TEMP_DIR . '/css/styles.css.cache',
				INFUSE_PUBLIC_DIR . '/css/styles.css',
				Config::get( 'site.production-level' ) );

			if( $echoOutput )
				echo ($success) ? "ok\n" : "not ok\n";
		}

		if( $echoOutput )
			echo "Compiling JS...";
		
		// JS asset compilation
		$jsDir = INFUSE_BASE_DIR . '/assets/js';
		if( is_dir( $jsDir ) )
		{
			$success = $engine->compileJs(
				$jsDir,
				INFUSE_TEMP_DIR . '/js/header.js.cache',
				INFUSE_PUBLIC_DIR . '/js/header.js',
				Config::get( 'site.production-level' ) );

			if( $echoOutput )
				echo ($success) ? "ok\n" : "not ok\n";			
		}
	}
}