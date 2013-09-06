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
		'admin' => true,
		'api' => true,
		'models' => array(
			'Module'
		),
		'routes' => array(
			'get /infuse/schema' => array(
				'action' => 'schema'
			)
		)
	);

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