<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.4
 * @copyright 2013 Jared King
 * @license MIT
 */
 
namespace infuse\controllers;

use \infuse\ViewEngine;
use \infuse\libs\Fusion;

class Infuse extends \infuse\Controller {
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
		'models' => array(
			'Module'
		),
		'routes' => array(
			'get /infuse/schema' => array(
				'action' => 'schema'
			)
		)
	);

	function middleware( $req, $res )
	{
		if( $req->isHtml() )
		{
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
	}

	function schema( $req, $res )
	{
		if( !$req->isCli() )
			return $res->setCode( 404 );

		if( $req->cliArgs( 2 ) == 'install' )
		{
			echo "-- Installing schema...";

			if( Fusion::installSchema() )
				echo "ok\n";
			else
				echo "error\n";
		}
	}
}