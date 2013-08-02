{extends file='module.tpl'}
{block name='content'}
	<table class="main2">
		<thead>
			<tr>
				<th class="fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">New Ban</th>
			</tr>
		</thead>
		<tr>
			<td>
				<form action="4dm1n.php?module=Ban&what=new" method="POST">
					<p>{$err_type}Type:<br /><select name="type">
											<option value="1" {if $smarty.request.type == 1}SELECTED{/if}>IP</option>
											<option value="2" {if $smarty.request.type == 2}SELECTED{/if}>User</option>
											<option value="3" {if $smarty.request.type == 3}SELECTED{/if}>E-mail Address</option>
						</select></p>
					<p>{$err_value}Value:<br /><input type="text" name="value" value="{$smarty.request.value}" /></p>
					<p>{$err_reason}Reason:<br /><input type="text" name="reason" value="{$smarty.request.reason}" /></p>
					<input type="submit" name="Submit" value="Create" />
				</form>
			</td>
		</tr>
	</table>
{/block}