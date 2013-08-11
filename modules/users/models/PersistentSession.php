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

use \infuse\Util;

class PersistentSession extends \infuse\Model
{
/*
DROP TABLE IF EXISTS `Persistent_Sessions`;
CREATE TABLE `Persistent_Sessions` (`user_email` varchar(255) NOT NULL, `created` int(11) NOT NULL, `series` char(128) NOT NULL, `token` char(128) NOT NULL, PRIMARY KEY (`token`));
*/
	public static $idProperty = 'token';

	public static $properties = array(
		'token' => array(
			'type' => 'id',
			'mutable' => true,
			'required' => true,
			'auto_increment' => false,
			'db_type' => 'char',
			'length' => 128
		),
		'user_email' => array(
			'type' => 'text',
			'validate' => 'email'
		),
		'series' => array(
			'type' => 'text',
			'db_type' => 'char',
			'length' => 128,
			'required' => true,
			'validate' => 'string:128'
		),		
		'created' => array(
			'type' => 'date',
			'required' => true,
			'validate' => 'timestamp',
			'default' => 'today'
		)
	);
}