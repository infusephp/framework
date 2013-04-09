<?php
/**
 * Model representation of a user
 * 
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

class User extends Model
{
	/////////////////////////////////////
	// Model Properties
	/////////////////////////////////////
	
	protected static $tablename = 'Users';
	public static $idFieldName = 'uid';
	protected static $escapedFields = array( 'website', 'about' );
	public static $properties = array(
		array(
			'title' => 'User ID',
			'name' => 'uid',
			'type' => 2,
			'filter' => '<a href="/user/{uid}" target="_blank">{uid}</a>'
		),
		array(
			'title' => 'User Email',
			'name' => 'user_email',
			'type' => 2,
			'filter' => '<a href="mailto:{user_email}">{user_email}</a>',
			'validation' => array('Validate','email'),
			'required' => true
		),
		array(
			'title' => 'First Name',
			'name' => 'first_name',
			'type' => 2,
			'validation' => array('Validate','firstName'),
			'required' => true
		),
		array(
			'title' => 'Last Name',
			'name' => 'last_name',
			'type' => 2,
			'validation' => array('Validate','lastName')
		),
		array(
			'title' => 'User Password',
			'name' => 'user_password',
			'type' => 7,
			'validation' => array('Validate','password'),
			'required' => true			
		),
		array(
			'title' => 'Registration Date',
			'name' => 'registered_timestamp',
			'type' => 8,
			'required' => true,
			'nowrap' => true
		),
		array(
			'title' => 'IP',
			'name' => 'ip',
			'type' => 2,
			'filter' => '<a href="http://www.infobyip.com/ip-{ip}.html" target="_blank">{ip}</a>',
			'required' => true
		),
		array(
			'title' => 'Group',
			'name' => 'group_',
			'type' => 2,
			'filter' => '<a href="/4dm1n/groups/{group_}" target="_blank">{group_}</a>',
			'validation' => array('Validate','group'),
			'default' => 15,
			'required' => true
		),
		array(
			'title' => 'Enabled',
			'name' => 'enabled',
			'type' => 4,
			'validation' => array('Validate','boolean_'),
			'required' => true,
			'default' => true
		),
		array(
			'title' => 'Profile Picture',
			'name' => 'profile_picture',
			'type' => 5,
			'enum' => array(
				0 => 'Gravatar',
				1 => 'Facebook' ),
			'nowrap' => true,
			'required' => false,
			'default' => 0
		),
		array(
			'title' => 'Time Zone',
			'name' => 'time_zone',
			'type' => 5,
			'enum' => array(
				'America/New_York' => 'EST (GMT - 5)',
				'America/Chicago' => 'CST (GMT - 6)',
				'America/Denver' => 'MST (GMT - 7)',
				'America/Los_Angeles' => 'PST (GMT - 8)',
				'America/Anchorage' => 'AST (GMT - 9)',
				'America/Honolulu' => 'HST (GMT - 10)'
			),
			'nowrap' => true,
			'validation' => array('Validate','timeZone'),
			'required' => true			
		),
		array(
			'title' => 'Phone',
			'name' => 'cellphone',
			'type' => 2,
			'validation' => array('Validate','phone'),
			'validation_params' => array(true,'cell')
		),
		array(
			'title' => 'Location',
			'name' => 'location',
			'type' => 2
		),
		array(
			'title' => 'Web Site',
			'name' => 'website',
			'filter' => '<a href="{website}" target="_blank">{website}</a>',
			'type' => 2
		),
		array(
			'title' => 'About',
			'name' => 'about',
			'type' => 3
		)
	);

	/////////////////////////////////////
	// Private Class Variables
	/////////////////////////////////////
	
	/**
	 * @staticvar
	 * Billing Plans
	 */
	private static $plans = array(
		5 => array(
			'id' => 5,
			'name' => 'Forever Free',
			'features' => array(
				'lists' => -1,
				'categories' => -1,
				'space' => 250000000
			),
			'price' => 0,
			'stripePlanId' => 'personal-free',
		),
		6 => array(
			'id' => 6,
			'name' => 'Personal Premium',
			'features' => array(
				'lists' => -1,
				'categories' => -1,
				'space' => 2000000000,
				'realtime' => true,
				'history' => true
			),
			'price' => 3,
			'stripePlanId' => 'personal-premium',
		)
	);
	private $logged_in;
	
	/**
	* Constructor
	* @param int $id id
	* @param boolean $check_logged_in check if the user is logged in
	*/
	function __construct( $id, $check_logged_in = false, $logged_in = false )
	{
		if( is_numeric( $id ) )
			$this->id = $id;
		else
		{
			$exp = explode( '-', $id );
			$last = end($exp);
			if( is_numeric($last) )
				$this->id = $last;
			else
				$this->id = -1;
		}
			
		if( $logged_in && $id > 0 )
			$this->logged_in = true;
		else if( $check_logged_in )
			$this->logged_in = $this->logged_in_();		
	}
	
	/////////////////////////////////////
	// GETTERS
	/////////////////////////////////////
	
	/**
	 * Gets the available plans
	 *
	 * @param boolean $reverse reverse the plans
	 *
	 * @return array plans
	 */
	static function plans( $reverse = false )
	{	
		$plans = self::$plans;
		if( $reverse )
			$plans = array_reverse( $plans, true );
		return $plans;
	}
	
	/**
	 * Gets the current billing plan
	 *
	 * @param string $feature feature to lookup
	 *
	 * @return int billing plan
	 */
	function billingPlan( $feature = null )
	{
		$planId = $this->getProperty('plan');
		
		// free plan by default
		if( $planId == -1 || !isset( self::$plans[ $planId ] ) )
			$planId = 5;
		
		$plan = self::$plans[ $planId ];
		
		if( $feature )
		{		
			if( in_array( $feature, array( 'price', 'name', 'id', 'stripePlanId' ) ) )
				return $plan[ $feature ];
			else
				return $plan[ 'features' ][ strtolower( $feature ) ];
		}
		else
			return $plan;
	}
	
	/**
	 * Checks if the user has SSL with their plan
	 *
	 * @return boolean has security
	 */
	function hasSSL()
	{
		// all plans have SSL
		return true;
	}
	
	/**
	* Gets the temporary string if the user is temporary
	*
	* @return link|false true if temporary
	*/
	function temporary()
	{
		return Database::select(
			'User_Links',
			'link',
			array(
				'where' => array(
					'uid' => $this->id,
					'type' => 2 // 0 = forgot, 1 = verify, 2 = temporary
				),
				'single' => true ) );
	}
	
	/**
	* Checks if the user is logged in
	* @return boolean true if logged in
	*/
	function logged_in()
	{
		return $this->logged_in || ( defined( 'CRON' ) && CRON );
	}
	
	/**
	* Checks if the user is actively registered
	* @return boolean true if the user is actively registered
	*/
	function registered()
	{
		if( $this->id == -1 )
			return false;
			
		return $this->getProperty('uid') == $this->id;
	}
	
	/**
	* Checks if the user's information is public
	* @todo not implemented
	* @return true if public 
	*/
	function public_()
	{
		// TODO
		return true;
	}	
	
	/**
	* Get's the user's name
	* @param boolean $full get full name if true
	* @return string name
	*/
	function name( $full = false )
	{
		if( $this->id == -1 )
			return 'Guest';
		else
		{
			if( !$this->registered() )
				return '(no longer registered)';
				
			$names = $this->getProperty( array( 'first_name', 'last_name', 'user_email' ) );
			if( $names[ 'first_name' ] != '' )
				return $names[ 'first_name' ] . ( ($full) ? ' ' . $names[ 'last_name' ] : '' );
			else
				return $names[ 'user_email' ];
		}
	}	
	
	/**
	* Checks if the user is an admin
	* @todo not implemented
	* @return boolean true if admin
	*/
	function admin()
	{
		return $this->id() == 1;
	}
	
	/**
	* Gets the group the user belongs to
	* @return Group group
	*/
	function group()
	{
		if( $this->id == -1 )
			return new Group( -1 );
		else
			return new Group( $this->getProperty( 'group_' ) );
	}
	
	/**
	* Gets the groups a member is a part of
	*
	* @param User|int $inRelationTo user or user ID
	*
	* @return array(Group) groups
	*/
	function groups( $inRelationTo = null)
	{
		$return = array( new Group(-1) );
		
		$uid = -1;
		if( $inRelationTo instanceof User )
			$uid = $inRelationTo->id();
		else if( is_numeric( $inRelationTo ) )
			$uid = $inRelationTo;
		
		if( $this->isMemberOf( 2, $uid ) )
			$return[] = new Group( 2 );

		$group = $this->group();
		if( !in_array( $group->id(), array( -1, 2 ) ) )
			$return[] = $group;
			
		return $return;
	}
	
	/**
	 * Gets the date the user joined
	 *
	 * @param $format string PHP date format string
	 *
	 * @return string date
	 */
	function registerDate( $format = 'F j, Y' )
	{
		return date( $format, $this->getProperty( 'registered_timestamp' ) );
	}
	
	/**
	* Checks if the user is a member of a group
	*
	* @param int $gid group ID
	* @param User|int $inRelationTo user or user id
	*
	* @return boolean true if member
	*/
	function isMemberOf( $gid, $inRelationTo = -1 )
	{
		// special cases
		
		// evreryone
		if( $gid == -1 )
			return true;
		
		// following
		if( $gid == 2 && $this->id() > 0 && $this->isFollowing( $inRelationTo ) )
			return true;
			
		return $this->getProperty('group_') == $gid;
	}
	
	/**
	 * Checks if the user has connected with Facebook
	 *
	 * @param boolean connected?
	 */
	function fbConnected()
	{
		// WARNING: this does not mean we still have permission, check with FB for that
		return $this->getProperty('fbid') != '';
	}
	
	/**
	* Gets the URL of the profile for the user
	* @return string URL
	*/
	function profileURL()
	{
		if( $this->id <= 0 )
			return urlPrefix() . Config::value( 'site', 'host-name' ) . '/user/not-found-0';
			
		return urlPrefix() . Config::value( 'site', 'host-name' ) . '/user/' . seoUrl( $this->name(true), $this->id );
	}
	
	/**
	* Generates the URL for the user's profile picture
	*
	* Gravatar is used for profile pictures. To accomplish this we need to generate a hash of the user's e-mail.
	*
	* @param int $size size of the picture (it is square, usually)
	* @param int $type type of picture to get (0 = gravatar, 1 = facebook)
	*
	* @return string url
	*/
	function profilePicture( $size = 200, $type = null )
	{
		$type = ($type !== null) ? $type : $this->getProperty('profile_picture');
		
		switch( $type)
		{
		// use Facebook
		case 1:
			return 'https://graph.facebook.com/' . $this->getProperty('fbid') . '/picture?type=large';
		break;
		// use Gravatar
		default:
		case 0:
			$hash = md5( strtolower( trim( $this->getProperty('user_email') ) ) );
			return "https://secure.gravatar.com/avatar/$hash?s=$size&d=mm";
		break;
		}
	}	
	
	/**
	 * Gets the usage for a particular feature
	 *
	 * @param string $feature feature
	 *
	 * @return int usage
	 */
	function currentFeatureUsage( $feature )
	{
		if( $feature == 'lists' )
		{
			return (int)Database::select(
				'Lists',
				'count(id)',
				array(
					'where' => array(
						'uid' => $this->id ),
					'single' => true ) );
		}
		else if( $feature == 'categories' )
		{
			return (int)Database::select(
				'List_Categories',
				'count(*)',
				array(
					'where' => array(
						'uid' => $this->id ),
					'single' => true ) );
		}
		else if( $feature == 'space' )
		{
			return (int)Database::select(
				'List_Files as f LEFT JOIN Lists as l ON f.list = l.id',
				'SUM(size)',
				array(
					'where' => array(
						'l.uid = ' . $this->id ),
					'single' => true ) );
		}
		else
			return 0;
	}
	
	/**
	 * Gets the number of lists the current user can view
	 *
	 * @param boolean $percentage returns a percentage when true (0-100)
	 *
	 * @return int number of lists
	 */
	function numLists( $percentage = false )
	{
		// get lists that are owned by the user
		$count = $this->currentFeatureUsage( 'lists' );
		
		if( $percentage )
		{
			$allowed = $this->billingPlan( 'lists' );
			if( $allowed == -1 )
				return 0;
			else
				return round( $count / $allowed * 100 , 2 );
		}
		else
			return $count;
	}
	
	/**
	 * Gets the number of categories underneath the organization
	 *
	 * @param boolean $percentage returns a percentage when true (0-100)
	 *
	 * @return int count
	 */
	function numCategories( $percentage = false )
	{
		$count = $this->currentFeatureUsage( 'categories' );
		
		if( $percentage )
		{
			$allowed = $this->billingPlan( 'categories' );
			if( $allowed == -1 )
				return 0;
			else
				return round( $count / $allowed * 100 , 2 );
		}
		else
			return $count;
	}

	/**
	 * Gets the amount of space used by the organization
	 *
	 * @param boolean $humanReadable human readable string
	 *
	 * @return int count
	 */
	function spaceUsed( $humanReadable = false, $percent = false )
	{
		$space = $this->currentFeatureUsage( 'space' );
		
		if( $percent )
			return round( $space / $this->billingPlan( 'space' ) * 100 , 2 );
		else if( $humanReadable )
			return formatNumberAbbreviation( $space, 2 ) . 'B';
		else
			return $space;
	}
	
	/**
	* Gets the user's lists
	* @param int $limit number of lists to retrieve
	* @param boolean $categorize if true 
	* @param int $cateogry category ID
	* @return array(ListBase) lists
	*/
	function lists( $limit = 0, $categorize = false, $category = -1 )
	{
		Modules::load('lists');
		
		// PERMISSIONS, public lists only
		// TODO: this function is a clusterfuck
		
		$orderBy = ($categorize) ? 'category ASC,name ASC' : 'name ASC';
		
		$categorySql = ( $category >= 0 ) ? array( 'category' => $category ) : array();
		
		$limitSql = ( $limit > 0 ) ? "0,$limit" : '';
		
		$lists = array_merge(
			(array)Database::select('Lists', 'id', array(
				'where' => array_merge( $categorySql, array( 'uid' => $this->id, 'public' => 1 ) ),
				'fetchStyle' => 'singleColumn',
				'limit' => $limitSql
			) ),
			(array)Database::select('Lists as d,List_Permissions as p','d.id', array(
				'where' => array_merge( $categorySql, array( 'd.uid' => $this->id, 'p.uid' => Globals::$currentUser->id(),
				'p.permission > 0', 'p.dt = d.id' ) ),
				'fetchStyle' => 'singleColumn',
				'limit' => $limitSql
			) )
		);
		
		// TODO: duplicates
		// TODO: list permissions

		$return = array();
		foreach( $lists as $id )
		{
			if( is_numeric( $id ) )
			{
				$list = new ListBase( $id );
				$list->loadProperties();
				if( !$list->permission()->canView() )
					continue;
				
				if( $categorize )
				{
					$cid = $list->category()->id();
					if( !isset( $return[$cid] ) )
						$return[$cid] = array();

					$return[$cid][] = $list;
				}
				else
					$return[] = $list;
			}
		}
		
		// SORT
		// TODO

		return $return;		
	}
	
	/**
	 * Gets the user's teams
	 *
	 * @return array(Organization)
	 */
	function teams()
	{
		if( !$this->logged_in() )
			return array();
		
		// cached?
		$cachedTeams = $this->getProperty( 'teams' );
		if( $cachedTeams !== false )
			return $cachedTeams;

		Modules::load( 'organizations' );
		
		$return = array();

		foreach( (array)Database::select(
			'Organization_Members',
			'organization',
			array(
				'where' => array(
					'user' => $this->id
				),
				'fetchStyle' => 'singleColumn' )
		) as $id )
			$return[] = new Organization( $id, false );
			
		$this->cacheProperty( 'teams', $return );

		return $return;
	}
	
	/**
	 * Checks if the user is a member of an organization
	 *
	 * @return boolean member?
	 */
	function isOrganizationMember()
	{
		return Database::select(
			'Organization_Members',
			'count(*)',
			array(
				'where' => array(
					'user' => $this->id ),
				'single' => true ) ) > 0;
	}
	
	/**
	* Gets the user's followers
	*
	* @return array(User) followers
	*/
	function followers()
	{
		$uids = Database::select(
			'Followers',
			'follower',
			array(
				'where' => array(
					'following' => $this->id ),
				'fetchStyle' => 'singleColumn' ) );
		
		$followers = array();
		foreach( $uids as $uid )
		{
			$follower = new User( $uid );
			if( $follower->registered() && $follower->public_() )
				$followers[] = $follower;
		}
		
		return $followers;
	}
	
	/**
	* Gets the people the user follows
	*
	* @return array(User) following
	*/
	function following()
	{
		$uids = Database::select(
			'Followers',
			'following',
			array(
				'where' => array(
					'follower' => $this->id ),
				'fetchStyle' => 'singleColumn' ) );
		
		$following = array();
		foreach( $uids as $uid )
		{
			$person = new User( $uid );
			if( $person->registered() && $person->public_() )
				$following[] = $person;
		}
		
		return $following;
	}
	
	/**
	* Checks if the user is following someone
	*
	* @param int $uid user ID
	*
	* @return boolean true if following
	*/
	function isFollowing( $uid )
	{
		if( $uid == $this->id || $uid < 0 )
			return false;
			
		$value = Database::select(
			'Followers',
			'count(*)',
			array(
				'where' => array(
					'following' => $uid,
					'follower' => $this->id ),
			'single' => true ) ) > 0;
		
		return $value;
	}
	
	/**
	* Checks if the user is followed by someone
	*
	* @param int $uid user ID
	*
	* @return boolean true if followed
	*/
	function isFollowedBy( $uid )
	{
		if( $uid == $this->id || $uid < 0 )
			return false;
			
		$value = Database::select(
			'Followers',
			'count(*)',
			array(
				'where' => array(
					'follower' => $uid,
					'following' => $this->id ),
			'single' => true ) ) > 0;
		
		return $value;
	}
		
	/**
	* Gets the number of followers
	*
	* @return int number of followers
	*/
	function followerCount()
	{
		return Database::select(
			'Followers',
			'count(*)',
			array(
				'where' => array(
					'following' => $this->id ),
			'single' => true ) );
	}
	
	/**
	* Gets the number of people the user follows
	*
	* @return int number following
	*/
	function followingCount()
	{
		return Database::select(
			'Followers',
			'count(*)',
			array(
				'where' => array(
					'follower' => $this->id ),
			'single' => true ) );
	}	
	
	/**
	* Gets the notifications
	*
	* @param int $limit limit
	*
	* @return array(Notification) notifications
	*/
	function notifications( $limit = 50 )
	{
		// TODO: check that at least N notifications are available before returning	
		$cachedNotifications = $this->getProperty( 'notifications' );
		if( $cachedNotifications !== false )
			return $cachedNotifications;
	
		Modules::load('Notifications');
		
		if( $this->id < 0 || Globals::$currentUser->id() != $this->id )
			return false;
			
		$ids = Database::select(
			'Notifications',
			'*',
			array(
				'where' => array(
					'user' => $this->id ),
				'orderBy' => 'time DESC',
				'limit' =>  "0,$limit" ) );
		
		$return = array();
		foreach( (array)$ids as $notificationInfo )
		{
			$notification = new Notification( $notificationInfo[ 'id' ] );
			$notification->cacheProperties($notificationInfo);
			$return[] = $notification;
		}
		
		$this->cacheProperty( 'notifications', $return);

		return $return;
	}
	
	/**
	 * Gets unread notifications.
	 *
	 * @return array(Notification) notifications
	 */
	function unreadNotifications()
	{
		$notifications = $this->notifications();
		
		$return = array();
		
		foreach( $notifications as $notification )
		{
			if( !$notification->read() )
				$return[] = $notification;
		}
		
		return $return;
	}
	
	/**
	* Gets the number of unread notifications
	*
	* @return int number of unread notifications
	*/
	function unreadNotificationCount()
	{
		return count( $this->unreadNotifications() );
	}
	
	/**
	 * Gets the most recent N notifications
	 *
	 * @param int $n number of notifications to fetch
	 *
	 * @return array(Notification) notifications
	 */
	function recentNotifications( $n )
	{
		// TODO: seems kinda redundant with notifications()
		$notifications = $this->notifications($n);
		
		return array_slice( $notifications, 0, $n );
	}
	
	/**
	* Gets the people a user has been messaging, sorted by recency
	*
	* @return string people (comma-seperated)
	*/
	function conversations()
	{
		Modules::load( 'Messages' );

		$convos = array();
		
		// TODO: sort by time
		
		// messages from this user
		$from = (array)Database::select(
			'Messages',
			'CONCAT(sender,",",receiver) as people,MAX(time) as time',
			array(
				'where' => array(
					'sender' => $this->id
				),
				'groupBy' => 'receiver'
			)
		);
		
		// combine senders and receivers, sorted
		foreach( $from as $row )
		{
			// break apart row
			$exp = explode( ',', $row[ 'people' ] );

			// sort
			sort( $exp );
			
			// tokenize
			$convos[ implode( ',', $exp ) ] = $row[ 'time' ];
		}
		
		// messages to this user
		// individual (to is the user id) or group (user is in a sorted comma-separated string)
		$to = (array)Database::select(
			'Messages',
			'CONCAT(sender,",",receiver) as people,MAX(time) as time',
			array(
				'where' => array(
					'FIND_IN_SET("' . $this->id . '",receiver)'
				),
				'groupBy' => 'receiver'
			)
		);
		
		// combine senders and receivers, sorted
		foreach( $to as $row )
		{
			// break apart row
			$exp = explode( ',', $row[ 'people' ] );
		
			// sort
			sort( $exp );
			
			// tokenize
			$key = implode( ',', $exp );
			
			if( !isset( $convos[ $key ] ) || ( isset( $convos[ $key ] ) && $convos[ $key ] < $row[ 'time' ] ) )
				$convos[ $key ] = $row[ 'time' ];
		}
		
		arsort( $convos );
		
		$return = array();

		foreach( $convos as $users => $time )
			$return[] = new Conversation( $users );
			
		return $return;
	}
	
	/**
	* Gets the number of unread messages
	*
	* @return int number of unread messages
	*/
	function unreadMessageCount()
	{
		if( !$this->logged_in() )
			return false;
		
		// TODO: super inefficient
		
		// get all of the conversations the user is a part of
		$convos = $this->conversations();
		
		// check the read status of each message and aggregate unread messages
		$unreadCount = 0;
		foreach( $convos as $conversation )
		{
			foreach( $conversation->messages() as $message )
			{
				if( !$message->read() )
					$unreadCount++;
			}
		}
		
		return $unreadCount;
	}
	
	///////////////////////////////
	// SETTERS
	///////////////////////////////
	
	/**
	* Creates a user
	*
	* @param array $data user data
	* @param boolean $verifiedEmail true if the e-mail has been verified
	* 
	* @return boolean success
	*/
	static function create( $data, $verifiedEmail = false )
	{
		$data[ 'ip' ] = $_SERVER[ 'REMOTE_ADDR' ];
		$data[ 'registered_timestamp' ] = time();
					
		// verify key
		$verify = md5(uniqid( rand(), true ));
		
		if( $verifiedEmail )
			// enable the account
			$data[ 'enabled' ] = 1;
		else
			// disable the account until the e-mail is verified
			$data[ 'enabled' ] = 0;
	
		$user = parent::create( $data );
		
		if( $user )
		{
			if( !$verifiedEmail )
			{
				// create the verification link
				Database::insert(
					'User_Links',
					array(
						'uid' => $user->id(),
						'type' => 1, // 0 = forgot, 1 = verify, 2 = temporary
						'link' => $verify,
						'timestamp' => time() ) );
			
				// ask the user to verify their e-mail
				$user->sendEmail( 'email-verification', array( 'verify' => $verify ) );
			}
			else
				// send the user a welcome message
				$user->sendEmail( 'registration-welcome', array() );
			
			return $user;		
		}
		else
		{
		
		}
	}
	
	static function createTemporary( $data )
	{
		if( !Validate::email( $data[ 'user_email' ], true, true ) )
			return false;

		// temporary string
		$temporary = md5(uniqid( rand(), true ));
		
		// create the temporary user
		if( Database::insert(
			'Users',
			array(
				'user_email' => $data[ 'user_email' ],
				'ip' => $_SERVER[ 'REMOTE_ADDR' ],
				'enabled' => 0 ) ) )
		{
			$uid = Database::lastInsertID();
			
			// create the temporary link
			Database::insert(
				'User_Links',
				array(
					'uid' => $uid,
					'type' => 2, // 0 = forgot, 1 = verify, 2 = temporary
					'link' => $temporary,
					'timestamp' => time() ) );
			
			return $uid;
		}
			
		else
			return false;	
	}	
	
	/**
	* Edits the user's information
	*
	* @param array $data data (mapped field to values)
	*
	* @return boolean true if successful
	*/
	function edit( $data )
	{
		return parent::edit( $data );
		
/*
		ErrorStack::setContext( 'user-edit' );
	
		if( Permissions::getPermission( 'edit_users', Globals::$currentUser->id(), Globals::$currentUser->group()->id() ) != 1 && $this->id != Globals::$currentUser->id() )
		{
			ErrorStack::add( 'permission error', __CLASS__, __FUNCTION__ );
			return false;
		}
		
		if (count($data) == 0)
			return true;
*/

		// verify current password is correct, if supplied
		$passwordRequired = isset( $data[ 'user_email' ] ) || isset( $data[ 'user_password' ] );
		$passwordVerified = Globals::$currentUser->group()->id() == ADMIN || ( $passwordRequired && encryptPassword( $data[ 'current_password' ] ) == $this->getProperty( 'user_password' ) );

/*
		$validated = true;
		$update_array = array( 'uid' => $this->id );
		
		// loop through each supplied field and validate
		foreach ($data as $field => $field_info)
		{
			if( array_key_exists( $field, static::$properties ) )
				$value = $data[ $field ];
			else
				continue;

			if( is_callable( static::$properties[ $field ][ 'validation' ] ) )
			{
				$args = array( &$value );
				if( is_array( static::$properties[ $field ][ 'validation_params' ] ) )
				{
					foreach( static::$properties[ $field ][ 'validation_params' ] as $p )
						$args[] = $p;
				}
				
				if( call_user_func_array( static::$properties[ $field ][ 'validation' ], $args ) )
					$update_array[ $field ] = $value;
				else
				{
					//echo $field;
					$validated = false;
				}
			}
			else
				$update_array[ $field ] = $value;
		}
*/

		// cannot update e-mail or password if current password has not been supplied & verified
		if( $passwordRequired && !$passwordVerified )
			ErrorStack::add( 'The password could not be verified.', __CLASS__, 'current-password' );
			
		if( ( ( isset( $update_array[ 'user_password' ] ) || isset( $update_array[ 'user_email' ] ) ) && !$passwordVerified ) || count( $update_array ) <= 1 )
			return false;
		
		if( isset( $update_array[ 'user_password' ] ) )
			$update_array[ 'user_password' ] = encryptPassword( $update_array[ 'user_password' ] );
/*
		if( !$validated )
			return false;
		
		if( Database::update( 'Users', $update_array, array( 'uid' ) ) )
		{
			// update the local cache
			foreach( $update_array as $key => $update )
			{
				if( !in_array( $key, array( 'uid', 'user_password' ) ) )
					$this->cacheProperty( $key, $update );
			}
		}
*/
	}
	
	/**
	* Upgrades the user from temporary to a fully registered account
	* @param string $name name
	* @param string $password1 password
	* @param string $password2 password
	* @return boolean true if successful
	*/
	function upgradeFromTemporary( $name, $password1, $password2 )
	{
		ErrorStack::setContext( 'user-register' );
		
		$validated = true;
	
		// break name into first and last name
		$exp = explode( ' ', $name );
		
		if( isset( $exp[ 0 ] ) )
		{
			$first_name = $exp[ 0 ];
			unset( $exp[ 0 ] );
		}
		
		$last_name = implode( ' ', $exp );
		
		if( !Validate::firstName( $first_name ) )
			$validated = false;
		
		if( isset( $password1 ) && isset( $password2 ) )
		{
			$password = array( $password1, $password2 );
			if( !Validate::password( $password ) )
				$validated = false;
		}
		else
			$validated = false;

		return $validated &&
		Database::update(
			'Users',
			array(
				'uid' => $this->id,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'user_password' => encryptPassword( $password1 ),
				'registered_timestamp' => time(),
				'ip' => $_SERVER[ 'REMOTE_ADDR' ],
				'enabled' => 1 ),
			array( 'uid' ) ) &&
		Database::delete(
			'User_Links',
			array(
				'uid' => $this->id,
				'type = 1 OR type = 2' ) );
	}
	
	function forgotStep2( $token, $password, $password2 )
	{
		// set the context
		ErrorStack::setContext( 'user-forgot' );
		
		// check for a valid token
		if( Database::select(
			'User_Links',
			'count(uid)',
			array(
				'where' => array(
					'link' => $token,
					'type' => 0, // 0 = forgot, 1 = verify, 2 = temporary					
					'timestamp > ' . strtotime( '-30 minutes' ), 
					'uid' => $this->id ),
				'single' => true ) ) != 1 )
			return false;

		$pass = array( $password, $password2 );

		// Validate the password
		if( !Validate::password( $pass ) )
			return false;
		
		// Update the password
		return Database::update(
			'Users',
			array(
				'uid' => $this->id,
				'user_password' => encryptPassword( $password ) ),
			array( 'uid' ) ) && Database::delete( 'User_Links', array( 'uid' => $this->id, 'type' => 0 ) );
	}
	
	/**
	 * Follow a user
	 *
	 * @param int $uid User to follow
	 *
	 * @return boolean success
	 */
	function follow( $uid )
	{
		return Database::insert(
			'Followers',
			array(
				'follower' => $this->id,
				'following' => $uid,
				'timestamp' => time() ) );
	}
	
	/**
	 * Stop following a user
	 *
	 * @param int $uid User to stop following
	 *
	 * @return boolean success
	 */
	function unfollow( $uid )
	{
		return Database::delete(
			'Followers',
			array(
				'follower' => $this->id,
				'following' => $uid ) );
	}
	
	/**
	* Unfavorites a list
	*
	* @param int $list list ID
	*
	* @return boolean true if successful
	*/
	function unfavoriteList( $list )
	{
		return Database::delete(
			'List_Favorites',
			array(
				'list' => $list,
				'uid' => $this->id ) );
	}
	
	/**
	 * Updates the user's billing information
	 *
	 * @param string $stripeToken Stripe Token
	 *
	 * @return boolean success
	 */
	function updateBillingInformation( $stripeToken )
	{
		// fire up stripe
		Modules::load( 'billing' );

		// does the user already have a stripe customer ID?
		$custId = ( strlen( $this->getProperty('stripeCustID') ) > 0 ) ? $this->getProperty('stripeCustID') : false;
		
		try
		{
			if( $custId )
			{
				// try to retrieve the customer
				$customer = Stripe_Customer::retrieve( $custId );
				// set the new card
				$customer->card = $stripeToken;
				// save it
				$customer->save();

				return true;
			}
		}
		catch( Exception $e )
		{
			$custId = false;
		}
		
		// no existing stripe customer so create one
		try
		{
			$customerInfo = array(
				"description" => 'User #' . $this->id,
				'email' => $this->getProperty('user_email') );
			
			if( strlen( $stripeToken ) > 0 )
				$customerInfo[ "card" ] = $stripeToken;
			 
			$customer = Stripe_Customer::create( $customerInfo );

			if( Database::update(
				'Users',
				array(
					'uid' => $this->id,
					'stripeCustID' => $customer->id ),
				array( 'uid' ) ) )
			{	
				$this->cacheProperty('stripeCustID', $customer->id );
				
				return true;
			}
		}
		catch( Exception $e )
		{
			return false;
		}
		
		return false;
	}
	
	/**
	 * Changes the current billing plan
	 *
	 * @param int plan
	 *
	 * @return boolean success
	 */
	function changeBillingPlan( $plan )
	{
		if( !isset( self::$plans[ $plan ] ) )
			return false;
		
		Modules::load( 'billing' );

		// sign the user up for the plan on stripe
		try
		{
			$stripeBillingPlanId = self::$plans[ $plan ][ 'stripePlanId' ];
			
			$customer = Stripe_Customer::retrieve($this->getProperty('stripeCustID'));
			$subscription = $customer->updateSubscription(
				array(
					"plan" => $stripeBillingPlanId,
					"prorate" => true ) );
			
			if( $subscription->status == 'active' )
			{
				$updateArray = array(
					'uid' => $this->id,
					'plan' => $plan,
					'hasBillingProblem' => 0,
					'nextDueDate' => time() );

				// update the user's billing stats
				Database::update(
					'Users',
					$updateArray,
					array( 'uid' ) );
					
				$this->cacheProperties( $updateArray );
					
				return true;
			}
		}
		catch( Exception $e )
		{
			return false;
		}
	}
	
	///////////////////////////////////
	// UTILITIES
	///////////////////////////////////
	
	/**
	* Attempts to log the user in
	*
	* @param string $email e-mail address
	* @param string $password password
	* @param boolean $remember remember me
	* @param int $fbid facebook id
	* @param bool $setSessionVars when true sets the $_SESSION with user info
	*
	* @return boolean true if successful
	*/
	function login( $email, $password, $remember = false, $fbid = 0, $setSessionVars = true )
	{
		if( $this->logged_in )
			return true;
			
		if( empty( $email ) ) // Validate the email.
		{
			// TODO: update this message
			ErrorStack::add( Messages::USER_BAD_EMAIL, __CLASS__, __FUNCTION__ );
			return false;
		}

		if( empty( $password ) && $fbid == 0 ) // Validate the password.
		{
			ErrorStack::add( Messages::USER_BAD_PASSWORD, __CLASS__, __FUNCTION__ );
			return false;
		}
		// Query the database.
		if( $fbid > 0 )
			$userInfo = Database::select(
				'Users AS u',
				'uid, user_email, enabled',
				array(
					'where' => array(
						'user_email' => $email,
						'fbid' => $fbid,
						'NOT EXISTS ( SELECT uid FROM User_Links AS l WHERE type = 1 AND u.uid = l.uid )', // 0 = forgot, 1 = verify, 2 = temporary
						'NOT EXISTS ( SELECT uid FROM User_Links AS l WHERE type = 2 AND u.uid = l.uid )' // 0 = forgot, 1 = verify, 2 = temporary
					),
					'singleRow' => true ),0,true );
		else
			$userInfo = Database::select(
				'Users AS u',
				'uid,user_email,enabled',
				array(
					'where' => array(
						'user_email' => $email,
						'user_password' => encryptPassword( $password ),
						'NOT EXISTS ( SELECT uid FROM User_Links AS l WHERE type = 1 AND u.uid = l.uid )', // 0 = forgot, 1 = verify, 2 = temporary
						'NOT EXISTS ( SELECT uid FROM User_Links AS l WHERE type = 2 AND u.uid = l.uid )' // 0 = forgot, 1 = verify, 2 = temporary
					),
					'singleRow' => true ) );

		if( Database::numrows() == 1 )
		{ // A match was made.
			$banned = false; // TODO: user bans
			if( $userInfo[ 'enabled' ] != 1 || $banned ) // check if disabled or banned
			{
				ErrorStack::add( Messages::USER_LOGIN_BANNED, __CLASS__, __FUNCTION__ );
				return false;
			}
			// success
			else
			{
				$this->id = $userInfo[ 'uid' ];
			
				if( $setSessionVars )
				{
					// update the session with the user's id
					$this->changeSessionUserID( $userInfo[ 'uid' ] );
				
					// store the user agent
					$_SESSION[ 'user_agent' ] = $_SERVER[ 'HTTP_USER_AGENT' ];
				}

				if( $remember )
				{
					$series = $this->generateToken();
					$token = $this->generateToken();
					setcookie( 'persistent', $userInfo[ 'user_email' ] . '!-!' . $series . '!-!' . $token . '!-!' . $_SERVER[ 'HTTP_USER_AGENT' ], time() + 3600*24*30*3, '/', $_SERVER[ 'HTTP_HOST' ], false, true );
					
					Database::insert(
						'Persistent_Sessions',
						array(
							'user_email' => $userInfo[ 'user_email' ],
							'series' => encryptPassword( $series ),
							'token' => encryptPassword( $token ),
							'created' => time()
						)
					);
				}

				// create an entry in the login history table
				Database::insert(
					'User_Login_History',
					array(
						'uid' => $userInfo[ 'uid' ],
						'timestamp' => time(),
						'type' => ($fbid > 0) ? '1' : '0', // regular (0) or FB (1)
						'ip' => $_SERVER['REMOTE_ADDR']
					)
				);

				$this->logged_in = true;
				
				return true;
			}
		}
		else // No match was made.
		{
			ErrorStack::add( Messages::USER_LOGIN_NO_MATCH, __CLASS__, __FUNCTION__ );
			return false;
		}
	}
	
	/**
	* Logs the user out
	* @return boolean true if successful
	*/
	function logout()
	{
		if( $this->logged_in() )
		{
			Database::Delete( 'Persistent_Sessions', array( 'user_email' => $this->getProperty( 'user_email' ) ) ); // Delete all persistent sessions
		    $params = session_get_cookie_params(); // empty the session cookie
			setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			);
			$_SESSION = array(); // Destroy the variables.
			session_destroy(); // Destroy the session itself.
			$this->id = -1;
			$this->changeSessionUserID( -1 ); // back to a guest...
			
			$this->logged_in = false;
			
			return true;
		}
		return false;
	}
	
	/**
	 * Deletes the user account (CAREFUL!)
	 *
	 * @return boolean success?
	 */
	function delete2( $password )
	{
		// check for the confirm and password
		// only the current user can delete their account
		if( $this->id > 1 && Globals::$currentUser->id() == $this->id && encryptPassword( $password ) == $this->getProperty( 'user_password' ) )
		{
			// delete stripe customer
			try
			{
				Modules::load('billing');
	 			$cu = Stripe_Customer::retrieve($this->getProperty('stripeCustID'));
 				$cu->delete();
 			}
 			catch( Exception $e )
 			{
 				// uh oh
 			}
 		
			// delete the user
			return Database::delete(
				'Users',
				array(
					'uid' => $this->id ) );
			
			// logout the user
			$this->logout();
		}
		
		return false;
	}
	
	/**
	 * Sends the user an e-mail
	 *
	 * @param string $message message name
	 * @param array $details details of the message
	 *
	 * @return boolean success
	 */
	function sendEmail( $message, $details = array() )
	{
		$subject = 'Message from ' . Config::value( 'site', 'title' );
		
		switch ($message)
		{
		case 'registration-welcome':
			$subject = 'Welcome to ' . Config::value( 'site', 'title' );
		break;
		case 'email-verification':
			Globals::$smarty->assign( 'verify', $details[ 'verify' ] );
			$subject = 'Please validate your e-mail address';
		break;
		case 'forgot-password':
			$subject = 'Password change request on ' . Config::value( 'site', 'title' );
			Globals::$smarty->assign( 'ip', $details[ 'ip' ] );
			Globals::$smarty->assign( 'forgot_link', $details[ 'forgot_link' ] );
		break;
		case 'notification':
			// skip the e-mail if requested
			if( !$this->getProperty( 'emailNotify' ) )
				return true;
				
			$subject = $details[ 'subject' ];
			Globals::$smarty->assign( 'notification', $details[ 'notification' ] );
		break;
		case 'list-share-unregistered':
			$subject = $details[ 'person' ] . ' has shared the list ' . $details[ 'list' ] . ' with you on ' . Config::value( 'site', 'title' );
			Globals::$smarty->assign( 'url', $details[ 'url' ] );
			Globals::$smarty->assign( 'person', $details[ 'person' ] );
			Globals::$smarty->assign( 'person_url', $details[ 'person_url' ] );
			Globals::$smarty->assign( 'list', $details[ 'list' ] );
		break;
		case 'organization-added-unregistered':
			$subject = $details[ 'person' ] . ' has added you to the organization ' . $details[ 'organization' ] . ' on ' . Config::value( 'site', 'title' );
			Globals::$smarty->assign( 'url', $details[ 'url' ] );
			Globals::$smarty->assign( 'person', $details[ 'person' ] );
			Globals::$smarty->assign( 'person_url', $details[ 'person_url' ] );
			Globals::$smarty->assign( 'organization', $details[ 'organization' ] );			
		break;
		case 'organization-welcome':
			$subject = 'Welcome to Teams on ' . Config::value( 'site', 'title' );
			Globals::$smarty->assign( 'url', $details[ 'url' ] );
			Globals::$smarty->assign( 'organization', $details[ 'organization' ] );
			Globals::$smarty->assign( 'newListURL', $details[ 'newListURL' ] );
			Globals::$smarty->assign( 'addMemberURL', $details[ 'addMemberURL' ] );
		break;
		case 'payment-received':
			$subject = 'Thank you for your payment';
			Globals::$smarty->assign( 'timestamp', $details[ 'timestamp' ] );
			Globals::$smarty->assign( 'plan', $details[ 'plan' ] );
			Globals::$smarty->assign( 'owner_name', $details[ 'owner_name' ] );
			Globals::$smarty->assign( 'amount', number_format( $details[ 'amount' ], 2 ) );			
		break;
		case 'payment-problem':
			$subject = 'We had a problem processing your payment on ' . Config::value( 'site', 'title' );
			Globals::$smarty->assign( 'timestamp', $details[ 'timestamp' ] );
			Globals::$smarty->assign( 'plan', $details[ 'plan' ] );
			Globals::$smarty->assign( 'owner_name', $details[ 'owner_name' ] );
			Globals::$smarty->assign( 'amount', number_format( $details[ 'amount' ], 2 ) );
			Globals::$smarty->assign( 'card_last4', $details[ 'card_last4' ] );
			Globals::$smarty->assign( 'card_expires', $details[ 'card_expires' ] );
			Globals::$smarty->assign( 'card_type', $details[ 'card_type' ] );
			Globals::$smarty->assign( 'error_message', $details[ 'error_message' ] );
		break;
		default:
			return false;
		break;
		}
		
		Globals::$smarty->assign( 'message', $message );
		Globals::$smarty->assign('user', $this );
		
		// load the Mail module
		Modules::load( 'Mail' );
		$mail = new Mail;
		
		// basic e-mail info
		$mail->From = SMTP_FROM_ADDRESS;
		$mail->FromName = Config::value( 'site', 'title' );
		$mail->Subject = $subject;
		
		// generate the body
		$body = Globals::$smarty->fetch(Modules::$moduleDirectory . 'user/templates/emails.tpl');
		
		// text body
		$mail->AltBody = $body;
		
		// html body
		$mail->MsgHTML( nl2br($body) );
		
		// send it to the user
		$mail->AddAddress( $this->getProperty( 'user_email' ) );
		
		// send the e-mail
		return $mail->Send();
	}
	
	/////////////////////////
	// PRIVATE FUNCTIONS
	/////////////////////////
	
	private function logged_in_()
	{
		// check if the user's session is already logged in
		if( isset( $_SESSION[ 'user_agent' ] ) && $_SESSION[ 'user_agent' ] == $_SERVER[ 'HTTP_USER_AGENT' ] && isset( $_SESSION[ 'user_id' ] ) && $_SESSION[ 'user_id' ] > 0 )
		{
			$this->id = $_SESSION[ 'user_id' ];
			return true;
		}
		// check for 'remember me'
		else if( isset( $_COOKIE[ 'persistent' ] ) )
		{
			$cookieParams = explode( '!-!', $_COOKIE[ 'persistent' ] );
			$uid = Database::select( 'Users', 'uid', array( 'where' => array( 'user_email' => $cookieParams[ 0 ] ), 'single' => true ) );
			if( Database::numrows() == 1 )
			{ // check if the email has changed, if it has changed persistent sessions are no longer valid
				$email = $cookieParams[ 0 ];
				$series = $cookieParams[ 1 ];
				$seriesEnc = encryptPassword( $cookieParams[ 1 ] );
				$token = $cookieParams[ 2 ];
				$tokenEnc = encryptPassword( $cookieParams[ 2 ] );
				$tokenDB = Database::select( 'Persistent_Sessions', 'token', array( 'where' => array( 'user_email' => $email, 'created > ' . (time() - 3600*24*30*3), 'series' => $seriesEnc ), 'single' => true ) );
				if( Database::numrows() == 1 && $cookieParams[3] == $_SERVER[ 'HTTP_USER_AGENT' ] )
				{ // so good, so far
					if( $tokenDB == $tokenEnc )
					{ // we have a persistent session
						// update the token
						Database::delete( 'Persistent_Sessions', array( 'user_email' => $email, 'series' => $seriesEnc, 'token' => $tokenEnc ) );
						
						$newToken = $this->generateToken();
						Database::insert( 'Persistent_Sessions', array( 'user_email' => $email, 'series' => $seriesEnc, 'token' => encryptPassword( $newToken ), 'created' => time() ) );
						setcookie( 'persistent', $email . '!-!' . $series . '!-!' . $newToken . '!-!' . $_SERVER[ 'HTTP_USER_AGENT' ], time() + 3600*24*30*3, '/', $_SERVER[ 'HTTP_HOST' ], false, true );
						
						$this->id = $uid;
						$this->changeSessionUserID( $uid );
						$_SESSION[ 'user_agent' ] = $_SERVER[ 'HTTP_USER_AGENT' ];
						$_SESSION[ 'persistent' ] = true;

						// create an entry in the login history table
						Database::insert(
							'User_Login_History',
							array(
								'uid' => $this->id,
								'timestamp' => time(),
								'type' => 0, // regular (0) or FB (1)
								'ip' => $_SERVER['REMOTE_ADDR'] ) );						
						
						return true;
					}
					else
					{ // same series, but different token.
					// the user is trying to use an older token
					// most likely an attack
						Database::delete( 'Persistent_Sessions', array( 'user_email' => $email ) ); // flush all sessions
					}
				}
			}
		}

		$this->id = -1; // guest
		$_SESSION[ 'user_id' ] = -1;
		return false;
	}
		
	private function changeSessionUserID( $newId )
	{
		// regenerate session id to prevent session hijacking
		session_regenerate_id();
		
		// set the user id
		$_SESSION[ 'user_id' ] = $newId;
	}
	
	private function generateToken()
	{
		$str='';
		for ($i=0; $i<16; $i++)
			$str.=base_convert(mt_rand(1,36),10,36);
		return $str;
	}
}