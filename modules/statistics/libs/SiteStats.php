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

use \infuse\Config;
use \infuse\Database;
use \infuse\Util;
use \infuse\models\StatisticSnapshot;

class SiteStats
{
	static $historyMetrics = array(
		'database.size',
		'database.numTables',
		'users.numUsers',
		'users.numGroups',
		'users.dailySignups'
	);

	/**
	 * Generates a snapshot from the current stats
	 *
	 * @return array
	 */
	static function generateSnapshot()
	{
		$return = array();
		
		/* PHP Statistics */
		$return['php'] = array();
		
		// php version
		$return['php']['version'] = phpversion();
		
		/* Site Statistics */
		$return['site'] = array();
		
		// site status
		$return['site']['status'] = !Config::value( 'site', 'disabled' );

		// site mode
		$return['site']['mode'] = Config::value( 'site', 'production-level' );
		
		// session adapter
		$return['site']['session'] = Config::value( 'session', 'adapter' );		

		/* Infuse Statistics */
		$return[ 'infuse' ] = array();
		
		// load composer.json
		$infuseComposer = json_decode( file_get_contents( INFUSE_BASE_DIR . '/composer.json' ) );
		
		// infuse version
		$return['infuse']['version'] = $infuseComposer->version;
		
		/* Database Statistics */
		$return['database'] = array();

		// DB Type
		$query = Database::sql( 'SELECT VERSION()' );
		$return['database']['version'] = $query->fetchColumn( 0 );		
		
		// Get table information.
		$query = Database::sql("SHOW table STATUS");
		
		$status = $query->fetchAll( \PDO::FETCH_ASSOC );
		
		$dbsize = 0;
		// Calculate DB size by adding table size + index size:
		foreach( $status as $row )
			$dbsize += $row['Data_length'] + $row['Index_length'];
			
		$return['database']['size'] = $dbsize;
		
		// number of tables
		$return['database']['numTables'] = count( $status );
		
		/* User Statistics */
		$return['users'] = array();
		
		// total number of users
		$return['users']['numUsers'] = (int)Database::select(
			'Users',
			'count(*)',
			array(
				'single' => true ) );

		// number of groups
		$return['users']['numGroups'] = (int)Database::select(
			'Groups',
			'count(*)',
			array(
				'single' => true ) );
						
		// newest user
		$return['users']['newestUser'] = Database::select(
			'Users',
			'uid',
			array(
				'orderBy' => 'registered_on DESC',
				'single' => true ) );

		// daily signups
		$return['users']['dailySignups'] = (int)Database::select(
			'Users',
			'count(uid)',
			array(
				'where' => array(
					'registered_on > ' . strtotime('today') ),
				'single' => true ) );

		return $return;
	}
	
	/**
	 * Gets the latest stats snapshot
	 *
	 * @return array
	 */
	static function getLatestSnapshot()
	{
		$lastSnapshot = StatisticSnapshot::findOne( array( 'sort' => 'timestamp DESC' ) );
		
		return json_decode( $lastSnapshot->get( 'stats' ) );
	}
	
	/**
	 * Gets the history for a given metric
	 *
	 * @param string $metric
	 * @param string $start
	 * @param string $end
	 *
	 * @return false|array
	 */
	static function history( $metric, $start, $end )
	{
		if( !in_array( $metric, self::$historyMetrics ) )
			return false;
		
		$snapshots = StatisticSnapshot::find( array(
			'where' => array(
				"`timestamp` >= '$start'",
				"`timestamp` <= '$end'" ),
			'sort' => 'timestamp ASC' ) );
		
		$series = array();
		
		foreach( $snapshots[ 'models' ] as $snapshot )
		{
			$decoded = json_decode( $snapshot->get( 'stats' ), true );
			
			$series[ date( 'm/d/Y', $snapshot->get( 'timestamp' ) ) ] = Util::array_value( $decoded, $metric );
		}
		
		return $series;
	}
	
	/**
	 * Captures a screenshot of the current stats
	 *
	 * @return boolean
	 */
	static function captureSnapshot()
	{
		// generate a snapshot
		$snapshot = self::generateSnapshot();
		
		// only save the history metrics
		$stats = array();
		
		foreach( self::$historyMetrics as $metric )
			Util::array_set( $stats, $metric, Util::array_value( $snapshot, $metric ) );
		
		// save it in the DB
		$success = StatisticSnapshot::create( array(
			'stats' => json_encode( $stats ) ) );
	}
}