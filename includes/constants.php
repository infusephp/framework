<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

use infuse\Config;
use infuse\Util;

/* site configuration */
if( !defined('SITE_TITLE') )
	define ('SITE_TITLE', Config::get( 'site.title' ) );
if( !defined( 'INFUSE_BASE_DIR' ) )
	define( 'INFUSE_BASE_DIR', dirname( __DIR__ ) );
define( 'INFUSE_APP_DIR', INFUSE_BASE_DIR . '/app' );
define( 'INFUSE_ASSETS_DIR', INFUSE_BASE_DIR . '/assets' );
define( 'INFUSE_PUBLIC_DIR', INFUSE_BASE_DIR . '/public' );
define( 'INFUSE_TEMP_DIR', INFUSE_BASE_DIR . '/temp' );
define( 'INFUSE_VIEWS_DIR', INFUSE_BASE_DIR . '/views' );

/* user levels */
define( 'SUPER_USER', -2 );
define( 'ANONYMOUS', -1 );
define( 'ADMIN', 1 );

/* error codes */
define( 'ERROR_NO_PERMISSION', 'no_permission' );
define( 'VALIDATION_FAILED', 'validation_failed' );
define( 'VALIDATION_REQUIRED_FIELD_MISSING', 'required_field_missing' );
define( 'VALIDATION_NOT_UNIQUE', 'not_unique' );

/* useful constants */
define( 'SKIP_ROUTE', -1 );

function unsetSessionVar( $param )
{
	unset( $_SESSION[ $param ] );
}