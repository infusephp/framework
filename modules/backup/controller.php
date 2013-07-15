<?php
/*
 * @package Infuse
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0
 * @copyright 2013 Jared King
 * @license MIT
	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
	associated documentation files (the "Software"), to deal in the Software without restriction,
	including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
	and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
	subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in all copies or
	substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
	LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
	IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
	SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
 
namespace infuse\controllers;

class Backup extends \infuse\Controller
{
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