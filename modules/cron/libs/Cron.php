<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.4
 * @copyright 2013 Jared King
 * @license MIT
 */

namespace infuse\libs;

use \infuse\Modules;
use \infuse\models\CronJob;
use \infuse\Database;
use \infuse\Config;

class Cron
{
	/**
	 * Runs a specific cron job
	 *
	 * @param CronJob job
	 * @param boolean $echoOutput
	 *
	 * @return boolean result
	 */
	static function runJob( $job, $echoOutput = false )
	{
		$job->load();
		// only run a job if we can get the lock, otherwise skip
		if( !$job->getLock() )
			return true;

		$success = false;
		$output = '';

		try
		{
			$info = $job->toArray();

			if( Modules::exists( $info[ 'module' ] ) )
			{
				if( $echoOutput )
					echo "Starting {$info['module']}/{$info['command']}:\n";
				
				ob_start();
				
				Modules::load( $info[ 'module' ] );
				
				$success = Modules::controller( $info[ 'module' ] )->cron( $info[ 'command' ] );
				
				$output = ob_get_clean();
			}
			else
			{
				$output = "{$info['module']} does not exist.";
			}
		}
		catch( \Exception $e )
		{
			// uh oh
			$output .= "\n" . $e->getMessage();
		}
		
		$job->saveRun( $success, $output );
		
		if( $echoOutput )
			echo $output . (( $success ) ? "\tFinished Successfully\n" : "\tFailed\n");

		$job->releaseLock();
		
		return $success;
	}
	
	/**
	 * Checks the cron schedule and runs tasks
	 *
	 * @param boolean $echoOutput echos output
	 *
	 * @return boolean true if all tasks ran successfully
	 */
	static function scheduleCheck( $echoOutput = false )
	{
		if( $echoOutput )
			echo "-- Starting Cron on " . Config::value('site', 'title') . "\n";
		
		$success = true;
		
		$jobs =  CronJob::overdueJobs();

		foreach( $jobs as $job )
		{
			$taskSuccess = self::runJob( $job, $echoOutput );
			
			$success = $success & $taskSuccess;
		}
		
		return $success;
	}
}