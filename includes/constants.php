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

// site configuration
if( !defined('SITE_TITLE') )
	define ('SITE_TITLE', Config::value( 'site', 'title' ) );
if( !defined( 'HOST_NAME' ) )
	define( 'HOST_NAME', Config::value( 'site', 'host-name' ) );
if( !defined( 'ENABLE_SSL' ) )
	define( 'ENABLE_SSL', Config::value( 'site', 'ssl-enabled' ) );
if( !defined( 'SMTP_HOST' ) )
{
	define( 'SMTP_HOST', Config::value( 'smtp', 'host' ) );
	define( 'SMTP_USERNAME', Config::value( 'smtp', 'username' ) );
	define( 'SMTP_PASSWORD', Config::value( 'smtp', 'password' ) );
	define( 'SMTP_PORT', Config::value( 'smtp', 'port' ) );
	define( 'SMTP_FROM_ADDRESS', Config::value( 'smtp', 'from' ) );
}

define ('BASE_URL', urlPrefix() . Config::value( 'site', 'host-name' ) . '/' );

// user levels
define ('ANONYMOUS', -1);
define ('ADMIN', 1);

// error codes
define( 'ERROR_NO_PERMISSION', 'no_permission' );
define( 'ERROR_VALIDATION', 'validation_error' );