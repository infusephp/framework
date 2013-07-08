<?php
/*
 * @package Infuse
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

namespace infuse;

class Messages
{
	private static $messages = array(
	
	/* Generic */
	'success' => 'Success!',
	'no_permission' => 'You do not have permission to do that',

	/* Users */
	'user_bad_email' => 'Please enter a valid e-mail address.',
	'user_bad_password' => 'Please enter a valid password.',
	'user_login_no_match' => 'We could not find a match for that email address and password.',
	'user_login_banned' => 'Sorry, your account has been banned or disabled.',
	'user_login_temporary' => 'It looks like your account has not been setup yet. Please go to sign up to finish creating your account.',
	'user_login_unverified' => 'You must verify your account with the e-mail that was sent to you before you can log in.',
	'user_forgot_password_success' => '<strong>Success!</strong> Your password has been changed.',
	'user_forgot_email_no_match' => 'We could not find a match for that e-mail address.',
	'user_forgot_expired_invalid' => 'This link has expired or is invalid.',
	
	/* Validation */
	'validation_failed' => '{{field_name}} is invalid',
	'required_field_missing' => '{{field_name}} missing',
	'not_unique' => '{{field_name}} has already been used',
	'email_address_banned' => 'This e-mail address has been banned.',
	'user_name_banned' => 'This user name has been banned.',
	'passwords_not_matching' => 'The two passwords do not match.',

	/* Custom */
	
	);
	
	static function get( $key, $params = array() )
	{
		$key = strtolower( $key );
		
		$message = (isset(self::$messages[$key])) ? self::$messages[$key] : '';

		foreach( (array)$params as $param => $value )
			$message = str_replace( '{{' . $param . '}}', $value, $message );

		return $message;
	}
}
