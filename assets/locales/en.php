<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

return array(
	'phrases' => array(
		/* Generic */
		'success' => 'Success!',
		'no_permission' => 'You do not have permission to do that',

		/* Users */
		'user_bad_email' => 'Please enter a valid e-mail address.',
		'user_bad_username' => 'Please enter a valid username.',
		'user_bad_password' => 'Please enter a valid password.',
		'user_login_no_match' => 'We could not find a match for that email address and password.',
		'user_login_banned' => 'Sorry, your account has been banned or disabled.',
		'user_login_temporary' => 'It looks like your account has not been setup yet. Please go to sign up to finish creating your account.',
		'user_login_unverified' => 'You must verify your account with the e-mail that was sent to you before you can log in.',
		'user_forgot_password_success' => '<strong>Success!</strong> Your password has been changed.',
		'user_forgot_email_no_match' => 'We could not find a match for that e-mail address.',
		'user_forgot_expired_invalid' => 'This link has expired or is invalid.',
		'invalid_password' => 'Oops, looks like the password is incorrect.',
		
		/* Validation */
		'validation_failed' => '{{field_name}} is invalid',
		'required_field_missing' => '{{field_name}} missing',
		'not_unique' => '{{field_name}} has already been used',
		'email_address_banned' => 'This e-mail address has been banned.',
		'user_name_banned' => 'This user name has been banned.',
		'passwords_not_matching' => 'The two passwords do not match.',

		/* Custom */
		
	)
);