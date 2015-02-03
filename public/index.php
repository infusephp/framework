<?php

/**
 * @package infuse/framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0.0
 * @copyright 2015 Jared King
 * @license MIT
 */

define('INFUSE_BASE_DIR', dirname(__DIR__));
set_include_path(get_include_path().PATH_SEPARATOR.INFUSE_BASE_DIR);

require_once 'vendor/autoload.php';
include 'assets/constants.php';

$config = @include 'config.php';
$app = new App($config);
$app->go();
