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
 
namespace nfuse\models;

use \nfuse\Database as Database;
use \nfuse\Modules as Modules;
use \nfuse\ErrorStack as ErrorStack;
use \nfuse\libs\Validate as Validate;
use \nfuse\Config as Config;
use \nfuse\Messages as Messages;

class User extends \nfuse\Model
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
			'filter' => '<a href="/users/{uid}" target="_blank">{uid}</a>'
		),
		array(
			'title' => 'User Email',
			'name' => 'user_email',
			'type' => 2,
			'filter' => '<a href="mailto:{user_email}">{user_email}</a>',
			'validation' => array('\nfuse\libs\Validate','email'),
			'required' => true
		),
		array(
			'title' => 'First Name',
			'name' => 'first_name',
			'type' => 2,
			'validation' => array('\nfuse\libs\Validate','firstName'),
			'required' => true
		),
		array(
			'title' => 'Last Name',
			'name' => 'last_name',
			'type' => 2,
			'validation' => array('\nfuse\libs\Validate','lastName')
		),
		array(
			'title' => 'User Password',
			'name' => 'user_password',
			'type' => 7,
			'validation' => array('\nfuse\libs\Validate','password'),
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
			'title' => 'Enabled',
			'name' => 'enabled',
			'type' => 4,
			'validation' => array('\nfuse\libs\Validate','boolean_'),
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
			'validation' => array('\nfuse\libs\Validate','timeZone'),
			'required' => true			
		)
	);

	/////////////////////////////////////
	// Private Class Variables
	/////////////////////////////////////

	private $logged_in;
	private static $currentUser;
	
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
	
	static function currentUser()
	{
		if( !self::$currentUser )
			self::$currentUser = new User( -1, true );
		
		return self::$currentUser;
	}
	
	/////////////////////////////////////
	// GETTERS
	/////////////////////////////////////
	
	function can( $permission, $requester = null )
	{
		if( !$requester )
			$requester = self::currentUser();
		
		// allow user registrations
		if( $permission == 'create' && !$requester->isLoggedIn() ) {
			return true;
		} else if( $permission == 'edit' && $requester->id() == $this->id() ) {
			return true;
		}

		return parent::can( $permission, $requester );
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
					'link_type' => 2 // 0 = forgot, 1 = verify, 2 = temporary
				),
				'single' => true ) );
	}
	
	/**
	* Checks if the user is logged in
	* @return boolean true if logged in
	*/
	function isLoggedIn()
	{
		return $this->logged_in;
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
	function isPublic()
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
	function isAdmin()
	{
		return $this->isMemberOf( ADMIN );
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
		Modules::load( 'groups' );
		
		// everyone
		$return = array( new Group(-1) );
		
		$gids = Database::select(
			'Group_Members',
			'gid',
			array(
				'where' => array(
					'uid' => $this->id ),
				'fetchStyle' => 'singleColumn' ) );
		foreach( $gids as $gid )
			$return[] = new Group( $gid );
		
		$uid = -1;
		if( $inRelationTo instanceof User )
			$uid = $inRelationTo->id();
		else if( is_numeric( $inRelationTo ) )
			$uid = $inRelationTo;
		
		// friends
		if( $this->isMemberOf( 2, $uid ) )
			$return[] = new Group( 2 );
			
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
		
		return Database::select(
			'Group_Members',
			'count(*)',			
			array(
				'where' => array(
					'gid' => $gid,
					'uid' => $this->id ),
				'single' => true ) ) == 1;
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
		if( $this->id > 0 )
			return 'http://' . Config::value( 'site', 'host-name' ) . '/users/' . seoUrl( $this->name(true), $this->id );
		
		return false;
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
	
	static function getTemporaryUser( $email )
	{
		Modules::load( 'Validation' );
		
		if( !\nfuse\libs\Validate::email( $email, array( 'skipRegisteredCheck' => true ) ) )
			return false;
	
		$uid =  Database::select(
			'Users NATURAL JOIN User_Links',
			'uid',
			array(
				'where' => array(
					'user_email' => $email,
					'link_type = 1 OR link_type = 2' ),
			'single' => true ) );

		if( Database::numrows() == 1 )
			return new User( $uid );
		else
			return false;
	}
	
	static function emailTaken( $email )
	{
		$uid = (isset($this) && get_class($this) == __CLASS__) ? 'uid <> ' . $this->id : '';
		
		return Database::select(
			'Users',
			'count(uid)',
			array(
				'where' => array(
					'user_email' => $email,
					$uid ),
				'single' => true ) ) > 0;
	}
	
	static function usernameTaken( $username )
	{
		$uid = (isset($this) && get_class($this) == __CLASS__) ? 'uid <> ' . $this->id : '';

		return Database::select(
			'Users',
			'count(*)',
			array(
				'where' => array(
					'user_name' => $username,
					$uid ),
				'single' => true ) ) > 0;
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
		ErrorStack::setContext( 'create' );
		
		$data[ 'ip' ] = $_SERVER[ 'REMOTE_ADDR' ];
		$data[ 'registered_timestamp' ] = time();

		$user = parent::create( $data );
		
		if( $user )
		{
			if( !$verifiedEmail )
			{
				// verify key
				$verify = md5(uniqid( rand(), true ));
			
				// create the verification link
				Database::insert(
					'User_Links',
					array(
						'uid' => $user->id(),
						'link_type' => 1, // 0 = forgot, 1 = verify, 2 = temporary
						'link' => $verify,
						'link_timestamp' => time() ) );
				
				// ask the user to verify their e-mail
				$user->sendEmail( 'email-verification', array( 'verify' => $verify ) );
			}
			else
				// send the user a welcome message
				$user->sendEmail( 'registration-welcome', array() );
		}
		
		return $user;
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
					'link_type' => 2, // 0 = forgot, 1 = verify, 2 = temporary
					'link' => $temporary,
					'link_timestamp' => time() ) );
			
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
		ErrorStack::setContext( 'edit' );
		
		$params = array();
		$protectedFields = array( 'user_email', 'user_password' );
		
		// check if the current password is accurate
		$passwordValidated = false;
		$passwordRequired = false;
		
		if( isset( $data[ 'current_password' ] ) ) {
			if( encryptPassword( $data[ 'current_password' ] ) == $this->getProperty( 'user_password' ) || self::$currentUser->isAdmin() )
				$passwordValidated = true;
		}

		foreach( $data as $key => $value ) {			
			if( self::hasProperty( $key ) ) {
				if( in_array( $key, $protectedFields ) ) {
					if (strlen(implode((array)$value)) == 0)
						continue;
					
					$passwordRequired = true;
				}			
			
				$params[ $key ] = $value;
			}
		}
		
		if( $passwordRequired && !$passwordValidated ) {
			ErrorStack::add( 'Oops, looks like the password is incorrect.', __CLASS__, 'current-password' );
			return false;
		}
		
		return parent::edit( $params );
	}
	
	/**
 	 * Upgrades the user from temporary to a fully registered account
	 *
	 * @param string $name name
	 * @param array $password password
	 *
	 * @return boolean true if successful
	 */
	function upgradeFromTemporary( $name, $password )
	{
		\nfuse\ErrorStack::setContext( 'user-register' );
		
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
		
		if( !$encryptedPassword = Validate::password( $password ) )
			$validated = false;

		return $validated &&
			Database::update(
				'Users',
				array(
					'uid' => $this->id,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'user_password' => $encryptedPassword,
					'registered_timestamp' => time(),
					'ip' => $_SERVER[ 'REMOTE_ADDR' ],
					'enabled' => 1 ),
				array( 'uid' ) ) &&
			Database::delete(
				'User_Links',
				array(
					'uid' => $this->id,
					'link_type = 1 OR link_type = 2' ) );
	}
	
	/**
	* Processes a verify e-mail hash
	*
	* @param string $verify verification hashflo
	*
	* @return boolean success
	*/
	static function verifyEmail( $verify )
	{
		$uid = Database::select(
			'User_Links',
			'uid',
			array(
				'where' => array(
					'link' => $verify,
					'link_type' => 1 // 0 = forgot, 1 = verify, 2 = temporary
				),
				'single' => true ) );

		// enable the user and delete the verify link
		if(
			Database::numrows() == 1 &&
			Database::update(
				'Users',
				array(
					'uid' => $uid,
					'enabled' => 1 ),
				array( 'uid' ) ) &&
			Database::delete(
				'User_Links',
				array(
					'uid' => $uid,
					'link_type' => 1 ) ) ) {
			
			// log the user in
			self::$currentUser = new User( $uid, false, true );
			
			// send a welcome e-mail
			self::$currentUser->sendEmail( 'registration-welcome' );
			
			return true;
		}
			
		return false;
	}
	
	/**
	 * The first step in the forgot password sequence
	 *
	 * @param string $email e-mail address
	 *
	 * @return boolean success?
	 */
	static function forgotStep1( $email )
	{
		Modules::load( 'validation' );
		
		ErrorStack::setContext( 'forgot' );
		
		if( Validate::email( $email, array( 'skipRegisteredCheck' => true ) ) )
		{
			$uid = Database::select(
				'Users',
				"uid",
				array(
					'where' => array(
						'user_email' => $email ),
					'single' => true ) );

			if( Database::numrows() == 1 )
			{
				$user = new User( $uid );
				$user->loadProperties();
							
				// Generate a forgot password link guid
				$guid = str_replace( '-', '', guid() );

				if( Database::insert(
					'User_Links',
					array(
						'uid' => $uid,
						'link_type' => 0, // 0 = forgot, 1 = verify, 2 = temporary
						'link' => $guid,
						'link_timestamp' => time() ) ) )
				{
					// send the user the forgot link
					$user->sendEmail(
						'forgot-password',
						array(
							'ip' => $_SERVER[ 'REMOTE_ADDR' ],
							'forgot' => $guid ) );
					
					return true;
				}
			}
			else
			// Could not make a match.
				ErrorStack::add( Messages::USER_FORGOT_EMAIL_NO_MATCH, '\nfuse\libs\Validate', 'email' );
		}

		ErrorStack::clearContext();		
		
		return false;
	}	
	
	/**
	 * Step 2 in the forgot password process. Resets the password with a valid token.
	 *
	 * @param string $token token
	 * @param array $password new password
	 * 
	 * @return boolean success
	 */
	static function forgotStep2( $token, $password )
	{
		// set the context
		\nfuse\ErrorStack::setContext( 'forgot' );
			
		if( !$uid = Database::select(
			'User_Links',
			'uid',
			array(
				'where' => array(
					'link' => $token,
					'link_type' => 0, // 0 = forgot, 1 = verify, 2 = temporary
					'link_timestamp > ' . strtotime( '-30 minutes' ) ),
				'single' => true ) ) ) {
			ErrorStack::add( Messages::USER_FORGOT_EXPIRED_INVALID );
			return false;
		}
		
		$user = new User( $uid );		
		
		// Validate the password
		if( !Validate::password( $password ) )
			return false;

		// Update the password
		return Database::update(
			'Users',
			array(
				'uid' => $user->id,
				'user_password' => $password ),
			array( 'uid' ) ) &&
			Database::delete(
				'User_Links',
				array(
					'uid' => $user->id,
					'link_type' => 0 ) );
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
			
		\nfuse\ErrorStack::setContext('login');
			
		if( empty( $email ) ) // Validate the email.
		{
			// TODO: update this message
			\nfuse\ErrorStack::add( \nfuse\Messages::USER_BAD_EMAIL, __CLASS__, __FUNCTION__ );
			return false;
		}

		if( empty( $password ) && $fbid == 0 ) // Validate the password.
		{
			\nfuse\ErrorStack::add( \nfuse\Messages::USER_BAD_PASSWORD, __CLASS__, __FUNCTION__ );
			return false;
		}
		
		$verifyTimeWindow = time() - 3600*24;
		
		// Query the Database.
		if( $fbid > 0 )
			$userInfo = Database::select(
				'Users AS u',
				'uid, user_email, enabled',
				array(
					'where' => array(
						'user_email' => $email,
						'fbid' => $fbid,
						'NOT EXISTS ( SELECT uid FROM User_Links AS l WHERE link_type = 1 AND u.uid = l.uid AND l.link_timestamp < ' . $verifyTimeWindow . ' )', // 0 = forgot, 1 = verify, 2 = temporary
						'NOT EXISTS ( SELECT uid FROM User_Links AS l WHERE link_type = 2 AND u.uid = l.uid )' // 0 = forgot, 1 = verify, 2 = temporary
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
						'NOT EXISTS ( SELECT uid FROM User_Links AS l WHERE link_type = 1 AND u.uid = l.uid AND l.link_timestamp < ' . $verifyTimeWindow . ' )', // 0 = forgot, 1 = verify, 2 = temporary
						'NOT EXISTS ( SELECT uid FROM User_Links AS l WHERE link_type = 2 AND u.uid = l.uid )' // 0 = forgot, 1 = verify, 2 = temporary
					),
					'singleRow' => true ) );

		if( Database::numrows() == 1 )
		{ // A match was made.
			$banned = false; // TODO: user bans
			if( $userInfo[ 'enabled' ] != 1 || $banned ) // check if disabled or banned
			{
				\nfuse\ErrorStack::add( \nfuse\Messages::USER_LOGIN_BANNED, __CLASS__, __FUNCTION__ );
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
			\nfuse\ErrorStack::add( \nfuse\Messages::USER_LOGIN_NO_MATCH, __CLASS__, __FUNCTION__ );
			return false;
		}
	}
	
	/**
	* Logs the user out
	* @return boolean true if successful
	*/
	function logout()
	{
		if( $this->isLoggedIn() )
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
	 * @param string $password
	 *
	 * @return boolean success?
	 */
	function deleteConfirm( $password )
	{
		ErrorStack::setContext( 'delete' );
	
		// check for the confirm and password
		// only the current user can delete their account
		if( $this->id > 1 && self::currentUser()->id() == $this->id && encryptPassword( $password ) == $this->getProperty( 'user_password' ) )
		{
			// delete the user
			Database::delete(
				'Users',
				array(
					'uid' => $this->id ) );
			
			// logout the user
			$this->logout();
			
			return true;
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
		
		$details[ 'message' ] = $message;
		$details[ 'user' ] = $this;
		$details[ 'baseUrl' ] = ((Config::value( 'site', 'ssl-enabled' )) ? 'https://' : 'http://') . Config::value( 'site', 'host-name' ) . '/';
		$details[ 'siteEmail' ] = Config::value( 'site', 'email' );

		switch ($message)
		{
		case 'registration-welcome':
			$subject = 'Welcome to ' . Config::value( 'site', 'title' );
		break;
		case 'email-verification':
			$subject = 'Please validate your e-mail address';
			$details[ 'verifyLink' ] = "{$details['baseUrl']}users/verifyEmail/{$details['verify']}";
		break;
		case 'forgot-password':
			$subject = 'Password change request on ' . Config::value( 'site', 'title' );
			$details[ 'forgotLink' ] = "{$details['baseUrl']}users/forgot/{$details['forgot']}";
		break;
		default:
			return false;
		break;
		}
				
		try
		{
			ob_start();
			
			// load the Mail module
			Modules::load( 'mail' );
			$mail = new \nfuse\libs\Mail;
			
			// basic e-mail info
			$mail->From = SMTP_FROM_ADDRESS;
			$mail->FromName = Config::value( 'site', 'title' );
			$mail->Subject = $subject;
			
			// generate the body
			$engine = \nfuse\ViewEngine::engine();
			$engine->assignData( $details );
			$body = $engine->fetch( Modules::$moduleDirectory . 'users/templates/emails.tpl' );
		
			// text body
			$mail->AltBody = $body;
			
			// html body
			$mail->MsgHTML( nl2br($body) );
			
			// send it to the user
			$mail->AddAddress( $this->getProperty( 'user_email' ) );
			
			// send the e-mail
			$success = $mail->Send();
			
			$errors = ob_get_contents();
			ob_end_clean();
			
			if( $errors )
			{
				\nfuse\ErrorStack::add( $errors, __CLASS__, __FUNCTION__ );
				return false;
			}
			else
				return $success;
		}
		catch( Exception $ex )
		{
			ErrorStack::add( $ex->getMessage(), __CLASS__, __FUNCTION__ );
			return false;
		}
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

			if( !$this->registered() ) {
				$this->id = -1;
				$_SESSION[ 'user_id' ] = -1;
				return false;
			}
			
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