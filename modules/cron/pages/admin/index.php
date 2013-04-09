<?php

$displayTasks = TRUE;

$months = array ( 1 => 'January',
						'February',
						'March',
						'April',
						'May',
						'June',
						'July',
						'August',
						'September',
						'October',
						'November',
						'December'
);

$dow = array ( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

if ($what == 'edit' && METHOD == 'ajax')
{
	$next_run = Cron::editTask($_POST['id'], $_POST['name'], $_POST['command'], $_POST['minute'], $_POST['hour'], $_POST['day'], $_POST['month'], $_POST['week']);
	if ($next_run)
	{
		echo '0';
		$last_ran = Database::select( 'Cron', 'last_ran', array( 'where' => array( 'id' => $_POST['id'] ), 'single' => true ) );
		$last_ran = ($last_ran == 0) ? 'Never' : date('F j, Y g:i A',$last_ran);
		echo '!-!<next_run>'.date('F j, Y g:i A',$next_run).'</next_run>!-!<last_ran>'.$last_ran.'</last_ran>';
		exit;
	} // if
	
	echo $GLOBALS['ajax_errors'];
	exit;
}
elseif ($what == 'new')
{
	if (isset ($_POST['Submit']))
	{
		if( Cron::createTask($_POST['name'], $_POST['command'], $_POST['minute'], $_POST['hour'], $_POST['day'], $_POST['month'], $_POST['dow'] ) )
			redirect ("4dm1n.php?module=Cron");
	} // if

	Globals::$smarty->assign ('months',$months);
	Globals::$smarty->assign ('dow',$dow);		
	Globals::$smarty->display( $this->adminTemplateDir() . 'new.tpl' );
	exit;
}
elseif ($what == 'delete' && METHOD == 'ajax')
{
	if (Cron::deleteTask($_GET['id']))
		echo '0';
	else
		echo $GLOBALS['ajax_errors'];
		
	exit;
}
elseif ($what == 'run')
{
	if( Cron::runTask( val( $params, 'id' ) ) )
		Globals::$smarty->assign('success','The task was executed successfully.');
	else
		Globals::$smarty->assign('error','The task was not executed successfully.');
}
else if( METHOD == 'ajax' )
{
	$tasks = Database::select( 'Cron', '*' );

	if ($tasks != NULL)
	{
		foreach ($tasks as $key => $value)
		{
			$tasks[$key]['next_run'] = date('F j, Y g:i A',$value['next_run']);
			$tasks[$key]['last_ran'] = ($value['last_ran'] == 0) ? 'Never' : date('F j, Y g:i A',$value['last_ran']);
		} // foreach
	} // if

	if ($_GET['response'] == 'json')
	{
		$json_array2 = array();
		for ($i = 0; $i < count( $tasks ); $i++)
			$json_array2[$i] = array($tasks[$i]['id'],'run',$tasks[$i]['name'],$tasks[$i]['command'],$tasks[$i]['minute'],
				$tasks[$i]['hour'],$tasks[$i]['day'],$tasks[$i]['month'],$tasks[$i]['day'],$tasks[$i]['last_ran'],$tasks[$i]['next_run']);

		echo json_encode(array('aaData'=>&$json_array2));
		exit;
	} // if
}

$tasks = Database::select( 'Cron', '*' );

foreach( (array)$tasks as $key => $value )
{
	$tasks[$key]['next_run'] = date('F j, Y g:i A',$value['next_run']);
	$tasks[$key]['last_ran'] = ($value['last_ran'] == 0) ? 'Never' : date('F j, Y g:i A',$value['last_ran']);
} // foreach

Globals::$smarty->assign( 'tasks', $tasks );

$command_js_values = null;
foreach (Cron::tasks() as $v)
	$command_js_values .= "'{$v[1]} ({$v[0]})',";

$command_js_values = substr_replace($command_js_values,'',-1,1);
Globals::$smarty->assign('command_js_values','['.$command_js_values.']');

$command_js_keys = null;
foreach (Cron::tasks() as $v)
	$command_js_keys .= "'$v[1]',";

$command_js_keys = substr_replace($command_js_keys,'',-1,1);
Globals::$smarty->assign('command_js_keys','['.$command_js_keys.']');

return Globals::$smarty->fetch( $this->adminTemplateDir() . 'index.tpl');