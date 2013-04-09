{extends file='module.tpl'}
{block name='content'}
	<table class="main2">
		<thead>
			<tr>
				<th class="fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">New Task</th>
			</tr>
		</thead>
		<tr>
			<td>
				<form action="4dm1n.php?module=Cron&what=new" method="POST">
					{$err_form}{$err_name}<p>Name: <input type="text" name="name" value="{$smarty.request.name}" /></p>
					<p>Command: <select name="command">
						{foreach from=Cron::tasks() item=v}
							<option value="{$v.1}" {if $smarty.request.command == $v.1}SELECTED{/if}>{$v.1} ({$v.0})</option>
						{/foreach}
						</select></p>
					<p>Minute: <select name="minute"><option value="*" {if $smarty.request.minute == '*'}SELECTED{/if}>*</option>{section name=minute loop=60 start=0}<option value="{$smarty.section.minute.index}" {if $smarty.section.minute.index == $smarty.request.minute}SELECTED{/if}>{$smarty.section.minute.index}</option>{/section}</select></p>
					<p>Hour: <select name="hour"><option value="*" {if $smarty.request.hour == '*'}SELECTED{/if}>*</option>{section name=hour loop=24 start=0}<option value="{$smarty.section.hour.index}" {if $smarty.section.hour.index == $smarty.request.hour}SELECTED{/if}>{$smarty.section.hour.index}</option>{/section}</select></p>
					<p>Day of Month: <select name="day"><option value="*" {if $smarty.request.day == '*'}SELECTED{/if}>*</option>{section name=day loop=32 start=1}<option value="{$smarty.section.day.index}" {if $smarty.section.day.index == $smarty.request.day}SELECTED{/if}>{$smarty.section.day.index}</option>{/section}</select></p>
					<p>Month: <select name="month"><option value="*" {if $smarty.request.month == '*'}SELECTED{/if}>*</option>{foreach from=$months key=k item=v}<option value="{$k}" {if $k == $smarty.request.month}SELECTED{/if}>{$v}</option>{/foreach}</select></p>
					<p>Day of Week: <select name="dow"><option value="*" {if $smarty.request.dow == '*'}SELECTED{/if}>*</option>{foreach from=$dow key=k item=v}<option value="{$k}" {if $k === $smarty.request.dow}SELECTED{/if}>{$v}</option>{/foreach}</select></p>
					<p><input type="submit" name="Submit" value="Create" /></p>
				</form>
			</td>
		</tr>
	</table>
{/block}