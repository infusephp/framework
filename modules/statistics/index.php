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
 
namespace nfuse\controllers;

use \nfuse\libs\SiteStats as SiteStats;
use \nfuse\Modules as Modules;

class Statistics extends \nfuse\Controller
{
	function adminHome( $req, $res )
	{
		if( !$this->can( 'view-admin' ) )
			return $res->setCode( 401 );
	
		function file_size ($filesize)
		{
			$bytes = array('KB', 'KB', 'MB', 'GB', 'TB');
		
			if ($filesize < 1024) $filesize = 1; // Values are always displayed.
		
			for ($i = 0; $filesize > 1024; $i++)  { // In KB at least.
				$filesize /= 1024;
			} // for
		
			$file_size_info['size'] = ceil($filesize);
			$file_size_info['type'] = $bytes[$i];
		
			return $file_size_info;
		}
		
		$stats = SiteStats::generateSnapshot();
	
		$stats['users']['newestUser'] = new \nfuse\models\User( $stats['users']['newestUser'] );
		
		$dbsize = file_size( $stats['database']['size'] );
		$stats['database']['size'] =  $dbsize['size'] . " " . $dbsize['type'];
		
		$res->render( $this->adminTemplateDir() . 'index.tpl', array(
			'stats' => $stats
		) );
	}
	
	function adminHistoryDefault( $req, $res )
	{
		$mInfo = Modules::info( 'statistics' );
		
		$res->redirect( '/4dm1n/statistics/history/' . $mInfo[ 'defaultHistoryMetric' ] );
	}
	
	function adminHistory( $req, $res )
	{
		if( !$this->can( 'view-admin' ) )
			return $res->setCode( 401 );

		$metric = $req->params( 'metric' );
		$start = $req->query( 'start' );
		$end = $req->query( 'end' );
		
		function beginOfDay( $time )
		{
			list( $y, $m, $d ) = explode( '-', date( 'Y-m-d', $time ) );
			return mktime( 0, 0, 0, $m, $d, $y );
		}
		
		function endOfDay( $time )
		{
			list( $y, $m, $d ) = explode( '-', date( 'Y-m-d', $time ) );
			return mktime( 0, 0, 0, $m, $d + 1, $y ) - 1;
		}
		
		if( !$start )
			$start = beginOfDay( strtotime( '-7 days' ) );
		else
			$start = strtotime( $start );

		if( !$end )
			$end = endOfDay( time() );
		else
			$end = strtotime( $end ) + 3600*24 - 1;
		
		$history = SiteStats::history( $metric, $start, $end );

		$res->render( $this->adminTemplateDir() . 'history.tpl', array(
			'metrics' => SiteStats::$historyMetrics,
			'history' => $history,
			'metric' => $metric,
			'start' => date('m/d/Y', $start),
			'end' => date('m/d/Y', $end)
		) );
	}

	function cron( $command )
	{
		if( $command == 'capture-snapshot' )
			return SiteStats::captureSnapshot();
	}
}