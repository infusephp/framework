<?php
/*
 * @package nFuse
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
 
namespace nfuse\models;

use \nfuse\libs\Cron as Cron;
use \nfuse\libs\CronDate as CronDate;

class CronJob extends \nfuse\Model
{
	protected static $tablename = 'Cron';
	public static $properties = array(
		array(
			'title' => 'ID',
			'name' => 'id',
			'type' => 'text'
		),
		array(
			'title' => 'Name',
			'name' => 'name',
			'type' => 'text'
		),
		array(
			'title' => 'Next Run',
			'name' => 'next_run',
			'type' => 'date'
		),
		array(
			'title' => 'Last Ran',
			'name' => 'last_ran',
			'type' => 'date'
		),
		array(
			'title' => 'Last Run Success',
			'name' => 'last_run_result',
			'type' => 'boolean',
			'default' => 0
		),
		array(
			'title' => 'Module',
			'name' => 'module',
			'type' => 'text'
		),
		array(
			'title' => 'Command',
			'name' => 'command',
			'type' => 'text'
		),
		array(
			'title' => 'Minute',
			'name' => 'minute',
			'type' => 'text'
		),
		array(
			'title' => 'Hour',
			'name' => 'hour',
			'type' => 'text'
		),
		array(
			'title' => 'Day',
			'name' => 'day',
			'type' => 'text'
		),
		array(
			'title' => 'month',
			'name' => 'month',
			'type' => 'text'
		),
		array(
			'title' => 'Week',
			'name' => 'week',
			'type' => 'text'
		),
		array(
			'title' => 'Last Run Output',
			'name' => 'last_run_output',
			'type' => 'text',
			'filter' => '<pre>{last_run_output}</pre>',
			'truncate' => false
		)		
	);
	
	/**
	* Creates a task
	*
	* @param array $data
	*
	* @return boolean success
	*/
	static function create( $data )
	{
		$modelName = get_called_class();
		$model = new $modelName(ACL_NO_ID);
		
		// permission?
		if( !$model->can( 'create' ) )
		{
			ErrorStack::add( ERROR_NO_PERMISSION );
			return false;
		}
		
		if( !self::validateCronTimePiece( val( $data, 'minute' ), 0, 59 ) )
			return false;
		
		if( !self::validateCronTimePiece( val( $data, 'hour' ), 0, 23 ) )
			return false;
		
		if( !self::validateCronTimePiece( val( $data, 'day' ), 1, 31 ) )
			return false;
		
		if( !self::validateCronTimePiece( val( $data, 'month' ), 1, 12 ) )
			return false;
		
		if( !self::validateCronTimePiece( val( $data, 'week' ), 0, 6 ) )
			return false;
		
		$data[ 'next_run' ] = self::calcNextRun( $data[ 'minute' ], $data[ 'hour' ], $data[ 'day' ],$data[ 'month' ],$data[ 'week' ] );

		return parent::create( $data );
	}
		
	/**
	* Edits a cron job
	* 
	* @param array $data
	*
	* @return boolean success
	*/
	function edit( $data )
	{
		if( isset( $data[ 'minute' ] ) && !self::validateCronTimePiece( $data[ 'minute' ], 0, 59 ) )
			return false;
		
		if( isset( $data[ 'hour' ] ) && !self::validateCronTimePiece( $data[ 'hour' ], 0, 23 ) )
			return false;
		
		if( isset( $data[ 'day' ] ) && !self::validateCronTimePiece( $data[ 'day' ], 1, 31 ) )
			return false;
		
		if( isset( $data[ 'month' ] ) && !self::validateCronTimePiece( $data[ 'month' ], 1, 12 ) )
			return false;
		
		if( isset( $data[ 'week' ] ) && !self::validateCronTimePiece( $data[ 'week' ], 0, 6 ) )
			return false;

		if( isset( $data[ 'minute' ] ) && isset( $data[ 'hour' ] ) && isset( $data[ 'day' ] ) && isset( $data[ 'month' ] ) && isset( $data[ 'week' ] ) )
			$data[ 'next_run' ] = self::calcNextRun( $data[ 'minute' ], $data[ 'hour' ], $data[ 'day' ], $data[ 'month' ], $data[ 'week' ] );

		return parent::edit( $data );
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
			$this->getProperty( 'minute' ),
			$this->getProperty( 'hour' ),
			$this->getProperty( 'day' ),
			$this->getProperty( 'month' ),
			$this->getProperty( 'week' ) );
		
		return $this->edit( array(
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