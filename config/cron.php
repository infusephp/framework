<?php

/**
 * @package infuse/framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0.0
 * @copyright 2015 Jared King
 * @license MIT
 */

return [
    [
        'module' => 'statistics',
        'command' => 'capture-metrics',
        'minute' => '10',
        'hour' => '1',
        'day' => '*',
        'month' => '*',
        'week' => '*',
        'expires' => 3600, // 1 hour
    ],
    [
        'module' => 'users',
        'command' => 'garbage-collection',
        'minute' => '30',
        'hour' => '0',
        'day' => '1',
        'month' => '*',
        'week' => '*',
        'expires' => 3600 // 1 hour
    ]
];
