<?php

$what = what( );

if ($what == 'new')
{
	if (isset($_GET['permission']))
	{	
		$success = false;
		if (isset($_POST['Submit']))
		{
			
			if (!isset($_POST['group'])) $_POST['group'] = null;
			if (!isset($_POST['user'])) $_POST['user'] = null;
			
			if (Permissions::setPermission($_GET['permission'],$_POST['user'],$_POST['group'],$_POST['type'],$_POST['value'])) {
				redirect('4dm1n.php?module=Permissions');
			} // if
		} // if
		
		if ($success == false)
		{
			(isset($_POST['type'])) ? $smarty->assign('type',$_POST['type']) : null;
			(isset($_POST['user'])) ? $smarty->assign('user',$_POST['user']) : null;
			(isset($_POST['user_name'])) ? $smarty->assign('user_name',$_POST['user_name']) : null;
			(isset($_POST['group'])) ? $selected_group = $_POST['group'] : $selected_group = 0;
			(isset($_POST['value'])) ? $smarty->assign('value',$_POST['value']) : null;
			
			Globals::$smarty->getFunction('groupSelect');
			Globals::$smarty->assign ('groups',groupSelect($selected_group));
		} // if
	} // if
	
	Globals::$smarty->display( $this->adminTemplateDir() . 'new.tpl');
}
else if ($what == 'edit')
{
	if (Permissions::editPermission($_GET['id'], $_GET['value']))
		redirect('4dm1n.php?module=Permissions');
}
else if ($what =='delete' && METHOD == 'ajax')
{
	if (Permissions::deletePermission($_GET['id']))
		echo '0';
	else
		echo $GLOBALS['ajax_errors'];
		
	exit;
}
else
{
	$permissions_array = Permissions::raw();
	
	foreach ($permissions_array as $key => $value) {
		$permissions_array[$key]['users'] = array();
		$permissions_array[$key]['groups'] = array();
		$users_db = Database::select( 'Permissions', 'user,value,id', array( 'where' => array( 'name' => $key, 'user > 0' ) ) );
		if (Database::numrows() > 0) {
			foreach ($users_db as $user_id) {
				$name = Database::select( 'Users', 'user_name', array( 'where' => array( 'uid' => $user_id['user'] ), 'single' => true ) );
				if (Database::numrows() > 0) {
					$permissions_array[$key]['users'][$user_id['user']]['id'] = $user_id['id'];
					$permissions_array[$key]['users'][$user_id['user']]['value'] = $user_id['value'];
					$permissions_array[$key]['users'][$user_id['user']]['name'] = $name;
				} // if 
			} // foreach
		} // if

		$groups_db = Database::select( 'Permissions', '_group,value,id', array( 'name' => $key, '_group > 0' ) );
		if( Database::numrows() > 0) {
			foreach ($groups_db as $group_id) {
				$name = Database::select( 'Groups', 'group_name', array( 'where' => array( 'id' => $group_id['_group'] ), 'single' => true ) );
				if( Database::numrows() > 0)
				{
					$permissions_array[$key]['groups'][$group_id['_group']]['id'] = $group_id['id'];
					$permissions_array[$key]['groups'][$group_id['_group']]['value'] = $group_id['value'];
					$permissions_array[$key]['groups'][$group_id['_group']]['name'] = $name;
				} // if
			} // foreach
		} // foreach
		
//		if (count($permissions_array[$key]['users']) == 0 && count($permissions_array[$key]['groups']) == 0) unset($permissions_array[$key]);
	} // foreach

	Globals::$smarty->assign ('permissions_array',$permissions_array);
	
	Globals::$smarty->display( $this->adminTemplateDir() . 'index.tpl');
} // if

?>