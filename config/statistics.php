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
    'metrics' => [
        'DatabaseSize',
        'DatabaseTables',
        'DatabaseVersion',
        'PhpVersion',
        'SignupsToday',
        'SiteMode',
        'SiteSession',
        'SiteStatus',
        'FrameworkVersion',
        'Users',
    ],
    'dashboard' => [
        'Usage' => [
            'TotalUsers',
            'SignupsToday',
        ],
        'Site' => [
            'SiteStatus',
            'FrameworkVersion',
            'PhpVersion',
            'SiteMode',
            'SessionAdapter',
        ],
        'Database' => [
            'DatabaseSize',
            'DatabaseVersion',
            'DatabaseTables',
        ],
    ]
];
