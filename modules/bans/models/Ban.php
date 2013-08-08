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
 
namespace infuse\models;

use \infuse\Database;

define( 'BAN_TYPE_IP', 1 );
define( 'BAN_TYPE_USERNAME', 2 );
define( 'BAN_TYPE_EMAIL', 3 );

class Ban extends \infuse\Model
{
	public static $properties = array(
		'id' => array(
			'type' => 'id'
		),
		'type' => array(
			'type' => 'enum',
			'enum' => array (
				1 => 'IP',
				2 => 'Username',
				3 => 'E-mail Address' ),
			'db_type' => 'tinyint',
			'length' => 1
		),
		'value' => array(
			'type' => 'text',
			'length' => 40
		),	
		'reason' => array(
			'type' => 'text'
		)
	);
	
	
	/**
	 * Checks if a value has been banned.
	 *
	 * @param string $value value
	 * @param int $type type
	 *
	 * @return boolean
	 */
	static function isBanned( $value, $type )
	{
		return Ban::totalRecords( array( 'type' => $type, 'value' => $value ) ) > 0;
	}
}