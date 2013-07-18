{extends file='parent.tpl'}
{block name=content}
<h1>Add API Client</h1>
{if $success}
	<div class="alert alert-success">The client was added successfully.</div>
{else}
	<div class="alert alert-error">There was an error adding the client.</div>
{/if}
<form method="post" action="/oauth/addClient">
	<p>
		<label for="client_name">App Name:</label>
		<input type="text" name="name" id="client_name" />
	</p>
	<p>
		<label>Description</label>
		<textarea name="description"></textarea>
	</p>
	<p>
		<label for="client_id">Client ID:</label>
		<input type="text" name="client_id" id="client_id" value="{$clientId}" class="input-small" />
	</p>
	<p>
		<label for="client_secret">Client Secret (password/key):</label>
		<textarea name="client_secret" id="client_secret">{$clientSecret}</textarea>
	</p>
	<p>
		<label for="redirect_uri">Redirect URI:</label>
		<input type="text" name="redirect_uri" id="redirect_uri" class="input-xlarge" />
	</p>
	<input type="submit" name="Submit" value="Submit" class="btn btn-primary" />
</form>
{/block}