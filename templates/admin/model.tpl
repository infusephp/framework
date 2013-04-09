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
	<p>
		<button href="#" disabled="disabled" class="disabled new-item btn btn-success btn-large"><i class="icon-plus icon-white"></i> New Item</button>
	</p>
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
{/block}