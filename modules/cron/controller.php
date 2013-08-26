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

class Cron extends \infuse\Controller
{
	public static $properties = array(
		'title' => 'Cron',
		'description' => 'Schedules background tasks within the framework.',
		'version' => '1.0',
		'author' => array(
			'name' => 'Jared King',
			'email' => 'j@jaredtking.com',
			'website' => 'http://jaredtking.com'
		),
		'admin' => true,
		'api' => true,
		'model' => 'CronJob',
		'routes' => array(
			'get /cron/scheduleCheck' => 'checkSchedule'
		)
	);
		
	function checkSchedule( $req, $res )
	{
		if( !$req->isCli() )
			return $res->setCode( 404 );
		
		\infuse\libs\Cron::scheduleCheck(true);
	}
}