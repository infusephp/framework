<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */
 
namespace app\cron;

use app\cron\libs\Cron;

class Controller extends \infuse\Acl
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
		'scaffoldAdmin' => true,
		'models' => array( 'CronJob' ),
		'routes' => array(
			'get /cron/scheduleCheck' => 'checkSchedule'
		)
	);
		
	function checkSchedule( $req, $res )
	{
		if( !$req->isCli() )
			return $res->setCode( 404 );
		
		Cron::scheduleCheck(true);
	}
}