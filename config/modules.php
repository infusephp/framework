<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @version 1.0.0
 *
 * @copyright 2015 Jared King
 * @license MIT
 */

return [
    'middleware' => [
      'auth',
      'admin',
      'api',
      'cron',
      'email',
      'statistics',
      'users',
    ],
    'all' => [
        'auth',
        'admin',
        'api',
        'cron',
        'email',
        'home',
        'statistics',
        'users',
    ],
];
