<?php

class Cron
{
	//////////////////////////////
	// Private Class Variables
	//////////////////////////////
	
	/**
	* Generates a timestamp from cron date parameters
	* @param string $minute minute
	* @param string $hour hour
	* @param string $day day
	* @param string $dow day of week
	* @return int timestamp
	*/
	static function calcNextRun( $minute, $hour, $day, $month, $dow )
	{
		$cron_date = new cronDate(time());
		$cron_date->second = 0;
		
		if ($minute == '*') $minute = null;
		if ($hour == '*') $hour = null;
		if ($day == '*') $day = null;
		if ($month == '*') $month = null;
		if ($dow == '*') $dow = null;
		
		$Job = array(
					'Minute' => $minute,
					'Hour' => $hour,
					'Day' => $day,
					'Month' => $month,
					'DOW' => $dow
		);
	
		$done = 0;
		while ($done < 100) {
			if (!is_null($Job['Minute']) && ($cron_date->minute != $Job['Minute'])) {
		                if ($cron_date->minute > $Job['Minute']) {
		                        $cron_date->modify('+1 hour');
		                }
		                $cron_date->minute = $Job['Minute'];
		        }
		        if (!is_null($Job['Hour']) && ($cron_date->hour != $Job['Hour'])) {
		                if ($cron_date->hour > $Job['Hour']) {
		                        $cron_date->modify('+1 day');
		                }
		                $cron_date->hour = $Job['Hour'];
		                $cron_date->minute = 0;
		        }
		        if (!is_null($Job['DOW']) && ($cron_date->dow != $Job['DOW'])) {
		                $cron_date->dow = $Job['DOW'];
		                $cron_date->hour = 0;
		                $cron_date->minute = 0;
		        }
		        if (!is_null($Job['Day']) && ($cron_date->day != $Job['Day'])) {
		                if ($cron_date->day > $Job['Day']) {
		                        $cron_date->modify('+1 month');
		                }
		                $cron_date->day = $Job['Day'];
		                $cron_date->hour = 0;
		                $cron_date->minute = 0;
		        }
		        if (!is_null($Job['Month']) && ($cron_date->month != $Job['Month'])) {
		                if ($cron_date->month > $Job['Month']) {
		                        $cron_date->modify('+1 year');
		                }
		                $cron_date->month = $Job['Month'];
		                $cron_date->day = 1;
		                $cron_date->hour = 0;
		                $cron_date->minute = 0;
		        }
		
		        $done = (is_null($Job['Minute']) || $Job['Minute'] == $cron_date->minute) &&
		                (is_null($Job['Hour']) || $Job['Hour'] == $cron_date->hour) &&
		                (is_null($Job['Day']) || $Job['Day'] == $cron_date->day) &&
		                (is_null($Job['Month']) || $Job['Month'] == $cron_date->month) &&
		                (is_null($Job['DOW']) || $Job['DOW'] == $cron_date->dow)?100:($done+1);
		} // while
	
		return $cron_date->timestamp;
	}
	
	/**
	* Runs a specific cron task
	*
	* @param int $id task id
	*
	* @return boolean result
	*/
	static function runTask( $id )
	{
		if (!isset($id) || !is_numeric($id))
			return false;
		
		try
		{	
			$task = Database::select(
				'Cron',
				'minute,hour,day,month,week,command,module',
				array(
					'where' => array(
						'id' => $id ),
					'singleRow' => true ) );
			
			$command = $task[ 'command' ];

			if( !Modules::exists($task[ 'module' ]) )
				return false;
				
			Modules::load( $task[ 'module' ] );
	
			$next_run = self::calcNextRun($task[ 'minute' ], $task[ 'hour' ], $task[ 'day' ], $task[ 'month' ], $task[ 'week' ]);
	
			return Modules::controller( $task[ 'module' ] )->cron( $command ) &&
				Database::update(
					'Cron',
					array(
						'id' => $id,
						'last_ran' => time(),
						'next_run' => $next_run ),
					array( 'id' ) );
		}
		catch( Exception $e )
		{
			return false;
		}
			
		return false;
	}

	/**
	* Checks the cron schedule and runs tasks
	* @param boolean $ecoOutput echoes output
	* @return boolean true if all tasks ran successfully
	*/
	static function scheduleCheck( $echoOutput = false )
	{
		if( $echoOutput )
			echo "-- Starting Cron on " . Config::value('site', 'title') . "\n";
			 
		Modules::loadAll();
		
		$tasks = Database::select( 'Cron', 'id, next_run, last_ran, module, command' );

		$success = true;

		if( Database::numrows() > 0)
		{
			foreach ($tasks as $t)
			{					
				if ((time() - $t['next_run']) > 0 )//&& $t['last_ran'] < $t['next_run'])
				{
					if( $echoOutput )
						echo "Starting {$t['module']}/{$t['command']}:\n";

					$result = self::runTask( $t['id'] );
					
					$success = $success & $result;

					if( $echoOutput )
						echo ( $result ) ? "\tFinished Successfully\n" : "\tFailed\n";
				}
			}
		}
		
		return $success;
	}
}