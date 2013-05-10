<?php

DEFINE( 'CRON', true );

require_once ('includes/initialize.php');

// Command Line Calls only
if( !isCLI() )
	exit;

chdir(__DIR__);

\nfuse\Modules::load( 'cron' );

if( \nfuse\libs\Cron::scheduleCheck(true) )
	echo "Success\n";
else
	echo "Failure\n";