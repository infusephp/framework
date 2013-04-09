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

// load configuration
require_once "libs/Spyc.php";
require_once 'libs/Config.php';

Config::load( 'config.yml' );

// error reporting
if( Config::value( 'site', 'production-level' ) )
{
	ini_set("display_errors", 0);
	ini_set("log_errors", 1);
	ini_set("error_log", "syslog");
	error_reporting  (E_ERROR | E_WARNING | E_PARSE);
}
else
{
	ini_set( 'display_errors', 'On' );
	error_reporting(E_ALL);
}

// site messages: different files can be loaded for different languages
require_once "includes/lang/en/Messages.php";

// important libraries
require_once 'libs/ErrorStack.php';
require_once 'libs/Globals.php';
require_once "includes/functions.php";
require_once 'libs/Database.php';
require_once "libs/Acl.php";
require_once "libs/Model.php";
require_once "libs/Controller.php";
require_once 'libs/Page.php';
require_once "libs/Smarty/Smarty.class.php";
require_once "libs/Smarty_nFuse.php";
require_once "libs/Modules.php";

// start initializing stuff
require_once "includes/security.php";
require_once "includes/common.php";