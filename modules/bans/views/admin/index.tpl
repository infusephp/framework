{extends file='module.tpl'}
{block name='content'}
	<table id="dataTable" cellpadding="0" cellspacing="0" border="0"></table>
	<script type="text/javascript">
	var edit_permission = {Permissions::getPermission('edit_bans')};
	var delete_permission = {Permissions::getPermission('delete_bans')};
    var sorting = [[0,'asc'],[1,'asc']]; // set sorting column and direction

	var data_headers = [
						{ name : 'type', title : 'Type', type: 5, select_keys: [1,2,3], select_values: ['IP','User','E-mail Address'], nowrap : true },
						{ name : 'value', title : 'Value', type: 2 },
						{ name : 'reason', title : 'Reason', type: 2 }
	];
			
	$(document).ready(dt_loadData);
	
	</script>
{/block}