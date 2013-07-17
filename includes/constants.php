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

use \infuse\Config;

/* site configuration */
if( !defined('SITE_TITLE') )
	define ('SITE_TITLE', Config::value( 'site', 'title' ) );
if( !defined( 'SMTP_HOST' ) )
{
	define( 'SMTP_HOST', Config::value( 'smtp', 'host' ) );
	define( 'SMTP_USERNAME', Config::value( 'smtp', 'username' ) );
	define( 'SMTP_PASSWORD', Config::value( 'smtp', 'password' ) );
	define( 'SMTP_PORT', Config::value( 'smtp', 'port' ) );
	define( 'SMTP_FROM_ADDRESS', Config::value( 'smtp', 'from' ) );
}
if( !defined( 'INFUSE_BASE_DIR' ) )
	define( 'INFUSE_BASE_DIR', dirname( __DIR__ ) );
define( 'INFUSE_APP_DIR', INFUSE_BASE_DIR . '/app' );
define( 'INFUSE_MODULES_DIR', INFUSE_BASE_DIR . '/modules' );
define( 'INFUSE_TEMP_DIR', INFUSE_BASE_DIR . '/temp' );
define( 'INFUSE_VIEWS_DIR', INFUSE_BASE_DIR . '/views' );

/* user levels */
define( 'SUPER_USER', -2 );
define( 'ANONYMOUS', -1 );
define( 'ADMIN', 1 );
define( 'CLI', 4 );

/* error codes */
define( 'ERROR_NO_PERMISSION', 'no_permission' );
define( 'VALIDATION_FAILED', 'validation_failed' );
define( 'VALIDATION_REQUIRED_FIELD_MISSING', 'required_field_missing' );
define( 'VALIDATION_NOT_UNIQUE', 'not_unique' );

/* acl */
define( 'ACL_RESULT_NOT_CACHED', -1 );
define( 'ACL_NO_ID', -1 );

/* some useful functions */
function val( $a = array(), $k = '' )
{
	return (array_key_exists($k, (array)$a)) ? $a[$k] : null;
}

function print_pre($item)
{
	echo '<pre>' . print_r( $item, true ) . '</pre>';
}

function unsetSessionVar( $param )
{
	unset( $_SESSION[ $param ] );
}

function json_decode_array($d)
{
	return json_decode($d, true);
}