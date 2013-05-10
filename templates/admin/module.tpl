{extends file='admin/parent.tpl'}
{block name="main"}
	<h1>
		{$title}
		{if $moduleDescription}
			<small>
				<a href="#module-help" data-toggle="collapse"><i class="icon-question-sign"></i></a>
			</small>
		{/if}
	</h1>
	<div id="module-help" class="collapse">
		{$moduleDescription}
	</div>
	{block name="content"}{/block}
{/block}