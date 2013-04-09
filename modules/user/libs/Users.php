<?php
/**
 * This class provides support and management of users.
 *
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
class Users
{		
	////////////////////////////
	// SETTERS
	////////////////////////////
	
	/**
	* Processes a verify e-mail hash
	*
	* @param string $verify verification hash
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
					'type' => 1 // 0 = forgot, 1 = verify, 2 = temporary
				),
				'single' => true ) );
				
		if( Database::numrows() == 1 )
			// enable the user and delete the link
			return Database::update(
				'Users',
				array(
					'uid' => $uid,
					'enabled' => 1 ),
				array( 'uid' ) ) && Database::delete( 'User_Links', array( 'uid' => $uid, 'type' => 1 ) );
			
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
		Modules::load( 'Validation' );
		
		ErrorStack::setContext( 'user-forgot' );
		
		if( Validate::email( $email, false ) )
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
				$user->loadInfo();
			
				// Generate a forgot password link guid
				$guid = str_replace( '-', '', guid() );

				if( Database::insert(
					'User_Links',
					array(
						'uid' => $uid,
						'type' => 0, // 0 = forgot, 1 = verify, 2 = temporary						
						'link' => $guid,
						'timestamp' => time() ) ) )
				{
					// send the user the forgot link
					$user->sendEmail( 'forgot-password', array( 'ip' => $_SERVER[ 'REMOTE_ADDR' ], 'forgot_link' => 'https://' . Config::value( 'site', 'host-name' ) . '/user/forgot/' . $uid . '?t=' . $guid ) );
					
					return true;
				}
			}
			else
			// Could not make a match.
				ErrorStack::add( Messages::USER_FORGOT_EMAIL_NO_MATCH, 'Validate', 'email' );
		}

		ErrorStack::clearContext();		
		
		return false;
	}
}