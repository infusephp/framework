<?php
/*
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

class Messages
{
	static $messages = array();

	const SUCCESS = 'Success!';
	// Users
	const USER_LOGIN_FIRST = '<strong>Slow down!</strong> You must first login or register to begin viewing, saving, and sharing lists.';
	const USER_BAD_EMAIL = 'Please enter a valid e-mail address.';
	const USER_BAD_PASSWORD = 'Please enter a valid password.';
	const USER_LOGIN_NO_MATCH = 'We could not find a match for that email address and password.';
	const USER_LOGIN_BANNED = 'Sorry, your account has been banned or disabled.';
	const USER_NO_LISTS = 'This user has not shared any lists with you.';
	const USER_FORGOT_PASSWORD_SUCCESS = '<strong>Success!</strong> Your password has been changed.';
	const USER_FORGOT_EMAIL_NO_MATCH = 'We could not find a match for that e-mail address.';
	const USER_FORGOT_EXPIRED_INVALID = 'This link has expired or is invalid.';
	// Validation
	const VALIDATE_INVALID_EMAIL_ADDRESS = 'Please enter a valid e-mail address.';
	const VALIDATE_INVALID_NAME = 'Please enter a valid name.';
	const VALIDATE_EMAIL_ADDRESS_REGISTERED = 'The supplied e-mail address belongs to a previously registered user.';
	const VALIDATE_EMAIL_ADDRESS_BANNED  = 'This e-mail address has been banned.';
	const VALIDATE_INVALID_USER_NAME = 'Please enter a valid user name.';
	const VALIDATE_USER_NAME_REGISTERED = 'The supplied user name belongs to a previously registered user.';
	const VALIDATE_INVALID_PASSWORD = 'Please enter a password at least {{1}} characters long.';
	const VALIDATE_PASSWORD_NOT_MATCHING = 'The two passwords do not match.';
	

	static function error( $message, $close = false )
	{
		return '<div class="alert alert-error">' . ( ($close)?'<button class="close" data-dismiss="alert" type="button">&times;</button>':'') . $message . '</div>';	
	}
	
	static function warning( $message, $close = false )
	{
		return '<div class="alert alert-warning">' . ( ($close)?'<button class="close" data-dismiss="alert" type="button">&times;</button>':'') . $message . '</div>';	
	}
	
	static function success( $message, $close = false )
	{
		return '<div class="alert alert-success">' . ( ($close)?'<button class="close" data-dismiss="alert" type="button">&times;</button>':'') . $message . '</div>';	
	}
	
	static function info( $message, $close = false )
	{
		return '<div class="alert alert-info">' . ( ($close)?'<button class="close" data-dismiss="alert" type="button">&times;</button>':'') . $message . '</div>';	
	}
	
	static function generateMessage( $message, $variables = array() )
	{
		$i = 1;
		foreach( (array)$variables as $variable )
		{
			$message = str_replace( '{{' . $i . '}}', $variable, $message );
			$i++;
		}

		return $message;
	}
}
