<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.4
 * @copyright 2013 Jared King
 * @license MIT
 */
 
namespace infuse\controllers;

use \infuse\libs\SiteStats;
use \infuse\Modules;
use \infuse\Util;

class Statistics extends \infuse\Controller
{
	public static $properties = array(
		'title' => 'Statistics',
		'description' => 'Displays statistics for the site in the admin panel.',
		'version' => '1.1',
		'author' => array(
			'name' => 'Jared King',
			'email' => 'j@jaredtking.com',
			'website' => 'http://jaredtking.com'
		),
		'admin' => true,
		'model' => 'StatisticSnapshot',
		'defaultHistoryMetric' => 'users.numUsers',
		'routes' => array(
			'get /admin/statistics' => 'adminHome',
			'get /admin/statistics/history' => 'adminHistoryDefault',
			'get /admin/statistics/history/:metric' => 'adminHistory'
		)
	);

	function adminHome( $req, $res )
	{
		if( !$this->can( 'view-admin' ) )
			return $res->setCode( 401 );

		$stats = SiteStats::generateSnapshot();
	
		$stats['users']['newestUser'] = new \infuse\models\User( $stats['users']['newestUser'] );
		
		$stats['database']['size'] =  Util::formatNumberAbbreviation( $stats['database']['size'], 1 );
		
		$res->render( 'admin/index', array(
			'stats' => $stats
		) );
	}
	
	function adminHistoryDefault( $req, $res )
	{
		$mInfo = Modules::info( 'statistics' );
		
		$res->redirect( '/admin/statistics/history/' . $mInfo[ 'defaultHistoryMetric' ] );
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

		$res->render( 'admin/history', array(
			'metrics' => SiteStats::$historyMetrics,
			'history' => $history,
			'metric' => $metric,
			'start' => date('m/d/Y', $start),
			'end' => date('m/d/Y', $end)
		) );
	}

	function cron( $command )
	{
		if( $command == 'capture-snapshot' ) {
			if( SiteStats::captureSnapshot() ) {
				echo "Successfully captured snapshot\n";
				return true;
			} else {
				echo "Failed to capture snapshot\n";
				return false;
			}
		}
	}
}