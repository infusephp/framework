<?php
/**
 * This class represents a permission in the global ACL.
 *
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

namespace nfuse\models;

class Permission extends \nfuse\Model
{
	protected static $tablename = 'ACL';
	public static $properties = array(
		array(
			'title' => 'ID',
			'name' => 'id',
			'type' => 2
		),	
		array(
			'title' => 'Model',
			'name' => 'model',
			'type' => 2
		),
		array(
			'title' => 'Model ID',
			'name' => 'model_id',
			'type' => 2,
			'null' => true
		),	
		array(
			'title' => 'User',
			'name' => 'user',
			'type' => 2,
			'null' => true
		),	
		array(
			'title' => 'Group',
			'name' => 'group_',
			'type' => 2,
			'null' => true
		),	
		array(
			'title' => 'Permission',
			'name' => 'permission',
			'type' => 2
		)
	);
}