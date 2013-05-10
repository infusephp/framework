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

class CronJob extends \nfuse\Model
{
	protected static $tablename = 'Cron';
	public static $properties = array(
		array(
			'title' => 'ID',
			'name' => 'id',
			'type' => 2
		),
		array(
			'title' => 'Name',
			'name' => 'name',
			'type' => 2
		),
		array(
			'title' => 'Next Run',
			'name' => 'next_run',
			'type' => 8
		),
		array(
			'title' => 'Last Ran',
			'name' => 'last_ran',
			'type' => 8
		),
		array(
			'title' => 'Module',
			'name' => 'module',
			'type' => 2
		),
		array(
			'title' => 'Command',
			'name' => 'command',
			'type' => 2
		),
		array(
			'title' => 'Minute',
			'name' => 'minute',
			'type' => 2
		),
		array(
			'title' => 'Hour',
			'name' => 'hour',
			'type' => 2
		),
		array(
			'title' => 'Day',
			'name' => 'day',
			'type' => 2
		),
		array(
			'title' => 'month',
			'name' => 'month',
			'type' => 2
		),
		array(
			'title' => 'Week',
			'name' => 'week',
			'type' => 2
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

		if( isset($data[ 'minute' ]) && ( $data[ 'minute' ] < 0 || $data[ 'minute' ] > 59) && $data[ 'minute' ] != '*' )
			return false;
		
		if ( isset( $data[ 'hour' ] ) && ( $data[ 'hour' ] < 0 || $data[ 'hour' ] > 23 ) && $data[ 'hour' ] != '*' )
			return false;
		
		if ( isset($data[ 'day' ]) && ( $data[ 'day' ] < 1 || $data[ 'day' ] > 31) && $data[ 'day' ] != '*' )
			return false;
		
		if ( isset($data[ 'month' ]) && ( $data[ 'month' ] < 1 || $data[ 'month' ] > 12 ) && $data[ 'month' ] != '*' )
			return false;
		
		if ( isset($data[ 'week' ]) && ( $data[ 'week' ] < 0 || $data[ 'week' ] > 6 ) && $data[ 'week' ] != '*' )
			return false;
		
		$data[ 'next_run' ] = Cron::calcNextRun( $data[ 'minute' ], $data[ 'hour' ], $data[ 'day' ],$data[ 'month' ],$data[ 'week' ] );

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
		if( !$this->can( 'edit' ) )
		{
			ErrorStack::add( ERROR_NO_PERMISSION );			
			return false;
		}
				
		if( isset($data[ 'minute' ]) && ( $data[ 'minute' ] < 0 || $data[ 'minute' ] > 59) && $data[ 'minute' ] != '*' )
			return false;
		
		if ( isset( $data[ 'hour' ] ) && ( $data[ 'hour' ] < 0 || $data[ 'hour' ] > 23 ) && $data[ 'hour' ] != '*' )
			return false;
		
		if ( isset($data[ 'day' ]) && ( $data[ 'day' ] < 1 || $data[ 'day' ] > 31) && $data[ 'day' ] != '*' )
			return false;
		
		if ( isset($data[ 'month' ]) && ( $data[ 'month' ] < 1 || $data[ 'month' ] > 12 ) && $data[ 'month' ] != '*' )
			return false;
		
		if ( isset($data[ 'week' ]) && ( $data[ 'week' ] < 0 || $data[ 'week' ] > 6 ) && $data[ 'week' ] != '*' )
			return false;

		$data[ 'next_run' ] = Cron::calcNextRun( $data[ 'minute' ], $data[ 'hour' ], $data[ 'day' ],$data[ 'month' ],$data[ 'week' ] );

		return parent::edit( $data );
	}	
}