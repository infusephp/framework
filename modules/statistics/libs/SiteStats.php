<?php

namespace infuse\libs;

use \infuse\Config as Config;
use \infuse\Database as Database;

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
		
		// site title
		$return['site']['title'] = Config::value( 'site', 'title' );

		// site status
		$return['site']['status'] = !Config::value( 'site', 'disabled' );

		// site mode
		$return['site']['mode'] = Config::value( 'site', 'production-level' );
		
		// session adapter
		$return['site']['session'] = Config::value( 'session', 'adapter' );		

		/* Infuse Statistics */
		$return[ 'infuse' ] = array();
		
		// infuse version
		$return['infuse']['version'] = INFUSE_VERSION;
		
		/* Database Statistics */
		$return['Database'] = array();

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

		return $return;
	}
	
	/**
	 * Gets the latest stats snapshot
	 *
	 * @return array
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
			
		$stats = Database::select(
			'Site_Stats_History',
			'stats,timestamp',
			array(
				'where' => array(
					"`timestamp` >= '$start'",
					"`timestamp` <= '$end'" ),
				'orderBy' => 'timestamp ASC' ) );
		
		$series = array();
		
		foreach( $stats as $day )
		{
			$decoded = json_decode( $day[ 'stats' ], true );
			
			$series[ date( 'm/d/Y', $day[ 'timestamp' ] ) ] = self::getNestedVar( $decoded, $metric );
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
			self::setNestedVar( $stats, $metric, self::getNestedVar( $snapshot, $metric ) );
		
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
	 * Looks up an element in an array using dot notation (i.e. fruit.apples.qty => ['fruit']['apples']['qty']
	 *
	 */
	private static function getNestedVar(&$context, $name)
	{
	    $pieces = explode('.', $name);
	    foreach ($pieces as $piece) {
	        if (!is_array($context) || !array_key_exists($piece, $context)) {
	            // error occurred
	            return null;
	        }
	        $context = &$context[$piece];
	    }
	    return $context;
	}
	
	/**
	 * Sets an element in an array using dot notation (i.e. fruit.apples.qty sets ['fruit']['apples']['qty']
	 *
	 */
	private static function setNestedVar(&$context, $name, $value)
	{
	    $pieces = explode('.', $name);
	    foreach ($pieces as $k => $piece)
	        $context = &$context[$piece];
	    
	    return $context = $value;
	}
}