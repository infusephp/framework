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

use \infuse\Util as Util;

function val( $a = array(), $k = '' )
{
	return (array_key_exists($k, $a)) ? $a[$k] : null;
}

function print_pre($item)
{
	echo '<pre>';
	print_r($item);
	echo '</pre>';
}

function unsetSessionVar( $param )
{
	unset( $_SESSION[ $param ] );
}

function json_decode_array($d)
{
	return json_decode($d, true);
}

// TODO all of these have been deprecated and moved to Util

function toBytes($str)
{
	return Util::toBytes( $str );
}

function formatNumberAbbreviation($number, $decimals = 1)
{
	return Util::formatNumberAbbreviation( $number, $decimals );
}

function set_cookie_fix_domain($Name, $Value = '', $Expires = 0, $Path = '', $Domain = '', $Secure = false, $HTTPOnly = false)
{
	return Util::set_cookie_fix_domain( $Name, $Value, $Expires, $Path, $Domain, $Secure, $HTTPOnly );
}

function guid()
{
	return Util::guid();
}

function seoUrl( $string, $id = null )
{
	return Util::seoUrl( $string, $id );
}

function encryptPassword( $password, $nonce = '' )
{
	return Util::encryptPassword( $password, $nonce );
}

function get_tz_options($selectedzone, $name = 'time_zone')
{
	return Util::get_tz_options( $selectedzone, $name );
}
