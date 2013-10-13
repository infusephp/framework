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

class Bans extends \infuse\Controller
{
	public static $properties = array(
		'title' => 'Bans',
		'description' => 'Ban users by various criteria.',
		'version' => '1.0',
		'author' => array(
			'name' => 'Jared King',
			'email' => 'j@jaredtking.com',
			'website' => 'http://jaredtking.com'
		),
		'admin' => true,
		'model' => 'Ban'
	);

	function middleware( $req, $res )
	{
		try
		{
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
		}
		catch( \Exception $e )
		{
			\infuse\Logger::error( \infuse\Logger::formatException( $e ) );
		}
	}
}