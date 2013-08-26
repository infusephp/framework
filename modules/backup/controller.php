<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.15.4
 * @copyright 2013 Jared King
 * @license MIT
 */
 
namespace infuse\controllers;

class Backup extends \infuse\Controller
{
	public static $properties = array(
		'title' => 'Backup',
		'description' => 'Backup the app database',
		'version' => '1.0',
		'author' => array(
			'name' => 'Jared King',
			'email' => 'j@jaredtking.com',
			'website' => 'http://jaredtking.com'
		),
		'admin' => true,
		'routes' => array(
			'get /4dm1n/backup' => 'adminHome',
			'get /4dm1n/backup/optimize' => 'optimizeDb',
			'post /4dm1n/backup/restore' => 'restoreDb',
			'get /4dm1n/backup/download' => 'backupDb'
		)
	);
	
	function optimizeDb( $req, $res )
	{
		if( \infuse\libs\Backup::optimizeDatabase() )
			\infuse\ViewEngine::engine()->assign( 'optimizeSuccess', true );
		
		$this->adminHome( $req, $res );
	}

	function backupDb( $req, $res )
	{
		if( !$this->can( 'view-admin' ) && $this->can('backup-site') )
			return $res->setCode( 401 );

		$tmpDir = INFUSE_TEMP_DIR . '/backup/';

		@mkdir( $tmpDir );
		$filename = $tmpDir . str_replace( " ", "", \infuse\Config::value( 'site', 'title' )) . "-" . date("m-d-Y") . ".sql";
		
		// generate the SQL and save in file
		if( \infuse\libs\Backup::backupSQL( $filename ) )
		{
			// Force user to download file.
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($filename));
			readfile("$filename");
			
			\infuse\libs\Backup::cleanTmpDir($tmpDir);
		}	
		
		exit;			
	}
	
	function adminHome( $req, $res )
	{
		if( !$this->can( 'view-admin' ) )
			return $res->setCode( 401 );

		$canBackupSite = $this->can('backup-site');

		$res->render( 'admin/index.tpl', array(
			'canBackupSite' => $canBackupSite,
			'title' => 'Backup'
		) );
	}
	
	function cron( $command )
	{
		if ($command == 'backup_database')
			return Backup::backupSQL( 'backup/' . str_replace(" ", "", \infuse\Config::value( 'site_title' ) ) . "-" . date("m-d-Y") . ".sql" );
		else if ($command == 'optimize_database')
			return Backup::optimizeDatabase();
	}
}