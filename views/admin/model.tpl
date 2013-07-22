{extends file='admin/parent.tpl'}
{block name=header}
<script type="text/javascript">
	{if isset($modelJSON)}var modelInfo = {$modelJSON};{/if}
	var module = '{$moduleName}';
</script>
{/block}
{block name=main}

	<ul class="nav nav-pills">
		{foreach from=$models item=model}
			<li class="{if $model.model == $modelInfo.model && !isset($schema)}active{/if}">
				<a href="/4dm1n/{$moduleName}/{$model.model}#/">{$model.proper_name_plural}</a>
			</li>
		{/foreach}
		<li class="{if isset($schema)}active{/if}">
			<a href="/4dm1n/{$moduleName}/schema">Database Schema</a>
		</li>
	</ul>
	<hr/>

	{block name=content}{/block}
	
	{if isset($schema)}
		
		<p class="lead">
			Infuse Framework will suggest a database schema for your model based on the <a href="https://github.com/jaredtking/infuse/wiki/Models">specified
			properties</a>. On the left is the current schema in your database and on the right is the proposed schema. Click <strong>Update</strong> to
			accept the suggested schema.
		</p>

		{if $success}
			<p class="alert alert-success">The schema was updated!</p>
		{elseif $error}
			<p class="alert alert-error">{$error}</p>
		{/if}
		
		{foreach from=$models key=model item=info}
		
			<div class="title-bar">{$info.proper_name}</div>
	
			<div class="row-fluid">
				<div class="span6">
					<h3>Current Schema</h3>
	
					{if !$currentSchema[$model]}
						<pre>{$tablename[$model]} does not exist in the database</pre>
					{else}
						<pre>{$currentSchema[$model]}</pre>
					{/if}
	
				</div>
				<div class="span6">
					<h3>Suggested Schema</h3>
					
					{if !$suggestedSchema[$model]}
						<pre>No suggestions</pre>
					{else}
						<pre>{$suggestedSchema[$model]}</pre>
					{/if}
	
					<p>
						{if count($extraFields[$model]) > 0}
							<a href="/4dm1n/{$moduleName}/schema/clean/{$model}" class="btn btn-large btn-danger pull-right">Delete Old Fields</a>
						{/if}
						<a href="/4dm1n/{$moduleName}/schema/update/{$model}" class="btn btn-large btn-success">&larr; Update</a>
					</p>
				
				</div>
			</div>
			<br/>
		{/foreach}
	{else}
		<div ng-view></div>
	{/if}
{/block}