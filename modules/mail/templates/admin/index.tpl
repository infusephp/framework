{extends file='module.tpl'}
{block name='content'}
	{if $success}
	<p style="text-align: center;"><b>The message was sent successfully.</b></p>
	{/if}
	{$message_error}
	<form action="{$smarty.server.PHP_SELF}?module=Mail" method="post" name="find">
		<table cellpadding="0" cellspacing="0" style="width: 800px; text-align: center;">
			<tr>
				<td>Group: {$groups}</td>
				<td>Single User: <input type="hidden" name="user" value="{$user}" /><input type="text" name="user_name" size="8" value="{$user_name}" readonly="true" /> <input type="button" onclick="OpenWindow ('4dm1n.php?module=User&find=true&callback_info=id,user_name','800','600',true);document.getElementById('select_group').disabled=true;" name="find" value="Find" /> <input type="button" value="Clear" onclick="document.getElementById('select_group').disabled=false;document.forms[0].user_name.value='';document.forms[0].user.value=''" /></td>
				<td><input type="checkbox" name="allusers" onclick="document.getElementById('select_group').disabled=!document.getElementById('select_group').disabled;document.forms[0].find.disabled=!document.forms[0].find.disabled;document.forms[0].user_name.value='';document.forms[0].user.value=''" {if $smarty.request.allusers}checked{/if} />All Registered Users</td>
			</tr>
			<tr>
				<td colspan="3"><input type="checkbox" name="no_email" {if $smarty.request.no_email}checked{/if} />Do not e-mail {if $modules.Facebook}<input type="checkbox" name="facebook_notify" {if $smarty.request.facebook_notify}checked{/if} />Notify via Facebook{/if} <input type="checkbox" name="sms_notify" {if $smarty.request.sms_notify}checked{/if}/>Text Message (SMS)</td>
			</tr>
			<tr>
				<td colspan="3">
					<p>Subject: <input type="text" name="subject" value="{$subject}" size="50" /></p>
					Message: <div id="characterCount">(0 Characters)</div><br />
					{CKeditor::create('message',$message)}<br />
					<input type="submit" name="Submit" value="Send" />
				</td>
			</tr>
		</table>
	</form>
	<script type="text/javascript">
	function callback(data) {
		$('input[name="user"]').val(data['id']);
		$('input[name="user_name"]').val(data['user_name']);
	}

	function GetLength()
	{
	// TODO: Character Count not 100% accurate
		
		var editor = CKEDITOR.instances.message;

		iLength = editor.getData().replace(/<[^>]*>|\s/g, '').length;
		iLength += editor.getData().split(' ').length-1;
	/*
	// This functions shows that you can interact directly with the editor area
	// DOM. In this way you have the freedom to do anything you want with it.

	// Get the editor instance that we want to interact with.

	// Get the Editor Area DOM (Document object).
	var oDOM = oEditor.EditorDocument ;

	var iLength ;

	// The are two diffent ways to get the text (without HTML markups).
	// It is browser specific.

	if ( document.all ) // If Internet Explorer.
	{
	iLength = oDOM.body.innerText.length ;
	}
	else // If Gecko.
	{
	var r = oDOM.createRange() ;
	r.selectNodeContents( oDOM.body ) ;
	iLength = r.toString().length ;
	}
	*/
		
	document.getElementById('characterCount').innerHTML = '(' + iLength + ' Characters)' ;
	setTimeout('GetLength()', 500);
		
	}
	window.onload = function() { setTimeout('GetLength()', 500) };

	</script>
{/block}