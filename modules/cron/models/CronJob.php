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

/*
 * @package Infuse
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0
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
 
namespace infuse\models;

use \infuse\libs\Cron;
use \infuse\libs\CronDate;
use \infuse\ErrorStack;

class CronJob extends \infuse\Model
{
	public static $properties = array(
		'id' => array(
			'type' => 'id'
		),
		'name' => array(
			'type' => 'text'
		),
		'module' => array(
			'type' => 'text',
			'length' => 100
		),
		'command' => array(
			'type' => 'text',
			'length' => 100
		),
		'minute' => array(
			'type' => 'text',
			'length' => 2,
			'validate' => array( '\infuse\models\CronJob', 'validateMinute' )
		),
		'hour' => array(
			'type' => 'text',
			'length' => 2,
			'validate' => array( '\infuse\models\CronJob', 'validateHour' )
		),
		'day' => array(
			'type' => 'text',
			'length' => 2,
			'validate' => array( '\infuse\models\CronJob', 'validateDay' )
		),
		'month' => array(
			'type' => 'text',
			'length' => 2,
			'validate' => array( '\infuse\models\CronJob', 'validateMonth' )
		),
		'week' => array(
			'type' => 'text',
			'length' => 2,
			'validate' => array( '\infuse\models\CronJob', 'validateWeek' )
		),
		'next_run' => array(
			'type' => 'date'
		),
		'last_ran' => array(
			'type' => 'date'
		),
		'last_run_result' => array(
			'type' => 'boolean',
			'default' => false
		),
		'last_run_output' => array(
			'type' => 'longtext',
			'filter' => '<pre>{last_run_output}</pre>',
			'truncate' => false
		)		
	);
	
	static function validateMinute( &$minute )
	{
		return self::validateCronTimePiece( $minute, 0, 59 );
	}

	static function validateHour( &$hour )
	{
		return self::validateCronTimePiece( $hour, 0, 23 );
	}

	static function validateDay( &$day )
	{
		return self::validateCronTimePiece( $day, 1, 31 );
	}

	static function validateMonth( &$month )
	{
		return self::validateCronTimePiece( $month, 1, 12 );
	}

	static function validateWeek( &$week )
	{
		return self::validateCronTimePiece( $week, 0, 6 );
	}
	
	function postCreateHook()
	{
		$data[ 'next_run' ] = self::calcNextRun(
			$this->get( 'minute' ),
			$this->get( 'hour' ),
			$this->get( 'day' ),
			$this->get( 'month' ),
			$this->get( 'week' ) );

		return true;
	}
	
	function preSetHook( &$data )
	{
		if( isset( $data[ 'minute' ] ) && isset( $data[ 'hour' ] ) && isset( $data[ 'day' ] ) && isset( $data[ 'month' ] ) && isset( $data[ 'week' ] ) )
			$data[ 'next_run' ] = self::calcNextRun( $data[ 'minute' ], $data[ 'hour' ], $data[ 'day' ], $data[ 'month' ], $data[ 'week' ] );

		return true;
	}
	
	/**
	 * Updates the job with the results from the latest run
	 *
	 * @param boolean $result
	 * @param string $output
	 *
	 * @return boolean
	 */
	function saveRun( $result, $output )
	{
		$nextRun = self::calcNextRun(
			$this->get( 'minute' ),
			$this->get( 'hour' ),
			$this->get( 'day' ),
			$this->get( 'month' ),
			$this->get( 'week' ) );
		
		return $this->set( array(
			'next_run' => $nextRun,
			'last_ran' => time(),
			'last_run_result' => $result,
			'last_run_output' => $output ) );
	}
	
	/**
	 * Generates a timestamp from cron date parameters
	 *
	 * @param string $minute minute
	 * @param string $hour hour
	 * @param string $day day
	 * @param string $dow day of week
	 *
	 * @return int timestamp
	 */
	private static function calcNextRun( $minute, $hour, $day, $month, $dow )
	{
		$cron_date = new CronDate( time() );
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
		}
	
		return $cron_date->timestamp;
	}	
	
	private static function validateCronTimePiece( $p, $lower, $upper )
	{
		return (is_numeric( $p ) && $p >= $lower && $p <= $upper) || $p == '*';
	}	
}