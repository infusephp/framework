<?php

/**
 * @package Idealist Framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0.0
 * @copyright 2014 Jared King
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
        'SiteVersion',
        'Users'
    ],
    'dashboard' => [
        'Signup Funnel' => [
            'SignupsToday'
        ],
        'Usage' => [
            'TotalUsers'
        ],
        'Site' => [
            'SiteStatus',
            'SiteVersion',
            'PhpVersion',
            'SiteMode',
            'SessionAdapter',
        ],
        'Database' => [
            'DatabaseSize',
            'DatabaseVersion',
            'DatabaseTables'
        ]
    ]
];
