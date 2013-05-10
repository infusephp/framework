<?php

namespace nfuse\libs;

use \nfuse\Config as Config;
use \nfuse\Database as Database;

class SiteStats
{
	/**
	 * Generates a snapshot from the current stats
	 *
	 */
	static function generateSnapshot()
	{
		$return = array();
		
		/* Database Statistics */
		$return['Database'] = array();
		
		$query = Database::sql("SHOW table STATUS"); // Get table information.
		
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
		
		return $return;
	}
	
	/**
	 * Gets all of the real-time stats (i.e. active users, current sw versions)
	 *
	 *
	 */
	static function generateRealtimeStats()
	{
		$return = array();
		
		/* User Statistics */
		$return['users'] = array();
						
		// newest user
		$return['users']['newestUser'] = Database::select(
			'Users',
			'uid',
			array(
				'orderBy' => 'registered_timestamp DESC',
				'single' => true ) );

		// daily signups
		$return['users']['dailySignups'] = (int)Database::select(
			'Users',
			'count(uid)',
			array(
				'where' => array(
					'registered_timestamp > ' . strtotime('today') ),
				'single' => true ) );

		/* Database Statistics */
		$return['database'] = array();
		
		// DB Type
		$query = Database::sql( 'SELECT VERSION()' );
		$return['database']['version'] = $query->fetchColumn( 0 );
		
		/* PHP Statistics */
		$return['php'] = array();
		
		// php version
		$return['php']['version'] = phpversion();
		
		/* Site Statistics */
		$return['site'] = array();
		
		// site title
		$return['site']['title'] = Config::value( 'site', 'title' );

		// site status
		$return['site']['status'] = !Config::value( 'site', 'disabled' );

		// site mode
		$return['site']['mode'] = Config::value( 'site', 'production-level' );
		
		// site email
		$return['site']['email'] = Config::value( 'site', 'email' );
		
		// session adapter
		$return['site']['session'] = Config::value( 'session', 'adapter' );
		
		return $return;
	}	
	
	/**
	 * Captures a screenshot of the current stats
	 *
	 *
	 */
	static function captureSnapshot()
	{
		// generate a snapshot
		$stats = self::generateSnapshot();
		
		// save it in the DB
		return Database::insert(
			'Site_Stats_History',
			array(
				'timestamp' => time(),
				'stats' => json_encode( $stats )
			)
		);
	}
	
	/**
	 * Gets the latest stats snapshot
	 *
	 *
	 */
	static function getLatestSnapshot()
	{
		return json_decode( Database::select(
			'Site_Stats_History',
			'stats',
			array(
				'orderBy' => 'timestamp DESC',
				'single' => true,
				'limit' => '0,1'
		)), true);
	}
}