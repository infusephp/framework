<?php

return [
    'middleware' => [
      'auth',
      'admin',
      'email'
    ],
    'all' => [
		'auth',
		'admin',
		'api',
		'cron',
		'email',
		'home',
		'statistics',
		'users'
	]
];