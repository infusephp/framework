<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */
 
namespace app\groups;

use app\groups\models\Group;

class Controller extends \infuse\Acl
{
	public static $properties = array(
		'title' => 'Groups',
		'description' => 'Admin panel for managing user groups',
		'version' => '1.0',
		'author' => array(
			'name' => 'Jared King',
			'email' => 'j@jaredtking.com',
			'website' => 'http://jaredtking.com'
		),
		'scaffoldAdmin' => true,
		'models' => array(
			'Group',
			'GroupMember'
		)
	);
}