<?php

$what = what( );

if ($what == 'edit' && METHOD == 'ajax')
{
	if( Ban::edit( $_POST[ 'id' ], $_POST[ 'type' ], $_POST[ 'value' ], $_POST[ 'reason' ] ) )
		echo '0';
	else
		echo $GLOBALS[ 'ajax_errors'];

	exit;
}
elseif ($what == 'new')
{
	if( isset( $_POST[ 'Submit' ] ) )
	{
		if( Ban::create( $_POST[ 'type' ], $_POST[ 'value' ], $_POST[ 'reason' ] ) )
			redirect ("4dm1n.php?module=Ban");
	} // if
	
	Globals::$smarty->display( $this->adminTemplateDir() . 'new.tpl' );
}
elseif ($what == 'delete' && METHOD == 'ajax')
{
	if( Ban::delete( $_GET[ 'id' ] ) )
		echo '0';
	else
		echo $GLOBALS['ajax_errors'];
		
	exit;
}
else if( METHOD == 'ajax')
{
	$ban = Database::select( 'Ban', '*' );

	if ($ban != NULL)
	{
		foreach ($ban as $key => $value)
			$ban[$key]['type2'] = Ban::$types[$value['type']];
	} // if

	if ($_GET['response'] == 'json')
	{
		$json_array2 = array();
		
		for ($i = 0; $i < count( $ban ); $i++)
			$json_array2[$i] = array($ban[$i]['id'], $ban[$i]['type'], $ban[$i]['value'], $ban[$i]['reason'] );

		echo json_encode(array('aaData'=>&$json_array2));
		exit();
	} // if
}
else
	Globals::$smarty->display( $this->adminTemplateDir() . 'index.tpl');

?>