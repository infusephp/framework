<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.2
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
use \infuse\Util;

/* site configuration */
if( !defined('SITE_TITLE') )
	define ('SITE_TITLE', Config::get( 'site', 'title' ) );
if( !defined( 'SMTP_HOST' ) )
{
	define( 'SMTP_HOST', Config::get( 'smtp', 'host' ) );
	define( 'SMTP_USERNAME', Config::get( 'smtp', 'username' ) );
	define( 'SMTP_PASSWORD', Config::get( 'smtp', 'password' ) );
	define( 'SMTP_PORT', Config::get( 'smtp', 'port' ) );
	define( 'SMTP_FROM_ADDRESS', Config::get( 'smtp', 'from' ) );
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

/* some useful functions */
function val( $a = array(), $k = '' )
{
	return Util::array_value( $a, $k );
}

function print_pre($item)
{
	echo '<pre>' . print_r( $item, true ) . '</pre>';
}

function unsetSessionVar( $param )
{
	unset( $_SESSION[ $param ] );
}