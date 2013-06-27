{extends file='admin/parent.tpl'}
{block name=header}
<script type="text/javascript">
	var select_enabled = {if $smarty.request.find}1{else}0{/if};
	var modelInfo = {$modelJSON};
	var module = '{$moduleName}';
</script>
{/block}
{block name=main}

	<ul class="nav nav-pills">
		<li class="{if !$schema}active{/if}">
			<a href="/4dm1n/{$moduleName}#/">{$modelNamePlural}</a>
		</li>
		<li class="{if $schema}active{/if}">
			<a href="/4dm1n/{$moduleName}/schema">Database Schema</a>
		</li>
	</ul>

	{block name=content}{/block}
	
	{if $schema}
		
		<p class="lead">
			Infuse Framework will suggest a database schema for your model based on the <a href="https://github.com/jaredtking/infuse/wiki/Models">specified
			properties</a>. On the left is the current schema in your database and on the right is the proposed schema. Click <strong>Update</strong> to
			accept the suggested schema.
		</p>

		<p class="alert alert-info">You are responsible for manually renaming or dropping columns. This is to ensure no data is accidentally deleted.</p>

		{if $success}
			<p class="alert alert-success">The schema was updated!</p>
		{elseif $error}
			<p class="alert alert-error">{$error}</p>
		{/if}

		<div class="row-fluid">
			<div class="span6">
				<h3>Current Schema</h3>

				{if !$currentSchema}
					<pre>{$tablename} does not exist in the database</pre>
				{else}
					<pre>{$currentSchema}</pre>
				{/if}

			</div>
			<div class="span6">
				<h3>Suggested Schema</h3>

				{if !$suggestedSchema}
					<pre>No suggestions</pre>
				{else}
					<pre>{$suggestedSchema}</pre>
				{/if}

				<p>
					<a href="/4dm1n/{$moduleName}/schema/update" class="btn btn-large btn-success">&larr; Update</a>
				</p>

			</div>
		</div>
	{else}
		<div ng-view></div>
	{/if}
{/block}