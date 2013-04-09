<?php

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
		$return['database'] = array();
		
		$query = Database::sql("SHOW table STATUS"); // Get table information.
		
		$status = $query->fetchAll( PDO::FETCH_ASSOC );
		
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
		$return['users']['numUsers'] = (int)Database::select( 'Users', 'count(*)', array( 'single' => true ) );

		// daily active users
		// TODO: can this query be improved?
		$return['users']['numDailyActive'] = (int)Database::select(
			'User_Login_History',
			'count(timestamp)',
			array(
				'where' => array(
					'timestamp IN (SELECT timestamp FROM `User_Login_History` GROUP BY uid HAVING `timestamp` > ' . strtotime('today') . ' )'
				),
				'single' => true
			));
		
		// daily signups
		$return['users']['numDailySignups'] = (int)Database::select( 'Users', 'count(uid)', array( 'where' => array( 'registered_timestamp > ' . strtotime('today') ), 'single' => true ) );

		// number of groups
		$return['users']['numGroups'] = (int)Database::select( 'Groups', 'count(*)', array( 'single' => true ) );
		
		// number of notifications
		$return['users']['numNotifications'] = Database::select( 'Notifications', 'count(id)', array( 'single' => true ) );
		
		// number of facebook users
		$return['users']['numFbUsers'] = Database::select( 'Users', 'count(uid)', array( 'where' => array( 'fbid > 0' ), 'single' => true ) );
		
		/* List Statistics */
		$return['lists'] = array();

		// number of lists
		$return['lists']['numLists'] = (int)Database::select( 'Lists', 'count(id)', array( 'single' => true ) );
		
		// number of public lists
		$return['lists']['numPublicLists'] = (int)Database::select( 'Lists', 'count(id)', array( 'where' => array( 'public' => 1 ), 'single' => true ) );
		
		// number of fields
		$return['lists']['numFields'] = Database::select( 'List_Fields', 'count(id)', array( 'single' => true ) );
		
		// number of items
		$return['lists']['numItems'] = Database::select( 'List_Items', 'count(*)', array( 'single' => true ) );
		
		// number of apps
		$return['lists']['numApps'] = Database::select( 'Apps', 'count(id)', array( 'single' => true ) );
		
		// number of categories
		$return['lists']['numCategories'] = Database::select( 'List_Categories', 'count(id)', array( 'single' => true ) );
		
		// number of commments
		$return['lists']['numComments'] = Database::select( 'List_Discussion_Posts', 'count(id)', array( 'single' => true ) );
		
		// number of tags
		$return['lists']['numTags'] = Database::select( 'List_Tags', 'count(id)', array( 'single' => true ) );

		// number of subscriptions
		$return['lists']['numSubscriptions'] = Database::select( 'List_Subscriptions', 'count(id)', array( 'single' => true ) );

		// number of interactions = favs + votes + comments + messages + notifications
		$return['users']['numInteractions'] = $return['lists']['numComments'] + $return['users']['numNotifications'] + $return['lists']['numSubscriptions'];
		
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
		
		// number of active guests
		$return['users']['numActiveVisitors'] = (int)Database::select( 'Sessions', 'count(*)', array( 'where' => array( 'access >= ' . strtotime('-5 minutes'), 'logged_in IS NULL' ), 'groupBy' => 'id', 'single' => true ) );
				
		// active members
		$activeMembers = (array)Database::select( 'Sessions', 'logged_in', array( 'where' => array( 'access >= ' . strtotime("-5 minutes"), 'logged_in > 0' ), 'fetchStyle' => 'singleColumn' ) );
		$activeMembers[] = Globals::$currentUser->id();
		$activeMembers = array_unique( $activeMembers );

		$return['users']['numActiveMembers'] = count( $activeMembers );		
		$return['users']['activeMembers'] = $activeMembers;
		
		// number of logged on users
		$return['users']['numActiveUsers'] = $return['users']['numActiveMembers'] + $return['users']['numActiveVisitors'];

		// newest user
		$return['users']['newestUser'] = Database::select( 'Users', 'uid', array( 'orderBy' => 'registered_timestamp DESC', 'single' => true ) );

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