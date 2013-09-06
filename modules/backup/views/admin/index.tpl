{extends file='admin/module.tpl'}
{block name='content'}
{if isset($success)}<div class="alert alert-success">The restore was successful.</div>{/if}
{if isset($optimizeSuccess)}<div class="alert alert-success">Database optimization complete.</div>{/if}
<div class="row">
	<div class="col-md-4">
		<h3>Optimize Database</h3>
		<p>
			<a class="btn btn-large btn-primary" href="/4dm1n/backup/optimize">Optimize</a>
		</p>
	</div>
{if $canBackupSite}
	<div class="col-md-4">
		<h3>Backup Database</h3>
		<p>
			<a class="btn btn-large btn-success" href="/4dm1n/backup/download">Backup</a>
		</p>
	</div>
{/if}
</div>
{/block}