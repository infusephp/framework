<?php

if ($what == 'schedcheck')
{
	Cron::scheduleCheck();
	
	return 'Success';
} // if