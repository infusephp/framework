<?php
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

use \infuse\libs\Cron as Cron;
use \infuse\libs\CronDate as CronDate;
use \infuse\ErrorStack as ErrorStack;

class CronJob extends \infuse\Model
{
	public static $properties = array(
		'id' => array(
			'type' => 'text'
		),
		'name' => array(
			'type' => 'text'
		),
		'next_run' => array(
			'type' => 'date'
		),
		'last_ran' => array(
			'type' => 'date'
		),
		'last_run_result' => array(
			'type' => 'boolean',
			'default' => 0
		),
		'module' => array(
			'type' => 'text'
		),
		'command' => array(
			'type' => 'text'
		),
		'minute' => array(
			'type' => 'text'
		),
		'hour' => array(
			'type' => 'text'
		),
		'day' => array(
			'type' => 'text'
		),
		'month' => array(
			'type' => 'text'
		),
		'week' => array(
			'type' => 'text'
		),
		'last_run_output' => array(
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
	 * Updates the model
	 *
	 * @param array|string $data key-value properties or name of property
	 * @param string new $value value to set if name supplied
	 *
	 * @return boolean
	 */
	function set( $data, $value = false )
	{
		ErrorStack::setContext( 'edit' );
		
		if( !is_array( $data ) )
			$data = array( $data => $value );

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

		return parent::set( $data );
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