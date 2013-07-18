<?php

/**
 * Base representation of a user
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
 
namespace infuse\models;

use \infuse\Database;
use \infuse\Modules;
use \infuse\ErrorStack;
use \infuse\Validate;
use \infuse\Config;
use \infuse\Messages;
use \infuse\Util;
use \infuse\Logger;

abstract class AbstractUser extends \infuse\Model
{
	/////////////////////////////////////
	// Model Properties
	/////////////////////////////////////
		
	public static $idProperty = 'uid';
	
	protected static $escapedFields = array();
	
	public static $properties = array(
		'uid' =>  array(
			'type' => 'id'
		),
		'user_email' => array(
			'type' => 'text',
			'filter' => '<a href="mailto:{user_email}">{user_email}</a>',
			'validate' => 'email',
			'required' => true,
			'unique' => true,
			'title' => 'E-mail'
		),
		'user_password' => array(
			'type' => 'password',
			'length' => 128,
			'validate' => 'matching|password:8',
			'required' => true,
			'title' => 'Password'
		),
		'first_name' => array(
			'type' => 'text',
			'validate' => 'string:1',
			'required' => true
		),
		'last_name' => array(
			'name' => 'last_name',
			'type' => 'text'
		),
		'ip' => array(
			'type' => 'text',
			'filter' => '<a href="http://www.infobyip.com/ip-{ip}.html" target="_blank">{ip}</a>',
			'required' => true,
			'length' => 16
		),
		'registered_on' => array(
			'type' => 'date',
			'validate' => 'timestamp',
			'required' => true,
			'no_wrap' => true,
			'default' => 'today'
		),
		'enabled' => array(
			'type' => 'boolean',
			'validate' => 'boolean',
			'required' => true,
			'default' => true
		)	
	);
					
	/////////////////////////////////////
	// Protected Class Variables
	/////////////////////////////////////

	protected $logged_in;
	
	protected static $currentUser;
	
	protected static $verifyTimeWindow = 86400; // one day
	
	protected static $forgotLinkTimeframe = 1800; // 30 minutes
	
	protected static $usernameProperties = array( 'user_email' );	
	
	/**
	* Constructor
	* @param int $id id
	* @param boolean $check_logged_in check if the user is logged in
	*/
	function __construct( $id = false, $check_logged_in = false, $logged_in = false )
	{
		if( is_numeric( $id ) )
			$this->id = $id;
		else if( $id )
		{
			$exp = explode( '-', $id );
			$last = end($exp);
			if( is_numeric($last) )
				$this->id = $last;
			else
				$this->id = -1;
		}
			
		if( $logged_in && $this->id > 0 )
			$this->logged_in = true;
		else if( $check_logged_in )
			$this->logged_in = $this->logged_in_();
	}
	
	static function currentUser()
	{
		if( !static::$currentUser )
			static::$currentUser = new User( -1, true );
		
		return static::$currentUser;
	}
	
	/////////////////////////////////////
	// GETTERS
	/////////////////////////////////////
	
	function can( $permission, $requester = null )
	{
		if( !$requester )
			$requester = static::currentUser();
		
		// allow user registrations
		if( $permission == 'create' && !$requester->isLoggedIn() )
			return true;
		else if( $permission == 'edit' && $requester->id() == $this->id )
			return true;

		return parent::can( $permission, $requester );
	}
		
	/**
	* Gets the temporary string if the user is temporary
	*
	* @return link|false true if temporary
	*/
	function isTemporary()
	{
		return UserLink::totalRecords( array( 'uid' => $this->id, 'link_type' => 2 ) ) > 0;
	}
	
	/**
	* Checks if the account has been verified
	*
	* @param boolean $withinTimeWindow when true, allows a time window before the account is considered unverified
	*
	* @return boolean
	*/
	function isVerified( $withinTimeWindow = true )
	{
		$timeWindow = ( $withinTimeWindow ) ? time() - static::$verifyTimeWindow : time() + 100;
		
		return UserLink::totalRecords( array(
			'uid' => $this->id,
			'link_type' => 1,
			'link_timestamp < ' . $timeWindow ) ) == 0;
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
	* Get's the user's name
	*
	* @param boolean $full get full name if true
	*
	* @return string name
	*/
	function name( $full = false )
	{
		if( $this->id == -1 )
			return 'Guest';
		else
		{
			if( !$this->exists() )
				return '(not registered)';
			
			return $this->get( 'user_email' );
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
		
		// super user
		if( $this->id === SUPER_USER )
			return array( new Group( ADMIN ) );

		// everyone
		$return = array( new Group(-1) );
		
		$gids = Database::select(
			'GroupMembers',
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
		if( $uid && $this->isMemberOf( 2, $uid ) )
			$return[] = new Group( 2 );
						
		return $return;
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
		/* special cases */

		// super user
		if( $this->id === SUPER_USER )
			return true;
		
		// evreryone
		if( $gid == -1 )
			return true;
				
		/* database */
		
		return Database::select(
			'GroupMembers',
			'count(*)',
			array(
				'where' => array(
					'gid' => $gid,
					'uid' => $this->id ),
				'single' => true ) ) == 1;
	}
	
	/**
	* Gets the URL of the profile for the user
	*
	* @return string URL
	*/
	function url()
	{
		if( $this->id > 0 )
			return 'http://' . Config::value( 'site', 'host-name' ) . '/users/' . Util::seoUrl( $this->name(true), $this->id );
		
		return false;
	}
	
	/**
	* Generates the URL for the user's profile picture
	*
	* Gravatar is used for profile pictures. To accomplish this we need to generate a hash of the user's e-mail.
	*
	* @param int $size size of the picture (it is square, usually)
	*
	* @return string url
	*/
	function profilePicture( $size = 200 )
	{
		// use Gravatar
		$hash = md5( strtolower( trim( $this->get('user_email') ) ) );
		return "https://secure.gravatar.com/avatar/$hash?s=$size&d=mm";
	}
	
	/**
	 * Gets a temporary user from an e-mail address if one exists
	 *
	 * @param string $email e-mail address
	 *
	 * @return User|false
	 */
	static function getTemporaryUser( $email )
	{
		if( !Validate::is( $email, 'email' ) )
			return false;
		
		if( $user = User::findOne( array(
			'where' => array(
				'user_email' => $email ) ) ) )
		{
			if( $user->isTemporary() )
				return $user;
		}
			
		return false;
	}
		
	///////////////////////////////
	// SETTERS
	///////////////////////////////
	
	function preCreateHook( &$data )
	{
		$data[ 'ip' ] = $_SERVER[ 'REMOTE_ADDR' ];
		
		return true;
	}
	
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
		$user = parent::create( $data );
		
		if( $user )
		{
			if( !$verifiedEmail )
			{
				// create the verification link and send user an e-mail
				if( $link = UserLink::create( array( 'uid' => $user->id(), 'link_type' => 1 ) ) )
					$user->sendEmail(
						'email-verification',
						array( 'verify' => $link->get( 'link' ) ) );
			}
			else
				// send the user a welcome message
				$user->sendEmail( 'registration-welcome' );
		}
		
		return $user;
	}
	
	/**
	 * Creates a temporary user. Useful for creating invites.
	 *
	 * @param array $data user data
	 *
	 * @return User temporary user
	 */
	static function createTemporary( $data )
	{
		if( !Validate::is( $data[ 'user_email' ], 'email' ) )
			return false;

		$insertArray = array(
			'user_email' => $data[ 'user_email' ],
			'ip' => $_SERVER[ 'REMOTE_ADDR' ],
			'registered_on' => time(),
			'enabled' => 0
		);
		
		// create the temporary user
		if( Database::insert( 'Users', $insertArray ) )
		{
			$uid = Database::lastInsertID();
			
			// create the temporary link
			UserLink::create( array( 'uid' => $uid, 'link_type' => 2 ) );
			
			return new User( $uid );
		}
		else
			return false;	
	}	
	
	function preSetHook( &$data )
	{
		if( !is_array( $data ) )
			$data = array( $data => $value );
		
		$params = array();
		$protectedFields = array( 'user_email', 'user_password', 'username' );
		
		// check if the current password is accurate
		$passwordValidated = false;
		$passwordRequired = false;
		
		if( isset( $data[ 'current_password' ] ) )
		{
			if( Util::encryptPassword( $data[ 'current_password' ] ) == $this->get( 'user_password' ) || static::$currentUser->isAdmin() )
				$passwordValidated = true;
		}

		foreach( $data as $key => $value )
		{
			if( static::hasProperty( $key ) )
			{
				if( in_array( $key, $protectedFields ) )
				{
					if (strlen(implode((array)$value)) == 0)
					{
						unset( $data[ $key ] );
						continue;
					}
					
					$passwordRequired = true;
				}			
			
				$data[ $key ] = $value;
			}
		}
		
		if( $passwordRequired && !$passwordValidated && !static::currentUser()->isAdmin() )
		{
			ErrorStack::add( array( 'error' => 'invalid_password' ) );
			return false;
		}
		
		return true;
	}
	
	/**
 	 * Upgrades the user from temporary to a fully registered account
	 *
	 * @param array $data user data
	 *
	 * @return boolean true if successful
	 */
	function upgradeFromTemporary( $data )
	{
		if( !$this->isTemporary() )
			return true;
		
		$updateArray = array_replace( $data, array(
			'registered_on' => time(),
			'ip' => $_SERVER[ 'REMOTE_ADDR' ],
			'enabled' => 1 ) );
		
		$success = false;
		
		User::currentUser()->elevateToSuperUser();
		
		if( $this->set( $updateArray ) )
		{
			// remove temporary and unverified links
			Database::delete(
				'UserLinks',
				array(
					'uid' => $this->id,
					'link_type = 1 OR link_type = 2' ) );
			
			// send the user a welcome message
			$this->sendEmail( 'registration-welcome' );

			$success = true;
		}
		
		User::currentUser()->returnFromSuperUser();

		return $success;
	}
	
	/**
	 * Processes a verify e-mail hash
	 *
	 * @param string $verify verification hash
	 *
	 * @return User|false
	*/
	static function verifyEmail( $verify )
	{
		ErrorStack::setContext( 'user.verify' );
		
		if( $link = UserLink::findOne( array( 'where' => array( 'link' => $verify, 'link_type' => 1 ) ) ) )
		{
			$user = new User( $link->get( 'uid' ) );
		
			User::currentUser()->elevateToSuperUser();
			
			// enable the user and delete the verify link
			$success = $user->set( 'enabled', 1 ) && $link->delete();
			
			User::currentUser()->returnFromSuperUser();
			
			// send a welcome e-mail
			$user->sendEmail( 'registration-welcome' );
			
			return $user;
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
		ErrorStack::setContext( 'user.forgot' );
		
		if( Validate::is( $email, 'email' ) )
		{
			if( $user = User::findOne( array( 'where' => array( 'user_email' => $email ) ) ) )
			{
				$user->load();
				
				// make sure there are no other forgot links
				$oldLinks = UserLink::totalRecords( array(
					'link_type' => 0,
					'link_timestamp > ' . (time() - static::$forgotLinkTimeframe) ) );
				
				if( $oldLinks > 0 )
					return true;
				
				if( $link = UserLink::create( array( 'uid' => $user->id(), 'link_type' => 0 ) ) )
				{
					// send the user the forgot link
					$user->sendEmail(
						'forgot-password',
						array(
							'ip' => $_SERVER[ 'REMOTE_ADDR' ],
							'forgot' => $link->get( 'link' ) ) );
					
					return true;
				}
			}
			else
			// Could not make a match.
				ErrorStack::add( 'user_forgot_email_no_match' );
		}
		else
		{
			ErrorStack::add( array(
				'error' => VALIDATION_FAILED,
				'params' => array(
					'field' => 'email',
					'field_name' => 'Email' ) ) );
		}
		
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
		ErrorStack::setContext( 'user.forgot' );
		
		$link = UserLink::findOne( array(
			'where' => array(
				'link' => $token,
				'link_type' => 0,
				'link_timestamp > ' . (time() - static::$forgotLinkTimeframe) ) ) );
		
		if( $link )
		{
			$user = new User( $link->get( 'uid' ) );
			
			User::currentUser()->elevateToSuperUser();
			
			// Update the password
			$success = $user->set( 'user_password', $password );
			
			if( $success )
				$link->delete();

			User::currentUser()->returnFromSuperUser();
				
			return $success;
		}
		else
		{
			ErrorStack::add( 'user_forgot_expired_invalid' );
			return false;
		}
	}
	
	///////////////////////////////////
	// UTILITIES
	///////////////////////////////////
	
	/**
	 * Elevates the current user to super user status. This grants all permissions
	 * to everything. BE CAREFUL. Typically, this is reserved for cron jobs that need
	 * to work with models belonging to other users.
	 *
	 * WARNING: do not forget to remove super user permissions when done with returnFromSuperUser()
	 * or else the user will have free reign to do anything
	 */
	static function elevateToSuperUser()
	{
		static::currentUser();
		static::$currentUser->oldUid = static::$currentUser->id;
		static::$currentUser->id = SUPER_USER;
	}
	
	/**
	 * Removes super user permission.
	 *
	 */
	static function returnFromSuperUser()
	{
		static::currentUser();
		if( isset( static::$currentUser->oldUid ) )
			static::$currentUser->id = static::$currentUser->oldUid;
	}
	
	/**
	 * Checks if the a given username and password are valid
	 *
	 * @param string $username username
	 * @param string $password password
	 *
	 * @return User|false user if successful
	 */
	static function checkLogin( $username, $password )
	{
		if( empty( $username ) )
		{
			ErrorStack::add( 'user_bad_username' );
			return false;
		}

		if( empty( $password ) )
		{
			ErrorStack::add( 'user_bad_password' );
			return false;
		}
		
		// build the query string for the username
		$usernameWhere = '(' . implode( ' OR ', array_map( function( $prop, $username ) {
			return $prop . " = '" . $username . "'";
		}, static::$usernameProperties, array_fill( 0, count( static::$usernameProperties ), addslashes( $username ) ) ) ) . ')';
		
		// look the user up
		$user = static::findOne( array(
			'where' => array(
				$usernameWhere,
				'user_password' => Util::encryptPassword( $password ) ) ) );
		
		if( $user )
		{
			$user->load();
			
			// check if banned
			// TODO
			$banned = false;
						
			if( !$user->get( 'enabled' ) || $banned )
			{
				ErrorStack::add( 'user_login_banned' );
				return false;
			}
			// check if temporary
			else if( $user->isTemporary() )
			{
				ErrorStack::add( 'user_login_temporary' );
				return false;
			}
			// check if verified
			else if( !$user->isVerified() )
			{
				ErrorStack::add( 'user_login_unverified' );
				return false;
			}
			
			// success!
			return $user;
		}
		else
		{			
			ErrorStack::add( 'user_login_no_match' );
			return false;
		}

	}
	
	/**
	 * Performs a traditional login.
	 *
	 * @param string $username username
	 * @param string $password password
	 * @param boolean $remember remember me
	 * @param bool $setSessionVars when true sets the $_SESSION with user info
	 *
	 * @return boolean true if successful
	*/
	function login( $username, $password, $remember = false, $setSessionVars = true )
	{
		if( $this->logged_in )
			return true;
		
		ErrorStack::setContext( 'user.login' );
		
		if( $user = static::checkLogin( $username, $password ) )
			return $this->loginForUid( $user->id(), 0, $remember, $setSessionVars );
	}
	
	/**
	 * Logs in the user for a given uid.
	 * 
	 * This function is useful when logging in through multiple providers (oauth, fb, twitter).
	 * Please do not abuse this function.
	 *
	 * @param int $uid
	 * @param int $type an integer flag to denote the login type (regular = 0)
	 * @param boolean $persistent true if the user should be logged in for a very long time
	 * @param boolean $setSessionVars true if the login should be saved to sessions
	 *
	 * @return boolean
	 */
	function loginForUid( $uid, $type = 0, $persistent = false, $setSessionVars = true )
	{
		$this->id = $uid;
	
		if( $setSessionVars )
		{
			// update the session with the user's id
			$this->changeSessionUserID( $uid );
		
			// store the user agent
			$_SESSION[ 'user_agent' ] = $_SERVER[ 'HTTP_USER_AGENT' ];
		}

		if( $persistent )
		{
			$series = $this->generateToken();
			$token = $this->generateToken();
			setcookie( 'persistent', $this->get( 'user_email' ) . '!-!' . $series . '!-!' . $token . '!-!' . $_SERVER[ 'HTTP_USER_AGENT' ], time() + 3600*24*30*3, '/', $_SERVER[ 'HTTP_HOST' ], false, true );
			
			Database::insert(
				'Persistent_Sessions',
				array(
					'user_email' => $this->get( 'user_email' ),
					'series' => Util::encryptPassword( $series ),
					'token' => Util::encryptPassword( $token ),
					'created' => time()
				)
			);
		}

		// create an entry in the login history table
		Database::insert(
			'User_Login_History',
			array(
				'uid' => $uid,
				'timestamp' => time(),
				'type' => $type,
				'ip' => $_SERVER[ 'REMOTE_ADDR' ]
			)
		);

		$this->logged_in = true;
		
		return true;		
	}
	
	/**
	* Logs the user out
	* @return boolean true if successful
	*/
	function logout()
	{
		if( $this->isLoggedIn() )
		{
			Database::Delete( 'Persistent_Sessions', array( 'user_email' => $this->get( 'user_email' ) ) ); // Delete all persistent sessions
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
		ErrorStack::setContext( 'user.delete' );
	
		// check for the confirm and password
		// only the current user can delete their account
		if( $this->id > 1 && static::currentUser()->id() == $this->id && Util::encryptPassword( $password ) == $this->get( 'user_password' ) )
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
		$email = $this->getProperty( 'user_email' );
	
		$template = '';
	
		$subject = 'Message from ' . Config::value( 'site', 'title' );
		
		$details[ 'message' ] = $message;
		$details[ 'user' ] = $this;
		$details[ 'baseUrl' ] = ((Config::value( 'site', 'ssl-enabled' )) ? 'https://' : 'http://') . Config::value( 'site', 'host-name' ) . '/';
		$details[ 'siteEmail' ] = Config::value( 'site', 'email' );
		$details[ 'email' ] = $email;

		switch ($message)
		{
		case 'registration-welcome':
			$subject = 'Welcome to ' . Config::value( 'site', 'title' );
			$template = 'welcome';
		break;
		case 'email-verification':
			$subject = 'Please validate your e-mail address';
			$details[ 'verifyLink' ] = "{$details['baseUrl']}users/verifyEmail/{$details['verify']}";
			$template = 'email-verification';
		break;
		case 'forgot-password':
			$subject = 'Password change request on ' . Config::value( 'site', 'title' );
			$details[ 'forgotLink' ] = "{$details['baseUrl']}users/forgot/{$details['forgot']}";
			$template = 'forgot-password';
		break;
		default:
			return false;
		break;
		}
				
		try
		{
			ob_start();
			
			// setup the mailer
			$mail = new \PHPMailer;
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = 'tls';
			$mail->Host = Config::value( 'smtp', 'host' );
			$mail->Username = Config::value( 'smtp', 'username' );
			$mail->Password = Config::value( 'smtp', 'password' );
			$mail->Port = Config::value( 'smtp', 'port' );
			
			// basic e-mail info
			$mail->From = SMTP_FROM_ADDRESS;
			$mail->FromName = Config::value( 'site', 'title' );
			$mail->Subject = $subject;
			
			// generate the body
			$engine = \infuse\ViewEngine::engine();
			$engine->assignData( $details );
			$body = $engine->fetch( 'emails/' . $template . '.tpl' );
		
			// text body
			$mail->AltBody = $body;
			
			// html body
			$mail->MsgHTML( nl2br($body) );
			
			// send it to the user
			$mail->AddAddress( $email );
			
			// send the e-mail
			$success = $mail->Send();
			
			$errors = ob_get_contents();
			ob_end_clean();
			
			if( $errors )
			{
				ErrorStack::add( 'email_send_failure' );
				
				Logger::error( $errors );
				
				return false;
			}
			else
				return $success;
		}
		catch( Exception $ex )
		{
			ErrorStack::add( 'email_send_failure' );
			
			Logger::error( Logger::formatException( $ex ) );
			
			return false;
		}
	}
	
	/////////////////////////
	// PROTECTED FUNCTIONS
	/////////////////////////
	
	protected function logged_in_()
	{
		// check if the user's session is already logged in
		if( isset( $_SESSION[ 'user_agent' ] ) && $_SESSION[ 'user_agent' ] == $_SERVER[ 'HTTP_USER_AGENT' ] && isset( $_SESSION[ 'user_id' ] ) && $_SESSION[ 'user_id' ] > 0 )
		{
			$this->id = $_SESSION[ 'user_id' ];

			if( !$this->exists() ) {
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
				$seriesEnc = Util::encryptPassword( $cookieParams[ 1 ] );
				$token = $cookieParams[ 2 ];
				$tokenEnc = Util::encryptPassword( $cookieParams[ 2 ] );
				$tokenDB = Database::select( 'Persistent_Sessions', 'token', array( 'where' => array( 'user_email' => $email, 'created > ' . (time() - 3600*24*30*3), 'series' => $seriesEnc ), 'single' => true ) );
				if( Database::numrows() == 1 && $cookieParams[3] == $_SERVER[ 'HTTP_USER_AGENT' ] )
				{ // so good, so far
					if( $tokenDB == $tokenEnc )
					{ // we have a persistent session
						// update the token
						Database::delete( 'Persistent_Sessions', array( 'user_email' => $email, 'series' => $seriesEnc, 'token' => $tokenEnc ) );
						
						$newToken = $this->generateToken();
						Database::insert( 'Persistent_Sessions', array( 'user_email' => $email, 'series' => $seriesEnc, 'token' => Util::encryptPassword( $newToken ), 'created' => time() ) );
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
								'type' => 0, // regular = 0
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
		
	protected function changeSessionUserID( $newId )
	{
		// regenerate session id to prevent session hijacking
		session_regenerate_id();
		
		// set the user id
		$_SESSION[ 'user_id' ] = $newId;
	}
	
	protected function generateToken()
	{
		$str='';
		for ($i=0; $i<16; $i++)
			$str.=base_convert(mt_rand(1,36),10,36);
		return $str;
	}
}