<?php

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
