<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.4
 * @copyright 2013 Jared King
 * @license MIT
 */
 
namespace infuse\models;

class StatisticSnapshot extends \infuse\Model
{
	public static $properties = array(
		'id' => array(
			'type' => 'id'
		),
		'timestamp' => array(
			'type' => 'date',
			'validate' => 'timestamp',
			'default' => 'today'
		),
		'stats' => array(
			'type' => 'longtext'
		)
	);
}