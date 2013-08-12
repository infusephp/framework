<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.3
 * @copyright 2013 Jared King
 * @license MIT
 */
 
namespace infuse\controllers;
 
class Modulesinfo extends \infuse\Controller {
	public static $properties = array(
		'title' => 'Modules',
		'description' => 'Displays information about installed modules in admin dashboard',
		'version' => '1.0',
		'author' => array(
			'name' => 'Jared King',
			'email' => 'j@jaredtking.com',
			'website' => 'http://jaredtking.com'
		),
		'admin' => true,
		'api' => true,
		'model' => 'Module',
		'routes' => array(
		)
	);
}