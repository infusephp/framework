<?php

class nFuse_Module_Mail extends Module
{
	protected static $description = 'Contains the mailer class used by many modules to send e-mails and adds a newsletter feature to the administration panel.';
	protected static $version = '1.1';
	protected static $author = array(
		'name' => 'Jared King',
		'email' => 'jared@nfuseweb.com',
		'web_site' => 'http://nfuseweb.com'
	);		
	protected static $admin = array(
		'title' => 'Mail Users',
		'icon' => 'icon-envelope',
		'help' => '	With this form users can be contacted through the site. Messages can be sent to a specific group, a specific user, or all registered users.
			The message will be from the site e-mail address(es). Check "Do not e-mail" to send a message without using e-mail (other ways must be checked).
			SMSes may be sent, but keep in mind the standard message limit is 153 characters. Longer messages will be broken up into multiple messages.'
	);

	function __construct()
	{
		parent::__construct();
	}
	
	static function initialize()
	{
		parent::initialize();
		
		Permissions::createPermission ('view_mail_admin',array('view_admin_panel'),'Mail');
	}
	
	function moduleFocusAdmin()
	{
		include 'pages/admin/index.php';
	}
	
	static function viewAdminPermission()
	{
		return Permissions::getPermission( 'view_mail_admin' ) == 1;
	}
}

?>