{extends file='admin/module.tpl'}
{block name='content'}
{if $success}<div class="alert alert-success">The restore was successful.</div>{/if}
{if $optimizeSuccess}<div class="alert alert-success">Database optimization complete.</div>{/if}
<div class="row-fluid">
	<div class="span4">
		<h3>Optimize Database</h3>
		<p>
			<a class="btn btn-large btn-primary" href="/4dm1n/backup/optimize">Optimize</a>
		</p>
	</div>
{if $canBackupSite}
	<div class="span4">
		<h3>Backup Database</h3>
		<p>
			<a class="btn btn-large btn-success" href="/4dm1n/backup/download">Backup</a>
		</p>
	</div>
{/if}
{if $canRestoreSite}
	<div class="span4">
		<h3>Restore Database</h3>
		<form action="/4dm1n/backup/restore" method="POST" enctype="multipart/form-data" name="restore" onsubmit="return submitForm()">
			{$failure}
			<input type="hidden" name="MAX_FILE_SIZE" value="10485760">
			Choose a file to restore from: <br><input name="uploadedfile" type="file">
			<p><input class="btn btn-danger" type="submit" name="Submit" value="Upload File"></p>
		</form>
	</div>
	<script>
	function submitForm() {
		if (confirm('You may be overwriting data if you restore from this file. Are you sure you want to do this?')) {
			document.restore.submit()
			return true;
		} else {
			return false;
		}
	}
	</script>
{/if}
</div>
{/block}