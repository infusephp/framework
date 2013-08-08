<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.2
 * @copyright 2013 Jared King
 * @license MIT
	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
	associated documentation files (the "Software"), to deal in the Software without restriction,
	including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
	and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
	subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in all copies or
	substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
	LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
	IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
	SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace infuse\libs;

use \infuse\Modules;
use \infuse\models\CronJob;
use \infuse\Database;
use \infuse\Config;

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
			'CronJobs',
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