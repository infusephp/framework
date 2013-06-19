{extends file='admin/parent.tpl'}
{block name=header}
<script type="text/javascript">
	var select_enabled = {if $smarty.request.find}1{else}0{/if};
	pager_size = 25; // 10,25,50,100
	var sorting = [];
	var model = {$modelJSON};
	
	$(document).ready(dt_loadData);
</script>
{/block}
{block name=main}
	{block name=content}{/block}
	
	<ul class="nav nav-pills">
		<li class="{if !$schema}active{/if}">
			<a href="/4dm1n/{$moduleName}">{$modelNamePlural}</a>
		</li>
		<li class="{if $schema}active{/if}">
			<a href="/4dm1n/{$moduleName}/schema">Database Schema</a>
		</li>
	</ul>
	
	{if $schema}
		<div class="row-fluid">
			<div class="span6">
				<h3>Current Schema</h3>

{if !$currentSchema}
<pre>{$tablename} does not exist in the database</pre>
{else}
<pre>CREATE TABLE IF NOT EXISTS `{$tablename}` (
{foreach from=$currentSchema item=column}
	`{$column.Field}` {$column.Type} {if $column.Null=='Yes'}NULL{else}NOT NULL{/if}{if $column.Default} DEFAULT '{$column.Default}'{/if}{if $column.Extra} {$column.Extra}{/if}{if $column.Key} {if $column.Key=='PRI'}PRIMARY KEY{else}{$column.Key}{/if}{/if},
{/foreach}
) ;</pre>
{/if}

			</div>
			<div class="span6">
				<h3>Suggested Schema</h3>

{if !$suggestedSchema}
<pre>No suggestions</pre>
{else}
<pre>CREATE TABLE IF NOT EXISTS `{$tablename}` (
{foreach from=$suggestedSchema item=column}
	`{$column.Field}` {$column.Type} {if $column.Null=='Yes'}NULL{else}NOT NULL{/if}{if $column.Default} DEFAULT '{$column.Default}'{/if}{if $column.Extra} {$column.Extra}{/if}{if $column.Key} {if $column.Key=='PRI'}PRIMARY KEY{else}{$column.Key}{/if}{/if},
{/foreach}
) ;</pre>
{/if}

			</div>
		</div>
	{else}
		<table id="dataTable" class="table table-striped" cellpadding="0" cellspacing="0" border="0"></table>
		<div class="modal hide fade" id="dialog-delete-item">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Are you sure?</h3>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete this item?</p>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn" data-dismiss="modal">Close</a>
				<a href="#" class="btn btn-danger" id="dialog-delete-confirm">Yes</a>
			</div>
		</div>
		<div class="modal hide fade" id="dialog-edit-item">
			<form id="form-edit-item" style="margin:0;padding:0;">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit Item</h3>
				</div>
				<div class="modal-body"></div>
				<div class="modal-footer">
					<a href="#" class="btn" data-dismiss="modal">Cancel</a>
					<button class="btn btn-success" id="dialog-edit-confirm">Save</button>
				</div>
			</form>
		</div>
		<div class="modal hide fade" id="dialog-new-item">
			<form id="form-new-item" style="margin:0;padding:0;">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>New Item</h3>
				</div>
				<div class="modal-body"></div>
				<div class="modal-footer">
					<a href="#" class="btn" data-dismiss="modal">Cancel</a>
					<button class="btn btn-success" id="dialog-new-confirm">Save</button>
				</div>
			</form>
		</div>
	{/if}
{/block}