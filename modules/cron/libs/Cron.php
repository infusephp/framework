<?php

namespace nfuse\libs;

use \nfuse\Modules as Modules;
use \nfuse\models\CronJob as CronJob;
use \nfuse\Database as Database;
use \nfuse\Config as Config;

class Cron
{
	/**
	 * Runs a specific cron task
	 *
	 * @param int $id task id
	 *
	 * @return boolean result
	 */
	static function runTask( $id, $echoOutput = false )
	{
		if( !isset( $id ) || !is_numeric( $id ) )
			return false;
		
		$success = false;
		$output = '';
		
		$task = new CronJob( $id );
		$task->loadProperties();
		$info = $task->toArray();

		try
		{
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
		
		$task->saveRun( $success, $output );
		
		if( $echoOutput )
			echo $output . (( $success ) ? "\tFinished Successfully\n" : "\tFailed\n");
		
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
		
		$tasks =  Database::select(
			'Cron',
			'id',
			array(
				'where' => array(
					'next_run <= ' . time() ),
				'fetchStyle' => 'singleColumn' ) );

		$success = true;
		
		foreach( $tasks as $id )
		{
			$taskSuccess = self::runTask( $id, $echoOutput );
			
			$success = $success & $taskSuccess;
		}
		
		return $success;
	}
}