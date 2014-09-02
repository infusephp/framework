<?php

return  [
  'site' => [
    'title' => 'Idealist Framework',
    'email' => 'site@example.com',
    'production-level' => false,
    'host-name' => 'example.com',
    'ssl-enabled' => false,
    'salt' => 'replacewithrandomstring',
    'time-zone' => 'America/Chicago',
    'language' => 'en',
  ],
  'logger' => [
    'enabled' => true
  ],
  'admin' => [
    'index' => 'statistics'
  ],
  'database' => [
    'type' => 'mysql',
    'host' => 'localhost',
    'name' => 'mydb',
    'user' => 'root',
    'password' => '',
    'port' => 3306,
    'charset' => 'utf8'
  ],
  'views' => [
    'engine' => 'smarty'
  ],
  'models' => [
    'cache' => [
      'strategies' => [
        // 'redis',
        // 'memcache',
        'local'
      ],
      'prefix' => 'idealist:'
    ],
  ],
  'email' => [
    'from_email' => 'no-reply@example.com',
    'from_name' => 'Idealist Framework',
    'type' => 'nop'
    // For SMTP use:
    // 'type' => 'smtp'
    // 'username' => 'username',
    // 'password' => 'password',
    // 'port' => 25,
    // 'host' => 'smtp.example.com',
  ],
  // For redis sessions use:
  /*
  'sessions' => [
    'enabled' => true,
    'adapter' => 'redis',
    'lifetime' => 86400,
    'prefix' => 'idealist:'
  ],
  */
  'sessions' => [
    'enabled' => true,
    'adapter' => 'database',
    'lifetime' => 86400
  ],
  'queue' => [
    'type' => 'synchronous',
    'queues' => [
      'emails'
    ],
    'listeners' => [
      'emails' => [
        [ 'email\\Controller', 'processEmail' ] ],
    ],
  ],
  'assets' => [
    'base_url' => ''
  ],
  /*
  'redis' => [
    'scheme' => 'tcp',
    'host' => '127.0.0.1',
    'password' => 'notused',
    'port' => 6379
  ],
  */
  /*
  'memcache' => [
    'enabled' => true,
    'host' => '127.0.0.1',
    'port' => 11211,
    'prefix' => 'idealist:',
  ],
  */
  'modules' => include 'config/modules.php',
  'routes' => include 'config/routes.php',
  'cron' => include 'config/cron.php',
  'statistics' => include 'config/statistics.php'
];