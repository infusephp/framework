<?php

$selected_group = ADMIN;
$message = NULL;
$success = true;
$errors = null;

if (isset ($_POST['Submit']))
{
	if (isset($_POST['message']))
	{
		$group_str = null;
		if (isset($_POST['group']) && count($_POST['group']) > 0) {
			foreach ($_POST['group'] as $v) {
				$group_str .= "group_id = '$v' OR ";
			} // if
			$group_str = substr_replace($group_str,'',-4,4);
		} // if

		$addresses = null;
		if (isset($_POST['allusers']))
			$addresses = Database::select( 'Users', 'user_email,first_name,last_name' );
		elseif (isset($_POST['user']) && is_numeric($_POST['user']))
			$addresses = Database::select( 'Users', 'user_email,first_name,last_name', array( 'where' => array( 'uid' => $_POST['user'] ), 'singleRow' => true ) );
		elseif (isset($_POST['group']) && count($_POST['group']) > 0)
			$addresses = Database::select( 'Users', 'user_email,first_name,last_name', array( 'where' => array( $group_str ) ) );
		
		/*if (isset($_POST['facebook_notify']))
		{
			$database->tablename = 'Facebook_Messages';
			$database->insertRecord(array('date' => date('y-m-d'), 'subject' => $_POST['subject'], 'message' => $_POST['message']));
			$message_id = mysql_insert_id();

			$uids = $database->getData('uid,site_uid','Facebook_Users','uid != 4',null,null,true);
			$where = null;
			if (empty($_POST['allusers']) && isset($_POST['user']) && is_numeric($_POST['user'])) {
				foreach ($uids as $key => $value) {
					if ($value['site_uid'] != $_POST['user']) {
						unset($uids[$key]);
					} // if
				} // foreach
			} // if
			
			if (empty($_POST['allusers']) && isset($_POST['group']) && count($_POST['group']) > 0) {
				$group_uids = $database->getData ('uid','Users',$group_str.' AND uid != 4',NULL,NULL);
				foreach ($uids as $key => $value) {
					if (!in_array($value['site_uid'],$group_uids)) {
						unset($uids[$key]);
					} // if
				} // if
			} // if
			
			foreach ($uids as $value) {
				$modules['Facebook']->api_client->notifications_send($value['uid'],"<a href='" . $GLOBALS['config']['facebook_app_address'] . "'>" . $GLOBALS['config']['site_title'] . '</a> has sent you a message. <a href="' . $GLOBALS['config']['facebook_app_address'] . '&what=message&action=view&id=' . $message_id . '">Click here</a> to view.','app_to_user');
			} // foreach
		} // if*/
		
		/*if (isset($_POST['sms_notify'])) {
			/*
			 * Clickatell Site Login:
			 * User: nfusetechnology
			 * Password: Callisto9
			 * Client ID: KJI505
			 *
			$clickatell_user = "nfusetechnology";
			$clickatell_password = "Callisto9";
			$clickatell_api_id = "3120669";
			$clickatell_baseurl ="https://api.clickatell.com";
			
			$ch = curl_init();
			
			curl_setopt($ch,CURLOPT_URL,$clickatell_baseurl.'/http/auth');
			curl_setopt($ch,CURLOPT_POST,3);
			curl_setopt($ch,CURLOPT_POSTFIELDS,"user=$clickatell_user&password=$clickatell_password&api_id=$clickatell_api_id");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$ret = curl_exec($ch);
			
//			echo $ret;
			
			// split our response. return string is on first line of the data returned
			$sess = split(":",$ret);
			if ($sess[0] == "OK") {
				$sess_id = trim($sess[1]); // remove any whitespace
				$text = str_replace(array("\n", "\r","\t",'&amp;','&nbsp;'),array('','','','&',' '),strip_tags($_POST['message']));
				$text = urlencode($text);
				$length = strlen($text);
				if ($length <= 153) $concat = 1;
				if ($length > 153 && $length <= 153*2) $concat = 2;
				if ($length > 153 * 2 && $length <= 153 * 3) $concat = 3;
								
				$where = null;
				if (empty($_POST['allusers']) && isset($_POST['group']) && count($_POST['group']) > 0) {
					$where = "";
					foreach ($_POST['group'] as $g) {
						$where .= "group_id = '$g' OR ";
					} // foreach
					$where = substr_replace($where,'',-4,4);
				} elseif (empty($_POST['allusers']) && isset($_POST['user']) && is_numeric($_POST['user'])) {
					$where = "uid = '{$_POST['user']}'";
				} // if

				$cellphones = $database->getData('cellphone','Users',$where,null,null);
				
				if (!is_array($cellphones)) $cellphones = array($cellphones);
				
				$success = TRUE;
				foreach ($cellphones as $to) {
					if ($to != "") {
						if (strlen($to) < 11) {
							if (strlen($to) == "10") {
								$to = "1" . $to;
							} else if (substr($to,0,4 != "1918")) {
								$to = "1918" . $to;
							} // if
						} // if
						
						curl_setopt($ch,CURLOPT_URL,$clickatell_baseurl.'/http/sendmsg');
						curl_setopt($ch,CURLOPT_POST,4);
						curl_setopt($ch,CURLOPT_POSTFIELDS,"session_id=$sess_id&to=$to&text=$text&concat=$concat");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						
						$ret = curl_exec($ch);
						
						$send = split(":",$ret);
						if ($send[0] != "ID") {
							$success = FALSE;
							echo "<p style='color: red;'>SMS Failure: " . $send[1] . "</p>";
						} // if
					} // if
				} // foreach
			} else {
				$errors .= "<p style='color: red;'>Authentication failure: ". $ret[0] . "</p>";
				$success = FALSE;
			} // if

			curl_close($ch);
			
		} // if*/
		
		if (!isset($_POST['no_email']))
		{
			$body = $_POST['message'];
			$body    = eregi_replace("[\]",'',$body);
			$mail->From     = Config::value( 'site_email' );
			$mail->FromName = Config::value( 'site_title' );
			
			if (!empty($_POST['subject']))
				$mail->Subject = $_POST['subject'];
			else
				$mail->Subject = "Message from {Config::value('site_title')}";

			$mail->AltBody = $body;
			$mail->MsgHTML($body);
	
			if (is_array($addresses)) {
				foreach ($addresses as $value) {
		
					if( Validation::validateEmail($value['user_email'])) {
						$mail->AddAddress($value['user_email'],$value['first_name'] . " " . $value['last_name']);
					} // if
		
				} // foreach
			} // if
				
			if (@strlen($addresses) > 0 || is_array($addresses)) {
				if(!$mail->Send()) {
					$errors .= "<p style='color: red;'>Could not message $value.</p>";
					$success = FALSE;
				} // if
			} else {
				$errors .= "<p style='color: red;'>No users were selected.</p>";
				$success = FALSE;
			} // if
		} // if
		
		if (isset($errors))
			Globals::$smarty->assign('message_error',$errors);

		(isset($_POST['message'])) ? Globals::$smarty->assign('message', $_POST['message']) : $message = NULL;
		(isset($_POST['subject'])) ? Globals::$smarty->assign('subject',$_POST['subject']) : Globals::$smarty->assign('subject',NULL);
			if (isset($_POST['group']) && count($_POST['group']) > 0) {
				$selected_group = $_POST['group'];
		} // if
		//(isset($_POST['user'])) ? Globals::$smarty->assign('user',$_POST['user']) : Globals::$smarty->assign('user',NULL);
		//(isset($_POST['user_name'])) ? Globals::$smarty->assign('user_name',$_POST['user_name']) : Globals::$smarty->assign('user_name',NULL);
		Globals::$smarty->assign ('success',$success);
	}
	else
		displayError ("invalid_message",'errors',NULL,'module');
} // if

Globals::$smarty->assign ('groups',array());

Globals::$smarty->display( $this->templateDir() . 'admin/index.tpl' );

?>