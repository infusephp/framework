<?php

$what = urlParam( 1, $url );

$canRestoreSite = $this->can('restore-site');
$canBackupSite = $this->can('backup-site');

$tmpDir = "temp/backup/";

if ($what == 'optimize')
	Backup::optimizeDatabase();
elseif ($what == 'backup')
{
	$filename = $tmpDir . str_replace( " ", "", Config::value( 'site_title' )) . "-" . date("m-d-Y") . ".sql";
	
	// generate the SQL and save in file
	if( Backup::backupSQL( $filename ) )
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
		
		Backup::cleanTmpDir($tmpDir);
	}	
	
	exit;
}
elseif ($what == 'restore')
{
	exit;
	// TODO
	if (isset($_POST['Submit'])) {
		$target_path = $tmpDir . basename($_FILES['uploadedfile']['name']);

		$file_type = explode (".", $_FILES['uploadedfile']['name']);

		if (in_array('sql', $file_type))
		{
			if(!move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path))
				Globals::$smarty->assign ("upload_failure",'failure',NULL,'module');

			if (file_exists ($target_path))
			{
				$file = fopen($target_path,"r");
				$line_count = $backup->restoreSQL($file);
				fclose($file);
				if ($line_count > 0) {
					Globals::$smarty->assign ('success',TRUE);
				} else {
					displayError("restore_failure",'failure',NULL,'module');
				}
				$backup->cleanTmpDir ($tmpDir);
			}

		} else
			displayError("invalid_file",'failure',NULL,'module');
	}
}

Globals::$smarty->assign( 'canBackupSite', $canBackupSite );
Globals::$smarty->assign( 'canRestoreSite', $canRestoreSite );
 
return Globals::$smarty->fetch( $this->templateDir() . 'admin/index.tpl');