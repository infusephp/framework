<?php

$what = what( );

if ( $what == 'new' )
{
	if ( Permissions::getPermission( 'create_groups' ) == 1 )
	{	
		if ( isset( $_POST['Submit'] ) )
		{
			if ( User_Groups::create( $_POST['group_name'] ) )
				redirect ( '4dm1n.php?module=User_Groups' );
		} // if
	} // if
	
	Globals::$smarty->display( $this->adminTemplateDir() . 'new.tpl' );
}
else if( $what == 'edit' && METHOD == 'ajax' )
{
	if ( Groups::rename( $_POST['id'], $_POST['group_name'] ) )
		echo '0';
	else
		echo $GLOBALS['ajax_errors'];

	exit;
}
else if( $what == 'delete' && METHOD == 'ajax' )
{
	if( User_Groups::delete( $_GET[ 'id' ] ) )
		echo '0';
	else
		echo $GLOBALS['ajax_errors'];
		
	exit( );
}
else if( METHOD == 'ajax' )
{
	$groups = Database::select( 'Users_Groups', 'id, group_name' );
	
	// find the number of users in each group
	foreach( $groups as $key => $value )
		$groups[$key]['num_users'] = Database::select( 'Users', 'count(*)', array( 'where' => array( 'group_id' => $value['id'] ), 'fetchStyle' => 'singleColumn' ) );
		
	if( $_GET['response'] == 'json' )
	{
		$json_array2 = array( );
		for ( $i = 0; $i < count( $groups ); $i++ )
		{
			$json_array2[ $i ] = array( $groups[$i]['id'], 'view_users', $groups[$i]['group_name'], $groups[$i]['num_users'] );
		} // for

		echo json_encode( array( 'aaData' => &$json_array2 ) );
		exit;
	} // if
}
else
	Globals::$smarty->display( $this->adminTemplateDir() . 'index.tpl' );

?>