{extends file='module.tpl'}
{block name='content'}
	<table class="main2">
		<thead>
			<tr>
				<th class="fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">New Permission</th>
			</tr>
		</thead>
		<tr>
			<td>
				<form name="find" method="POST" action="{$smarty.server.PHP_SELF}?module=Permissions&what=new&permission={$smarty.request.permission}">
				<p>Permission: {$smarty.request.permission}</p>
				<input type="hidden" name="user" value="{$user}" />
				<p><input type="radio" name="type" onclick="enableUser();" value="user" />User: <input type="text" name="user_name" value="{$user_name}" size="15" readonly="true" /> <input id="user_text" type="button" onclick="OpenWindow ('4dm1n.php?module=User&find=true&callback_info=id,user_name','800','600',true)" value="Select"></p>
				<p><input type="radio" name="type" onclick="enableGroup();" value="group" />Group: {$groups}</p>
				<p>Value: <input type="text" name="value" value="{$value}" size="1" maxlength="1" /></p>
				<p><input type="submit" name="Submit" value="Done" /></p>
				</form>
			</td>
		</tr>
	</table>
	<script type="text/javascript">
		{if $type == 'group'}document.find.type[1].click();{else}document.find.type[0].click();{/if}
		function enableUser() {
			document.getElementById('user_text').disabled=false;
			document.getElementById('select_group').disabled=true;
		}
		
		function enableGroup() {
			document.getElementById('user_text').disabled=true;
			document.getElementById('select_group').disabled=false;
		} 
		
		function callback(data) {
			$('input[name="user"]').val(data['id']);
			$('input[name="user_name"]').val(data['user_name']);
		}
	</script>
{/block}