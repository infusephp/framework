<?php
// *************************************************************************************************
// Title: 		  King Designs
// Developed by:  Jared King
// Email:		  jared@kwebdesigns.com
// Website:		  http://kwebdesigns.com
// Copyright:	  2007(C)Jared King - (King Designs)
// License:		  You may not use or redistribute this software without the permission of the author.
// Description:   Smarty function that puts all of the groups in a select input.
// *************************************************************************************************

if ( !defined('IN_SITE') ) { // Prevent page from being loaded by itself.
	die("Hacking attempt.");
}

function groupSelect ($selected = false, $onchange = false, $multiple = false) {

	if( Globals::$currentUser->group() != ADMIN )
		return false;

	$groups = Users::groups();

	$multiple2 = null;
	if ($multiple) {
		$multiple = "multiple='multiple'";
		$multiple2 = '[]';
	} // if
	
	$selected_array = array();
	if (is_array($selected)) {
		foreach ($selected as $key => $value) {
			$selected_array[$value] = true;
		} // if
	} else {
		if ($selected != false) {
			$selected_array[$selected] = true;
		} // if
	} // if
		
	$javascript = NULL;
	if ($onchange) {
		$javascript = "onchange='document.$onchange.submit()'";
	} // if
	$output = "<select id='select_group' name='group".$multiple2."' $javascript $multiple>\n";
	$output .= "<option value = '--'>- All Groups -</option>\n";
	foreach ($groups as $key => $value) {

		if (isset($selected_array[$groups[$key]['id']])) {
			$output .= '<option value ="'.$groups[$key]['id'].'" SELECTED>'.$groups[$key]['group_name']."</option>\n";
		} else {
			$output .= '<option value ="'.$groups[$key]['id'].'">'.$groups[$key]['group_name']."</option>\n";
		} // if
	} // foreach
	$output .= '</select>';

	return $output;

} // grouopSelect
?>