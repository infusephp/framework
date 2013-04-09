<?php

$what = what( );

switch ($what) {
	case "disable":		Modules::disableModule($_GET['name']);		break;
	case "enable":		Modules::enableModule($_GET['name']);		break;
	case "scan":		Modules::scanModules();						break;
	case "uninstall":	Modules::uninstallModule($_GET['name']);	break;
	case "install":		Modules::installModule($_GET['name']);		break;
} // switch

if( METHOD == 'ajax' )
{
	$modules_db = Database::select( 'Modules', '*' );

	foreach ($modules_db as $key => $value)
	{		
		$modules_db[$key]['required'] = in_array( $value['name'], Modules::requiredModules());
		$modules_db[$key]['running'] = in_array( $value['name'], Modules::$modules);
	} // if

	if ($_GET['response'] == 'json')
	{	
		$json_array2 = array();
		$i = 0;
		foreach ($modules_db as $value)
		{
			$link1 = "";
			$link1_name = "";
			$link2 = "";
			$link2_name = "";
			if( Permissions::getPermission('enable_disable_modules') == 1 && !$value['required'])
			{
				if ($value['status'] == 0)
				{
					$link1_name = 'Delete';
					$link1 = $_SERVER['PHP_SELF'] . '?module=Modules&amp;what=uninstall&amp;name=' . $value['name'];
				}
				else if ($value['status'] == 1)
				{
					$link1_name = 'Enable';
					$link1 = $_SERVER['PHP_SELF'] . '?module=Modules&amp;what=enable&amp;name=' . $value['name'];
				}
				else if ($value['status'] == 2)
				{
					$link1_name = 'Disable';
					$link1 = $_SERVER['PHP_SELF'] . '?module=Modules&amp;what=disable&amp;name=' . $value['name'];
				} // if
					
			} // if
			
			if (Permissions::getPermission('install_uninstall_modules') == 1 && !$value['required'])
			{
				if ($value['status'] > 0)
				{
					$link2_name = 'Uninstall';
					$link2 = '#" onclick="if (confirm(\'Are you sure you want to uninstall '.$value['name'].'? To fully uninstall '.$value['name'].' you must delete it from the modules directory.\')) { window.location=\''.$_SERVER['PHP_SELF'].'?module=Modules&amp;what=uninstall&amp;name='.$value['name'].'\'  }';
				}
				else
				{
					$link2_name = 'Install';
					$link2 = $_SERVER['PHP_SELF'].'?module=Modules&amp;what=install&amp;name='.$value['name'];
				}
					
			} // if
			
			$status = '';
			if ($value['status'] == 0)
			{
				$status = 'Uninstalled';
			}
			else if ($value['status'] == 1)
			{
				$status = 'Disabled';
			}
			else if ($value['status'] == 2)
			{
				$status = 'Running';
			}
			
			$status_color = "";
			if ($value['status'] == 1)
			{
				$status_color = '#cccccc';
			}
			else if ($value['status'] == 2)
			{
				//if( $value['running'] )
					$status_color = '#00aa00';
				/*else
					$status_color = '#cc0000';*/
			} // if
			
			$required = ($value['required']) ? 'Yes' : '';
			
			$author = Modules::moduleAuthor( $value[ 'name' ] );
			$authorLink = "<a href='{$author['web_site']}' target='_new'>{$author['name']}</a>";

			$json_array2[$i] = array(
				$i,
				$status_color,
				$link1_name,
				$link2_name,
				$link1,
				$link2,
				$value['name'],
				Modules::moduleDescription( $value[ 'name' ] ),
				$authorLink,
				Modules::moduleVersion( $value[ 'name' ] ),
				$required,
				$status
			);

			++$i;
			
		} // foreach

		echo json_encode(array('aaData'=>&$json_array2));
		exit;
	} // if
	
} // if

Globals::$smarty->display( $this->templateDir() . 'admin/index.tpl');

?>