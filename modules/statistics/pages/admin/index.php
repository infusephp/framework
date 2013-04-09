<?php

function file_size ($filesize)
{
	$bytes = array('KB', 'KB', 'MB', 'GB', 'TB');

	if ($filesize < 1024) $filesize = 1; // Values are always displayed.

	for ($i = 0; $filesize > 1024; $i++)  { // In KB at least.
		$filesize /= 1024;
	} // for

	$file_size_info['size'] = ceil($filesize);
	$file_size_info['type'] = $bytes[$i];

	return $file_size_info;
}

$stats = array_merge_recursive( SiteStats::generateSnapshot(), SiteStats::generateRealtimeStats() );

foreach( $stats['users']['activeMembers'] as $key => $uid )
	$stats['users']['activeMembers'][$key] = new User( $uid );

$stats['users']['newestUser'] = new User( $stats['users']['newestUser'] );

$dbsize = file_size( $stats['database']['size'] );
$stats['database']['size'] =  $dbsize['size'] . " " . $dbsize['type'];

Globals::$smarty->assign('stats', $stats);

return Globals::$smarty->fetch( $this->adminTemplateDir() . 'index.tpl' );