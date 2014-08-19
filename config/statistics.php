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
			'Users'
		],
		'Site' => [
			'SiteStatus',
			'SiteVersion',
			'PhpVersion',
			'SiteMode',
			'SiteSession',
		],
		'Database' => [
			'DatabaseSize',
			'DatabaseVersion',
			'DatabaseTables'
		]
	]
];